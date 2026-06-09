<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('course_enrollments', 'class_id')) {
            return;
        }

        DB::table('course_enrollments')
            ->whereNull('class_id')
            ->orderBy('id')
            ->get(['id', 'course_id', 'study_group_id'])
            ->each(function ($enrollment) {
                $classId = DB::table('course_classes')
                    ->where('course_id', $enrollment->course_id)
                    ->where('study_group_id', $enrollment->study_group_id)
                    ->value('id');

                if ($classId) {
                    DB::table('course_enrollments')
                        ->where('id', $enrollment->id)
                        ->update(['class_id' => $classId]);
                }
            });
    }

    public function down(): void
    {
        // Data backfill only; keep any existing enrollment/class links intact on rollback.
    }
};
