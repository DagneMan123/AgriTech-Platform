<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crop_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crop_id')->constrained('crops')->onDelete('cascade');
            $table->string('image_path');
            $table->string('image_url');
            $table->text('caption')->nullable();
            $table->date('photo_date')->nullable();
            $table->enum('image_type', ['growth_stage', 'disease', 'pest', 'harvest', 'general'])->default('general');
            $table->timestamps();
            $table->index('crop_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crop_images');
    }
};
