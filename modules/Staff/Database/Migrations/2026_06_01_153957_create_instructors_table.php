<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('instructors', function (Blueprint $table) {
            $table->id();

            // 1. الربط مع حساب المستخدم
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');

            // 2. الربط مع القسم العلمي
            $table->foreignId('department_id')->nullable();

            $table->string('name', 150);

            // 3. الرقم الوطني
            $table->char('national_id', 12)->nullable()->unique();

            // 4. بيانات الاتصال
            $table->string('email', 100)->nullable()->unique();
            $table->string('phone', 30)->nullable();

            // 5. الدرجة العلمية
            $table->string('academic_rank', 50)->nullable();

            // 6. الحالة الوظيفية
            $table->enum('status', ['Active', 'On_Leave', 'Suspended'])->default('Active');

            // 7. الأعمدة الإضافية التي كان يستخدمها المتحكم القديم
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instructors');
    }
};
