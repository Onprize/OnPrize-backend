<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryPartner extends Model
{
    protected $fillable = [
        'user_id',
        'vehicle_type',
        'vehicle_number',
        'license_number',
        'license_image',
        'vehicle_rc_image',
        'is_verified',
        'verification_status',
        'verified_at',
        'is_available',
        'current_latitude',
        'current_longitude',
        'last_location_update',
        'total_deliveries',
        'avg_rating',
        'total_earnings',
    ];

    protected function casts(): array
    {
        return [
            'is_verified' => 'boolean',
            'is_available' => 'boolean',
            'verified_at' => 'datetime',
            'last_location_update' => 'datetime',
            'current_latitude' => 'decimal:8',
            'current_longitude' => 'decimal:8',
            'avg_rating' => 'decimal:2',
            'total_earnings' => 'decimal:2',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function deliveryRequests()
    {
        return $this->hasMany(DeliveryRequest::class);
    }

    public function deliveries()
    {
        return $this->hasMany(Order::class, 'delivery_partner_id');
    }

    public function reviews()
    {
        return $this->morphMany(Review::class, 'reviewable');
    }

    public function isVerified()
    {
        return $this->verification_status === 'verified';
    }

    public function isAvailable()
    {
        return $this->is_available && $this->isVerified();
    }
}
