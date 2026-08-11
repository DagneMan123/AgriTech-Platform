<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crop_growth_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crop_id')->constrained('crops')->onDelete('cascade');
            $table->date('record_date');
            $table->string('growth_stage');
            $table->string('height_cm')->nullable();
            $table->string('health_status');
            $table->text('observations')->nullable();
            $table->decimal('moisture_level', 5, 2)->nullable();
            $table->decimal('ph_level', 4, 2)->nullable();
            $table->string('pest_disease')->nullable();
            $table->text('treatment_applied')->nullable();
            $table->string('photographed_by')->nullable();
            $table->string('photo_path')->nullable();
            $table->timestamps();
            $table->index(['crop_id', 'record_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crop_growth_records');
    }
};
