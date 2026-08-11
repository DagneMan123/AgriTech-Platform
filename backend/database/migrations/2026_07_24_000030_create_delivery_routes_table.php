<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_routes', function (Blueprint $table) {
            $table->id();
            $table->string('route_name');
            $table->string('start_location');
            $table->string('end_location');
            $table->decimal('start_latitude', 10, 8);
            $table->decimal('start_longitude', 11, 8);
            $table->decimal('end_latitude', 10, 8);
            $table->decimal('end_longitude', 11, 8);
            $table->decimal('distance_km', 8, 2);
            $table->integer('estimated_time_minutes');
            $table->decimal('cost_per_km', 8, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_routes');
    }
};
