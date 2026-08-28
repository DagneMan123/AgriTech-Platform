<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('document_type'); // e.g., 'kebele_id', 'trade_license', 'driving_license', etc.
            $table->string('document_name'); // Original file name
            $table->string('file_path'); // Storage path
            $table->string('file_type'); // mime type
            $table->integer('file_size'); // in bytes
            $table->enum('verification_status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            $table->index(['user_id', 'document_type']);
            $table->index('verification_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_documents');
    }
};
