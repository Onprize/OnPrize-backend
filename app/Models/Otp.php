<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Otp extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'identifier',
        'otp',
        'type',
        'expires_at',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'verified_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function isExpired()
    {
        return $this->expires_at < now();
    }

    public function isVerified()
    {
        return !is_null($this->verified_at);
    }
}
