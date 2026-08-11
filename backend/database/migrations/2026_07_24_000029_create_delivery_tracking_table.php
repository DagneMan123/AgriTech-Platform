<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_tracking', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_id')->constrained('deliveries')->onDelete('cascade');
            $table->string('status');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('location_description')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('tracked_at');
            $table->timestamps();
            $table->index(['delivery_id', 'tracked_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_tracking');
    }
};
