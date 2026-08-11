<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('report_name');
            $table->string('report_type');
            $table->foreignId('generated_by_user_id')->constrained('users')->onDelete('restrict');
            $table->enum('period', ['daily', 'weekly', 'monthly', 'quarterly', 'yearly', 'custom'])->default('monthly');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('file_path');
            $table->text('summary')->nullable();
            $table->json('data')->nullable();
            $table->enum('status', ['generating', 'ready', 'failed'])->default('generating');
            $table->timestamps();
            $table->index(['generated_by_user_id', 'report_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
