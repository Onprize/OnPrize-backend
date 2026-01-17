<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RestaurantController extends Controller
{
    public function index(Request $request)
    {
        $query = Restaurant::with(['owner', 'categories'])
            ->where('status', 'approved');

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->has('category')) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        if ($request->has('lat') && $request->has('lng')) {
            $lat = $request->lat;
            $lng = $request->lng;
            $radius = $request->radius ?? 10;
            
            $query->selectRaw("*, 
                ( 6371 * acos( cos( radians(?) ) * 
                cos( radians( latitude ) ) * 
                cos( radians( longitude ) - radians(?) ) + 
                sin( radians(?) ) * 
                sin( radians( latitude ) ) ) ) AS distance", 
                [$lat, $lng, $lat])
                ->having('distance', '<', $radius)
                ->orderBy('distance');
        }

        $restaurants = $query->paginate(20);

        return response()->json($restaurants);
    }

    public function show($id)
    {
        // Validate that ID is numeric
        if (!is_numeric($id) || $id <= 0) {
            return response()->json([
                'message' => 'Invalid restaurant ID'
            ], 400);
        }

        $restaurant = Restaurant::with(['owner', 'categories', 'menuCategories.menuItems'])
            ->findOrFail($id);

        return response()->json($restaurant);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string',
            'address_line1' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'postal_code' => 'required|string',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'cuisine_types' => 'nullable|array',
            'delivery_fee' => 'nullable|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
        ]);

        $restaurant = Restaurant::create([
            'owner_id' => $request->user()->id,
            'name' => $request->name,
            'slug' => Str::slug($request->name) . '-' . uniqid(),
            'email' => $request->email,
            'phone' => $request->phone,
            'address_line1' => $request->address_line1,
            'address_line2' => $request->address_line2,
            'city' => $request->city,
            'state' => $request->state,
            'postal_code' => $request->postal_code,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'cuisine_types' => $request->cuisine_types,
            'delivery_fee' => $request->delivery_fee ?? 0,
            'min_order_amount' => $request->min_order_amount ?? 0,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Restaurant created successfully. Awaiting admin approval.',
            'restaurant' => $restaurant,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $restaurant = Restaurant::findOrFail($id);

        if ($restaurant->owner_id !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email',
            'phone' => 'sometimes|string',
            'description' => 'nullable|string',
            'delivery_fee' => 'sometimes|numeric|min:0',
            'min_order_amount' => 'sometimes|numeric|min:0',
            'is_open' => 'sometimes|boolean',
            'is_accepting_orders' => 'sometimes|boolean',
        ]);

        $restaurant->update($request->only([
            'name', 'email', 'phone', 'description', 'delivery_fee',
            'min_order_amount', 'is_open', 'is_accepting_orders'
        ]));

        return response()->json([
            'message' => 'Restaurant updated successfully',
            'restaurant' => $restaurant,
        ]);
    }

    public function destroy($id)
    {
        $restaurant = Restaurant::findOrFail($id);

        if ($restaurant->owner_id !== auth()->id() && !auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $restaurant->delete();

        return response()->json(['message' => 'Restaurant deleted successfully']);
    }

    public function myRestaurants(Request $request)
    {
        $restaurants = Restaurant::where('owner_id', $request->user()->id)
            ->with('categories')
            ->get();

        return response()->json($restaurants);
    }

    public function updateStatus(Request $request, $id)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'status' => 'required|in:pending,approved,rejected,suspended',
        ]);

        $restaurant = Restaurant::findOrFail($id);
        $restaurant->update(['status' => $request->status]);

        return response()->json([
            'message' => 'Restaurant status updated successfully',
            'restaurant' => $restaurant,
        ]);
    }
}
