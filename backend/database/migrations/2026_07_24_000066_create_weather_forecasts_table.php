<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weather_forecasts', function (Blueprint $table) {
            $table->id();
            $table->string('location');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->date('forecast_date');
            $table->integer('hour')->nullable();
            $table->decimal('temperature_celsius', 5, 2);
            $table->decimal('feels_like_celsius', 5, 2)->nullable();
            $table->integer('humidity_percentage');
            $table->string('weather_condition');
            $table->text('weather_description')->nullable();
            $table->decimal('rainfall_mm', 8, 2)->default(0);
            $table->decimal('wind_speed_kmh', 8, 2);
            $table->integer('wind_direction_degrees')->nullable();
            $table->integer('pressure_mb')->nullable();
            $table->integer('cloud_coverage_percentage')->nullable();
            $table->decimal('uv_index', 3, 1)->nullable();
            $table->timestamp('fetched_at');
            $table->timestamps();
            $table->index(['location', 'forecast_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weather_forecasts');
    }
};
