<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_materials', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->foreignId('expert_id')->constrained('users')->onDelete('restrict');
            $table->enum('content_type', ['article', 'video', 'document', 'image'])->default('article');
            $table->string('content_url')->nullable();
            $table->string('thumbnail_url')->nullable();
            $table->text('content_text')->nullable();
            $table->string('category');
            $table->integer('view_count')->default(0);
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->date('published_date')->nullable();
            $table->timestamps();
            $table->index(['expert_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_materials');
    }
};
