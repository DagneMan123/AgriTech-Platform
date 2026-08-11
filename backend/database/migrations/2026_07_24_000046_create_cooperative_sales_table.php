<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cooperative_sales', function (Blueprint $table) {
            $table->id();
            $table->string('sale_number')->unique();
            $table->foreignId('cooperative_id')->constrained()->onDelete('restrict');
            $table->foreignId('buyer_id')->constrained('users')->onDelete('restrict');
            $table->decimal('total_amount', 12, 2);
            $table->enum('status', ['pending', 'confirmed', 'delivered', 'cancelled'])->default('pending');
            $table->date('sale_date');
            $table->date('delivery_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['cooperative_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cooperative_sales');
    }
};
