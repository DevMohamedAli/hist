<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('qualifications', function (Blueprint $table) {
            $columns = array_values(array_filter(
                ['year', 'specialization', 'grade'],
                fn (string $column): bool => Schema::hasColumn('qualifications', $column),
            ));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }

    public function down(): void
    {
        Schema::table('qualifications', function (Blueprint $table) {
            if (! Schema::hasColumn('qualifications', 'year')) {
                $table->integer('year')->nullable();
            }

            if (! Schema::hasColumn('qualifications', 'specialization')) {
                $table->string('specialization')->nullable();
            }

            if (! Schema::hasColumn('qualifications', 'grade')) {
                $table->string('grade')->nullable();
            }
        });
    }
};
