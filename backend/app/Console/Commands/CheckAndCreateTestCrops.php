<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Farmer;
use App\Models\Farm;
use App\Models\Crop;
use Illuminate\Console\Command;
use Carbon\Carbon;

class CheckAndCreateTestCrops extends Command
{
    protected $signature = 'crops:check-and-create {--farmer-id= : Specific farmer ID to use}';
    protected $description = 'Check if crops exist for logged-in farmer and create test crop if needed';

    public function handle()
    {
        $this->info('=== Crops Existence Check & Creation ===');
        $this->newLine();

        // Step 1: List all farms for the logged-in farmer
        $this->info('STEP 1: Finding farms for farmer...');
        
        $farmerId = $this->option('farmer-id');
        
        if ($farmerId) {
            $farmer = Farmer::find($farmerId);
            if (!$farmer) {
                $this->error("Farmer with ID {$farmerId} not found");
                return Command::FAILURE;
            }
        } else {
            // Get the first farmer
            $farmer = Farmer::with('user')->first();
            if (!$farmer) {
                $this->error("No farmers found in database");
                return Command::FAILURE;
            }
        }

        $this->line("Found farmer: ID {$farmer->id}, User: {$farmer->user->name} (Email: {$farmer->user->email})");
        $this->newLine();

        // Get farms for this farmer
        $farms = Farm::where('farmer_id', $farmer->id)->get();
        $this->info("Total farms for this farmer: {$farms->count()}");
        
        if ($farms->isEmpty()) {
            $this->error("  ✗ No farms found. Creating a test farm first...");
            
            // Create a test farm
            $farm = Farm::create([
                'farmer_id' => $farmer->id,
                'name' => 'Test Farm',
                'description' => 'Automatically created test farm',
                'address' => '123 Farm Lane',
                'region' => 'Addis Ababa',
                'zone' => 'Central',
                'woreda' => 'Arada',
                'kebele' => 'Test Kebele',
                'size_hectares' => 5.0,
                'land_type' => 'cultivated',
                'soil_type' => 'loam',
                'status' => 'active',
                'farm_type' => 'mixed',
                'irrigation_type' => 'rain-fed',
                'water_source' => 'rain'
            ]);
            
            $this->line("  ✓ Created test farm: ID {$farm->id}");
            $farms = [$farm];
        }

        foreach ($farms as $farm) {
            $this->line("  - Farm: {$farm->name} (ID: {$farm->id}, Size: {$farm->size_hectares} hectares, Status: {$farm->status})");
        }
        $this->newLine();

        // Step 2: List all crops associated with those farms
        $this->info('STEP 2: Checking crops for each farm...');
        
        $allCrops = Crop::whereIn('farm_id', $farms->pluck('id'))->get();
        $this->line("Total crops across all farms: {$allCrops->count()}");
        
        if ($allCrops->isNotEmpty()) {
            foreach ($allCrops as $crop) {
                $this->line("  ✓ Crop: {$crop->crop_type} ({$crop->variety}) - Status: {$crop->status}, Farm: {$crop->farm_id}");
            }
            $this->newLine();
            $this->info('✓ Crops already exist! No action needed.');
            return Command::SUCCESS;
        }

        $this->error("  ✗ No crops found for this farmer's farms");
        $this->newLine();

        // Step 3: Create a test crop if none exist
        $this->info('STEP 3: Creating test crop...');
        
        $testFarm = $farms->first();
        
        $testCrop = Crop::create([
            'farm_id' => $testFarm->id,
            'crop_type' => 'Maize',
            'variety' => 'BH661',
            'planting_date' => Carbon::now()->subMonths(2),
            'expected_harvest_date' => Carbon::now()->addMonths(1),
            'area_hectares' => 2.0,
            'expected_yield_kg' => 5000.00,
            'status' => 'planted',
            'notes' => 'Test crop created automatically'
        ]);
        
        $this->line("✓ Test crop created successfully!");
        $this->line("  - Crop Type: {$testCrop->crop_type}");
        $this->line("  - Variety: {$testCrop->variety}");
        $this->line("  - Status: {$testCrop->status}");
        $this->line("  - Farm ID: {$testCrop->farm_id}");
        $this->line("  - Area: {$testCrop->area_hectares} hectares");
        $this->line("  - Expected Yield: {$testCrop->expected_yield_kg} kg");
        $this->line("  - Planting Date: {$testCrop->planting_date->toDateString()}");
        $this->line("  - Expected Harvest: {$testCrop->expected_harvest_date->toDateString()}");
        $this->newLine();

        // Summary
        $this->info('=== Summary ===');
        $this->line("Farmer: {$farmer->user->name}");
        $this->line("Farms: {$farms->count()}");
        $this->line("Previous Crops: " . ($allCrops->count() + 0));
        $this->line("Test Crop Created: Yes");
        $this->line("Test Crop ID: {$testCrop->id}");
        
        $this->newLine();
        $this->info('✓ Task completed successfully!');
        
        return Command::SUCCESS;
    }
}
