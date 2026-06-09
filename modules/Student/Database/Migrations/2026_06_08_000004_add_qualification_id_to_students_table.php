<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (! Schema::hasColumn('students', 'qualification_id')) {
                $table->foreignId('qualification_id')
                    ->nullable()
                    ->after('qualification')
                    ->constrained('qualifications')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (Schema::hasColumn('students', 'qualification_id')) {
                $table->dropConstrainedForeignId('qualification_id');
            }
        });
    }
};
