<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('farm_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained('farms')->onDelete('cascade');
            $table->foreignId('crop_id')->nullable()->constrained('crops')->onDelete('set null');
            $table->string('activity_type');
            $table->text('description');
            $table->date('activity_date');
            $table->decimal('cost', 10, 2)->nullable();
            $table->string('performed_by')->nullable();
            $table->text('observations')->nullable();
            $table->string('weather_condition')->nullable();
            $table->timestamps();
            $table->index(['farm_id', 'activity_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('farm_activities');
    }
};
