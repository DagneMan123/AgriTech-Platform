<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add missing columns to farms table
        Schema::table('farms', function (Blueprint $table) {
            // Check and add farm_type column
            if (!Schema::hasColumn('farms', 'farm_type')) {
                $table->string('farm_type')->default('crop')->after('size_hectares');
            }
            
            // Check and add soil_type column
            if (!Schema::hasColumn('farms', 'soil_type')) {
                $table->text('soil_type')->nullable()->after('farm_type');
            }
            
            // Check and add irrigation_type column
            if (!Schema::hasColumn('farms', 'irrigation_type')) {
                $table->enum('irrigation_type', ['rain-fed', 'irrigated', 'mixed'])
                    ->default('rain-fed')
                    ->after('soil_type');
            }
            
            // Check and add water_source column
            if (!Schema::hasColumn('farms', 'water_source')) {
                $table->text('water_source')->nullable()->after('irrigation_type');
            }
            
            // Check and add altitude column
            if (!Schema::hasColumn('farms', 'altitude')) {
                $table->decimal('altitude', 8, 2)->nullable()->after('water_source');
            }
            
            // Check and add images column
            if (!Schema::hasColumn('farms', 'images')) {
                $table->json('images')->nullable()->after('altitude');
            }
            
            // Check and add activities column
            if (!Schema::hasColumn('farms', 'activities')) {
                $table->json('activities')->nullable()->after('images');
            }
        });
    }

    public function down(): void
    {
        Schema::table('farms', function (Blueprint $table) {
            // Drop all the added columns in reverse order
            if (Schema::hasColumn('farms', 'activities')) {
                $table->dropColumn('activities');
            }
            if (Schema::hasColumn('farms', 'images')) {
                $table->dropColumn('images');
            }
            if (Schema::hasColumn('farms', 'altitude')) {
                $table->dropColumn('altitude');
            }
            if (Schema::hasColumn('farms', 'water_source')) {
                $table->dropColumn('water_source');
            }
            if (Schema::hasColumn('farms', 'irrigation_type')) {
                $table->dropColumn('irrigation_type');
            }
            if (Schema::hasColumn('farms', 'soil_type')) {
                $table->dropColumn('soil_type');
            }
            if (Schema::hasColumn('farms', 'farm_type')) {
                $table->dropColumn('farm_type');
            }
        });
    }
};
