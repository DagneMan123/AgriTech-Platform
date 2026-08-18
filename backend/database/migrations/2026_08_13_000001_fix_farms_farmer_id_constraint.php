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
        try {
            Schema::table('farms', function (Blueprint $table) {
                // Get the database type
                $connection = DB::connection()->getDriverName();
                
                if ($connection === 'pgsql') {
                    // PostgreSQL specific
                    DB::statement('ALTER TABLE farms DROP CONSTRAINT IF EXISTS farms_farmer_id_foreign');
                } else {
                    // MySQL specific
                    try {
                        $table->dropForeign(['farmer_id']);
                    } catch (\Exception $e) {
                        // Constraint might not exist
                    }
                }
            });
        } catch (\Exception $e) {
            // Log but don't fail - constraint might not exist
        }

        // Now create the correct constraint
        Schema::table('farms', function (Blueprint $table) {
            $connection = DB::connection()->getDriverName();
            
            if ($connection === 'pgsql') {
                // PostgreSQL: directly add constraint
                DB::statement('
                    ALTER TABLE farms 
                    ADD CONSTRAINT farms_farmer_id_foreign 
                    FOREIGN KEY (farmer_id) 
                    REFERENCES users(id) 
                    ON DELETE CASCADE
                ');
            } else {
                // MySQL: use Laravel's helper
                $table->foreign('farmer_id')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        Schema::table('farms', function (Blueprint $table) {
            $connection = DB::connection()->getDriverName();
            
            if ($connection === 'pgsql') {
                DB::statement('ALTER TABLE farms DROP CONSTRAINT IF EXISTS farms_farmer_id_foreign');
            } else {
                try {
                    $table->dropForeign(['farmer_id']);
                } catch (\Exception $e) {
                    // Constraint might not exist
                }
            }
        });
    }
};
