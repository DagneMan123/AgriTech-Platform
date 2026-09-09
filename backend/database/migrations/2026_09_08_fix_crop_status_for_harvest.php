<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Update any crops that are not ready for harvest to 'growing'
        // This fixes the issue where crops exist but aren't showing in harvest form
        DB::table('crops')
            ->whereNotIn('status', ['growing', 'ready_for_harvest', 'planted'])
            ->update(['status' => 'growing']);
    }

    public function down(): void
    {
        // Rollback is not needed as we're just fixing statuses
    }
};
