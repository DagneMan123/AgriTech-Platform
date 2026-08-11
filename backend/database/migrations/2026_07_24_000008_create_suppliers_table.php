<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            $table->string('company_name');
            $table->string('company_registration')->nullable();
            $table->string('tax_id')->nullable();
            $table->string('license_number')->nullable();
            $table->text('company_address')->nullable();
            $table->string('company_phone')->nullable();
            $table->string('company_website')->nullable();
            $table->text('company_bio')->nullable();
            $table->string('contact_person')->nullable();
            $table->enum('supply_type', ['seeds', 'fertilizer', 'pesticides', 'equipment', 'mixed'])->default('mixed');
            $table->decimal('total_sales', 15, 2)->default(0);
            $table->integer('completed_orders')->default(0);
            $table->decimal('average_rating', 3, 2)->nullable();
            $table->enum('verification_status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('supply_type');
            $table->index('verification_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
