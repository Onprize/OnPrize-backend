<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@fooddelivery.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Test Customer',
            'email' => 'customer@test.com',
            'password' => Hash::make('password'),
            'role' => 'customer',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Restaurant Owner',
            'email' => 'restaurant@test.com',
            'password' => Hash::make('password'),
            'role' => 'restaurant_owner',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Delivery Partner',
            'email' => 'delivery@test.com',
            'password' => Hash::make('password'),
            'role' => 'delivery_partner',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $this->call([
            RestaurantCategorySeeder::class,
            RestaurantSeeder::class,
            MenuSeeder::class,
            DemoDataSeeder::class,
        ]);
    }
}
