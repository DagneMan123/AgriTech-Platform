<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Farmer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreateMissingFarmerProfiles extends Command
{
    protected $signature = 'farmers:create-missing-profiles';
    protected $description = 'Create missing farmer profiles for users with farmer role';

    public function handle()
    {
        $this->info('Creating missing farmer profiles...');

        try {
            $farmers = User::where('role', 'farmer')
                ->doesntHave('farmer')
                ->get();

            if ($farmers->isEmpty()) {
                $this->info('No missing farmer profiles found.');
                return Command::SUCCESS;
            }

            $this->info("Found {$farmers->count()} users without farmer profiles.");

            foreach ($farmers as $user) {
                try {
                    Farmer::firstOrCreate(
                        ['user_id' => $user->id],
                        [
                            'farmer_registration_number' => 'FRM-' . $user->id . '-' . time(),
                            'farm_name' => $user->name . ' Farm',
                            'region' => $user->region ?? 'Not Specified',
                            'zone' => 'Not Specified',
                            'woreda' => 'Not Specified',
                        ]
                    );

                    $this->line("✓ Created farmer profile for user: {$user->name} (ID: {$user->id})");
                } catch (\Exception $e) {
                    $this->error("✗ Failed to create farmer profile for user ID {$user->id}: {$e->getMessage()}");
                }
            }

            $this->info('✓ All missing farmer profiles have been created.');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
