<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Make order_id nullable in payments table using raw PostgreSQL SQL
        DB::statement('ALTER TABLE payments ALTER COLUMN order_id DROP NOT NULL;');

        // 2. Add missing columns to payments table
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->onDelete('cascade');
            }
            if (!Schema::hasColumn('payments', 'payment_number')) {
                $table->string('payment_number')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('payments', 'currency')) {
                $table->string('currency', 10)->default('ETB')->after('amount');
            }
            if (!Schema::hasColumn('payments', 'payment_gateway')) {
                $table->string('payment_gateway', 50)->default('chapa')->after('payment_method');
            }
            if (!Schema::hasColumn('payments', 'reference')) {
                $table->string('reference')->nullable()->after('transaction_id');
            }
            if (!Schema::hasColumn('payments', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('completed_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'payment_number', 'currency', 'payment_gateway', 'reference', 'paid_at']);
        });
    }
};
