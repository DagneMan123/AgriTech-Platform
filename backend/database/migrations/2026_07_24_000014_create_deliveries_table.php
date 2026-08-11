<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('transporter_id')->constrained()->onDelete('restrict');
            $table->string('tracking_number')->nullable()->unique();
            $table->enum('status', ['pending', 'assigned', 'picked_up', 'in_transit', 'delivered', 'failed'])->default('pending');
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('picked_up_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->text('pickup_location')->nullable();
            $table->text('delivery_location')->nullable();
            $table->decimal('latitude_start', 10, 8)->nullable();
            $table->decimal('longitude_start', 11, 8)->nullable();
            $table->decimal('latitude_end', 10, 8)->nullable();
            $table->decimal('longitude_end', 11, 8)->nullable();
            $table->decimal('distance_km', 8, 2)->nullable();
            $table->decimal('delivery_fee', 10, 2)->nullable();
            $table->string('signature_image')->nullable();
            $table->text('notes')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'status']);
            $table->index('transporter_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};
