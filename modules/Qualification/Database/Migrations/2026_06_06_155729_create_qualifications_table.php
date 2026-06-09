<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qualifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instructor_id')->nullable()->constrained('instructors')->nullOnDelete();
            $table->nullableMorphs('qualifiable');
            $table->string('degree_name');
            $table->string('institution');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qualifications');
    }
};
