<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('correspondence_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_student_available')->default(false)->index();
            $table->boolean('requires_approval')->default(false);
            $table->json('allowed_destination_roles')->nullable();
            $table->timestamps();
        });

        Schema::create('correspondence_reference_sequences', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('year')->unique();
            $table->unsignedInteger('last_number')->default(0);
            $table->timestamps();
        });

        Schema::create('correspondences', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number')->nullable()->unique();
            $table->foreignId('parent_id')->nullable()->constrained('correspondences')->nullOnDelete();
            $table->foreignId('thread_id')->nullable()->constrained('correspondences')->nullOnDelete();
            $table->foreignId('sender_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('correspondence_categories')->nullOnDelete();
            $table->string('type')->default('official_letter')->index();
            $table->string('subject');
            $table->longText('body');
            $table->string('priority')->default('normal')->index();
            $table->string('classification')->default('internal')->index();
            $table->string('status')->default('draft')->index();
            $table->boolean('requires_approval')->default(false)->index();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('sent_at')->nullable()->index();
            $table->timestamp('closed_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
            $table->index(['sender_user_id', 'status']);
            $table->index(['category_id', 'status']);
        });

        Schema::create('correspondence_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('correspondence_id')->constrained('correspondences')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('recipient_type')->default('to')->index();
            $table->string('delivery_status')->default('pending')->index();
            $table->timestamp('received_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->string('action_status')->nullable();
            $table->timestamps();
            $table->unique(['correspondence_id', 'user_id', 'recipient_type'], 'correspondence_recipient_unique');
            $table->index(['user_id', 'delivery_status']);
        });

        Schema::create('correspondence_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('correspondence_id')->constrained('correspondences')->restrictOnDelete();
            $table->string('original_filename');
            $table->string('stored_filename');
            $table->string('storage_disk')->default('local');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->default(0);
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $table->string('checksum', 128)->nullable();
            $table->string('visibility')->default('private');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('correspondence_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('correspondence_id')->constrained('correspondences')->restrictOnDelete();
            $table->foreignId('parent_reply_id')->nullable()->constrained('correspondence_replies')->nullOnDelete();
            $table->foreignId('sender_user_id')->constrained('users')->restrictOnDelete();
            $table->longText('body');
            $table->timestamps();
        });

        Schema::create('correspondence_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('correspondence_id')->constrained('correspondences')->restrictOnDelete();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status')->index();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('correspondence_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('correspondence_id')->constrained('correspondences')->restrictOnDelete();
            $table->foreignId('approver_user_id')->constrained('users')->restrictOnDelete();
            $table->string('decision')->index();
            $table->text('notes')->nullable();
            $table->string('content_hash', 128)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('decided_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('correspondence_approvals');
        Schema::dropIfExists('correspondence_status_histories');
        Schema::dropIfExists('correspondence_replies');
        Schema::dropIfExists('correspondence_attachments');
        Schema::dropIfExists('correspondence_recipients');
        Schema::dropIfExists('correspondences');
        Schema::dropIfExists('correspondence_reference_sequences');
        Schema::dropIfExists('correspondence_categories');
    }
};
