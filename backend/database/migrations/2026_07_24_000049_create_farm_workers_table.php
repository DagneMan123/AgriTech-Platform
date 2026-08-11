<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('farm_workers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained('farms')->onDelete('cascade');
            $table->string('worker_name');
            $table->string('phone')->nullable();
            $table->enum('employment_type', ['permanent', 'seasonal', 'casual'])->default('casual');
            $table->date('employment_start_date');
            $table->date('employment_end_date')->nullable();
            $table->decimal('daily_wage', 8, 2)->nullable();
            $table->enum('status', ['active', 'inactive', 'terminated'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['farm_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('farm_workers');
    }
};
