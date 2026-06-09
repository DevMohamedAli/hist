<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_classes', function (Blueprint $table) {
            if (! Schema::hasColumn('course_classes', 'study_group_id')) {
                $table->foreignId('study_group_id')
                    ->nullable()
                    ->after('semester_id')
                    ->constrained('study_groups')
                    ->nullOnDelete();
            }
        });

        DB::table('course_classes')
            ->whereNull('study_group_id')
            ->orderBy('id')
            ->get(['id', 'semester_id', 'group_name'])
            ->each(function ($courseClass) {
                $studyGroupId = DB::table('study_groups')
                    ->where('academic_semester_id', $courseClass->semester_id)
                    ->where('group_name', $courseClass->group_name)
                    ->value('id');

                if ($studyGroupId) {
                    DB::table('course_classes')
                        ->where('id', $courseClass->id)
                        ->update(['study_group_id' => $studyGroupId]);
                }
            });

        Schema::table('course_classes', function (Blueprint $table) {
            if (Schema::hasColumn('course_classes', 'study_group_id')) {
                $table->unique(['course_id', 'semester_id', 'study_group_id'], 'course_classes_unique_group_course');
            }
        });
    }

    public function down(): void
    {
        Schema::table('course_classes', function (Blueprint $table) {
            if (Schema::hasColumn('course_classes', 'study_group_id')) {
                $table->dropUnique('course_classes_unique_group_course');
                $table->dropConstrainedForeignId('study_group_id');
            }
        });
    }
};
