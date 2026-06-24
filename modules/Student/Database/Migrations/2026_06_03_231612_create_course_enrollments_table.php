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
        Schema::create('course_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('study_group_id')->constrained('study_groups')->onDelete('cascade');
            $table->foreignId('class_id')->constrained('course_classes')->onDelete('cascade'); // تأكد من إضافته للربط مع الشعبة
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');

            // الحقول الخام (هنا يتم تخزين 40 و 25)
            $table->decimal('raw_semester_work', 5, 2)->default(0);
            $table->decimal('raw_final_exam', 5, 2)->default(0);

            // المجموع الكلي (حقل عادي لنتمكن من حفظه وتحديثه)
            $table->decimal('total_mark', 5, 2)->default(0);

            // البيانات الأكاديمية
            $table->boolean('is_carried')->default(false);
            $table->string('grade_evaluation', 50)->nullable();
            $table->enum('status', ['Pending', 'Passed', 'Failed'])->default('Pending');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_enrollments');
    }
};
