<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemAddon;
use App\Models\OrderStatusHistory;
use App\Models\Restaurant;
use App\Models\MenuItem;
use Illuminate\Support\Str;

class OrderService
{
    protected $deliveryService;

    public function __construct(\App\Services\DeliveryService $deliveryService)
    {
        $this->deliveryService = $deliveryService;
    }

    public function createOrder($userId, $data)
    {
        return \DB::transaction(function () use ($userId, $data) {
            $restaurant = Restaurant::findOrFail($data['restaurant_id']);

        if (!$restaurant->isAcceptingOrders()) {
            throw new \Exception('Restaurant is not accepting orders');
        }

        $subtotal = 0;
        $orderItems = [];

        foreach ($data['items'] as $itemData) {
            $menuItem = MenuItem::findOrFail($itemData['menu_item_id']);
            
            if (!$menuItem->is_available) {
                throw new \Exception("Item {$menuItem->name} is not available");
            }

            $unitPrice = $menuItem->getEffectivePrice();
            
            if (isset($itemData['variant_id'])) {
                $variant = $menuItem->variants()->findOrFail($itemData['variant_id']);
                $unitPrice = $variant->price;
            }

            $itemTotal = $unitPrice * $itemData['quantity'];
            $subtotal += $itemTotal;

            $orderItems[] = [
                'item_data' => $itemData,
                'menu_item' => $menuItem,
                'unit_price' => $unitPrice,
                'total_price' => $itemTotal,
            ];
        }

        $deliveryFee = $restaurant->delivery_fee;
        $tax = $subtotal * 0.05;
        $discount = $data['discount'] ?? 0;
        $total = $subtotal + $deliveryFee + $tax - $discount;

        if ($total < $restaurant->min_order_amount) {
            throw new \Exception("Minimum order amount is {$restaurant->min_order_amount}");
        }

        $order = Order::create([
            'order_number' => 'ORD-' . strtoupper(Str::random(10)),
            'user_id' => $userId,
            'restaurant_id' => $data['restaurant_id'],
            'delivery_address_id' => $data['delivery_address_id'],
            'delivery_instructions' => $data['delivery_instructions'] ?? null,
            'subtotal' => $subtotal,
            'delivery_fee' => $deliveryFee,
            'tax' => $tax,
            'discount' => $discount,
            'total' => $total,
            'payment_method' => $data['payment_method'],
            'payment_status' => 'pending',
            'status' => 'pending',
        ]);

        foreach ($orderItems as $item) {
            $orderItem = OrderItem::create([
                'order_id' => $order->id,
                'menu_item_id' => $item['item_data']['menu_item_id'],
                'variant_id' => $item['item_data']['variant_id'] ?? null,
                'name' => $item['menu_item']->name,
                'quantity' => $item['item_data']['quantity'],
                'unit_price' => $item['unit_price'],
                'total_price' => $item['total_price'],
                'special_instructions' => $item['item_data']['special_instructions'] ?? null,
            ]);

            if (isset($item['item_data']['addons'])) {
                foreach ($item['item_data']['addons'] as $addonId) {
                    $addon = $item['menu_item']->addons()->findOrFail($addonId);
                    
                    OrderItemAddon::create([
                        'order_item_id' => $orderItem->id,
                        'addon_id' => $addonId,
                        'name' => $addon->name,
                        'price' => $addon->price,
                    ]);
                }
            }
        }

        $this->addStatusHistory($order->id, 'pending', 'Order placed by customer');

            return $order->load(['items.addons', 'restaurant', 'deliveryAddress']);
        });
    }

    public function updateOrderStatus($orderId, $status, $userId = null, $notes = null)
    {
        $order = Order::findOrFail($orderId);
        
        $validTransitions = $this->getValidStatusTransitions($order->status);
        
        if (!in_array($status, $validTransitions)) {
            throw new \Exception("Cannot transition from {$order->status} to {$status}");
        }

        $order->update(['status' => $status]);

        $timestampField = $this->getTimestampField($status);
        if ($timestampField) {
            $order->update([$timestampField => now()]);
        }

        $this->addStatusHistory($orderId, $status, $notes, $userId);

        // Auto-assign delivery partner when ready for pickup
        if ($status === 'ready_for_pickup') {
            try {
                $this->deliveryService->assignDeliveryPartner($orderId);
            } catch (\Exception $e) {
                \Log::warning("Failed to auto-assign delivery partner for order {$orderId}: " . $e->getMessage());
                // We don't stop the status update, just log the failure
                // Admin or Restaurant can retry later (feature to be added if needed)
            }
        }

        return $order;
    }

    public function cancelOrder($orderId, $userId)
    {
        $order = Order::findOrFail($orderId);

        if (!$order->canBeCancelled()) {
            throw new \Exception('Order cannot be cancelled at this stage');
        }

        $order->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        $this->addStatusHistory($orderId, 'cancelled', 'Cancelled by user', $userId);

        return $order;
    }

    protected function addStatusHistory($orderId, $status, $notes = null, $createdBy = null)
    {
        OrderStatusHistory::create([
            'order_id' => $orderId,
            'status' => $status,
            'notes' => $notes,
            'created_by' => $createdBy,
        ]);
    }

    protected function getValidStatusTransitions($currentStatus)
    {
        $transitions = [
            'pending' => ['confirmed', 'cancelled'],
            'confirmed' => ['preparing', 'cancelled'],
            'preparing' => ['ready_for_pickup'],
            'ready_for_pickup' => ['picked_up'],
            'picked_up' => ['on_the_way'],
            'on_the_way' => ['delivered'],
            'delivered' => [],
            'cancelled' => [],
            'refunded' => [],
        ];

        return $transitions[$currentStatus] ?? [];
    }

    protected function getTimestampField($status)
    {
        $fields = [
            'confirmed' => 'confirmed_at',
            'preparing' => 'preparing_at',
            'ready_for_pickup' => 'ready_at',
            'picked_up' => 'picked_up_at',
            'delivered' => 'delivered_at',
            'cancelled' => 'cancelled_at',
        ];

        return $fields[$status] ?? null;
    }
}
