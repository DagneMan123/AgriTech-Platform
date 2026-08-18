<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fix any existing foreign key constraints that might be pointing to wrong table
        // First, check if crops table exists
        if (Schema::hasTable('crops')) {
            // Drop existing foreign key if it exists and is wrong
            $table = 'crops';
            $foreignKey = 'crops_farm_id_foreign';
            
            // Get the database driver
            $driver = DB::getDriverName();
            
            if ($driver === 'pgsql') {
                // PostgreSQL
                $constraints = DB::select("
                    SELECT constraint_name 
                    FROM information_schema.table_constraints 
                    WHERE table_name = 'crops' AND constraint_type = 'FOREIGN KEY'
                ");
                
                foreach ($constraints as $constraint) {
                    DB::statement("ALTER TABLE crops DROP CONSTRAINT IF EXISTS \"{$constraint->constraint_name}\"");
                }
            } elseif ($driver === 'mysql') {
                // MySQL
                try {
                    DB::statement("ALTER TABLE crops DROP FOREIGN KEY crops_farm_id_foreign");
                } catch (\Exception $e) {
                    // Constraint doesn't exist, that's ok
                }
            }
            
            // Now recreate the correct foreign key
            if (!Schema::hasColumn('crops', 'farm_id')) {
                Schema::table('crops', function (Blueprint $table) {
                    $table->foreignId('farm_id')->constrained('farms')->onDelete('cascade');
                });
            } else {
                // Column exists, just ensure constraint is correct
                try {
                    Schema::table('crops', function (Blueprint $table) {
                        $table->foreign('farm_id')->references('id')->on('farms')->onDelete('cascade');
                    });
                } catch (\Exception $e) {
                    // Constraint already exists or other error
                }
            }
        }
        
        // Ensure farms table foreign key is correct
        if (Schema::hasTable('farms')) {
            $driver = DB::getDriverName();
            
            if ($driver === 'pgsql') {
                // PostgreSQL - drop wrong constraints
                $constraints = DB::select("
                    SELECT constraint_name 
                    FROM information_schema.table_constraints 
                    WHERE table_name = 'farms' AND constraint_type = 'FOREIGN KEY'
                ");
                
                foreach ($constraints as $constraint) {
                    if (strpos($constraint->constraint_name, 'farmer') !== false) {
                        DB::statement("ALTER TABLE farms DROP CONSTRAINT IF EXISTS \"{$constraint->constraint_name}\"");
                    }
                }
            } elseif ($driver === 'mysql') {
                // MySQL
                try {
                    DB::statement("ALTER TABLE farms DROP FOREIGN KEY farms_farmer_id_foreign");
                } catch (\Exception $e) {
                    // Doesn't exist, that's ok
                }
            }
            
            // Recreate correct constraint
            if (Schema::hasColumn('farms', 'farmer_id')) {
                try {
                    Schema::table('farms', function (Blueprint $table) {
                        $table->foreign('farmer_id')->references('id')->on('users')->onDelete('cascade');
                    });
                } catch (\Exception $e) {
                    // Constraint already exists
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We don't rollback foreign key fixes as they're correctional
    }
};
