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
        Schema::table('import_jobs', function (Blueprint $table) {
            // Add column to track which row contains the headers
            if (!Schema::hasColumn('import_jobs', 'header_row_index')) {
                $table->integer('header_row_index')->default(0)->after('original_columns');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('import_jobs', function (Blueprint $table) {
            if (Schema::hasColumn('import_jobs', 'header_row_index')) {
                $table->dropColumn('header_row_index');
            }
        });
    }
};
