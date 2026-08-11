<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OrderFactory extends Factory
{
    public function definition(): array
    {
        $subtotal = $this->faker->randomFloat(2, 1000, 50000);
        $tax = $subtotal * 0.1;

        return [
            'order_number' => 'ORD-' . Str::random(10),
            'buyer_id' => 1,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total_amount' => $subtotal + $tax,
            'delivery_address' => $this->faker->address(),
            'delivery_region' => $this->faker->randomElement(['Addis Ababa', 'Oromia', 'SNNPR']),
            'delivery_zone' => $this->faker->word(),
            'delivery_woreda' => $this->faker->word(),
            'delivery_latitude' => $this->faker->latitude(),
            'delivery_longitude' => $this->faker->longitude(),
            'status' => $this->faker->randomElement(['pending', 'confirmed', 'delivered']),
            'payment_status' => $this->faker->randomElement(['pending', 'paid']),
        ];
    }
}
