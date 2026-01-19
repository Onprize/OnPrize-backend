<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RestaurantOwnerProfile extends Model
{
    protected $fillable = [
        'user_id',
        'aadhaar_number',
        'pan_number',
        'gst_number',
        'fssai_license',
        'business_address',
        'business_city',
        'business_state',
        'business_pincode',
        'bank_account_number',
        'bank_ifsc',
        'bank_account_holder_name',
        'bank_name',
        'kyc_status',
        'kyc_rejection_reason',
        'kyc_verified_at',
        'aadhaar_front_image',
        'aadhaar_back_image',
        'pan_image',
        'fssai_certificate_image',
        'bank_passbook_image',
    ];

    protected function casts(): array
    {
        return [
            'kyc_verified_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns this profile
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if KYC is verified
     */
    public function isKycVerified(): bool
    {
        return $this->kyc_status === 'verified';
    }

    /**
     * Check if KYC is pending
     */
    public function isKycPending(): bool
    {
        return in_array($this->kyc_status, ['pending', 'under_review']);
    }

    /**
     * Mask Aadhaar number for display (show only last 4 digits)
     */
    public function getMaskedAadhaarAttribute(): ?string
    {
        if (!$this->aadhaar_number) return null;
        return 'XXXX-XXXX-' . substr($this->aadhaar_number, -4);
    }

    /**
     * Mask PAN for display
     */
    public function getMaskedPanAttribute(): ?string
    {
        if (!$this->pan_number) return null;
        return substr($this->pan_number, 0, 2) . 'XXXXX' . substr($this->pan_number, -2);
    }

    /**
     * Mask bank account for display
     */
    public function getMaskedBankAccountAttribute(): ?string
    {
        if (!$this->bank_account_number) return null;
        $length = strlen($this->bank_account_number);
        return str_repeat('X', $length - 4) . substr($this->bank_account_number, -4);
    }
}
