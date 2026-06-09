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
        Schema::create('import_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type'); // e.g., 'grades', 'courses', 'instructors', 'students', 'exam_schedules'
            $table->string('file_name');
            $table->string('file_path');
            $table->json('original_columns')->nullable(); // Detected columns from file
            $table->json('mapping')->nullable(); // User-defined mapping: { 'excel_col' => 'system_field' }
            $table->string('status')->default('pending'); // pending, validating, ready, importing, completed, failed
            $table->integer('total_rows')->default(0);
            $table->integer('processed_rows')->default(0);
            $table->json('errors')->nullable(); // Detailed error logs
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('import_jobs');
    }
};
