<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('farmers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            $table->string('farmer_registration_number')->nullable()->unique();
            $table->string('farm_name')->nullable();
            $table->string('region');
            $table->string('zone');
            $table->string('woreda');
            $table->string('kebele')->nullable();
            $table->decimal('farm_size', 8, 2)->nullable(); // in hectares
            $table->enum('farm_type', ['crop', 'livestock', 'mixed', 'fishery'])->default('mixed');
            $table->integer('years_of_experience')->nullable();
            $table->text('bio')->nullable();
            $table->string('bank_account')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('cooperative_name')->nullable();
            $table->decimal('total_earnings', 15, 2)->default(0);
            $table->integer('completed_orders')->default(0);
            $table->decimal('average_rating', 3, 2)->nullable();
            $table->enum('verification_status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('region');
            $table->index('verification_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('farmers');
    }
};
