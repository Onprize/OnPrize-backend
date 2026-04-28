<?php

namespace App\Services;

use App\Models\Otp;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendOtpMail;

class OtpService
{
    public function generate($identifier, $type = 'login')
    {
        // Delete old OTPs (important)
        Otp::where('identifier', $identifier)
            ->where('type', $type)
            ->delete();

        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = now()->addMinutes(15); // Increased from 10 to 15 minutes

        Otp::create([
            'identifier' => $identifier,
            'otp' => $otp,
            'type' => $type,
            'expires_at' => $expiresAt,
        ]);

        \Log::info('OTP generated', [
            'identifier' => $identifier,
            'type' => $type,
            'otp' => $otp,
            'expires_at' => $expiresAt,
            'server_time' => now(),
        ]);

        return $otp;
    }

   public function send($identifier, $otp)
{
    try {
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            Mail::to($identifier)->send(new SendOtpMail($otp));
        } else {
            \Log::warning('OTP not sent. Invalid email: ' . $identifier);
        }
    } catch (\Exception $e) {
        \Log::error('OTP Email Failed: ' . $e->getMessage());
        throw $e;
    }
}

    public function verify($identifier, $otp, $type = 'login')
    {
        \Log::info('OTP verify attempt', [
            'identifier' => $identifier,
            'otp' => $otp,
            'type' => $type,
            'server_time' => now(),
        ]);

        $otpRecord = Otp::where('identifier', $identifier)
            ->where('otp', $otp)
            ->where('type', $type)
            ->whereNull('verified_at')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (!$otpRecord) {
            // Log why verification failed
            $existingOtp = Otp::where('identifier', $identifier)
                ->where('type', $type)
                ->latest()
                ->first();

            \Log::error('OTP verification failed', [
                'identifier' => $identifier,
                'otp' => $otp,
                'type' => $type,
                'server_time' => now(),
                'existing_otp' => $existingOtp ? [
                    'otp' => $existingOtp->otp,
                    'expires_at' => $existingOtp->expires_at,
                    'verified_at' => $existingOtp->verified_at,
                    'is_expired' => $existingOtp->expires_at < now(),
                ] : 'no_otp_found',
            ]);
            return false;
        }

        $otpRecord->update(['verified_at' => now()]);

        \Log::info('OTP verified successfully', [
            'identifier' => $identifier,
            'otp' => $otp,
            'type' => $type,
        ]);

        return true;
    }

    public function cleanupExpired()
    {
        Otp::where('expires_at', '<', now()->subDay())->delete();
    }
}