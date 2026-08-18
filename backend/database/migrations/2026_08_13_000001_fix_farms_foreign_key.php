<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the incorrect constraint if it exists
        $connection = DB::connection()->getDriverName();
        
        if ($connection === 'pgsql') {
            // PostgreSQL
            try {
                DB::statement('ALTER TABLE farms DROP CONSTRAINT IF EXISTS farms_farmer_id_foreign CASCADE');
            } catch (\Exception $e) {
                // Constraint might not exist
            }
            
            // Add the correct constraint
            DB::statement('ALTER TABLE farms ADD CONSTRAINT farms_farmer_id_foreign FOREIGN KEY (farmer_id) REFERENCES users(id) ON DELETE CASCADE');
        } elseif ($connection === 'mysql') {
            // MySQL
            try {
                DB::statement('ALTER TABLE farms DROP FOREIGN KEY farms_farmer_id_foreign');
            } catch (\Exception $e) {
                // Constraint might not exist
            }
            
            // Add the correct constraint
            DB::statement('ALTER TABLE farms ADD CONSTRAINT farms_farmer_id_foreign FOREIGN KEY (farmer_id) REFERENCES users(id) ON DELETE CASCADE');
        }
    }

    public function down(): void
    {
        // This migration is a fix, we won't roll it back
    }
};
