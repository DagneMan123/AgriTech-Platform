<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('crop_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crop_id')->constrained('crops')->onDelete('cascade');
            $table->foreignId('farm_id')->constrained('farms')->onDelete('cascade');
            $table->foreignId('farmer_id')->constrained('farmers')->onDelete('cascade');
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

            // Indexes for better query performance
            $table->index(['crop_id', 'activity_date']);
            $table->index(['farm_id', 'activity_date']);
            $table->index(['farmer_id', 'activity_date']);
            $table->index('activity_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crop_activities');
    }
};
