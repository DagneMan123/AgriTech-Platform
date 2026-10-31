<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('harvests', function (Blueprint $table) {
            DB::statement("ALTER TABLE harvests ALTER COLUMN quality_grade TYPE VARCHAR(50) USING quality_grade::varchar");
        });
    }

    public function down(): void
    {
        Schema::table('harvests', function (Blueprint $table) {
            DB::statement("ALTER TABLE harvests ALTER COLUMN quality_grade TYPE NUMERIC(3,1) USING quality_grade::numeric");
        });
    }
};
