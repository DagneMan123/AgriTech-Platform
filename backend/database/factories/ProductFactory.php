<?php

namespace Database\Factories;

use App\Models\Farmer;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        $farmer = Farmer::inRandomOrder()->first();
        $farmerId = $farmer ? $farmer->id : 1;
        $origin = $farmer ? ($farmer->woreda ?: $farmer->region) : 'Addis Ababa';

        return [
            'farmer_id' => $farmerId,
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph(),
            'price' => $this->faker->randomFloat(2, 50, 5000),
            'price_unit' => 'ETB/kg',
            'quantity' => $this->faker->numberBetween(10, 1000),
            'quantity_unit' => $this->faker->randomElement(['kg', 'quintal', 'crate']),
            'category' => $this->faker->randomElement(['grains', 'vegetables', 'fruits', 'cereals', 'pulses']),
            'origin_location' => $origin,
            'harvest_date' => $this->faker->date(),
            'quality_grade' => $this->faker->randomElement(['Grade 1', 'Grade A', 'Premium']),
            'is_organic' => $this->faker->boolean(),
            'status' => 'available',
        ];
    }
}
