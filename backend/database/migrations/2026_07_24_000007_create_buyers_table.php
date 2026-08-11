<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buyers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            $table->enum('buyer_type', ['individual', 'restaurant', 'hotel', 'supermarket', 'exporter', 'wholesaler', 'retailer'])->default('individual');
            $table->string('business_name')->nullable();
            $table->string('business_registration')->nullable();
            $table->string('tax_id')->nullable();
            $table->text('business_address')->nullable();
            $table->string('business_phone')->nullable();
            $table->text('bio')->nullable();
            $table->decimal('total_spent', 15, 2)->default(0);
            $table->integer('total_orders')->default(0);
            $table->decimal('average_rating', 3, 2)->nullable();
            $table->enum('verification_status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->boolean('is_premium')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index('buyer_type');
            $table->index('verification_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buyers');
    }
};
