<?php

namespace App\Http\Controllers;

use App\Services\DeliveryService;
use App\Models\DeliveryPartner;
use App\Models\DeliveryRequest;
use App\Helpers\LocationHelper;
use Illuminate\Http\Request;

class DeliveryPartnerController extends Controller
{
    protected $deliveryService;

    public function __construct(DeliveryService $deliveryService)
    {
        $this->deliveryService = $deliveryService;
    }

    public function register(Request $request)
    {
        $request->validate([
            'vehicle_type' => 'required|in:bike,scooter,bicycle,car',
            'vehicle_number' => 'required|string',
            'license_number' => 'required|string',
        ]);

        $partner = DeliveryPartner::create([
            'user_id' => $request->user()->id,
            'vehicle_type' => $request->vehicle_type,
            'vehicle_number' => $request->vehicle_number,
            'license_number' => $request->license_number,
            'verification_status' => 'pending',
            'is_verified' => false,
            'is_available' => false,
        ]);

        $request->user()->update(['role' => 'delivery_partner']);

        return response()->json([
            'message' => 'Delivery partner registration successful. Awaiting verification.',
            'partner' => $partner,
        ], 201);
    }

    public function updateProfile(Request $request)
    {
        $partner = DeliveryPartner::where('user_id', $request->user()->id)
            ->firstOrFail();

        $request->validate([
            'vehicle_type' => 'sometimes|in:bike,scooter,bicycle,car',
            'vehicle_number' => 'sometimes|string',
            'license_number' => 'sometimes|string',
        ]);

        $partner->update($request->only([
            'vehicle_type',
            'vehicle_number',
            'license_number'
        ]));

        return response()->json($partner);
    }

    public function updateLocation(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $partner = $this->deliveryService->updateLocation(
            $request->user()->id,
            $request->latitude,
            $request->longitude
        );

        return response()->json($partner);
    }

    public function toggleAvailability(Request $request)
    {
        $partner = $this->deliveryService->toggleAvailability($request->user()->id);

        return response()->json([
            'message' => 'Availability updated',
            'is_available' => $partner->is_available,
        ]);
    }

    public function availableOrders(Request $request)
    {
        $radius = $request->radius ?? 30; // Default 30km radius
        
        $orders = $this->deliveryService->getAvailableOrders(
            $request->user()->id,
            $radius
        );

        return response()->json($orders);
    }

    public function acceptOrder(Request $request, $orderId)
    {
        try {
            // Check for specific delivery request first
            $deliveryRequest = DeliveryRequest::where('order_id', $orderId)
                ->where('delivery_partner_id', $request->user()->id)
                ->where('status', 'pending')
                ->first();

            if ($deliveryRequest) {
                $order = $this->deliveryService->acceptDeliveryRequest(
                    $deliveryRequest->id,
                    $request->user()->id
                );
            } else {
                // Fallback to claiming from pool
                $order = $this->deliveryService->claimOrder(
                    $orderId, 
                    $request->user()->id
                );
            }

            return response()->json([
                'message' => 'Delivery accepted successfully',
                'order' => $order->load(['restaurant', 'deliveryAddress', 'items']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to accept delivery',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    public function rejectOrder(Request $request, $orderId)
    {
        $deliveryRequest = DeliveryRequest::where('order_id', $orderId)
            ->where('delivery_partner_id', $request->user()->id)
            ->where('status', 'pending')
            ->first();

        if ($deliveryRequest) {
            $this->deliveryService->rejectDeliveryRequest(
                $deliveryRequest->id,
                $request->user()->id
            );
        }

        return response()->json(['message' => 'Delivery request rejected']);
    }

    public function myDeliveries(Request $request)
    {
        $deliveries = \App\Models\Order::where('delivery_partner_id', $request->user()->id)
            ->with(['restaurant', 'deliveryAddress', 'items'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($deliveries);
    }

    public function pendingRequests(Request $request)
    {
        $requests = DeliveryRequest::where('delivery_partner_id', $request->user()->id)
            ->where('status', 'pending')
            ->with('order.restaurant')
            ->get();

        return response()->json($requests);
    }

    public function verifyPartner(Request $request, $partnerId)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'verification_status' => 'required|in:verified,rejected',
        ]);

        $partner = DeliveryPartner::findOrFail($partnerId);
        
        $partner->update([
            'verification_status' => $request->verification_status,
            'is_verified' => $request->verification_status === 'verified',
            'verified_at' => $request->verification_status === 'verified' ? now() : null,
        ]);

        return response()->json([
            'message' => 'Verification status updated',
            'partner' => $partner,
        ]);
    }

    /**
     * Get nearby delivery partners for order assignment
     */
    public function getNearbyPartners(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'sometimes|numeric|min:1|max:50',
        ]);

        $lat = $request->latitude;
        $lng = $request->longitude;
        $radius = $request->radius ?? 30; // Default 30km radius

        $distanceSQL = LocationHelper::getDistanceSQL($lat, $lng, 'current_latitude', 'current_longitude');

        $partners = DeliveryPartner::with('user')
            ->where('is_available', true)
            ->where('is_verified', true)
            ->whereNotNull('current_latitude')
            ->whereNotNull('current_longitude')
            ->selectRaw("*, $distanceSQL AS distance")
            ->having('distance', '<', $radius)
            ->orderBy('distance')
            ->get();

        return response()->json([
            'partners' => $partners,
            'count' => $partners->count(),
            'radius_km' => $radius,
        ]);
    }
}
