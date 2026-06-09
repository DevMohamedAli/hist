<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Staff\Models\Instructor;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qualificationables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('qualification_id')->constrained('qualifications')->cascadeOnDelete();
            $table->morphs('qualifiable');
            $table->timestamps();

            $table->unique(['qualification_id', 'qualifiable_type', 'qualifiable_id'], 'qualificationables_unique');
        });

        $now = now();

        DB::table('qualifications')
            ->whereNotNull('instructor_id')
            ->orderBy('id')
            ->get(['id', 'instructor_id'])
            ->each(function (object $qualification) use ($now): void {
                DB::table('qualificationables')->updateOrInsert(
                    [
                        'qualification_id' => $qualification->id,
                        'qualifiable_type' => Instructor::class,
                        'qualifiable_id' => $qualification->instructor_id,
                    ],
                    [
                        'created_at' => $now,
                        'updated_at' => $now,
                    ],
                );
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('qualificationables');
    }
};
