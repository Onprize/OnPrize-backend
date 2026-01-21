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
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|unique:users,phone',
            'password' => 'required|string|min:8',
            'role' => 'required|in:customer,restaurant_owner,delivery_partner',
            // KYC fields for restaurant owners
            'aadhaar' => 'required_if:role,restaurant_owner,delivery_partner|string|size:12',
            'pan' => 'required_if:role,restaurant_owner,delivery_partner|string|size:10',
            'address' => 'required_if:role,restaurant_owner,delivery_partner|string|min:10',
            // fields for delivery partners
            'vehicle_type' => 'required_if:role,delivery_partner|in:bike,scooter,bicycle,car',
            'vehicle_number' => 'required_if:role,delivery_partner|string',
            'license_number' => 'required_if:role,delivery_partner|string',
            'bank_name' => 'required_if:role,delivery_partner|string',
            'account_number' => 'required_if:role,delivery_partner|string',
            'ifsc_code' => 'required_if:role,delivery_partner|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'address' => 'nullable|string|min:3',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'status' => 'active',
        ]);

        // Create initial address for customer if location provided
        if ($request->role === 'customer' && $request->latitude && $request->longitude) {
            $user->addresses()->create([
                'label' => 'Home',
                'address_line1' => $request->address ?? 'Current Location',
                'city' => 'Unknown', // Ideally parsed from reverse geocode on frontend/backend
                'state' => 'Unknown',
                'postal_code' => '000000',
                'country' => 'India',
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'is_default' => true,
            ]);
        }

        // Create restaurant owner profile if applicable
        if ($request->role === 'restaurant_owner' && ($request->aadhaar || $request->pan || $request->address)) {
            $user->restaurantOwnerProfile()->create([
                'aadhaar_number' => $request->aadhaar,
                'pan_number' => strtoupper($request->pan),
                'business_address' => $request->address,
                'kyc_status' => 'pending',
            ]);
        }

        // Create delivery partner profile if applicable
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

        $otp = $this->otpService->generate($user->email, 'registration');
        $this->otpService->send($user->email, $otp);

        return response()->json([
            'message' => 'Registration successful. OTP sent to your email.',
            'user_id' => $user->id,
        ], 201);
    }

    public function sendOtp(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string',
            'type' => 'required|in:registration,login,password_reset',
        ]);

        $otp = $this->otpService->generate($request->identifier, $request->type);
        $this->otpService->send($request->identifier, $otp);

        return response()->json([
            'message' => 'OTP sent successfully',
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string',
            'otp' => 'required|string|size:6',
            'type' => 'required|in:registration,login,password_reset',
        ]);

        $verified = $this->otpService->verify(
            $request->identifier,
            $request->otp,
            $request->type
        );

        if (!$verified) {
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
        return response()->json($request->user()->load(['deliveryPartner', 'addresses']));
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
