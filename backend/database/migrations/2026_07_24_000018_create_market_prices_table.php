<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('market_prices', function (Blueprint $table) {
            $table->id();
            $table->string('crop_name');
            $table->string('market_location'); // City/woreda
            $table->string('market_region');
            $table->decimal('price_per_unit', 12, 2);
            $table->string('unit'); // kg, bag, bunch, etc.
            $table->enum('price_trend', ['stable', 'increasing', 'decreasing'])->default('stable');
            $table->date('price_date');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['crop_name', 'market_location', 'price_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('market_prices');
    }
};
