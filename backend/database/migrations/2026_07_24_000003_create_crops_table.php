<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Only create if doesn't exist
        if (!Schema::hasTable('crops')) {
            Schema::create('crops', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('farm_id');
                $table->foreign('farm_id')->references('id')->on('farms')->onDelete('cascade');
                $table->string('crop_type');
                $table->string('variety')->nullable();
                $table->date('planting_date');
                $table->date('expected_harvest_date')->nullable();
                $table->decimal('area_hectares', 8, 2);
                $table->decimal('expected_yield_kg', 10, 2)->nullable();
                $table->enum('status', ['planning', 'planted', 'growing', 'ready_for_harvest', 'harvested'])->default('planning');
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['farm_id', 'status']);
            });
        }

        // Fix the constraint on farms table if it references the wrong table
        $connection = DB::connection()->getDriverName();
        
        try {
            if ($connection === 'pgsql') {
                // Check current constraint
                $constraint = DB::selectOne("
                    SELECT constraint_name, table_name
                    FROM information_schema.table_constraints
                    WHERE table_name = 'farms' 
                    AND constraint_type = 'FOREIGN KEY'
                ");
                
                if ($constraint) {
                    DB::statement('ALTER TABLE farms DROP CONSTRAINT IF EXISTS ' . $constraint->constraint_name . ' CASCADE');
                }
                
                DB::statement('
                    ALTER TABLE farms
                    ADD CONSTRAINT farms_farmer_id_foreign
                    FOREIGN KEY (farmer_id) REFERENCES users(id) ON DELETE CASCADE
                ');
            } elseif ($connection === 'mysql') {
                DB::statement('ALTER TABLE farms DROP FOREIGN KEY IF EXISTS farms_farmer_id_foreign');
                DB::statement('
                    ALTER TABLE farms
                    ADD CONSTRAINT farms_farmer_id_foreign
                    FOREIGN KEY (farmer_id) REFERENCES users(id) ON DELETE CASCADE
                ');
            }
        } catch (\Exception $e) {
            // Constraint might not exist or already correct
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('crops');
    }
};
