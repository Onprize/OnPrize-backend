<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Restaurant extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'owner_id',
        'name',
        'slug',
        'description',
        'logo',
        'banner_image',
        'email',
        'phone',
        'address_line1',
        'address_line2',
        'city',
        'state',
        'postal_code',
        'latitude',
        'longitude',
        'cuisine_types',
        'avg_rating',
        'total_reviews',
        'delivery_time',
        'delivery_fee',
        'min_order_amount',
        'is_open',
        'is_accepting_orders',
        'status',
        'opening_time',
        'closing_time',
        'commission_rate',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'cuisine_types' => 'array',
            'avg_rating' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'min_order_amount' => 'decimal:2',
            'commission_rate' => 'decimal:2',
            'is_open' => 'boolean',
            'is_accepting_orders' => 'boolean',
        ];
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function categories()
    {
        return $this->belongsToMany(RestaurantCategory::class, 'restaurant_category_mappings', 'restaurant_id', 'category_id');
    }

    public function menuCategories()
    {
        return $this->hasMany(MenuCategory::class);
    }

    public function menuItems()
    {
        return $this->hasMany(MenuItem::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function reviews()
    {
        return $this->morphMany(Review::class, 'reviewable');
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function isAcceptingOrders()
    {
        return $this->is_open && $this->is_accepting_orders && $this->isApproved();
    }
}
