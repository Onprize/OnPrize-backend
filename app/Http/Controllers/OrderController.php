<?php

namespace App\Http\Controllers;

use App\Services\OrderService;
use App\Models\Order;
use App\Models\Review;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function store(Request $request)
    {
        $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'delivery_address_id' => 'required|exists:user_addresses,id',
            'payment_method' => 'required|in:cash,card,upi,wallet',
            'items' => 'required|array|min:1',
            'items.*.menu_item_id' => 'required|exists:menu_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.variant_id' => 'nullable|exists:menu_item_variants,id',
            'items.*.addons' => 'nullable|array',
            'items.*.addons.*' => 'exists:menu_item_addons,id',
        ]);

        try {
            $order = $this->orderService->createOrder(
                $request->user()->id,
                $request->all()
            );

            return response()->json([
                'message' => 'Order placed successfully',
                'order' => $order,
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Order creation failed', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()?->id,
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Failed to create order',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    public function index(Request $request)
    {
        $orders = Order::where('user_id', $request->user()->id)
            ->with(['restaurant', 'items', 'deliveryPartner'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($orders);
    }

    public function show($id)
    {
        $order = Order::with([
            'restaurant',
            'items.menuItem',
            'items.variant',
            'items.addons',
            'deliveryPartner',
            'deliveryAddress',
            'statusHistory'
        ])->findOrFail($id);

        if ($order->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($order);
    }

    public function cancel(Request $request, $id)
    {
        try {
            $order = $this->orderService->cancelOrder($id, $request->user()->id);

            return response()->json([
                'message' => 'Order cancelled successfully',
                'order' => $order,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to cancel order',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        if ($order->restaurant->owner_id !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'status' => 'required|string',
        ]);

        try {
            $order = $this->orderService->updateOrderStatus(
                $id,
                $request->status,
                $request->user()->id,
                $request->notes
            );

            return response()->json([
                'message' => 'Order status updated successfully',
                'order' => $order,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update status',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    public function storeReview(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        if ($order->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (!$order->isDelivered()) {
            return response()->json(['message' => 'Can only review delivered orders'], 400);
        }

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
            'reviewable_type' => 'required|in:Restaurant,DeliveryPartner,MenuItem',
            'reviewable_id' => 'required|integer',
        ]);

        $review = Review::create([
            'order_id' => $order->id,
            'user_id' => $request->user()->id,
            'reviewable_type' => 'App\\Models\\' . $request->reviewable_type,
            'reviewable_id' => $request->reviewable_id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return response()->json($review, 201);
    }

    public function restaurantOrders(Request $request, $restaurantId)
    {
        $restaurant = \App\Models\Restaurant::findOrFail($restaurantId);

        if ($restaurant->owner_id !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $query = Order::where('restaurant_id', $restaurantId)
            ->with(['user', 'items', 'deliveryPartner'])
            ->orderBy('created_at', 'desc');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->paginate(20);

        return response()->json($orders);
    }
}
