<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('products') && !Schema::hasColumn('products', 'origin_location')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('origin_location')->nullable()->default('Addis Ababa')->after('category');
            });
        }

        if (Schema::hasTable('supplier_products') && !Schema::hasColumn('supplier_products', 'origin_location')) {
            Schema::table('supplier_products', function (Blueprint $table) {
                $table->string('origin_location')->nullable()->default('Addis Ababa');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'origin_location')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('origin_location');
            });
        }

        if (Schema::hasTable('supplier_products') && Schema::hasColumn('supplier_products', 'origin_location')) {
            Schema::table('supplier_products', function (Blueprint $table) {
                $table->dropColumn('origin_location');
            });
        }
    }
};
