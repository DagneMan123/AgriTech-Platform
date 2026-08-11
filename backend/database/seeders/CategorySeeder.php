<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductCategory;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Grains', 'description' => 'Wheat, maize, barley, etc.'],
            ['name' => 'Vegetables', 'description' => 'Fresh vegetables'],
            ['name' => 'Fruits', 'description' => 'Fresh fruits'],
            ['name' => 'Dairy', 'description' => 'Milk and dairy products'],
            ['name' => 'Meat', 'description' => 'Livestock and meat products'],
            ['name' => 'Spices', 'description' => 'Spices and seasonings'],
            ['name' => 'Honey', 'description' => 'Honey and bee products'],
            ['name' => 'Coffee', 'description' => 'Coffee and tea products'],
        ];

        foreach ($categories as $category) {
            ProductCategory::create($category);
        }
    }
}
