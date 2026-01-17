<?php

namespace App\Services;

use App\Models\Otp;
use Illuminate\Support\Facades\Mail;

class OtpService
{
    public function generate($identifier, $type = 'login')
    {
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
        Mail::raw("Your OTP is: $otp\n\nValid for 10 minutes.", function ($message) use ($identifier) {
            $message->to($identifier)
                ->subject('Your OTP Code');
        });
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
