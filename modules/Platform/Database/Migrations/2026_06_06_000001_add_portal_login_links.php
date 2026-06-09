<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (! Schema::hasColumn('students', 'user_id')) {
                $table->foreignId('user_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('users')
                    ->nullOnDelete();
            }
        });

        Schema::table('instructors', function (Blueprint $table) {
            if (! Schema::hasColumn('instructors', 'employee_id')) {
                $table->string('employee_id', 30)
                    ->nullable()
                    ->unique()
                    ->after('user_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('instructors', function (Blueprint $table) {
            if (Schema::hasColumn('instructors', 'employee_id')) {
                $table->dropUnique(['employee_id']);
                $table->dropColumn('employee_id');
            }
        });

        Schema::table('students', function (Blueprint $table) {
            if (Schema::hasColumn('students', 'user_id')) {
                $table->dropConstrainedForeignId('user_id');
            }
        });
    }
};
