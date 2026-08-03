<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vd_downloads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            // ── Source ────────────────────────────────────────────────────────
            $table->text('original_url');
            $table->string('platform')->nullable()
                ->comment('youtube, twitter, tiktok, instagram, facebook, vimeo, dailymotion, generic');

            // ── Metadata (populated after FetchVideoMetadataJob) ──────────────
            $table->string('video_title')->nullable();
            $table->string('video_thumbnail')->nullable()
                ->comment('External URL or local storage path');
            $table->unsignedInteger('video_duration')->nullable()
                ->comment('Duration in seconds');
            $table->string('uploader_name')->nullable();
            $table->date('upload_date')->nullable();

            // ── Selected format (set when user picks format) ──────────────────
            $table->string('selected_format_id')->nullable()
                ->comment('Format identifier from yt-dlp e.g. 137+140');
            $table->string('selected_quality')->nullable()
                ->comment('Human label e.g. 1080p, 720p, audio only');
            $table->string('selected_format_ext')->nullable()
                ->comment('File extension: mp4, webm, mp3, m4a');
            $table->boolean('is_audio_only')->default(false);

            // ── Output (populated after ProcessDownloadJob) ───────────────────
            $table->string('file_path')->nullable()
                ->comment('Relative path inside local storage disk');
            $table->unsignedBigInteger('file_size')->nullable()
                ->comment('File size in bytes');
            $table->string('file_name')->nullable()
                ->comment('Sanitized filename for download response header');

            // ── Status ────────────────────────────────────────────────────────
            // State machine: pending → processing → completed
            //                                     → failed → (retry) → processing
            //                pending/processing   → cancelled
            $table->enum('status', [
                'pending',
                'processing',
                'completed',
                'failed',
                'cancelled',
            ])->default('pending');

            $table->text('error_message')->nullable();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('last_attempted_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            // ── Queue tracking ────────────────────────────────────────────────
            $table->string('job_id')->nullable()
                ->comment('Laravel job UUID for status tracking');
            $table->timestamp('metadata_fetched_at')->nullable();
            $table->timestamp('download_started_at')->nullable();

            // ── Audit ─────────────────────────────────────────────────────────
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // ── Indexes ───────────────────────────────────────────────────────
            $table->index(['company_id', 'user_id', 'status'], 'vd_dl_company_user_status');
            $table->index(['company_id', 'status'],            'vd_dl_company_status');
            $table->index(['company_id', 'created_at'],        'vd_dl_company_created');
            $table->index(['status', 'created_at'],            'vd_dl_status_created');
            $table->index(['company_id', 'platform'],          'vd_dl_company_platform');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vd_downloads');
    }
};