<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transport_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('transport_requests', 'buyer_id')) {
                $table->foreignId('buyer_id')->nullable()->after('farmer_id')->constrained('users')->onDelete('cascade');
            }
        });

        // Make farmer_id nullable so buyers can create transport requests
        try {
            DB::statement('ALTER TABLE transport_requests ALTER COLUMN farmer_id DROP NOT NULL;');
        } catch (\Throwable $e) {
            // ignore if already nullable
        }
    }

    public function down(): void
    {
        Schema::table('transport_requests', function (Blueprint $table) {
            if (Schema::hasColumn('transport_requests', 'buyer_id')) {
                $table->dropForeign(['buyer_id']);
                $table->dropColumn('buyer_id');
            }
        });
    }
};
