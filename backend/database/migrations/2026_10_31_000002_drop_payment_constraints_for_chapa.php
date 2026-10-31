<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop the restrictive enum check constraints on payments table
        DB::statement('ALTER TABLE payments DROP CONSTRAINT IF EXISTS payments_payment_method_check;');
        DB::statement('ALTER TABLE payments DROP CONSTRAINT IF EXISTS payments_status_check;');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-add check constraints if needed
        DB::statement("ALTER TABLE payments ADD CONSTRAINT payments_payment_method_check CHECK (payment_method IN ('telebirr', 'cbe_birr', 'bank_transfer', 'cash_on_delivery', 'mobile_money', 'chapa', 'chapa_digital', 'card'));");
        DB::statement("ALTER TABLE payments ADD CONSTRAINT payments_status_check CHECK (status IN ('pending', 'completed', 'paid', 'failed', 'refunded'));");
    }
};
