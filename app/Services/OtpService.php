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
        // Use Vercel email service
        $vercelUrl = 'https://nemesis-letter-backend.vercel.app/api/mail/send-otp';
        $apiKey = 'nemesis_internal_secret_2024';
        
        try {
            $response = \Illuminate\Support\Facades\Http::post($vercelUrl, [
                'email' => $identifier,
                'code' => $otp,
                'apiKey' => $apiKey,
            ]);
            
            if ($response->failed()) {
                \Log::error('OTP Email Failed: ' . $response->body());
                throw new \Exception('Failed to send OTP email');
            }
        } catch (\Exception $e) {
            \Log::error('OTP Service Error: ' . $e->getMessage());
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
