<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vd_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();

            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            // Nullable FK — logs survive even if the download is soft-deleted
            $table->foreignId('download_id')
                ->nullable()
                ->constrained('vd_downloads')
                ->nullOnDelete();

            // ── Action ────────────────────────────────────────────────────────
            // Examples:
            // 'download.submitted'   — user submitted a URL
            // 'metadata.fetched'     — metadata job completed
            // 'metadata.failed'      — metadata job failed
            // 'download.started'     — download job began
            // 'download.completed'   — file ready
            // 'download.failed'      — download job failed
            // 'download.cancelled'   — user cancelled
            // 'download.retried'     — job re-queued
            // 'file.served'          — user downloaded the file
            // 'file.deleted'         — file cleaned up
            // 'settings.updated'     — company settings changed
            $table->string('action');

            // JSON bag for any extra context
            // e.g. {'format': 'mp4', 'quality': '1080p', 'error': '...'}
            $table->json('properties')->nullable();

            // Immutable audit record — no updated_at
            $table->timestamp('created_at')->nullable();

            // ── Indexes ───────────────────────────────────────────────────────
            $table->index(['company_id', 'download_id'],  'vd_log_company_download');
            $table->index(['company_id', 'created_at'],   'vd_log_company_created');
            $table->index(['company_id', 'action'],       'vd_log_company_action');
            $table->index(['user_id', 'created_at'],      'vd_log_user_created');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vd_activity_logs');
    }
};