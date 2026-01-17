<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RestaurantCategory;

class RestaurantCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Fast Food', 'slug' => 'fast-food', 'icon' => '🍔', 'sort_order' => 1],
            ['name' => 'Italian', 'slug' => 'italian', 'icon' => '🍝', 'sort_order' => 2],
            ['name' => 'Chinese', 'slug' => 'chinese', 'icon' => '🥡', 'sort_order' => 3],
            ['name' => 'Indian', 'slug' => 'indian', 'icon' => '🍛', 'sort_order' => 4],
            ['name' => 'Pizza', 'slug' => 'pizza', 'icon' => '🍕', 'sort_order' => 5],
            ['name' => 'Burger', 'slug' => 'burger', 'icon' => '🍔', 'sort_order' => 6],
            ['name' => 'Desserts', 'slug' => 'desserts', 'icon' => '🍰', 'sort_order' => 7],
            ['name' => 'Beverages', 'slug' => 'beverages', 'icon' => '🥤', 'sort_order' => 8],
            ['name' => 'Healthy', 'slug' => 'healthy', 'icon' => '🥗', 'sort_order' => 9],
            ['name' => 'Mexican', 'slug' => 'mexican', 'icon' => '🌮', 'sort_order' => 10],
        ];

        foreach ($categories as $category) {
            RestaurantCategory::create($category);
        }
    }
}
