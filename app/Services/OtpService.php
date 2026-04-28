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
        
        Otp::create([
            'identifier' => $identifier,
            'otp' => $otp,
            'type' => $type,
            'expires_at' => now()->addMinutes(10),
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
        $otpRecord = Otp::where('identifier', $identifier)
            ->where('otp', $otp)
            ->where('type', $type)
            ->whereNull('verified_at')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (!$otpRecord) {
            return false;
        }

        $otpRecord->update(['verified_at' => now()]);
        
        return true;
    }

    public function cleanupExpired()
    {
        Otp::where('expires_at', '<', now()->subDay())->delete();
    }
}