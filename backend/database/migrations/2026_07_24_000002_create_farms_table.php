<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Only create if doesn't exist (for fresh installs)
        if (!Schema::hasTable('farms')) {
            Schema::create('farms', function (Blueprint $table) {
                $table->id();
                $table->foreignId('farmer_id')->constrained('users')->onDelete('cascade');
                $table->string('name');
                $table->text('description')->nullable();
                $table->string('address')->nullable();
                $table->string('region');
                $table->string('zone');
                $table->string('woreda');
                $table->string('kebele')->nullable();
                $table->decimal('latitude', 10, 8)->nullable();
                $table->decimal('longitude', 11, 8)->nullable();
                $table->decimal('size_hectares', 8, 2);
                $table->string('farm_type')->default('crop');
                $table->enum('land_type', ['owned', 'rented', 'leased'])->default('owned');
                $table->text('soil_type')->nullable();
                $table->enum('irrigation_type', ['rain-fed', 'irrigated', 'mixed'])->default('rain-fed');
                $table->text('water_source')->nullable();
                $table->decimal('altitude', 8, 2)->nullable();
                $table->json('images')->nullable();
                $table->json('activities')->nullable();
                $table->enum('status', ['active', 'inactive', 'archived'])->default('active');
                $table->timestamps();
                $table->softDeletes();

                $table->index(['farmer_id', 'status']);
            });
        } else {
            // Table exists - ensure all columns exist
            Schema::table('farms', function (Blueprint $table) {
                if (!Schema::hasColumn('farms', 'farm_type')) {
                    $table->string('farm_type')->default('crop')->after('size_hectares');
                }
                if (!Schema::hasColumn('farms', 'soil_type')) {
                    $table->text('soil_type')->nullable()->after('farm_type');
                }
                if (!Schema::hasColumn('farms', 'irrigation_type')) {
                    $table->enum('irrigation_type', ['rain-fed', 'irrigated', 'mixed'])->default('rain-fed')->after('soil_type');
                }
                if (!Schema::hasColumn('farms', 'water_source')) {
                    $table->text('water_source')->nullable()->after('irrigation_type');
                }
                if (!Schema::hasColumn('farms', 'altitude')) {
                    $table->decimal('altitude', 8, 2)->nullable()->after('water_source');
                }
                if (!Schema::hasColumn('farms', 'images')) {
                    $table->json('images')->nullable()->after('altitude');
                }
                if (!Schema::hasColumn('farms', 'activities')) {
                    $table->json('activities')->nullable()->after('images');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('farms');
    }
};
