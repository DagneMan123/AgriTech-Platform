<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class RepairController
{
    public function fixCropsTable(): JsonResponse
    {
        try {
            $driver = DB::getDriverName();
            $messages = [];

            if ($driver === 'pgsql') {
                // Step 1: Drop and recreate the entire table
                $messages[] = 'Starting crops table rebuild...';

                // Drop existing table if it exists
                if (Schema::hasTable('crops')) {
                    DB::statement('DROP TABLE IF EXISTS crops CASCADE');
                    $messages[] = 'Dropped existing crops table';
                }

                // Step 2: Recreate the table properly
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
                $messages[] = 'Created new crops table with all columns';

                // Step 3: Create indexes
                DB::statement('CREATE INDEX crops_farm_id_status_index ON crops(farm_id, status)');
                $messages[] = 'Created indexes';

                return response()->json([
                    'success' => true,
                    'message' => 'Crops table successfully repaired and recreated',
                    'fixes_applied' => $messages,
                    'status' => 'READY_FOR_CROPS'
                ], 200);
            } elseif ($driver === 'mysql') {
                // MySQL approach
                if (Schema::hasTable('crops')) {
                    DB::statement('DROP TABLE IF EXISTS crops');
                    $messages[] = 'Dropped existing crops table';
                }

                DB::statement('
                    CREATE TABLE crops (
                        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                        farm_id BIGINT UNSIGNED NOT NULL,
                        crop_type VARCHAR(255) NOT NULL,
                        variety VARCHAR(255),
                        planting_date DATE NOT NULL,
                        expected_harvest_date DATE,
                        area_hectares DECIMAL(8, 2) NOT NULL,
                        expected_yield_kg DECIMAL(10, 2),
                        status VARCHAR(50) NOT NULL DEFAULT "planning",
                        notes TEXT,
                        created_at TIMESTAMP NULL,
                        updated_at TIMESTAMP NULL,
                        deleted_at TIMESTAMP NULL,
                        CONSTRAINT crops_farm_id_foreign FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE,
                        INDEX crops_farm_id_status_index (farm_id, status)
                    ) ENGINE=InnoDB
                ');
                $messages[] = 'Created new crops table with all columns (MySQL)';

                return response()->json([
                    'success' => true,
                    'message' => 'Crops table successfully repaired and recreated',
                    'fixes_applied' => $messages,
                    'status' => 'READY_FOR_CROPS'
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'Unsupported database driver: ' . $driver
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error repairing table: ' . $e->getMessage(),
                'error_details' => $e->getFile() . ':' . $e->getLine()
            ], 500);
        }
    }
}
