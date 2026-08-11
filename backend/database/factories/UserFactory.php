<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => $this->faker->randomElement(['farmer', 'buyer', 'supplier']),
            'region' => $this->faker->randomElement(['Addis Ababa', 'Oromia', 'SNNPR', 'Amhara']),
            'zone' => $this->faker->word(),
            'woreda' => $this->faker->word(),
            'is_active' => true,
            'remember_token' => Str::random(10),
        ];
    }
}
