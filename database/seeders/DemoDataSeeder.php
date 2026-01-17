<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Restaurant;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class DemoDataSeeder extends Seeder
{
    public function run()
    {
        $restaurant = Restaurant::where('name', 'Pizza Palace')->first();
        $user = User::where('email', 'restaurant@test.com')->first();
        $customer = User::where('email', 'customer@test.com')->first();

        if (!$restaurant || !$user || !$customer) {
            $this->command->info('Restaurant, User or Customer not found. Skipping demo data seeding.');
            return;
        }

        // Create User Address
        $addressId = DB::table('user_addresses')->insertGetId([
            'user_id' => $customer->id,
            'label' => 'Home',
            'address_line1' => '123 Demo St',
            'city' => 'New York',
            'state' => 'NY',
            'postal_code' => '10001',
            'country' => 'USA',
            'is_default' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // --- SEED ORDERS ---
        
        // 1. Pending Order
        $order1 = Order::create([
            'order_number' => 'ORD-' . strtoupper(Str::random(8)),
            'user_id' => $customer->id,
            'restaurant_id' => $restaurant->id,
            'delivery_address_id' => $addressId, // Correct FK
            'subtotal' => 25.98,
            'delivery_fee' => 2.50,
            'tax' => 2.00,
            'total' => 30.48,
            'status' => 'pending',
            'payment_status' => 'paid',
            'payment_method' => 'card',
            'created_at' => now()->subMinutes(10),
        ]);
        
        $items = $restaurant->menuItems()->take(2)->get();
        if ($items->count() > 0) {
            foreach($items as $item) {
                 OrderItem::create([
                    'order_id' => $order1->id,
                    'menu_item_id' => $item->id,
                    'name' => $item->name,
                    'quantity' => 1,
                    'unit_price' => $item->price,
                    'total_price' => $item->price,
                 ]);
            }
        }

        // 2. Preparing Order
        $order2 = Order::create([
             'order_number' => 'ORD-' . strtoupper(Str::random(8)),
             'user_id' => $customer->id,
             'restaurant_id' => $restaurant->id,
             'delivery_address_id' => $addressId,
             'subtotal' => 45.00,
             'delivery_fee' => 0.00,
             'tax' => 4.50,
             'total' => 49.50,
             'status' => 'preparing',
             'payment_status' => 'paid',
             'payment_method' => 'upi',
             'created_at' => now()->subMinutes(45),
        ]);
        
        if ($items->count() > 0) {
            OrderItem::create([
                'order_id' => $order2->id,
                'menu_item_id' => $items[0]->id,
                'name' => $items[0]->name,
                'quantity' => 2,
                'unit_price' => $items[0]->price,
                'total_price' => $items[0]->price * 2,
            ]);
        }

        // 3. Ready for Pickup Order
        Order::create([
             'order_number' => 'ORD-' . strtoupper(Str::random(8)),
             'user_id' => $customer->id,
             'restaurant_id' => $restaurant->id,
             'delivery_address_id' => $addressId,
             'subtotal' => 12.99,
             'delivery_fee' => 2.00,
             'tax' => 1.30,
             'total' => 16.29,
             'status' => 'ready_for_pickup',
             'payment_status' => 'paid',
             'payment_method' => 'cash',
             'created_at' => now()->subHours(1),
        ]);

        // 4. Delivered Order
        $order3 = Order::create([
             'order_number' => 'ORD-' . strtoupper(Str::random(8)),
             'user_id' => $customer->id,
             'restaurant_id' => $restaurant->id,
             'delivery_address_id' => $addressId,
             'subtotal' => 20.00,
             'delivery_fee' => 2.00,
             'tax' => 2.00,
             'total' => 24.00,
             'status' => 'delivered',
             'payment_status' => 'paid',
             'payment_method' => 'card',
             'created_at' => now()->subHours(3),
        ]);

        // --- SEED NOTIFICATIONS ---
        
        // Custom notifications table structure: user_id, title, body, type, data, is_read, read_at
        
        $notifications = [
            [
                'user_id' => $user->id,
                'title' => 'New Order Received',
                'body' => 'New order #' . $order1->order_number . ' requires your attention.',
                'type' => 'order',
                'data' => json_encode(['order_id' => $order1->id]),
                'is_read' => false,
                'read_at' => null,
                'created_at' => now()->subMinutes(10),
            ],
            [
                'user_id' => $user->id,
                'title' => 'Order #ORD-XYZ Preparing',
                'body' => 'Order #' . $order2->order_number . ' is now in preparing state.',
                'type' => 'order',
                'data' => json_encode(['order_id' => $order2->id]),
                'is_read' => true,
                'read_at' => now()->subMinutes(30),
                'created_at' => now()->subMinutes(45),
            ],
            [
                'user_id' => $user->id,
                'title' => 'Welcome on board!',
                'body' => 'Welcome to the partner app. Complete your profile to get more visibility.',
                'type' => 'system',
                'data' => null,
                'is_read' => false, // Unread
                'read_at' => null,
                'created_at' => now()->subDay(),
            ]
        ];

        DB::table('notifications')->insert($notifications);
        
        $this->command->info('Demo data seeded successfully!');
    }
}
