<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'escrow_status')) {
                $table->string('escrow_status', 30)->default('held')->after('payment_status');
            }
            if (!Schema::hasColumn('orders', 'escrow_held_at')) {
                $table->timestamp('escrow_held_at')->nullable()->after('escrow_status');
            }
            if (!Schema::hasColumn('orders', 'escrow_disbursed_at')) {
                $table->timestamp('escrow_disbursed_at')->nullable()->after('escrow_held_at');
            }
            if (!Schema::hasColumn('orders', 'payout_reference')) {
                $table->string('payout_reference')->nullable()->after('escrow_disbursed_at');
            }
            if (!Schema::hasColumn('orders', 'payout_account_details')) {
                $table->json('payout_account_details')->nullable()->after('payout_reference');
            }
        });

        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'escrow_status')) {
                $table->string('escrow_status', 30)->default('held')->after('status');
            }
            if (!Schema::hasColumn('payments', 'escrow_held_at')) {
                $table->timestamp('escrow_held_at')->nullable()->after('escrow_status');
            }
            if (!Schema::hasColumn('payments', 'escrow_disbursed_at')) {
                $table->timestamp('escrow_disbursed_at')->nullable()->after('escrow_held_at');
            }
            if (!Schema::hasColumn('payments', 'escrow_custody_account')) {
                $table->string('escrow_custody_account')->default('Chapa & AgriConnect Escrow Trust')->after('escrow_disbursed_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['escrow_status', 'escrow_held_at', 'escrow_disbursed_at', 'payout_reference', 'payout_account_details']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['escrow_status', 'escrow_held_at', 'escrow_disbursed_at', 'escrow_custody_account']);
        });
    }
};
