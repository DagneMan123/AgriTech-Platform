<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_applications', function (Blueprint $table) {
            $table->id();
            $table->string('application_number')->unique();
            $table->foreignId('farmer_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('financial_institution_id')->constrained('users')->onDelete('restrict');
            $table->decimal('requested_amount', 12, 2);
            $table->integer('loan_period_months');
            $table->decimal('annual_interest_rate', 5, 2);
            $table->string('loan_purpose');
            $table->enum('status', ['draft', 'submitted', 'under_review', 'approved', 'rejected', 'cancelled'])->default('draft');
            $table->text('application_notes')->nullable();
            $table->text('review_notes')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->index(['farmer_id', 'status']);
            $table->index(['financial_institution_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_applications');
    }
};
