<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\Notification;
use App\Models\User;
use App\Models\Restaurant;
use App\Models\UserAddress;
use Carbon\Carbon;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        $deliveryPartnerId = 4; // delivery@test.com
        $customerId = 2;
        $restaurantId = 1;
        $addressId = 1;

        // Create some delivered orders for today
        for ($i = 0; $i < 5; $i++) {
            Order::create([
                'order_number' => 'ORD-' . strtoupper(bin2hex(random_bytes(4))),
                'user_id' => $customerId,
                'restaurant_id' => $restaurantId,
                'delivery_partner_id' => $deliveryPartnerId,
                'delivery_address_id' => $addressId,
                'subtotal' => rand(200, 1000),
                'delivery_fee' => rand(30, 60),
                'tax' => 18,
                'total' => rand(300, 1200),
                'status' => 'delivered',
                'payment_method' => 'cash',
                'payment_status' => 'paid',
                'delivered_at' => Carbon::now()->subHours(rand(1, 8)),
                'created_at' => Carbon::now()->subHours(8),
            ]);
        }

        // Create some pending requests
        for ($i = 0; $i < 3; $i++) {
            $order = Order::create([
                'order_number' => 'REQ-' . strtoupper(bin2hex(random_bytes(4))),
                'user_id' => $customerId,
                'restaurant_id' => $restaurantId,
                'delivery_address_id' => $addressId,
                'subtotal' => rand(200, 1000),
                'delivery_fee' => rand(30, 60),
                'tax' => 18,
                'total' => rand(300, 1200),
                'status' => 'ready_for_pickup',
                'payment_method' => 'upi',
                'payment_status' => 'paid',
                'ready_at' => Carbon::now()->subMinutes(rand(5, 30)),
            ]);

            \App\Models\DeliveryRequest::create([
                'order_id' => $order->id,
                'delivery_partner_id' => $deliveryPartnerId,
                'status' => 'pending',
                'expires_at' => Carbon::now()->addMinutes(10),
            ]);
        }

        // Add some notifications
        Notification::create([
            'user_id' => $deliveryPartnerId,
            'title' => 'New Order Request',
            'body' => 'You have a new delivery request from Pizza Hut.',
            'type' => 'order',
            'data' => json_encode(['order_id' => 1]),
            'is_read' => false,
            'created_at' => Carbon::now()->subMinutes(5),
        ]);

        Notification::create([
            'user_id' => $deliveryPartnerId,
            'title' => 'Payment Received',
            'body' => 'Earnings for Order #ORD-1234 have been added to your wallet.',
            'type' => 'delivery',
            'is_read' => true,
            'created_at' => Carbon::now()->subHours(2),
        ]);

        Notification::create([
            'user_id' => $deliveryPartnerId,
            'title' => 'System Update',
            'body' => 'Welcome to On Prize Delivery Partner app! Start earning today.',
            'type' => 'system',
            'is_read' => false,
            'created_at' => Carbon::now()->subDays(1),
        ]);
    }
}
