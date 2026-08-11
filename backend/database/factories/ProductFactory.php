<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'category_id' => 1,
            'farmer_id' => 1,
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph(),
            'price' => $this->faker->randomFloat(2, 100, 10000),
            'quantity' => $this->faker->numberBetween(10, 1000),
            'available_quantity' => $this->faker->numberBetween(10, 1000),
            'unit' => $this->faker->randomElement(['kg', 'liter', 'bag', 'box']),
            'harvest_date' => $this->faker->date(),
            'location' => $this->faker->address(),
            'quality_grade' => $this->faker->randomElement(['premium', 'good', 'standard']),
            'organic_certified' => $this->faker->boolean(),
            'status' => 'published',
        ];
    }
}
