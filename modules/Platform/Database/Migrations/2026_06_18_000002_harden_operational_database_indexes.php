<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('import_jobs', function (Blueprint $table) {
            $table->index(['user_id', 'status'], 'import_jobs_user_status_index');
            $table->index(['type', 'status'], 'import_jobs_type_status_index');
            $table->index('created_at', 'import_jobs_created_at_index');
        });

        Schema::table('import_mapping_templates', function (Blueprint $table) {
            $table->unique(['user_id', 'type', 'name'], 'import_mapping_templates_user_type_name_unique');
            $table->index(['type', 'name'], 'import_mapping_templates_type_name_index');
        });

        Schema::table('activity_log_views', function (Blueprint $table) {
            $table->unique(['user_id', 'name'], 'activity_log_views_user_name_unique');
        });

        Schema::table('graduation_records', function (Blueprint $table) {
            $table->index(['status', 'graduation_date'], 'graduation_records_status_date_index');
            $table->index(['specialization_id', 'status'], 'graduation_records_specialization_status_index');
            $table->index('approved_by', 'graduation_records_approved_by_index');
        });

        Schema::table('correspondence_attachments', function (Blueprint $table) {
            $table->index(['correspondence_id', 'uploaded_by'], 'correspondence_attachments_correspondence_uploader_index');
            $table->index('checksum', 'correspondence_attachments_checksum_index');
        });

        Schema::table('correspondence_replies', function (Blueprint $table) {
            $table->index(['correspondence_id', 'sender_user_id'], 'correspondence_replies_correspondence_sender_index');
        });

        Schema::table('correspondence_status_histories', function (Blueprint $table) {
            $table->index(['correspondence_id', 'created_at'], 'correspondence_status_histories_correspondence_created_index');
        });

        Schema::table('correspondence_approvals', function (Blueprint $table) {
            $table->index(['correspondence_id', 'decision'], 'correspondence_approvals_correspondence_decision_index');
            $table->index('decided_at', 'correspondence_approvals_decided_at_index');
        });

        Schema::table('website_contact_submissions', function (Blueprint $table) {
            $table->index(['status', 'created_at'], 'website_contact_submissions_status_created_index');
        });
    }

    public function down(): void
    {
        Schema::table('website_contact_submissions', function (Blueprint $table) {
            $table->dropIndex('website_contact_submissions_status_created_index');
        });

        Schema::table('correspondence_approvals', function (Blueprint $table) {
            $table->dropIndex('correspondence_approvals_correspondence_decision_index');
            $table->dropIndex('correspondence_approvals_decided_at_index');
        });

        Schema::table('correspondence_status_histories', function (Blueprint $table) {
            $table->dropIndex('correspondence_status_histories_correspondence_created_index');
        });

        Schema::table('correspondence_replies', function (Blueprint $table) {
            $table->dropIndex('correspondence_replies_correspondence_sender_index');
        });

        Schema::table('correspondence_attachments', function (Blueprint $table) {
            $table->dropIndex('correspondence_attachments_correspondence_uploader_index');
            $table->dropIndex('correspondence_attachments_checksum_index');
        });

        Schema::table('graduation_records', function (Blueprint $table) {
            $table->dropIndex('graduation_records_status_date_index');
            $table->dropIndex('graduation_records_specialization_status_index');
            $table->dropIndex('graduation_records_approved_by_index');
        });

        Schema::table('activity_log_views', function (Blueprint $table) {
            $table->dropUnique('activity_log_views_user_name_unique');
        });

        Schema::table('import_mapping_templates', function (Blueprint $table) {
            $table->dropUnique('import_mapping_templates_user_type_name_unique');
            $table->dropIndex('import_mapping_templates_type_name_index');
        });

        Schema::table('import_jobs', function (Blueprint $table) {
            $table->dropIndex('import_jobs_user_status_index');
            $table->dropIndex('import_jobs_type_status_index');
            $table->dropIndex('import_jobs_created_at_index');
        });
    }
};
