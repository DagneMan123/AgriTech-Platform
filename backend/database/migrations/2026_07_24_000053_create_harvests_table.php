<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('harvests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crop_id')->constrained('crops')->onDelete('cascade');
            $table->date('harvest_date');
            $table->decimal('quantity_harvested', 10, 2);
            $table->string('unit');
            $table->decimal('quality_grade', 3, 1)->nullable();
            $table->decimal('market_price_per_unit', 10, 2)->nullable();
            $table->decimal('total_harvest_value', 12, 2)->nullable();
            $table->integer('number_of_workers');
            $table->decimal('labor_cost', 10, 2)->nullable();
            $table->text('harvest_notes')->nullable();
            $table->enum('storage_method', ['fresh', 'dried', 'processed', 'stored'])->default('fresh');
            $table->text('post_harvest_treatment')->nullable();
            $table->timestamps();
            $table->index(['crop_id', 'harvest_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('harvests');
    }
};
