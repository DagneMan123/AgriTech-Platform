<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('insurance_policies', function (Blueprint $table) {
            $table->id();
            $table->string('policy_number')->unique();
            $table->foreignId('farmer_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('financial_institution_id')->constrained('users')->onDelete('restrict');
            $table->string('insurance_type');
            $table->decimal('coverage_amount', 12, 2);
            $table->decimal('premium_amount', 10, 2);
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['active', 'expired', 'lapsed', 'cancelled'])->default('active');
            $table->text('terms_conditions')->nullable();
            $table->timestamps();
            $table->index(['farmer_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insurance_policies');
    }
};
