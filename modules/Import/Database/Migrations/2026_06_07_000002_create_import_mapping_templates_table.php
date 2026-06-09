<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_mapping_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type');
            $table->string('name');
            $table->json('mapping');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_mapping_templates');
    }
};
