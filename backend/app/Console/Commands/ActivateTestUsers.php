<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Farmer;
use Illuminate\Support\Facades\Hash;

class ActivateTestUsers extends Command
{
    protected $signature = 'db:activate-test-users';
    protected $description = 'Activate test users for development';

    public function handle()
    {
        $this->info('Activating test users...');

        // Activate all existing users
        $updated = User::where('is_active', false)->update(['is_active' => true]);
        $this->info("Activated {$updated} inactive users");

        // Create or update test farmer
        $farmer = User::updateOrCreate(
            ['email' => 'farmer@agritech.com'],
            [
                'name' => 'Sample Farmer',
                'phone' => '251911111111',
                'password' => Hash::make('password'),
                'role' => 'farmer',
                'region' => 'Oromia',
                'is_active' => true,
            ]
        );

        // Ensure farmer has a profile
        Farmer::firstOrCreate(
            ['user_id' => $farmer->id],
            [
                'farmer_registration_number' => 'FRM-' . $farmer->id . '-' . time(),
                'farm_name' => 'Sample Farm',
                'region' => 'Oromia',
                'zone' => 'West',
                'woreda' => 'Ambo',
            ]
        );

        $this->info("Test farmer created/updated: farmer@agritech.com / password");

        // Create or update test admin
        $admin = User::updateOrCreate(
            ['email' => 'admin@agritech.com'],
            [
                'name' => 'Admin User',
                'phone' => '251900000000',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'region' => 'Addis Ababa',
                'is_active' => true,
            ]
        );

        $this->info("Test admin created/updated: admin@agritech.com / password");

        $this->info('✅ Test users activated and ready for login!');
    }
}
