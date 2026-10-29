<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->string('contract_number')->unique();
            $table->foreignId('buyer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('farmer_id')->constrained('farmers')->onDelete('cascade');
            $table->string('title');
            $table->string('crop_type');
            $table->string('variety')->nullable();
            $table->decimal('quantity', 12, 2);
            $table->string('unit')->default('Quintal');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('total_amount', 15, 2);
            $table->decimal('advance_payment', 15, 2)->default(0);
            $table->string('currency')->default('ETB');
            $table->date('start_date');
            $table->date('delivery_date');
            $table->string('delivery_location')->nullable();
            $table->text('quality_specifications')->nullable();
            $table->string('status')->default('proposed'); // draft, proposed, active, in_progress, completed, cancelled
            $table->string('payment_terms')->nullable();
            $table->text('terms_and_conditions')->nullable();
            $table->text('farmer_notes')->nullable();
            $table->timestamp('signed_by_buyer_at')->nullable();
            $table->timestamp('signed_by_farmer_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['buyer_id', 'status']);
            $table->index(['farmer_id', 'status']);
            $table->index('contract_number');
        });

        Schema::create('contract_milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained('contracts')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('due_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->string('status')->default('pending'); // pending, in_progress, completed
            $table->decimal('payout_percentage', 5, 2)->default(0);
            $table->decimal('payout_amount', 12, 2)->default(0);
            $table->string('payout_status')->default('unpaid'); // unpaid, in_escrow, paid
            $table->timestamps();

            $table->index(['contract_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_milestones');
        Schema::dropIfExists('contracts');
    }
};
