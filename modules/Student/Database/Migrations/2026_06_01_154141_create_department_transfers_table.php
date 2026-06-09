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
        Schema::create('department_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('from_specialization_id')->constrained('specializations')->onDelete('restrict'); // ربط بسياق Academic
            $table->foreignId('to_specialization_id')->constrained('specializations')->onDelete('restrict');
            $table->date('transfer_date');
            $table->string('approval_reference', 150); // مادة 16
            $table->timestamps();
            $table->unique('student_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('department_transfers');
    }
};
