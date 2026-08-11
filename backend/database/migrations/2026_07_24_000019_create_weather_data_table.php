<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weather_data', function (Blueprint $table) {
            $table->id();
            $table->string('region');
            $table->string('zone')->nullable();
            $table->string('woreda')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->decimal('temperature', 5, 2);
            $table->decimal('min_temperature', 5, 2)->nullable();
            $table->decimal('max_temperature', 5, 2)->nullable();
            $table->decimal('humidity', 5, 2);
            $table->decimal('rainfall', 8, 2)->nullable(); // in mm
            $table->string('weather_condition'); // sunny, rainy, cloudy, etc.
            $table->decimal('wind_speed', 5, 2)->nullable(); // km/h
            $table->string('wind_direction')->nullable();
            $table->decimal('uv_index', 3, 1)->nullable();
            $table->date('forecast_date');
            $table->timestamps();

            $table->index(['region', 'forecast_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weather_data');
    }
};
