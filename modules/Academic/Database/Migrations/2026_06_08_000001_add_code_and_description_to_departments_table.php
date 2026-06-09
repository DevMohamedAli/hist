<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            if (! Schema::hasColumn('departments', 'code')) {
                $table->string('code', 20)->nullable()->unique()->after('name');
            }

            if (! Schema::hasColumn('departments', 'description')) {
                $table->text('description')->nullable()->after('code');
            }
        });
    }

    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            if (Schema::hasColumn('departments', 'description')) {
                $table->dropColumn('description');
            }

            if (Schema::hasColumn('departments', 'code')) {
                $table->dropColumn('code');
            }
        });
    }
};
