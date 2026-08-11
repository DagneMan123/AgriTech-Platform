<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_licenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('users')->onDelete('cascade');
            $table->string('license_number')->unique();
            $table->string('license_type');
            $table->date('issued_date');
            $table->date('expiry_date');
            $table->string('issuing_authority');
            $table->string('document_path')->nullable();
            $table->enum('status', ['active', 'expired', 'revoked', 'suspended'])->default('active');
            $table->timestamps();
            $table->index('supplier_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_licenses');
    }
};
