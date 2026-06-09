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
        Schema::create('academic_semesters', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique(); // SPRING-2026
            $table->enum('season', ['Spring', 'Fall', 'Summer', 'Winter']);
            $table->year('year');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->date('registration_start')->nullable();
            $table->date('registration_end')->nullable();
            $table->date('final_exams_start')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_semesters');
    }
};
