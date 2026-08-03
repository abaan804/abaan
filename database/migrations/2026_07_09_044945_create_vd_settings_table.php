<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vd_settings', function (Blueprint $table) {
            $table->id();

            // One settings row per company — enforced by unique constraint
            $table->foreignId('company_id')
                ->unique()
                ->constrained('companies')
                ->cascadeOnDelete();

            // ── Download limits ───────────────────────────────────────────────
            $table->unsignedInteger('max_file_size_mb')->default(500)
                ->comment('Maximum allowed file size per download in MB');

            $table->unsignedTinyInteger('max_concurrent_downloads')->default(3)
                ->comment('Maximum simultaneous downloads per company');

            // ── Retention ─────────────────────────────────────────────────────
            $table->unsignedSmallInteger('retention_days')->default(30)
                ->comment('How many days to keep downloaded files before auto-cleanup');

            // ── Platform restrictions ──────────────────────────────────────────
            // null = allow all platforms
            // JSON array of allowed platform keys e.g. ["youtube","vimeo"]
            $table->json('allowed_platforms')->nullable()
                ->comment('null means all platforms allowed');

            // ── Format options ────────────────────────────────────────────────
            $table->boolean('allow_audio_only')->default(true)
                ->comment('Whether users can download audio-only formats');

            // ── Storage quota ─────────────────────────────────────────────────
            // null = unlimited
            $table->decimal('storage_limit_gb', 8, 2)->nullable()
                ->comment('Maximum total storage this company may use in GB');

            // ── Notification preferences ──────────────────────────────────────
            $table->boolean('notify_on_complete')->default(true);
            $table->boolean('notify_on_failure')->default(true);

            // ── Audit ─────────────────────────────────────────────────────────
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vd_settings');
    }
};