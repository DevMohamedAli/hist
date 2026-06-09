<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_specialization', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->foreignId('specialization_id')->constrained('specializations')->onDelete('cascade');

            // في أي سمستر تدرس هذه المادة داخل هذا التخصص تحديداً؟ (تطبيق المادة 18)
            $table->integer('semester_level');

            $table->timestamps();

            // منع تكرار ربط نفس المادة بنفس التخصص في خطة الدراسة
            $table->unique(['course_id', 'specialization_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_specialization');
    }
};
