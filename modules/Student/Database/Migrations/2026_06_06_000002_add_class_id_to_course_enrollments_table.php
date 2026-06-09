<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_enrollments', function (Blueprint $table) {
            if (! Schema::hasColumn('course_enrollments', 'class_id')) {
                $table->foreignId('class_id')
                    ->nullable()
                    ->after('study_group_id')
                    ->constrained('course_classes')
                    ->nullOnDelete();

                $table->index(['student_id', 'class_id']);
            }
        });

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
        Schema::table('course_enrollments', function (Blueprint $table) {
            if (Schema::hasColumn('course_enrollments', 'class_id')) {
                $table->dropIndex(['student_id', 'class_id']);
                $table->dropConstrainedForeignId('class_id');
            }
        });
    }
};
