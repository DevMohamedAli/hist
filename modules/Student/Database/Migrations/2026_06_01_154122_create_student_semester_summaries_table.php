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
        Schema::create('student_semester_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('semester_id')->constrained('academic_semesters')->onDelete('cascade'); // ربط بسياق Academic
            $table->decimal('semester_gpa', 5, 2);
            $table->decimal('cumulative_gpa', 5, 2); // المعدل التراكمي للتحقق من مادة 15
            $table->integer('total_registered_units'); // مجموع الوحدات للتحقق من مادة 15 (كحد أقصى 24 وحدة)
            $table->integer('carried_courses_count')->default(0); // عدد المواد المحمولة
            $table->unique(['student_id', 'semester_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_semester_summaries');
    }
};
