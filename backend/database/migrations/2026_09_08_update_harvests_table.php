<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Check if table exists and fix column names
        if (Schema::hasTable('harvests')) {
            Schema::table('harvests', function (Blueprint $table) {
                // Check if columns exist before dropping or renaming
                $columns = Schema::getColumns('harvests');
                $columnNames = array_column($columns, 'name');

                // If quantity_harvested exists, rename to quantity
                if (in_array('quantity_harvested', $columnNames) && !in_array('quantity', $columnNames)) {
                    $table->renameColumn('quantity_harvested', 'quantity');
                }

                // Make notes column if it doesn't exist
                if (!in_array('notes', $columnNames)) {
                    $table->text('notes')->nullable()->after('quality_grade');
                }

                // If harvest_notes exists, we can keep it for backward compatibility
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('harvests')) {
            Schema::table('harvests', function (Blueprint $table) {
                $columns = Schema::getColumns('harvests');
                $columnNames = array_column($columns, 'name');

                // Reverse the rename if needed
                if (in_array('quantity', $columnNames) && !in_array('quantity_harvested', $columnNames)) {
                    $table->renameColumn('quantity', 'quantity_harvested');
                }
            });
        }
    }
};
