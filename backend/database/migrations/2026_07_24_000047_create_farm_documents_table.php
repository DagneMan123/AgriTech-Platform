<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('farm_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained('farms')->onDelete('cascade');
            $table->string('document_type');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file_path');
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->enum('status', ['valid', 'expired', 'pending_renewal'])->default('valid');
            $table->timestamps();
            $table->index(['farm_id', 'document_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('farm_documents');
    }
};
