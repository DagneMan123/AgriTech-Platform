<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->integer('quantity_available');
            $table->decimal('wholesale_price', 10, 2);
            $table->integer('minimum_order_quantity');
            $table->enum('status', ['active', 'discontinued'])->default('active');
            $table->timestamps();
            $table->unique(['supplier_id', 'product_id']);
            $table->index(['supplier_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_products');
    }
};
