<?php

namespace Database\Seeders;

use App\Models\FoodCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FoodCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Biryani', 'icon' => '🍚'],
            ['name' => 'North Indian', 'icon' => '🍛'],
            ['name' => 'South Indian', 'icon' => '🥘'],
            ['name' => 'Chinese', 'icon' => '🥡'],
            ['name' => 'Fast Food', 'icon' => '🍔'],
            ['name' => 'Starters', 'icon' => '🍢'],
            ['name' => 'Main Course', 'icon' => '🍽️'],
            ['name' => 'Breads', 'icon' => '🫓'],
            ['name' => 'Rice & Pulao', 'icon' => '🥣'],
            ['name' => 'Paneer Special', 'icon' => '🧀'],
            ['name' => 'Chicken Special', 'icon' => '🍗'],
            ['name' => 'Mutton Special', 'icon' => '🍖'],
            ['name' => 'Seafood', 'icon' => '🐟'],
            ['name' => 'Street Food', 'icon' => '🥙'],
            ['name' => 'Tandoori Items', 'icon' => '🍢'],
            ['name' => 'Rolls & Kebabs', 'icon' => '🌯'],
            ['name' => 'Thali', 'icon' => '🍱'],
            ['name' => 'Beverages', 'icon' => '🥤'],
            ['name' => 'Desserts', 'icon' => '🍰'],
            ['name' => 'Ice Creams', 'icon' => '🍦'],
            ['name' => 'Sweets', 'icon' => '🍬'],
            ['name' => 'Salads & Raita', 'icon' => '🥗'],
            ['name' => 'Snacks', 'icon' => '🥨'],
            ['name' => 'Healthy Food', 'icon' => '🥗'],
            ['name' => 'Breakfast', 'icon' => '🍳'],
        ];

        foreach ($categories as $index => $category) {
            FoodCategory::updateOrCreate(
                ['slug' => Str::slug($category['name'])],
                [
                    'name' => $category['name'],
                    'icon' => $category['icon'],
                    'sort_order' => $index + 1,
                ]
            );
        }
    }
}
