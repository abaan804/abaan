<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ledger_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();

            $table->foreignId('customer_id')->nullable()->constrained('ledger_customers')->cascadeOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('ledger_suppliers')->cascadeOnDelete();

            $table->string('title');
            $table->date('due_date');
            $table->decimal('amount', 14, 2)->nullable();

            $table->enum('status', ['pending', 'sent', 'dismissed'])->default('pending');
            $table->enum('channel', ['sms', 'whatsapp', 'email', 'in_app'])->default('in_app');

            $table->timestamps();

            $table->index(['company_id', 'due_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ledger_reminders');
    }
};