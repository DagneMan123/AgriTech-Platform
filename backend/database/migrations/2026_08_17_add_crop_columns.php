<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Check if crops table exists
        if (Schema::hasTable('crops')) {
            // Drop the existing crops table to recreate it properly
            Schema::dropIfExists('crops');
        }

        // Create crops table with all required columns
        Schema::create('crops', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('farm_id');
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

            $table->foreign('farm_id')->references('id')->on('farms')->onDelete('cascade');
            $table->index(['farm_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crops');
    }
};
