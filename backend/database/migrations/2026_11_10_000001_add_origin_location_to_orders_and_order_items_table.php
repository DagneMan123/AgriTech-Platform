<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (!Schema::hasColumn('orders', 'origin_location')) {
                    $table->string('origin_location')->nullable()->after('delivery_address');
                }
                if (!Schema::hasColumn('orders', 'pickup_location')) {
                    $table->string('pickup_location')->nullable()->after('origin_location');
                }
            });
        }

        if (Schema::hasTable('order_items')) {
            Schema::table('order_items', function (Blueprint $table) {
                if (!Schema::hasColumn('order_items', 'origin_location')) {
                    $table->string('origin_location')->nullable()->after('subtotal');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (Schema::hasColumn('orders', 'origin_location')) {
                    $table->dropColumn('origin_location');
                }
                if (Schema::hasColumn('orders', 'pickup_location')) {
                    $table->dropColumn('pickup_location');
                }
            });
        }

        if (Schema::hasTable('order_items')) {
            Schema::table('order_items', function (Blueprint $table) {
                if (Schema::hasColumn('order_items', 'origin_location')) {
                    $table->dropColumn('origin_location');
                }
            });
        }
    }
};
