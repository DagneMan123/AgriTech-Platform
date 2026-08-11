<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('location_type');
            $table->string('region');
            $table->string('zone');
            $table->string('woreda');
            $table->string('kebele')->nullable();
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->text('description')->nullable();
            $table->timestamps();
            $table->index(['region', 'zone', 'woreda']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
