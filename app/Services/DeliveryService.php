<?php

namespace App\Services;

use App\Models\DeliveryPartner;
use App\Models\DeliveryRequest;
use App\Models\Order;
use App\Models\User;

class DeliveryService
{
    public function findNearbyPartners($latitude, $longitude, $radius = 5)
    {
        return DeliveryPartner::where('is_available', true)
            ->where('verification_status', 'verified')
            ->whereNotNull('current_latitude')
            ->whereNotNull('current_longitude')
            ->selectRaw("*, 
                ( 6371 * acos( cos( radians(?) ) * 
                cos( radians( current_latitude ) ) * 
                cos( radians( current_longitude ) - radians(?) ) + 
                sin( radians(?) ) * 
                sin( radians( current_latitude ) ) ) ) AS distance", 
                [$latitude, $longitude, $latitude])
            ->having('distance', '<', $radius)
            ->orderBy('distance')
            ->get();
    }

    public function assignDeliveryPartner($orderId)
    {
        $order = Order::with('restaurant')->findOrFail($orderId);

        $nearbyPartners = $this->findNearbyPartners(
            $order->restaurant->latitude,
            $order->restaurant->longitude,
            10
        );

        if ($nearbyPartners->isEmpty()) {
            throw new \Exception('No delivery partners available nearby');
        }

        foreach ($nearbyPartners as $partner) {
            $request = DeliveryRequest::create([
                'order_id' => $orderId,
                'delivery_partner_id' => $partner->user_id,
                'status' => 'pending',
                'expires_at' => now()->addMinutes(5),
            ]);

            return $request;
        }

        throw new \Exception('Failed to assign delivery partner');
    }

    public function acceptDeliveryRequest($requestId, $partnerId)
    {
        $request = DeliveryRequest::where('delivery_partner_id', $partnerId)
            ->findOrFail($requestId);

        if ($request->isExpired()) {
            throw new \Exception('Delivery request has expired');
        }

        if ($request->status !== 'pending') {
            throw new \Exception('Delivery request is no longer available');
        }

        $request->update([
            'status' => 'accepted',
            'responded_at' => now(),
        ]);

        $order = $request->order;
        $order->update(['delivery_partner_id' => $partnerId]);

        DeliveryRequest::where('order_id', $order->id)
            ->where('id', '!=', $requestId)
            ->where('status', 'pending')
            ->update(['status' => 'expired']);

        return $order;
    }

    public function rejectDeliveryRequest($requestId, $partnerId)
    {
        $request = DeliveryRequest::where('delivery_partner_id', $partnerId)
            ->findOrFail($requestId);

        $request->update([
            'status' => 'rejected',
            'responded_at' => now(),
        ]);

        return $request;
    }

    public function claimOrder($orderId, $partnerId)
    {
        $order = Order::findOrFail($orderId);

        if ($order->delivery_partner_id) {
            throw new \Exception('Order already accepted by another partner');
        }

        // Assign partner
        $order->update([
            'delivery_partner_id' => $partnerId,
        ]);

        return $order;
    }

    public function updateLocation($partnerId, $latitude, $longitude)
    {
        $partner = DeliveryPartner::where('user_id', $partnerId)->firstOrFail();

        $partner->update([
            'current_latitude' => $latitude,
            'current_longitude' => $longitude,
            'last_location_update' => now(),
        ]);

        return $partner;
    }

    public function toggleAvailability($partnerId)
    {
        $partner = DeliveryPartner::firstOrCreate(
            ['user_id' => $partnerId],
            [
                'vehicle_type' => 'bike',
                'vehicle_number' => 'NEW-PARTNER',
                'license_number' => 'PENDING',
                'verification_status' => 'pending',
                'is_verified' => false,
                'is_available' => false,
            ]
        );

        $partner->update([
            'is_available' => !$partner->is_available,
        ]);

        return $partner;
    }

    public function getAvailableOrders($partnerId, $radius = 10)
    {
        $partner = DeliveryPartner::where('user_id', $partnerId)->firstOrFail();

        if (!$partner->current_latitude || !$partner->current_longitude) {
            return collect([]);
        }

        $orders = Order::with(['restaurant', 'user', 'deliveryAddress'])
            ->where('status', 'ready_for_pickup')
            ->whereNull('delivery_partner_id')
            ->get()
            ->filter(function ($order) use ($partner, $radius) {
                $distance = $this->calculateDistance(
                    $partner->current_latitude,
                    $partner->current_longitude,
                    $order->restaurant->latitude,
                    $order->restaurant->longitude
                );
                return $distance <= $radius;
            });

        return $orders;
    }

    protected function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371;

        $latDiff = deg2rad($lat2 - $lat1);
        $lonDiff = deg2rad($lon2 - $lon1);

        $a = sin($latDiff / 2) * sin($latDiff / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($lonDiff / 2) * sin($lonDiff / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;

        return $distance;
    }
}
