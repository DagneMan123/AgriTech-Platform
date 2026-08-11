<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('experts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            $table->string('expert_registration_number')->nullable()->unique();
            $table->string('specialization'); // agronomist, veterinarian, etc.
            $table->string('qualification')->nullable(); // degree/diploma
            $table->text('institution')->nullable(); // university/institute
            $table->integer('years_of_experience')->nullable();
            $table->text('bio')->nullable();
            $table->string('office_address')->nullable();
            $table->string('office_phone')->nullable();
            $table->text('expertise_areas')->nullable(); // JSON array of crops/areas
            $table->decimal('consultation_fee', 10, 2)->nullable();
            $table->integer('total_consultations')->default(0);
            $table->decimal('average_rating', 3, 2)->nullable();
            $table->enum('verification_status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->boolean('available_for_consultation')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('specialization');
            $table->index('verification_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('experts');
    }
};
