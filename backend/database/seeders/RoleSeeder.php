<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'admin', 'display_name' => 'Administrator'],
            ['name' => 'farmer', 'display_name' => 'Farmer'],
            ['name' => 'buyer', 'display_name' => 'Buyer'],
            ['name' => 'supplier', 'display_name' => 'Supplier'],
            ['name' => 'transport', 'display_name' => 'Transport Provider'],
            ['name' => 'cooperative', 'display_name' => 'Cooperative'],
            ['name' => 'expert', 'display_name' => 'Agricultural Expert'],
            ['name' => 'financial', 'display_name' => 'Financial Institution'],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->insert([
                'name' => $role['name'],
                'display_name' => $role['display_name'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
