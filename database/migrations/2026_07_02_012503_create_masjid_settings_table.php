<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('masjid_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('mosque_id')->constrained('masjid_mosques')->cascadeOnDelete();

            $table->string('currency_symbol')->default('Rs');
            $table->string('currency_code')->default('PKR');
            $table->enum('currency_position', ['before', 'after'])->default('before');
            $table->string('receipt_prefix')->default('MCM-');

            $table->unsignedInteger('default_reminder_days')->default(3);

            $table->boolean('notification_whatsapp')->default(false);
            $table->boolean('notification_sms')->default(false);
            $table->boolean('notification_email')->default(false);

            $table->enum('default_language', ['en', 'ur', 'ar'])->default('en');

            $table->timestamps();

            $table->unique(['mosque_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('masjid_settings');
    }
};