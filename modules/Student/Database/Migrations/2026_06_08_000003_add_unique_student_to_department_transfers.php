<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('department_transfers')
            ->select('student_id', DB::raw('MIN(id) as keep_id'))
            ->groupBy('student_id')
            ->havingRaw('COUNT(*) > 1')
            ->get()
            ->each(function ($duplicate): void {
                DB::table('department_transfers')
                    ->where('student_id', $duplicate->student_id)
                    ->where('id', '!=', $duplicate->keep_id)
                    ->delete();
            });

        if (! $this->hasStudentUniqueIndex()) {
            Schema::table('department_transfers', function (Blueprint $table) {
                $table->unique('student_id', 'department_transfers_student_id_unique');
            });
        }
    }

    public function down(): void
    {
        if ($this->hasStudentUniqueIndex()) {
            Schema::table('department_transfers', function (Blueprint $table) {
                $table->dropUnique('department_transfers_student_id_unique');
            });
        }
    }

    private function hasStudentUniqueIndex(): bool
    {
        return collect(Schema::getIndexes('department_transfers'))
            ->contains(fn (array $index): bool => ($index['name'] ?? null) === 'department_transfers_student_id_unique');
    }
};
