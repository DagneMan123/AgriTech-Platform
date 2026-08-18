<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Farmer;
use Illuminate\Console\Command;

class DiagnosticCheck extends Command
{
    protected $signature = 'diagnostic:check';
    protected $description = 'Run diagnostic checks on farmer dashboard setup';

    public function handle()
    {
        $this->info('=== Farmer Dashboard Diagnostic Check ===');
        $this->newLine();

        // Check 1: Farmer table exists
        $this->info('1. Checking farmers table...');
        try {
            $farmerCount = Farmer::count();
            $this->line("   ✓ Farmers table exists with {$farmerCount} records");
        } catch (\Exception $e) {
            $this->error("   ✗ Error accessing farmers table: {$e->getMessage()}");
        }
        $this->newLine();

        // Check 2: Users with farmer role
        $this->info('2. Checking users with farmer role...');
        $farmerUsers = User::where('role', 'farmer')->get();
        $this->line("   Found {$farmerUsers->count()} farmer users");
        $this->newLine();

        // Check 3: Farmer profiles for each user
        $this->info('3. Checking farmer profiles...');
        $missingProfiles = [];
        foreach ($farmerUsers as $user) {
            $farmer = Farmer::where('user_id', $user->id)->first();
            if ($farmer) {
                $this->line("   ✓ User {$user->id} ({$user->name}): Has farmer profile (ID: {$farmer->id})");
            } else {
                $this->line("   ✗ User {$user->id} ({$user->name}): MISSING farmer profile");
                $missingProfiles[] = $user;
            }
        }
        $this->newLine();

        // Check 4: Database integrity
        if (!empty($missingProfiles)) {
            $this->error("ISSUE FOUND: {count($missingProfiles)} user(s) missing farmer profiles");
            $this->line('');
            $this->info('To fix, run: php artisan farmers:create-missing-profiles');
        } else {
            $this->info('✓ All farmer users have farmer profiles');
        }
        $this->newLine();

        // Check 5: Verify required fields
        $this->info('4. Checking farmer profile completeness...');
        $incompleteProfiles = [];
        foreach (Farmer::all() as $farmer) {
            $missing = [];
            if (!$farmer->region) $missing[] = 'region';
            if (!$farmer->zone) $missing[] = 'zone';
            if (!$farmer->woreda) $missing[] = 'woreda';
            
            if (!empty($missing)) {
                $incompleteProfiles[] = ['farmer_id' => $farmer->id, 'missing' => implode(', ', $missing)];
                $this->line("   ✗ Farmer {$farmer->id}: Missing fields - " . implode(', ', $missing));
            }
        }

        if (empty($incompleteProfiles)) {
            $this->info('   ✓ All farmer profiles have required fields');
        }
        $this->newLine();

        // Summary
        $this->info('=== Summary ===');
        $this->line("Total Farmer Users: {$farmerUsers->count()}");
        $this->line("Farmer Profiles: " . Farmer::count());
        $this->line("Missing Profiles: " . count($missingProfiles));
        $this->line("Incomplete Profiles: " . count($incompleteProfiles));
        
        if (empty($missingProfiles) && empty($incompleteProfiles)) {
            $this->info('✓ Everything looks good!');
            return Command::SUCCESS;
        } else {
            $this->error('✗ Issues found - see above');
            return Command::FAILURE;
        }
    }
}
