<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->string('conversation_code')->unique();
            $table->foreignId('user_1_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('user_2_id')->constrained('users')->onDelete('cascade');
            $table->enum('conversation_type', ['direct', 'order_related', 'delivery_related', 'consultation'])->default('direct');
            $table->foreignId('related_order_id')->nullable()->constrained('orders')->onDelete('set null');
            $table->foreignId('related_delivery_id')->nullable()->constrained('deliveries')->onDelete('set null');
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();
            $table->unique(['user_1_id', 'user_2_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
