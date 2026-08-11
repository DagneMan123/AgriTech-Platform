<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('farms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farmer_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('region');
            $table->string('zone');
            $table->string('woreda');
            $table->string('kebele')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->decimal('size_hectares', 8, 2);
            $table->enum('land_type', ['owned', 'rented', 'leased'])->default('owned');
            $table->text('soil_type')->nullable();
            $table->enum('irrigation_type', ['rain-fed', 'irrigated', 'mixed'])->default('rain-fed');
            $table->text('activities')->nullable();
            $table->enum('status', ['active', 'inactive', 'archived'])->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['farmer_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('farms');
    }
};
