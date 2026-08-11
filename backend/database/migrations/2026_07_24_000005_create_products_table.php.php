// database/migrations/2024_01_01_000005_create_products_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farmer_id')->constrained('farmers')->onDelete('cascade');
            $table->foreignId('crop_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('product_category_id')->nullable()->constrained()->onDelete('set null');
            $table->string('name');
            $table->text('description');
            $table->decimal('price', 10, 2);
            $table->string('price_unit')->default('ETB/kg');
            $table->decimal('quantity', 10, 2);
            $table->string('quantity_unit')->default('kg');
            $table->string('category');
            $table->string('quality_grade')->nullable();
            $table->string('harvest_date')->nullable();
            $table->json('images')->nullable();
            $table->string('status')->default('available');
            $table->boolean('is_organic')->default(false);
            $table->string('certification')->nullable();
            $table->json('specifications')->nullable();
            $table->integer('views_count')->default(0);
            $table->timestamps();

            $table->index(['category', 'status']);
            $table->index('price');
            $table->index('name');
            $table->index('created_at');
        });

        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('image_path');
            $table->string('caption')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
        });

        Schema::create('wishlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->unique(['user_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wishlists');
        Schema::dropIfExists('product_images');
        Schema::dropIfExists('products');
        Schema::dropIfExists('product_categories');
    }
};
