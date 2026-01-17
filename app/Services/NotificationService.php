<?php

namespace App\Services;

use App\Models\Notification;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    public function sendOrderNotification($userId, $orderId, $type, $message)
    {
        $notification = Notification::create([
            'user_id' => $userId,
            'title' => $this->getNotificationTitle($type),
            'body' => $message,
            'type' => 'order',
            'data' => json_encode(['order_id' => $orderId]),
        ]);

        return $notification;
    }

    public function sendDeliveryNotification($userId, $orderId, $message)
    {
        $notification = Notification::create([
            'user_id' => $userId,
            'title' => 'Delivery Update',
            'body' => $message,
            'type' => 'delivery',
            'data' => json_encode(['order_id' => $orderId]),
        ]);

        return $notification;
    }

    public function sendPromotionNotification($userId, $title, $message, $data = null)
    {
        $notification = Notification::create([
            'user_id' => $userId,
            'title' => $title,
            'body' => $message,
            'type' => 'promotion',
            'data' => $data ? json_encode($data) : null,
        ]);

        return $notification;
    }

    public function sendSystemNotification($userId, $title, $message)
    {
        $notification = Notification::create([
            'user_id' => $userId,
            'title' => $title,
            'body' => $message,
            'type' => 'system',
        ]);

        return $notification;
    }

    public function getUserNotifications($userId, $unreadOnly = false)
    {
        $query = Notification::where('user_id', $userId)
            ->orderBy('created_at', 'desc');

        if ($unreadOnly) {
            $query->where('is_read', false);
        }

        return $query->paginate(20);
    }

    public function markAsRead($notificationId, $userId)
    {
        $notification = Notification::where('id', $notificationId)
            ->where('user_id', $userId)
            ->firstOrFail();

        $notification->markAsRead();

        return $notification;
    }

    public function markAllAsRead($userId)
    {
        Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return true;
    }

    protected function getNotificationTitle($type)
    {
        $titles = [
            'order_placed' => 'Order Placed',
            'order_confirmed' => 'Order Confirmed',
            'order_preparing' => 'Preparing Your Order',
            'order_ready' => 'Order Ready for Pickup',
            'order_picked_up' => 'Order Picked Up',
            'order_on_the_way' => 'Order On The Way',
            'order_delivered' => 'Order Delivered',
            'order_cancelled' => 'Order Cancelled',
        ];

        return $titles[$type] ?? 'Order Update';
    }
}
