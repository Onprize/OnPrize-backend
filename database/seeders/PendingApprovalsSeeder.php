<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Restaurant;
use App\Models\DeliveryPartner;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PendingApprovalsSeeder extends Seeder
{
    /**
     * Run the database seeds for pending restaurants and delivery partners
     */
    public function run(): void
    {
        // Create Pending Restaurants with Owners
        $this->createPendingRestaurants();

        // Create Pending Delivery Partners
        $this->createPendingDeliveryPartners();
    }

    private function createPendingRestaurants()
    {
        $pendingRestaurants = [
            [
                'name' => 'Spice Garden',
                'cuisine_types' => ['Indian', 'North Indian', 'Mughlai'],
                'description' => 'Authentic North Indian cuisine with rich flavors and traditional recipes',
                'city' => 'Mumbai',
                'state' => 'Maharashtra',
                'postal_code' => '400001',
            ],
            [
                'name' => 'Pizza Paradise',
                'cuisine_types' => ['Italian', 'Pizza', 'Pasta'],
                'description' => 'Wood-fired pizzas and fresh Italian pasta',
                'city' => 'Delhi',
                'state' => 'Delhi',
                'postal_code' => '110001',
            ],
            [
                'name' => 'Burger Hub',
                'cuisine_types' => ['American', 'Fast Food', 'Burgers'],
                'description' => 'Gourmet burgers with premium ingredients',
                'city' => 'Bangalore',
                'state' => 'Karnataka',
                'postal_code' => '560001',
            ],
            [
                'name' => 'Dosa Corner',
                'cuisine_types' => ['South Indian', 'Breakfast', 'Vegetarian'],
                'description' => 'Crispy dosas and authentic South Indian breakfast',
                'city' => 'Chennai',
                'state' => 'Tamil Nadu',
                'postal_code' => '600001',
            ],
            [
                'name' => 'Biryani House',
                'cuisine_types' => ['Indian', 'Biryani', 'Hyderabadi'],
                'description' => 'Authentic Hyderabadi biryani and Mughlai cuisine',
                'city' => 'Hyderabad',
                'state' => 'Telangana',
                'postal_code' => '500001',
            ],
        ];

        foreach ($pendingRestaurants as $index => $restaurantData) {
            // Create restaurant owner
            $owner = User::create([
                'name' => $restaurantData['name'] . ' Owner',
                'email' => Str::slug($restaurantData['name']) . '@restaurant.com',
                'phone' => '98765' . str_pad($index + 10, 5, '0', STR_PAD_LEFT),
                'password' => Hash::make('password'),
                'role' => 'restaurant_owner',
                'status' => 'active',
                'email_verified_at' => now(),
            ]);

            // Create restaurant with pending status
            $restaurant = Restaurant::create([
                'owner_id' => $owner->id,
                'name' => $restaurantData['name'],
                'slug' => Str::slug($restaurantData['name']),
                'description' => $restaurantData['description'],
                'email' => Str::slug($restaurantData['name']) . '@restaurant.com',
                'phone' => '91' . rand(7000000000, 9999999999),
                'address_line1' => rand(1, 999) . ' MG Road',
                'city' => $restaurantData['city'],
                'state' => $restaurantData['state'],
                'postal_code' => $restaurantData['postal_code'],
                'latitude' => 12.9716 + (rand(-1000, 1000) / 1000),
                'longitude' => 77.5946 + (rand(-1000, 1000) / 1000),
                'cuisine_types' => json_encode($restaurantData['cuisine_types']),
                'avg_rating' => 0,
                'total_reviews' => 0,
                'delivery_time' => rand(20, 45) . '-' . rand(45, 60) . ' mins',
                'delivery_fee' => rand(20, 50),
                'min_order_amount' => rand(100, 300),
                'is_open' => true,
                'is_accepting_orders' => false,
                'status' => 'pending', // PENDING STATUS
                'opening_time' => '09:00:00',
                'closing_time' => '23:00:00',
                'commission_rate' => 15.00,
            ]);

            // Add some basic menu items for the restaurant
            $category = MenuCategory::create([
                'restaurant_id' => $restaurant->id,
                'name' => 'Popular Items',
                'description' => 'Our most loved dishes',
                'sort_order' => 1,
                'is_active' => true,
            ]);

            // Add 3 menu items
            for ($i = 1; $i <= 3; $i++) {
                MenuItem::create([
                    'restaurant_id' => $restaurant->id,
                    'category_id' => $category->id,
                    'name' => 'Special Item ' . $i,
                    'slug' => Str::slug($restaurantData['name'] . '-item-' . $i),
                    'description' => 'Delicious and mouth-watering dish ' . $i,
                    'price' => rand(150, 500),
                    'is_veg' => rand(0, 1) == 1,
                    'is_available' => true,
                    'is_featured' => $i == 1,
                    'preparation_time' => rand(15, 30) . ' mins',
                    'avg_rating' => 0,
                    'total_reviews' => 0,
                ]);
            }

            echo "✅ Created pending restaurant: {$restaurantData['name']}\n";
        }
    }

    private function createPendingDeliveryPartners()
    {
        $vehicleTypes = ['bike', 'scooter', 'bicycle', 'car'];
        $cities = [
            ['city' => 'Mumbai', 'state' => 'Maharashtra', 'postal' => '400001'],
            ['city' => 'Delhi', 'state' => 'Delhi', 'postal' => '110001'],
            ['city' => 'Bangalore', 'state' => 'Karnataka', 'postal' => '560001'],
            ['city' => 'Chennai', 'state' => 'Tamil Nadu', 'postal' => '600001'],
            ['city' => 'Hyderabad', 'state' => 'Telangana', 'postal' => '500001'],
        ];

        $names = [
            'Rajesh Kumar',
            'Suresh Patel',
            'Amit Sharma',
            'Vijay Singh',
            'Rahul Verma',
            'Sandeep Yadav',
            'Manoj Reddy',
            'Arun Nair',
        ];

        foreach ($names as $index => $name) {
            // Create user for delivery partner
            $user = User::create([
                'name' => $name,
                'email' => Str::slug($name) . '@delivery.com',
                'phone' => '98' . rand(10000000, 99999999),
                'password' => Hash::make('password'),
                'role' => 'delivery_partner',
                'status' => 'active',
                'email_verified_at' => now(),
            ]);

            $location = $cities[$index % count($cities)];
            $vehicleType = $vehicleTypes[$index % count($vehicleTypes)];

            // Create delivery partner with pending verification
            DeliveryPartner::create([
                'user_id' => $user->id,
                'vehicle_type' => $vehicleType,
                'vehicle_number' => strtoupper(substr($location['state'], 0, 2)) . '-' . rand(10, 99) . '-' . strtoupper(chr(rand(65, 90))) . chr(rand(65, 90)) . '-' . rand(1000, 9999),
                'license_number' => 'DL-' . rand(1000000000, 9999999999),
                'is_verified' => false,
                'verification_status' => 'pending', // PENDING STATUS
                'is_available' => false,
                'current_latitude' => 12.9716 + (rand(-1000, 1000) / 1000),
                'current_longitude' => 77.5946 + (rand(-1000, 1000) / 1000),
                'total_deliveries' => 0,
                'avg_rating' => 0,
                'total_earnings' => 0,
            ]);

            echo "✅ Created pending delivery partner: {$name} ({$vehicleType})\n";
        }
    }
}
