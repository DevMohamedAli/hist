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
        Schema::table('qualifications', function (Blueprint $table) {
            if (! Schema::hasColumn('qualifications', 'qualifiable_type')) {
                $table->nullableMorphs('qualifiable');
            }
        });

        DB::table('qualifications')
            ->whereNull('qualifiable_type')
            ->whereNotNull('instructor_id')
            ->update([
                'qualifiable_type' => Instructor::class,
                'qualifiable_id' => DB::raw('instructor_id'),
            ]);

        Schema::table('qualifications', function (Blueprint $table) {
            $table->dropForeign(['instructor_id']);
            $table->foreignId('instructor_id')->nullable()->change();
            $table->foreign('instructor_id')->references('id')->on('instructors')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('qualifications', function (Blueprint $table) {
            $table->dropForeign(['instructor_id']);
            $table->foreignId('instructor_id')->nullable(false)->change();
            $table->dropMorphs('qualifiable');
            $table->foreign('instructor_id')->references('id')->on('instructors')->cascadeOnDelete();
        });
    }
};
