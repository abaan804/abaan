<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ledger_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();

            $table->enum('type', [
                'credit', 'debit', 'income', 'expense', 'transfer', 'opening_balance', 'adjustment',
            ]);

            $table->foreignId('customer_id')->nullable()->constrained('ledger_customers')->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('ledger_suppliers')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('ledger_categories')->nullOnDelete();
            $table->foreignId('payment_method_id')->nullable()->constrained('ledger_payment_methods')->nullOnDelete();

            $table->decimal('amount', 14, 2);
            $table->date('transaction_date');
            $table->string('reference_no')->nullable();
            $table->text('notes')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'transaction_date']);
            $table->index(['company_id', 'customer_id']);
            $table->index(['company_id', 'supplier_id']);
            $table->index(['company_id', 'type']);
            $table->index(['company_id', 'category_id']);
            $table->index(['company_id', 'payment_method_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ledger_transactions');
    }
};