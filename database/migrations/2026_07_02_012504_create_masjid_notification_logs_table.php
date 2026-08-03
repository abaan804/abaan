<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('masjid_notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('mosque_id')->constrained('masjid_mosques')->cascadeOnDelete();
            $table->foreignId('member_id')->nullable()->constrained('masjid_members')->nullOnDelete();

            $table->enum('channel', ['sms', 'whatsapp', 'email', 'in_app']);
            $table->enum('type', ['season_assigned', 'payment_reminder', 'balance_reminder', 'payment_received', 'receipt']);
            $table->enum('status', ['queued', 'sent', 'failed'])->default('queued');
            $table->json('payload')->nullable();
            $table->timestamp('sent_at')->nullable();

            $table->timestamps();

            $table->index(['company_id', 'mosque_id', 'member_id']);
            $table->index(['company_id', 'mosque_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('masjid_notification_logs');
    }
};