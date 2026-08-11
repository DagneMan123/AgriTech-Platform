<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transport_provider_id')->constrained('users')->onDelete('cascade');
            $table->string('vehicle_type');
            $table->string('registration_number')->unique();
            $table->string('make');
            $table->string('model');
            $table->year('year');
            $table->decimal('capacity_tons', 8, 2);
            $table->enum('status', ['active', 'inactive', 'maintenance'])->default('active');
            $table->string('insurance_number')->nullable();
            $table->date('insurance_expiry')->nullable();
            $table->string('license_number')->nullable();
            $table->date('license_expiry')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index('transport_provider_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
