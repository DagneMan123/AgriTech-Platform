<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class FixCropsTable extends Command
{
    protected $signature = 'fix:crops-table';
    protected $description = 'Fix the crops table by adding missing columns';

    public function handle()
    {
        try {
            $driver = DB::getDriverName();
            
            if ($driver === 'pgsql') {
                // For PostgreSQL, add missing columns
                $this->info('Fixing crops table for PostgreSQL...');
                
                // Check if crop_type column exists
                if (!Schema::hasColumn('crops', 'crop_type')) {
                    DB::statement('ALTER TABLE crops ADD COLUMN crop_type VARCHAR(255) NOT NULL DEFAULT \'unknown\'');
                    $this->info('Added crop_type column');
                }
                
                // Check if variety column exists
                if (!Schema::hasColumn('crops', 'variety')) {
                    DB::statement('ALTER TABLE crops ADD COLUMN variety VARCHAR(255) NULL');
                    $this->info('Added variety column');
                }
                
                // Check if planting_date column exists
                if (!Schema::hasColumn('crops', 'planting_date')) {
                    DB::statement('ALTER TABLE crops ADD COLUMN planting_date DATE NOT NULL DEFAULT CURRENT_DATE');
                    $this->info('Added planting_date column');
                }
                
                // Check if expected_harvest_date column exists
                if (!Schema::hasColumn('crops', 'expected_harvest_date')) {
                    DB::statement('ALTER TABLE crops ADD COLUMN expected_harvest_date DATE NULL');
                    $this->info('Added expected_harvest_date column');
                }
                
                // Check if area_hectares column exists
                if (!Schema::hasColumn('crops', 'area_hectares')) {
                    DB::statement('ALTER TABLE crops ADD COLUMN area_hectares NUMERIC(8, 2) NOT NULL DEFAULT 0');
                    $this->info('Added area_hectares column');
                }
                
                // Check if expected_yield_kg column exists
                if (!Schema::hasColumn('crops', 'expected_yield_kg')) {
                    DB::statement('ALTER TABLE crops ADD COLUMN expected_yield_kg NUMERIC(10, 2) NULL');
                    $this->info('Added expected_yield_kg column');
                }
                
                // Check if notes column exists
                if (!Schema::hasColumn('crops', 'notes')) {
                    DB::statement('ALTER TABLE crops ADD COLUMN notes TEXT NULL');
                    $this->info('Added notes column');
                }
                
                $this->info('Crops table fixed successfully!');
            } else {
                $this->error('This command only supports PostgreSQL at the moment');
            }
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
        }
    }
}
