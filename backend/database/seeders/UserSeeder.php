<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create Admin
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@agritech.com',
            'phone' => '251900000000',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'region' => 'Addis Ababa',
            'zone' => 'Central',
            'woreda' => 'Arada',
            'is_active' => true,
        ]);

        // Create Farmer
        User::create([
            'name' => 'Sample Farmer',
            'email' => 'farmer@agritech.com',
            'phone' => '251911111111',
            'password' => Hash::make('password'),
            'role' => 'farmer',
            'region' => 'Oromia',
            'zone' => 'West',
            'woreda' => 'Ambo',
            'is_active' => true,
        ]);

        // Create Buyer
        User::create([
            'name' => 'Sample Buyer',
            'email' => 'buyer@agritech.com',
            'phone' => '251922222222',
            'password' => Hash::make('password'),
            'role' => 'buyer',
            'region' => 'Addis Ababa',
            'zone' => 'Central',
            'woreda' => 'Bole',
            'is_active' => true,
        ]);

        // Create Supplier
        User::create([
            'name' => 'Sample Supplier',
            'email' => 'supplier@agritech.com',
            'phone' => '251933333333',
            'password' => Hash::make('password'),
            'role' => 'supplier',
            'region' => 'SNNPR',
            'zone' => 'Gurage',
            'woreda' => 'Wolkite',
            'is_active' => true,
        ]);

        // Create more users
        User::factory(10)->create();
    }
}
