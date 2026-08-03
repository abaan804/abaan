<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ledger_notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ledger_reminder_id')->constrained('ledger_reminders')->cascadeOnDelete();

            $table->enum('channel', ['sms', 'whatsapp', 'email', 'in_app']);
            $table->enum('status', ['queued', 'sent', 'failed'])->default('queued');
            $table->json('payload')->nullable();
            $table->timestamp('sent_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ledger_notification_logs');
    }
};