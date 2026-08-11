<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('farm_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained('farms')->onDelete('cascade');
            $table->string('image_path');
            $table->string('image_url');
            $table->text('caption')->nullable();
            $table->date('photo_date')->nullable();
            $table->enum('image_type', ['overview', 'crop', 'equipment', 'workers', 'general'])->default('general');
            $table->timestamps();
            $table->index('farm_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('farm_images');
    }
};
