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
        Schema::create('study_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('specialization_id')->constrained('specializations')->onDelete('cascade');
            $table->foreignId('academic_semester_id')->constrained('academic_semesters')->onDelete('cascade');
            $table->integer('semester_level'); // مستوى الدفعة (1، 2، 3...)
            $table->string('group_name', 50); // أ، ب، ج
            $table->integer('capacity')->default(50); // السعة الاستيعابية
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('study_groups');
    }
};
