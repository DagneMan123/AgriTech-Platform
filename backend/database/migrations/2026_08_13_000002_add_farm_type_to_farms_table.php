<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add missing columns to farms table that should exist but don't
     * This migration ensures all columns defined in the schema exist in the database
     */
    public function up(): void
    {
        Schema::table('farms', function (Blueprint $table) {
            // Add farm_type if it doesn't exist
            if (!Schema::hasColumn('farms', 'farm_type')) {
                $table->string('farm_type')->default('crop')->after('size_hectares');
            }

            // Add soil_type if it doesn't exist
            if (!Schema::hasColumn('farms', 'soil_type')) {
                $table->text('soil_type')->nullable()->after('farm_type');
            }

            // Add irrigation_type if it doesn't exist
            if (!Schema::hasColumn('farms', 'irrigation_type')) {
                $table->enum('irrigation_type', ['rain-fed', 'irrigated', 'mixed'])
                    ->default('rain-fed')
                    ->after('soil_type');
            }

            // Add water_source if it doesn't exist
            if (!Schema::hasColumn('farms', 'water_source')) {
                $table->text('water_source')->nullable()->after('irrigation_type');
            }

            // Add altitude if it doesn't exist
            if (!Schema::hasColumn('farms', 'altitude')) {
                $table->decimal('altitude', 8, 2)->nullable()->after('water_source');
            }

            // Add images if it doesn't exist
            if (!Schema::hasColumn('farms', 'images')) {
                $table->json('images')->nullable()->after('altitude');
            }

            // Add activities if it doesn't exist
            if (!Schema::hasColumn('farms', 'activities')) {
                $table->json('activities')->nullable()->after('images');
            }
        });
    }

    public function down(): void
    {
        Schema::table('farms', function (Blueprint $table) {
            // Drop columns if they exist
            $columns = ['farm_type', 'soil_type', 'irrigation_type', 'water_source', 'altitude', 'images', 'activities'];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('farms', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
