<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasIndex('academic_semesters', 'academic_semesters_season_year_unique')) {
            Schema::table('academic_semesters', function (Blueprint $table) {
                $table->dropUnique('academic_semesters_season_year_unique');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasIndex('academic_semesters', 'academic_semesters_season_year_unique')) {
            Schema::table('academic_semesters', function (Blueprint $table) {
                $table->unique(['season', 'year'], 'academic_semesters_season_year_unique');
            });
        }
    }
};
