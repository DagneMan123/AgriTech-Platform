<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transporters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            $table->string('company_name');
            $table->string('license_plate')->nullable();
            $table->string('license_number')->nullable();
            $table->string('insurance_number')->nullable();
            $table->enum('vehicle_type', ['truck', 'van', 'motorcycle', 'cart', 'mixed'])->default('truck');
            $table->decimal('vehicle_capacity', 8, 2)->nullable(); // in kg
            $table->text('service_areas')->nullable(); // JSON regions covered
            $table->decimal('price_per_km', 8, 2)->nullable();
            $table->string('contact_person')->nullable();
            $table->string('emergency_contact')->nullable();
            $table->decimal('total_earnings', 15, 2)->default(0);
            $table->integer('completed_deliveries')->default(0);
            $table->decimal('average_rating', 3, 2)->nullable();
            $table->enum('verification_status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('vehicle_type');
            $table->index('verification_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transporters');
    }
};
