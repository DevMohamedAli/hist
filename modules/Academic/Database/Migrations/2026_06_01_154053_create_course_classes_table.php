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
        Schema::create('course_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->foreignId('semester_id')->constrained('academic_semesters')->onDelete('cascade');
            $table->foreignId('instructor_id')->constrained('instructors')->onDelete('cascade'); // ربط بسياق Staff
            $table->string('group_name', 50); // مجموعة 1، مجموعة ب
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_classes');
    }
};
