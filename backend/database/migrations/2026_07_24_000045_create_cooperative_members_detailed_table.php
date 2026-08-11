<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cooperative_member_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cooperative_id')->constrained('cooperatives')->onDelete('cascade');
            $table->foreignId('farmer_id')->constrained('users')->onDelete('cascade');
            $table->enum('membership_status', ['active', 'inactive', 'suspended'])->default('active');
            $table->date('joined_date');
            $table->date('left_date')->nullable();
            $table->decimal('share_amount', 10, 2)->default(0);
            $table->integer('share_count')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['cooperative_id', 'farmer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cooperative_member_details');
    }
};
