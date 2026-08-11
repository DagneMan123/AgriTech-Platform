<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('reviewer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('reviewed_user_id')->constrained('users')->onDelete('cascade');
            $table->enum('review_type', ['product', 'service', 'delivery'])->default('product');
            $table->integer('rating'); // 1-5
            $table->text('comment')->nullable();
            $table->json('review_attributes')->nullable(); // JSON for different attributes
            $table->string('status')->default('approved'); // pending, approved, rejected
            $table->timestamps();

            $table->index(['reviewed_user_id', 'review_type']);
            $table->index(['reviewer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
