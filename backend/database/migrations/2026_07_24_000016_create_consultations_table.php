<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farmer_id')->constrained()->onDelete('cascade');
            $table->foreignId('expert_id')->constrained()->onDelete('cascade');
            $table->string('topic');
            $table->text('description');
            $table->enum('consultation_type', ['chat', 'call', 'video', 'visit'])->default('chat');
            $table->enum('status', ['pending', 'scheduled', 'completed', 'cancelled'])->default('pending');
            $table->timestamp('scheduled_date')->nullable();
            $table->text('expert_response')->nullable();
            $table->text('recommendations')->nullable();
            $table->decimal('consultation_fee', 10, 2)->nullable();
            $table->enum('payment_status', ['pending', 'completed', 'refunded'])->default('pending');
            $table->integer('rating')->nullable(); // 1-5
            $table->text('feedback')->nullable();
            $table->timestamps();

            $table->index(['farmer_id', 'status']);
            $table->index(['expert_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultations');
    }
};
