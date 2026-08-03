<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('masjid_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('mosque_id')->constrained('masjid_mosques')->cascadeOnDelete();
            $table->foreignId('member_id')->constrained('masjid_members')->cascadeOnDelete();
            $table->foreignId('season_id')->constrained('masjid_seasons')->cascadeOnDelete();
            $table->foreignId('season_member_id')->constrained('masjid_season_members')->cascadeOnDelete();

            $table->date('payment_date');
            $table->decimal('amount_paid', 12, 2);
            $table->enum('payment_method', ['cash', 'bank', 'online', 'cheque'])->default('cash');
            $table->string('reference_no')->nullable();
            $table->string('receipt_no')->nullable();
            $table->text('notes')->nullable();

            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'mosque_id', 'member_id']);
            $table->index(['company_id', 'mosque_id', 'season_id']);
            $table->index(['company_id', 'mosque_id', 'payment_date']);
            $table->index(['season_member_id']);
            $table->index(['company_id', 'receipt_no']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('masjid_payments');
    }
};