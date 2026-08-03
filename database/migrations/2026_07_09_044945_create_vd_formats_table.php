<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vd_formats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();

            // ── Cache key ─────────────────────────────────────────────────────
            // SHA-256 of the normalized URL — used for fast cache lookups
            // without storing the full URL in the index.
            $table->char('url_hash', 64);
            $table->text('original_url');
            $table->string('platform')->nullable();

            // ── Cached video metadata ─────────────────────────────────────────
            $table->string('video_title');
            $table->string('thumbnail_url')->nullable();
            $table->unsignedInteger('duration')->nullable()
                ->comment('Duration in seconds');
            $table->string('uploader_name')->nullable();
            $table->date('upload_date')->nullable();

            // ── Full format list from yt-dlp ──────────────────────────────────
            // Stored as JSON: [{id, ext, quality, resolution, fps, filesize, ...}]
            $table->json('formats');

            // ── Cache TTL ─────────────────────────────────────────────────────
            $table->timestamp('fetched_at')->useCurrent();
            $table->timestamp('expires_at')->nullable()
                ->comment('After this time the cache entry is considered stale');

            $table->timestamps();

            // ── Unique constraint: one cache entry per company per URL ─────────
            $table->unique(['company_id', 'url_hash'], 'vd_formats_company_url_unique');

            // ── Index for cleanup job ──────────────────────────────────────────
            $table->index('expires_at', 'vd_formats_expires');
            $table->index(['company_id', 'expires_at'], 'vd_formats_company_expires');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vd_formats');
    }
};