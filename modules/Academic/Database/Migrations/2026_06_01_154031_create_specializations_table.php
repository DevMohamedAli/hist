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
        Schema::create('specializations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained('departments')->onDelete('cascade');
            $table->string('name', 100);
            $table->string('code', 20)->nullable()->unique();
            $table->text('description')->nullable();

            // إضافة حقل عدد السمسترات المطلوبة للتخرج (الافتراضي 6 فصول دراسية / 3 سنوات)
            $table->integer('semesters_count')->default(6);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('specializations');
    }
};
