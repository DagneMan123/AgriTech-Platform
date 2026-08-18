<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Farm;
use App\Models\Crop;

class FarmSeeder extends Seeder
{
    public function run(): void
    {
        // Get the sample farmer user
        $farmer = User::where('email', 'farmer@agritech.com')->first();

        if (!$farmer) {
            return; // Farmer user doesn't exist, skip
        }

        // Create a farm for the farmer
        $farm = Farm::create([
            'farmer_id' => $farmer->id,
            'name' => 'Ambo Agricultural Farm',
            'description' => 'A sustainable farm specializing in cereal production',
            'address' => 'Ambo, Oromia Region',
            'region' => 'Oromia',
            'zone' => 'West',
            'woreda' => 'Ambo',
            'kebele' => 'Kebele 01',
            'size_hectares' => 50,
            'land_type' => 'cultivated',
            'soil_type' => 'loamy',
            'status' => 'active',
            'farm_type' => 'mixed',
            'irrigation_type' => 'rainwater',
            'water_source' => 'well',
            'latitude' => 8.9814,
            'longitude' => 37.8660,
            'altitude' => 2100,
        ]);

        // Create crops for the farm
        Crop::create([
            'farm_id' => $farm->id,
            'crop_type' => 'Wheat',
            'variety' => 'Digalu',
            'planting_date' => now()->subMonths(3),
            'expected_harvest_date' => now()->addMonths(1),
            'area_hectares' => 10,
            'expected_yield_kg' => 5000,
            'status' => 'growing',
            'notes' => 'High yielding variety, good disease resistance',
        ]);

        Crop::create([
            'farm_id' => $farm->id,
            'crop_type' => 'Maize',
            'variety' => 'BH540',
            'planting_date' => now()->subMonths(2),
            'expected_harvest_date' => now()->addMonths(2),
            'area_hectares' => 15,
            'expected_yield_kg' => 7500,
            'status' => 'growing',
            'notes' => 'Hybrid variety, requires good drainage',
        ]);

        Crop::create([
            'farm_id' => $farm->id,
            'crop_type' => 'Teff',
            'variety' => 'Red Teff',
            'planting_date' => now()->subMonths(1),
            'expected_harvest_date' => now()->addMonths(3),
            'area_hectares' => 5,
            'expected_yield_kg' => 2000,
            'status' => 'planted',
            'notes' => 'Traditional Ethiopian crop, high nutritional value',
        ]);

        Crop::create([
            'farm_id' => $farm->id,
            'crop_type' => 'Barley',
            'variety' => 'Local Barley',
            'planting_date' => now()->subMonths(4),
            'expected_harvest_date' => now()->subWeeks(1),
            'area_hectares' => 8,
            'expected_yield_kg' => 3200,
            'status' => 'ready_for_harvest',
            'notes' => 'Ready to be harvested soon',
        ]);

        // Create a second farm
        $farm2 = Farm::create([
            'farmer_id' => $farmer->id,
            'name' => 'Highland Farm - Arsi Zone',
            'description' => 'Highland farm specialized in pulse production',
            'address' => 'Robe, Arsi Zone',
            'region' => 'Oromia',
            'zone' => 'Arsi',
            'woreda' => 'Robe',
            'kebele' => 'Kebele 03',
            'size_hectares' => 30,
            'land_type' => 'cultivated',
            'soil_type' => 'clay',
            'status' => 'active',
            'farm_type' => 'pulses',
            'irrigation_type' => 'drip',
            'water_source' => 'spring',
            'latitude' => 7.8197,
            'longitude' => 40.1126,
            'altitude' => 2800,
        ]);

        Crop::create([
            'farm_id' => $farm2->id,
            'crop_type' => 'Chickpea',
            'variety' => 'Habru',
            'planting_date' => now()->subMonths(3),
            'expected_harvest_date' => now()->addMonths(1),
            'area_hectares' => 12,
            'expected_yield_kg' => 3000,
            'status' => 'growing',
            'notes' => 'High-yielding variety for pulse production',
        ]);

        Crop::create([
            'farm_id' => $farm2->id,
            'crop_type' => 'Bean',
            'variety' => 'Red Kidney Bean',
            'planting_date' => now()->subMonths(2),
            'expected_harvest_date' => now()->addMonths(2),
            'area_hectares' => 8,
            'expected_yield_kg' => 1600,
            'status' => 'growing',
            'notes' => 'Suitable for highland areas',
        ]);
    }
}
