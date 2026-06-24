<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('graduation_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('specialization_id')->constrained('specializations')->cascadeOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('certificate_number')->unique();
            $table->date('graduation_date');
            $table->decimal('cgpa', 5, 2);
            $table->integer('total_units')->default(0);
            $table->enum('status', ['pending_review', 'approved'])->default('approved');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique('student_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('graduation_records');
    }
};
