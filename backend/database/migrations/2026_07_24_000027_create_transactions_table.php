<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_number')->unique();
            $table->foreignId('from_user_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('to_user_id')->constrained('users')->onDelete('restrict');
            $table->decimal('amount', 12, 2);
            $table->enum('type', ['payment', 'refund', 'commission', 'withdrawal'])->default('payment');
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->index('transaction_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
