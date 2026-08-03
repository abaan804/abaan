<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained('subscriptions')->nullOnDelete();

            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->string('gateway'); // stripe, paypal, manual

            $table->string('gateway_transaction_id')->nullable();
            $table->enum('status', ['pending', 'success', 'failed', 'refunded'])->default('pending');

            $table->text('notes')->nullable(); // useful for manual payment proof/reference
            $table->timestamp('paid_at')->nullable();

            $table->timestamps();

            $table->index(['company_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};