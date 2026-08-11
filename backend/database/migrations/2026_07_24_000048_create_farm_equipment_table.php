<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('farm_equipment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained('farms')->onDelete('cascade');
            $table->string('equipment_type');
            $table->string('equipment_name');
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable();
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_price', 10, 2)->nullable();
            $table->enum('condition', ['new', 'good', 'fair', 'poor'])->default('good');
            $table->enum('status', ['operational', 'maintenance', 'broken', 'stored'])->default('operational');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['farm_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('farm_equipment');
    }
};
