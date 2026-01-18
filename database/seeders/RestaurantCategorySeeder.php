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
            ['name' => 'North Indian', 'slug' => 'north-indian', 'icon' => '🥘', 'sort_order' => 11],
            ['name' => 'South Indian', 'slug' => 'south-indian', 'icon' => '🍛', 'sort_order' => 12],
            ['name' => 'Street Food', 'slug' => 'street-food', 'icon' => '🥙', 'sort_order' => 13],
            ['name' => 'Tandoori', 'slug' => 'tandoori', 'icon' => '🍗', 'sort_order' => 14],
            ['name' => 'Biryani', 'slug' => 'biryani', 'icon' => '🍚', 'sort_order' => 15],
            ['name' => 'Sweets', 'slug' => 'sweets', 'icon' => '🍬', 'sort_order' => 16],
            ['name' => 'Mughlai', 'slug' => 'mughlai', 'icon' => '🍖', 'sort_order' => 17],
            ['name' => 'Bengali', 'slug' => 'bengali', 'icon' => '🐟', 'sort_order' => 18],
            ['name' => 'Maharashtrian', 'slug' => 'maharashtrian', 'icon' => '🍲', 'sort_order' => 19],
            ['name' => 'Desserts', 'slug' => 'desserts', 'icon' => '🍰', 'sort_order' => 20],
        ];

        foreach ($categories as $category) {
            RestaurantCategory::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}
