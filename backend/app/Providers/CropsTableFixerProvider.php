<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CropsTableFixerProvider extends ServiceProvider
{
    public function boot()
    {
        // Only attempt fix if we're in an API request and not in migration
        if (app()->runningInConsole() && in_array(app()->make('artisan')->getName() ?? 'artisan', ['migrate', 'migrate:fresh'])) {
            return;
        }

        try {
            // Check if crops table exists but is broken
            if (Schema::hasTable('crops')) {
                $driver = DB::getDriverName();

                if ($driver === 'pgsql') {
                    // Check if crop_type column exists
                    if (!Schema::hasColumn('crops', 'crop_type')) {
                        $this->fixCropsTable();
                    }
                }
            }
        } catch (\Exception $e) {
            // Silently fail during boot
            \Log::debug('CropsTableFixerProvider: ' . $e->getMessage());
        }
    }

    private function fixCropsTable()
    {
        try {
            $driver = DB::getDriverName();

            if ($driver === 'pgsql') {
                DB::statement('DROP TABLE IF EXISTS crops CASCADE');
                
                DB::statement('
                    CREATE TABLE crops (
                        id BIGSERIAL PRIMARY KEY,
                        farm_id BIGINT NOT NULL,
                        crop_type VARCHAR(255) NOT NULL,
                        variety VARCHAR(255),
                        planting_date DATE NOT NULL,
                        expected_harvest_date DATE,
                        area_hectares NUMERIC(8, 2) NOT NULL,
                        expected_yield_kg NUMERIC(10, 2),
                        status VARCHAR(50) NOT NULL DEFAULT \'planning\',
                        notes TEXT,
                        created_at TIMESTAMP,
                        updated_at TIMESTAMP,
                        deleted_at TIMESTAMP,
                        CONSTRAINT crops_farm_id_foreign FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE
                    )
                ');
                
                DB::statement('CREATE INDEX crops_farm_id_status_index ON crops(farm_id, status)');
                
                \Log::info('Crops table automatically repaired');
            }
        } catch (\Exception $e) {
            \Log::error('Failed to repair crops table: ' . $e->getMessage());
        }
    }

    public function register()
    {
        //
    }
}
