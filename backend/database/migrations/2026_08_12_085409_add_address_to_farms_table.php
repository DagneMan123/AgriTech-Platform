<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('farms', function (Blueprint $table) {
            // address የሚለው ኮሎን ከሌለ ብቻ እንዲጨምር ማድረግ
            if (!Schema::hasColumn('farms', 'address')) {
                $table->string('address')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('farms', function (Blueprint $table) {
            if (Schema::hasColumn('farms', 'address')) {
                $table->dropColumn('address');
            }
        });
    }
};