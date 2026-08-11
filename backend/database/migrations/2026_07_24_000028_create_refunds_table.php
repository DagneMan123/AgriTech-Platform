<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->string('refund_number')->unique();
            $table->foreignId('payment_id')->constrained()->onDelete('restrict');
            $table->foreignId('order_id')->constrained()->onDelete('restrict');
            $table->decimal('amount', 12, 2);
            $table->enum('status', ['requested', 'approved', 'rejected', 'completed'])->default('requested');
            $table->text('reason')->nullable();
            $table->text('admin_notes')->nullable();
            $table->foreignId('requested_by_user_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->index('refund_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};
