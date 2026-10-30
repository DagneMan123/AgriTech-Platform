<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->string('type')->default('warehouse'); // warehouse, cold_storage, retail, factory, office, farm_hub
            $table->string('contact_name');
            $table->string('contact_role')->nullable();
            $table->string('contact_phone');
            $table->string('secondary_phone')->nullable();
            $table->string('region')->default('Addis Ababa');
            $table->string('city')->default('Addis Ababa');
            $table->string('sub_city')->nullable();
            $table->string('woreda_kebele')->nullable();
            $table->text('street_address');
            $table->string('landmark')->nullable();
            $table->string('postal_code')->nullable();
            $table->boolean('is_default')->default(false);
            $table->text('instructions')->nullable();
            $table->string('receiving_hours')->nullable();
            $table->json('capabilities')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->integer('total_deliveries')->default(0);
            $table->timestamp('last_delivery_date')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'is_default']);
            $table->index(['user_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_addresses');
    }
};
