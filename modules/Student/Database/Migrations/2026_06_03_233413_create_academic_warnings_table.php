<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_warnings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('academic_semester_id')->constrained('academic_semesters');

            // نوع القرار: إنذار عادي، إنذار نهائي، أو قرار فصل
            $table->enum('type', ['Warning', 'Final_Warning', 'Dismissal']);

            // سبب الإنذار أو الفصل (لتوثيقه للإدارة)
            $table->string('reason');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_warnings');
    }
};
