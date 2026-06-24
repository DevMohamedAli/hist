<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('specializations', function (Blueprint $table) {
            $table->unique(['department_id', 'name'], 'specializations_department_name_unique');
            $table->index('department_id', 'specializations_department_id_index');
        });

        Schema::table('academic_semesters', function (Blueprint $table) {
            $table->index(['registration_start', 'registration_end'], 'academic_semesters_registration_window_index');
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->index('name', 'courses_name_index');
        });

        Schema::table('study_groups', function (Blueprint $table) {
            $table->unique(
                ['specialization_id', 'academic_semester_id', 'semester_level', 'group_name'],
                'study_groups_unique_group_per_semester'
            );
            $table->index(['academic_semester_id', 'specialization_id'], 'study_groups_semester_specialization_index');
        });

        Schema::table('course_classes', function (Blueprint $table) {
            $table->unique(
                ['course_id', 'semester_id', 'study_group_id'],
                'course_classes_unique_course_semester_group'
            );
            $table->index(['semester_id', 'instructor_id'], 'course_classes_semester_instructor_index');
            $table->index('study_group_id', 'course_classes_study_group_id_index');
        });

        Schema::table('course_specialization', function (Blueprint $table) {
            $table->index(
                ['specialization_id', 'semester_level'],
                'course_specialization_specialization_level_index'
            );
        });

        Schema::table('course_enrollments', function (Blueprint $table) {
            $table->unique(
                ['student_id', 'course_id', 'study_group_id'],
                'course_enrollments_student_course_group_unique'
            );
            $table->index(['student_id', 'status'], 'course_enrollments_student_status_index');
            $table->index(['course_id', 'status'], 'course_enrollments_course_status_index');
            $table->index('class_id', 'course_enrollments_class_id_index');
        });

        Schema::table('academic_warnings', function (Blueprint $table) {
            $table->unique(
                ['student_id', 'academic_semester_id', 'type'],
                'academic_warnings_student_semester_type_unique'
            );
            $table->index('academic_semester_id', 'academic_warnings_semester_id_index');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->index(['current_specialization_id', 'status'], 'students_specialization_status_index');
            $table->index(['status', 'current_semester_level'], 'students_status_semester_level_index');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex('students_specialization_status_index');
            $table->dropIndex('students_status_semester_level_index');
        });

        Schema::table('academic_warnings', function (Blueprint $table) {
            $table->dropUnique('academic_warnings_student_semester_type_unique');
            $table->dropIndex('academic_warnings_semester_id_index');
        });

        Schema::table('course_enrollments', function (Blueprint $table) {
            $table->dropUnique('course_enrollments_student_course_group_unique');
            $table->dropIndex('course_enrollments_student_status_index');
            $table->dropIndex('course_enrollments_course_status_index');
            $table->dropIndex('course_enrollments_class_id_index');
        });

        Schema::table('course_specialization', function (Blueprint $table) {
            $table->dropIndex('course_specialization_specialization_level_index');
        });

        Schema::table('course_classes', function (Blueprint $table) {
            $table->dropUnique('course_classes_unique_course_semester_group');
            $table->dropIndex('course_classes_semester_instructor_index');
            $table->dropIndex('course_classes_study_group_id_index');
        });

        Schema::table('study_groups', function (Blueprint $table) {
            $table->dropUnique('study_groups_unique_group_per_semester');
            $table->dropIndex('study_groups_semester_specialization_index');
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->dropIndex('courses_name_index');
        });

        Schema::table('academic_semesters', function (Blueprint $table) {
            $table->dropIndex('academic_semesters_registration_window_index');
        });

        Schema::table('specializations', function (Blueprint $table) {
            $table->dropUnique('specializations_department_name_unique');
            $table->dropIndex('specializations_department_id_index');
        });
    }
};
