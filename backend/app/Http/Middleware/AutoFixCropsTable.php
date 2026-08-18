<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AutoFixCropsTable
{
    public function handle(Request $request, Closure $next)
    {
        // Only fix if it's a crop-related request
        if (strpos($request->getPathInfo(), '/api/farmer/crops') !== false) {
            $this->ensureCropsTableIsCorrect();
        }

        return $next($request);
    }

    private function ensureCropsTableIsCorrect()
    {
        try {
            $driver = DB::getDriverName();

            if ($driver === 'pgsql') {
                // Check if table has all required columns
                $hasAllColumns = Schema::hasColumn('crops', 'crop_type') &&
                                 Schema::hasColumn('crops', 'variety') &&
                                 Schema::hasColumn('crops', 'planting_date') &&
                                 Schema::hasColumn('crops', 'expected_harvest_date') &&
                                 Schema::hasColumn('crops', 'area_hectares') &&
                                 Schema::hasColumn('crops', 'expected_yield_kg') &&
                                 Schema::hasColumn('crops', 'notes') &&
                                 Schema::hasColumn('crops', 'status');

                if (!$hasAllColumns) {
                    // Rebuild the table
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
                }
            }
        } catch (\Exception $e) {
            // Silently fail - don't break the request
            \Log::error('AutoFixCropsTable error: ' . $e->getMessage());
        }
    }
}
