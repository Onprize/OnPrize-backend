<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Restaurant;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\MenuItemVariant;
use App\Models\MenuItemAddon;
use Illuminate\Support\Str;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $pizzaPalace = Restaurant::where('name', 'Pizza Palace')->first();
        
        if ($pizzaPalace) {
            $pizzaCategory = MenuCategory::create([
                'restaurant_id' => $pizzaPalace->id,
                'name' => 'Pizzas',
                'sort_order' => 1,
                'is_active' => true,
            ]);

            $margherita = MenuItem::create([
                'restaurant_id' => $pizzaPalace->id,
                'category_id' => $pizzaCategory->id,
                'name' => 'Margherita Pizza',
                'slug' => Str::slug('Margherita Pizza') . '-' . uniqid(),
                'description' => 'Classic pizza with tomato sauce, mozzarella, and basil',
                'price' => 12.99,
                'is_veg' => true,
                'is_available' => true,
                'preparation_time' => '20 mins',
            ]);

            MenuItemVariant::create([
                'menu_item_id' => $margherita->id,
                'name' => 'Small (8 inch)',
                'price' => 12.99,
                'is_available' => true,
            ]);

            MenuItemVariant::create([
                'menu_item_id' => $margherita->id,
                'name' => 'Medium (12 inch)',
                'price' => 18.99,
                'is_available' => true,
            ]);

            MenuItemVariant::create([
                'menu_item_id' => $margherita->id,
                'name' => 'Large (16 inch)',
                'price' => 24.99,
                'is_available' => true,
            ]);

            MenuItemAddon::create([
                'menu_item_id' => $margherita->id,
                'name' => 'Extra Cheese',
                'price' => 2.00,
                'is_available' => true,
            ]);

            MenuItemAddon::create([
                'menu_item_id' => $margherita->id,
                'name' => 'Olives',
                'price' => 1.50,
                'is_available' => true,
            ]);

            $pepperoni = MenuItem::create([
                'restaurant_id' => $pizzaPalace->id,
                'category_id' => $pizzaCategory->id,
                'name' => 'Pepperoni Pizza',
                'slug' => Str::slug('Pepperoni Pizza') . '-' . uniqid(),
                'description' => 'Loaded with pepperoni and mozzarella cheese',
                'price' => 15.99,
                'is_veg' => false,
                'is_available' => true,
                'preparation_time' => '20 mins',
            ]);

            MenuItemVariant::create([
                'menu_item_id' => $pepperoni->id,
                'name' => 'Small (8 inch)',
                'price' => 15.99,
                'is_available' => true,
            ]);

            MenuItemVariant::create([
                'menu_item_id' => $pepperoni->id,
                'name' => 'Medium (12 inch)',
                'price' => 21.99,
                'is_available' => true,
            ]);

            MenuItemVariant::create([
                'menu_item_id' => $pepperoni->id,
                'name' => 'Large (16 inch)',
                'price' => 27.99,
                'is_available' => true,
            ]);

            $drinksCategory = MenuCategory::create([
                'restaurant_id' => $pizzaPalace->id,
                'name' => 'Beverages',
                'sort_order' => 2,
                'is_active' => true,
            ]);

            MenuItem::create([
                'restaurant_id' => $pizzaPalace->id,
                'category_id' => $drinksCategory->id,
                'name' => 'Coca Cola',
                'slug' => Str::slug('Coca Cola') . '-' . uniqid(),
                'description' => 'Chilled soft drink',
                'price' => 2.50,
                'is_veg' => true,
                'is_available' => true,
            ]);
        }
    }
}
