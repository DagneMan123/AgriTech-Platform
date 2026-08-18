<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Farmer;
use Illuminate\Console\Command;

class FixFarmerProfiles extends Command
{
    protected $signature = 'fix:farmer-profiles';
    protected $description = 'Create missing farmer profiles for farmer users';

    public function handle()
    {
        $farmerUsers = User::where('role', 'farmer')->get();
        
        $this->info("Found " . $farmerUsers->count() . " farmer users");
        
        $created = 0;
        foreach ($farmerUsers as $user) {
            $farmer = Farmer::where('user_id', $user->id)->first();
            
            if (!$farmer) {
                Farmer::create([
                    'user_id' => $user->id,
                    'farmer_registration_number' => 'FRM-' . $user->id . '-' . time(),
                    'farm_name' => $user->name . ' Farm',
                    'region' => $user->region ?? 'Not Specified',
                    'zone' => 'Not Specified',
                    'woreda' => 'Not Specified',
                    'verification_status' => 'pending',
                ]);
                $created++;
                $this->line("Created farmer profile for user {$user->id}");
            }
        }
        
        $this->info("Created $created new farmer profiles");
    }
}
