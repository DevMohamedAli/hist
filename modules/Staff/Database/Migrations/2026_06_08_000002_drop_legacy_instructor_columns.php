<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $columns = array_values(array_filter(
            ['qualifications', 'specialization', 'office_location'],
            fn (string $column): bool => Schema::hasColumn('instructors', $column),
        ));

        if ($columns === []) {
            return;
        }

        Schema::table('instructors', function (Blueprint $table) use ($columns) {
            $table->dropColumn($columns);
        });
    }

    public function down(): void
    {
        //
    }
};
