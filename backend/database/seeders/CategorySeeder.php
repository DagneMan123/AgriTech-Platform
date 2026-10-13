<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Cereals & Grains',
                'slug' => 'cereals-grains',
                'description' => 'Various types of cereals and grains including wheat, barley, corn, rice, etc.',
                'icon' => '🌾',
                'is_active' => true,
                'order' => 1,
            ],
            [
                'name' => 'Coffee & Teas',
                'slug' => 'coffee-teas',
                'description' => 'Ethiopian coffee, various teas and related products',
                'icon' => '☕',
                'is_active' => true,
                'order' => 2,
            ],
            [
                'name' => 'Vegetables',
                'slug' => 'vegetables',
                'description' => 'Fresh vegetables and leafy greens',
                'icon' => '🥬',
                'is_active' => true,
                'order' => 3,
            ],
            [
                'name' => 'Fruits',
                'slug' => 'fruits',
                'description' => 'Fresh seasonal fruits',
                'icon' => '🍎',
                'is_active' => true,
                'order' => 4,
            ],
            [
                'name' => 'Legumes & Pulses',
                'slug' => 'legumes-pulses',
                'description' => 'Beans, lentils, peas and other legumes',
                'icon' => '🫘',
                'is_active' => true,
                'order' => 5,
            ],
            [
                'name' => 'Spices & Herbs',
                'slug' => 'spices-herbs',
                'description' => 'Ethiopian spices, herbs and seasonings',
                'icon' => '🌶️',
                'is_active' => true,
                'order' => 6,
            ],
            [
                'name' => 'Dairy Products',
                'slug' => 'dairy',
                'description' => 'Milk, cheese, butter and dairy products',
                'icon' => '🥛',
                'is_active' => true,
                'order' => 7,
            ],
            [
                'name' => 'Livestock & Poultry',
                'slug' => 'livestock-poultry',
                'description' => 'Live animals, meat and poultry products',
                'icon' => '🐄',
                'is_active' => true,
                'order' => 8,
            ],
            [
                'name' => 'Honey & Bee Products',
                'slug' => 'honey-bee-products',
                'description' => 'Natural honey, beeswax and bee-related products',
                'icon' => '🍯',
                'is_active' => true,
                'order' => 9,
            ],
            [
                'name' => 'Oils & Seeds',
                'slug' => 'oils-seeds',
                'description' => 'Vegetable oils, seeds and nuts',
                'icon' => '🌻',
                'is_active' => true,
                'order' => 10,
            ],
        ];

        foreach ($categories as $category) {
            DB::table('categories')->updateOrInsert(
                ['slug' => $category['slug']],
                array_merge($category, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        $this->command->info('Categories seeded successfully!');
    }
}
