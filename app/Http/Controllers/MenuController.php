<?php

namespace App\Http\Controllers;

use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\MenuItemVariant;
use App\Models\MenuItemAddon;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MenuController extends Controller
{
    public function getCategories($restaurantId)
    {
        $categories = MenuCategory::where('restaurant_id', $restaurantId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->with('menuItems')
            ->get();

        return response()->json($categories);
    }

    public function storeCategory(Request $request, $restaurantId)
    {
        $restaurant = Restaurant::findOrFail($restaurantId);

        if ($restaurant->owner_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);

        $category = MenuCategory::create([
            'restaurant_id' => $restaurantId,
            'name' => $request->name,
            'description' => $request->description,
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => true,
        ]);

        return response()->json($category, 201);
    }

    public function updateCategory(Request $request, $restaurantId, $categoryId)
    {
        $restaurant = Restaurant::findOrFail($restaurantId);

        if ($restaurant->owner_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $category = MenuCategory::where('restaurant_id', $restaurantId)
            ->findOrFail($categoryId);

        $request->validate([
            'name' => 'sometimes|string|max:100',
            'description' => 'nullable|string',
            'sort_order' => 'sometimes|integer',
            'is_active' => 'sometimes|boolean',
        ]);

        $category->update($request->only(['name', 'description', 'sort_order', 'is_active']));

        return response()->json($category);
    }

    public function getItems($restaurantId)
    {
        $items = MenuItem::where('restaurant_id', $restaurantId)
            ->with(['category', 'variants', 'addons'])
            ->get();

        return response()->json($items);
    }

    public function storeItem(Request $request, $restaurantId)
    {
        $restaurant = Restaurant::findOrFail($restaurantId);

        if ($restaurant->owner_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'category_id' => 'required|exists:menu_categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0',
            'is_veg' => 'sometimes|boolean',
            'is_available' => 'sometimes|boolean',
            'preparation_time' => 'nullable|string',
        ]);

        $item = MenuItem::create([
            'restaurant_id' => $restaurantId,
            'category_id' => $request->category_id,
            'name' => $request->name,
            'slug' => Str::slug($request->name) . '-' . uniqid(),
            'description' => $request->description,
            'price' => $request->price,
            'discount_price' => $request->discount_price,
            'is_veg' => $request->is_veg ?? true,
            'is_available' => $request->is_available ?? true,
            'preparation_time' => $request->preparation_time,
        ]);

        return response()->json($item, 201);
    }

    public function updateItem(Request $request, $restaurantId, $itemId)
    {
        $restaurant = Restaurant::findOrFail($restaurantId);

        if ($restaurant->owner_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $item = MenuItem::where('restaurant_id', $restaurantId)
            ->findOrFail($itemId);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0',
            'is_veg' => 'sometimes|boolean',
            'is_available' => 'sometimes|boolean',
        ]);

        $item->update($request->only([
            'name', 'description', 'price', 'discount_price',
            'is_veg', 'is_available'
        ]));

        return response()->json($item);
    }

    public function destroyItem($restaurantId, $itemId)
    {
        $restaurant = Restaurant::findOrFail($restaurantId);

        if ($restaurant->owner_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $item = MenuItem::where('restaurant_id', $restaurantId)
            ->findOrFail($itemId);

        $item->delete();

        return response()->json(['message' => 'Menu item deleted successfully']);
    }

    public function storeVariant(Request $request, $restaurantId, $itemId)
    {
        $restaurant = Restaurant::findOrFail($restaurantId);

        if ($restaurant->owner_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
        ]);

        $variant = MenuItemVariant::create([
            'menu_item_id' => $itemId,
            'name' => $request->name,
            'price' => $request->price,
            'is_available' => true,
        ]);

        return response()->json($variant, 201);
    }

    public function storeAddon(Request $request, $restaurantId, $itemId)
    {
        $restaurant = Restaurant::findOrFail($restaurantId);

        if ($restaurant->owner_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
        ]);

        $addon = MenuItemAddon::create([
            'menu_item_id' => $itemId,
            'name' => $request->name,
            'price' => $request->price,
            'is_available' => true,
        ]);

        return response()->json($addon, 201);
    }

    public function getGlobalFoodCategories()
    {
        return response()->json(\App\Models\FoodCategory::orderBy('sort_order')->get());
    }
}
