<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (! Schema::hasColumn('students', 'current_study_group_id')) {
                $table->foreignId('current_study_group_id')
                    ->nullable()
                    ->after('current_specialization_id')
                    ->constrained('study_groups')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (Schema::hasColumn('students', 'current_study_group_id')) {
                $table->dropConstrainedForeignId('current_study_group_id');
            }
        });
    }
};
