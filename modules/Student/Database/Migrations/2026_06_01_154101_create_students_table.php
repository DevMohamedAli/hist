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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->char('registration_number', 9)->unique(); // مادة 12
            $table->string('full_name', 150);
            $table->char('national_id', 12)->unique();
            $table->enum('gender', ['Male', 'Female']);
            $table->string('nationality', 50);
            $table->date('birth_date');
            $table->string('mobile', 20)->nullable();
            $table->date('admission_date');
            $table->string('qualification', 100)->nullable();
            $table->foreignId('current_specialization_id')->nullable()->constrained('specializations')->onDelete('set null'); // ربط بسياق Academic
            $table->integer('current_semester_level')->default(1);
            $table->enum('status', ['Active', 'Suspended', 'Transferred_Out', 'Withdrawn', 'Dismissed', 'Graduated'])->default('Active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
