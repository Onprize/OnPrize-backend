<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Support\Str;

class RestaurantSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::where('email', 'restaurant@test.com')->first();

        $restaurants = [
            [
                'owner_id' => $owner->id,
                'name' => 'Pizza Palace',
                'slug' => Str::slug('Pizza Palace') . '-' . uniqid(),
                'description' => 'Best pizzas in town with authentic Italian taste',
                'email' => 'contact@pizzapalace.com',
                'phone' => '+1234567890',
                'address_line1' => '123 Main Street',
                'city' => 'New York',
                'state' => 'NY',
                'postal_code' => '10001',
                'latitude' => 40.7128,
                'longitude' => -74.0060,
                'cuisine_types' => ['Italian', 'Pizza'],
                'delivery_fee' => 2.50,
                'min_order_amount' => 10.00,
                'delivery_time' => '30-40 mins',
                'status' => 'approved',
                'is_open' => true,
                'is_accepting_orders' => true,
            ],
            [
                'owner_id' => $owner->id,
                'name' => 'Burger Hub',
                'slug' => Str::slug('Burger Hub') . '-' . uniqid(),
                'description' => 'Juicy burgers and crispy fries',
                'email' => 'info@burgerhub.com',
                'phone' => '+1234567891',
                'address_line1' => '456 Broadway',
                'city' => 'New York',
                'state' => 'NY',
                'postal_code' => '10002',
                'latitude' => 40.7589,
                'longitude' => -73.9851,
                'cuisine_types' => ['Fast Food', 'Burger'],
                'delivery_fee' => 3.00,
                'min_order_amount' => 12.00,
                'delivery_time' => '25-35 mins',
                'status' => 'approved',
                'is_open' => true,
                'is_accepting_orders' => true,
            ],
            [
                'owner_id' => $owner->id,
                'name' => 'Spice Garden',
                'slug' => Str::slug('Spice Garden') . '-' . uniqid(),
                'description' => 'Authentic Indian cuisine with rich flavors',
                'email' => 'hello@spicegarden.com',
                'phone' => '+1234567892',
                'address_line1' => '789 Park Avenue',
                'city' => 'New York',
                'state' => 'NY',
                'postal_code' => '10003',
                'latitude' => 40.7489,
                'longitude' => -73.9680,
                'cuisine_types' => ['Indian', 'Healthy'],
                'delivery_fee' => 2.00,
                'min_order_amount' => 15.00,
                'delivery_time' => '35-45 mins',
                'status' => 'approved',
                'is_open' => true,
                'is_accepting_orders' => true,
            ],
        ];

        foreach ($restaurants as $restaurant) {
            Restaurant::create($restaurant);
        }
    }
}
