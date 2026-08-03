<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('renewal_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();

            $table->foreignId('package_id')
                ->constrained('packages')
                ->cascadeOnDelete();

            $table->foreignId('submitted_by')
                ->constrained('users')
                ->cascadeOnDelete();

            // Billing details submitted by company
            $table->unsignedTinyInteger('billing_months')->default(1);
            $table->decimal('amount', 10, 2);

            // Payment proof
            $table->string('payment_screenshot')
                ->comment('Path in storage/app/private/renewal-screenshots/');
            $table->string('payment_method')->nullable()
                ->comment('e.g. Bank Transfer, JazzCash, EasyPaisa');
            $table->string('transaction_id')->nullable();
            $table->text('note')->nullable();

            // Status workflow
            $table->enum('status', [
                'pending',   // submitted, awaiting admin review
                'approved',  // admin approved and activated subscription
                'rejected',  // admin rejected
            ])->default('pending');

            // Admin action
            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('admin_note')->nullable();

            $table->timestamps();

            $table->index(['company_id', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('renewal_requests');
    }
};