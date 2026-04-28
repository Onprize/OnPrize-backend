<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    protected $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    public function register(Request $request)
    {
        // Sanitize email: trim whitespace and convert to lowercase
        $request->merge([
            'email' => trim(strtolower($request->email)),
        ]);

        // Rate limiting: prevent rapid registration attempts
        $lastRegistration = \App\Models\User::where('email', $request->email)
            ->where('created_at', '>', now()->subMinute())
            ->first();

        if ($lastRegistration) {
            return response()->json([
                'message' => 'Please wait before registering again',
                'retry_after' => 60 - now()->diffInSeconds($lastRegistration->created_at),
            ], 429);
        }

        // Rate limiting: prevent rapid OTP generation for same email
        $lastOtp = \App\Models\Otp::where('identifier', $request->email)
            ->where('type', 'registration')
            ->where('created_at', '>', now()->subMinute())
            ->first();

        if ($lastOtp) {
            return response()->json([
                'message' => 'Please wait before requesting another OTP',
                'retry_after' => 60 - now()->diffInSeconds($lastOtp->created_at),
            ], 429);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'nullable|string',
            'password' => 'required|string|min:8',
            'role' => 'required|in:customer,restaurant_owner,delivery_partner',

            // KYC fields
            'aadhaar' => 'required_if:role,restaurant_owner,delivery_partner|string|size:12',
            'pan' => 'required_if:role,restaurant_owner,delivery_partner|string|size:10',

            // FIXED: only one address validation
            'address' => 'required_if:role,restaurant_owner,delivery_partner|string|min:10',

            // delivery partner fields
            'vehicle_type' => 'required_if:role,delivery_partner|in:bike,scooter,bicycle,car',
            'vehicle_number' => 'required_if:role,delivery_partner|string',
            'license_number' => 'required_if:role,delivery_partner|string',
            'bank_name' => 'required_if:role,delivery_partner|string',
            'account_number' => 'required_if:role,delivery_partner|string',
            'ifsc_code' => 'required_if:role,delivery_partner|string',

            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        // Delete unverified user with same email if exists
        $existingUser = User::where('email', $request->email)->whereNull('email_verified_at')->first();
        if ($existingUser) {
            $existingUser->delete();
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'status' => 'active',
        ]);

        // Customer address
        if ($request->role === 'customer' && $request->latitude && $request->longitude) {
            $user->addresses()->create([
                'label' => 'Home',
                'address_line1' => $request->address ?? 'Current Location',
                'city' => 'Unknown',
                'state' => 'Unknown',
                'postal_code' => '000000',
                'country' => 'India',
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'is_default' => true,
            ]);
        }

        // Restaurant owner profile
        if ($request->role === 'restaurant_owner' && ($request->aadhaar || $request->pan || $request->address)) {
            $user->restaurantOwnerProfile()->create([
                'aadhaar_number' => $request->aadhaar,
                'pan_number' => strtoupper($request->pan),
                'business_address' => $request->address,
                'kyc_status' => 'pending',
            ]);
        }

        // Delivery partner profile
        if ($request->role === 'delivery_partner') {
            $user->deliveryPartner()->create([
                'aadhaar_number' => $request->aadhaar,
                'pan_number' => strtoupper($request->pan),
                'address' => $request->address,
                'vehicle_type' => $request->vehicle_type,
                'vehicle_number' => $request->vehicle_number,
                'license_number' => $request->license_number,
                'bank_name' => $request->bank_name,
                'account_number' => $request->account_number,
                'ifsc_code' => strtoupper($request->ifsc_code),
                'current_latitude' => $request->latitude,
                'current_longitude' => $request->longitude,
                'last_location_update' => now(),
                'verification_status' => 'pending',
                'is_verified' => false,
                'is_available' => false,
            ]);
        }

        // FIXED: send OTP to identifier (email or phone)
        $identifier = $user->email ?? $user->phone;

        $otp = $this->otpService->generate($identifier, 'registration');
        $this->otpService->send($identifier, $otp);

        return response()->json([
            'message' => 'Registration successful. OTP sent.',
            'user_id' => $user->id,
        ], 201);
    }

    public function sendOtp(Request $request)
    {
        // Sanitize identifier if it's an email
        if (filter_var($request->identifier, FILTER_VALIDATE_EMAIL)) {
            $request->merge([
                'identifier' => trim(strtolower($request->identifier)),
            ]);
        }

        $request->validate([
            'identifier' => 'required|string',
            'type' => 'required|in:registration,login,password_reset',
        ]);

        // Rate limiting: prevent sending OTP more than once per minute
        // Check BEFORE generate() deletes old OTPs
        $lastOtp = \App\Models\Otp::where('identifier', $request->identifier)
            ->where('type', $request->type)
            ->where('created_at', '>', now()->subMinute())
            ->first();

        if ($lastOtp) {
            $retryAfter = 60 - now()->diffInSeconds($lastOtp->created_at);
            \Log::warning('OTP rate limit exceeded', [
                'identifier' => $request->identifier,
                'type' => $request->type,
                'retry_after' => $retryAfter,
            ]);
            return response()->json([
                'message' => 'Please wait before requesting another OTP',
                'retry_after' => $retryAfter,
            ], 429);
        }

        $otp = $this->otpService->generate($request->identifier, $request->type);
        $this->otpService->send($request->identifier, $otp);

        \Log::info('OTP sent', [
            'identifier' => $request->identifier,
            'type' => $request->type,
            'otp' => $otp,
            'expires_at' => now()->addMinutes(15),
        ]);

        return response()->json([
            'message' => 'OTP sent successfully',
        ]);
    }

    public function verifyOtp(Request $request)
    {
        // Sanitize identifier if it's an email
        if (filter_var($request->identifier, FILTER_VALIDATE_EMAIL)) {
            $request->merge([
                'identifier' => trim(strtolower($request->identifier)),
            ]);
        }

        $request->validate([
            'identifier' => 'required|string',
            'otp' => 'required|string|size:6',
            'type' => 'required|in:registration,login,password_reset',
        ]);

        \Log::info('OTP verification attempt', [
            'identifier' => $request->identifier,
            'otp' => $request->otp,
            'type' => $request->type,
        ]);

        $verified = $this->otpService->verify(
            $request->identifier,
            $request->otp,
            $request->type
        );

        if (!$verified) {
            \Log::error('OTP verification failed', [
                'identifier' => $request->identifier,
                'otp' => $request->otp,
                'type' => $request->type,
            ]);
            throw ValidationException::withMessages([
                'otp' => ['Invalid or expired OTP'],
            ]);
        }

        $user = User::where('email', $request->identifier)
            ->orWhere('phone', $request->identifier)
            ->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'identifier' => ['User not found'],
            ]);
        }

        if ($request->type === 'registration') {
            $user->update(['email_verified_at' => now()]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'OTP verified successfully',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user->load(['deliveryPartner', 'addresses']),
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->identifier)
            ->orWhere('phone', $request->identifier)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'identifier' => ['The provided credentials are incorrect.'],
            ]);
        }

        if ($user->status !== 'active') {
            throw ValidationException::withMessages([
                'status' => ['Your account is not active'],
            ]);
        }

        $user->update(['last_login_at' => now()]);
        
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user->load(['deliveryPartner', 'addresses']),
        ]);
    }

    public function user(Request $request)
    {
        return response()->json(
            $request->user()->load(['deliveryPartner', 'addresses'])
        );
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }

    public function refresh(Request $request)
    {
        $request->user()->tokens()->delete();
        
        $token = $request->user()->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|unique:users,phone,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'profile_image' => 'nullable|url',
        ]);

        $updateData = $request->only(['name', 'email', 'phone', 'profile_image']);

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user,
        ]);
    }
}