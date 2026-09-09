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
        $connection = DB::connection()->getDriverName();

        if ($connection === 'pgsql') {
            // PostgreSQL - Drop and recreate with correct constraint
            DB::statement('DROP TABLE IF EXISTS crop_activities CASCADE');
            
            Schema::create('crop_activities', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('crop_id');
                $table->unsignedBigInteger('farm_id');
                $table->unsignedBigInteger('farmer_id');
                $table->enum('activity_type', [
                    'planting',
                    'watering',
                    'fertilizing',
                    'weeding',
                    'pesticide',
                    'pruning',
                    'harvesting',
                    'other'
                ]);
                $table->date('activity_date');
                $table->time('activity_time')->nullable();
                $table->text('description')->nullable();
                $table->decimal('quantity', 10, 2)->nullable();
                $table->string('unit', 50)->nullable();
                $table->decimal('cost', 12, 2)->nullable();
                $table->string('weather', 50)->nullable();
                $table->text('notes')->nullable();
                $table->softDeletes();
                $table->timestamps();

                // Add indexes first
                $table->index(['crop_id', 'activity_date']);
                $table->index(['farm_id', 'activity_date']);
                $table->index(['farmer_id', 'activity_date']);
                $table->index('activity_type');
            });

            // Add foreign keys after table creation to avoid constraint issues
            DB::statement('ALTER TABLE crop_activities ADD CONSTRAINT crop_activities_crop_id_foreign FOREIGN KEY (crop_id) REFERENCES crops(id) ON DELETE CASCADE');
            DB::statement('ALTER TABLE crop_activities ADD CONSTRAINT crop_activities_farm_id_foreign FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE');
            DB::statement('ALTER TABLE crop_activities ADD CONSTRAINT crop_activities_farmer_id_foreign FOREIGN KEY (farmer_id) REFERENCES farmers(id) ON DELETE CASCADE');
        } else {
            // MySQL
            if (Schema::hasTable('crop_activities')) {
                Schema::dropIfExists('crop_activities');
            }

            Schema::create('crop_activities', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('crop_id');
                $table->unsignedBigInteger('farm_id');
                $table->unsignedBigInteger('farmer_id');
                $table->enum('activity_type', [
                    'planting',
                    'watering',
                    'fertilizing',
                    'weeding',
                    'pesticide',
                    'pruning',
                    'harvesting',
                    'other'
                ]);
                $table->date('activity_date');
                $table->time('activity_time')->nullable();
                $table->text('description')->nullable();
                $table->decimal('quantity', 10, 2)->nullable();
                $table->string('unit', 50)->nullable();
                $table->decimal('cost', 12, 2)->nullable();
                $table->string('weather', 50)->nullable();
                $table->text('notes')->nullable();
                $table->softDeletes();
                $table->timestamps();

                // Add foreign keys explicitly
                $table->foreign('crop_id')->references('id')->on('crops')->onDelete('cascade');
                $table->foreign('farm_id')->references('id')->on('farms')->onDelete('cascade');
                $table->foreign('farmer_id')->references('id')->on('users')->onDelete('cascade');

                // Indexes
                $table->index(['crop_id', 'activity_date']);
                $table->index(['farm_id', 'activity_date']);
                $table->index(['farmer_id', 'activity_date']);
                $table->index('activity_type');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crop_activities');
    }
};

