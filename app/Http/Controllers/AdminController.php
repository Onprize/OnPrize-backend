<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Restaurant;
use App\Models\Order;
use App\Models\DeliveryPartner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'total_users' => User::where('role', 'customer')->count(),
            'total_restaurants' => Restaurant::count(),
            'total_delivery_partners' => DeliveryPartner::count(),
            'total_orders' => Order::count(),
            'pending_restaurants' => Restaurant::where('status', 'pending')->count(),
            'pending_partners' => DeliveryPartner::where('verification_status', 'pending')->count(),
            'today_orders' => Order::whereDate('created_at', today())->count(),
            'today_revenue' => Order::whereDate('created_at', today())
                ->where('payment_status', 'success')
                ->sum('total'),
        ];

        $recent_orders = Order::with(['user', 'restaurant'])
            ->latest()
            ->take(10)
            ->get();

        return response()->json([
            'stats' => $stats,
            'recent_orders' => $recent_orders,
        ]);
    }

    public function users(Request $request)
    {
        $query = User::query();

        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%')
                    ->orWhere('phone', 'like', '%' . $request->search . '%');
            });
        }

        $users = $query->paginate(20);

        return response()->json($users);
    }

    public function updateUserStatus(Request $request, $userId)
    {
        $request->validate([
            'status' => 'required|in:active,suspended,blocked',
        ]);

        $user = User::findOrFail($userId);
        $user->update(['status' => $request->status]);

        return response()->json([
            'message' => 'User status updated successfully',
            'user' => $user,
        ]);
    }

    public function restaurants(Request $request)
    {
        $query = Restaurant::with('owner');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $restaurants = $query->paginate(20);

        return response()->json($restaurants);
    }

    public function deliveryPartners(Request $request)
    {
        $query = DeliveryPartner::with('user');

        if ($request->has('verification_status')) {
            $query->where('verification_status', $request->verification_status);
        }

        if ($request->has('is_available')) {
            $query->where('is_available', $request->is_available);
        }

        $partners = $query->paginate(20);

        return response()->json($partners);
    }

    public function orders(Request $request)
    {
        $query = Order::with(['user', 'restaurant', 'deliveryPartner']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json($orders);
    }

    public function analytics(Request $request)
    {
        $period = $request->period ?? 'month';

        $analytics = [
            'orders_by_status' => Order::select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get(),
            
            'revenue_by_period' => $this->getRevenueByPeriod($period),
            
            'top_restaurants' => Restaurant::select('restaurants.*')
                ->withCount('orders')
                ->orderBy('orders_count', 'desc')
                ->take(10)
                ->get(),
            
            'top_delivery_partners' => DeliveryPartner::select('delivery_partners.*')
                ->orderBy('total_deliveries', 'desc')
                ->take(10)
                ->get(),
        ];

        return response()->json($analytics);
    }

    public function settings(Request $request)
    {
        $settings = \App\Models\AppSetting::all()->pluck('value', 'key');

        return response()->json($settings);
    }

    public function updateSettings(Request $request)
    {
        foreach ($request->all() as $key => $value) {
            \App\Models\AppSetting::set($key, $value);
        }

        return response()->json(['message' => 'Settings updated successfully']);
    }

    // Restaurant approval methods
    public function approveRestaurant($id)
    {
        $restaurant = Restaurant::findOrFail($id);
        $restaurant->update(['status' => 'approved']);

        return response()->json([
            'message' => 'Restaurant approved successfully',
            'restaurant' => $restaurant->load('owner'),
        ]);
    }

    public function rejectRestaurant($id)
    {
        $restaurant = Restaurant::findOrFail($id);
        $restaurant->update(['status' => 'rejected']);

        return response()->json([
            'message' => 'Restaurant rejected successfully',
            'restaurant' => $restaurant->load('owner'),
        ]);
    }

    public function toggleRestaurantStatus($id)
    {
        $restaurant = Restaurant::findOrFail($id);
        
        if ($restaurant->status === 'approved') {
            $restaurant->update(['status' => 'suspended']);
        } elseif ($restaurant->status === 'suspended') {
            $restaurant->update(['status' => 'approved']);
        }

        return response()->json([
            'message' => 'Restaurant status updated successfully',
            'restaurant' => $restaurant->load('owner'),
        ]);
    }

    // Delivery partner verification methods
    public function verifyDeliveryPartner($id)
    {
        $partner = DeliveryPartner::findOrFail($id);
        $partner->update([
            'verification_status' => 'verified',
            'is_verified' => true,
            'verified_at' => now(),
        ]);

        return response()->json([
            'message' => 'Delivery partner verified successfully',
            'partner' => $partner->load('user'),
        ]);
    }

    public function rejectDeliveryPartner($id)
    {
        $partner = DeliveryPartner::findOrFail($id);
        $partner->update([
            'verification_status' => 'rejected',
            'is_verified' => false,
        ]);

        return response()->json([
            'message' => 'Delivery partner rejected successfully',
            'partner' => $partner->load('user'),
        ]);
    }

    public function togglePartnerStatus($id)
    {
        $partner = DeliveryPartner::findOrFail($id);
        
        if ($partner->verification_status === 'verified') {
            $partner->update([
                'verification_status' => 'suspended',
                'is_verified' => false,
            ]);
        } elseif ($partner->verification_status === 'suspended') {
            $partner->update([
                'verification_status' => 'verified',
                'is_verified' => true,
            ]);
        }

        return response()->json([
            'message' => 'Partner status updated successfully',
            'partner' => $partner->load('user'),
        ]);
    }

    protected function getRevenueByPeriod($period)
    {
        $query = Order::where('payment_status', 'success');

        switch ($period) {
            case 'week':
                $query->where('created_at', '>=', now()->subWeek());
                break;
            case 'month':
                $query->where('created_at', '>=', now()->subMonth());
                break;
            case 'year':
                $query->where('created_at', '>=', now()->subYear());
                break;
        }

        return $query->selectRaw('DATE(created_at) as date, SUM(total) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }
}
