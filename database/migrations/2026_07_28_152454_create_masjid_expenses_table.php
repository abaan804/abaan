<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('masjid_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('mosque_id')->constrained('masjid_mosques')->cascadeOnDelete();

            $table->enum('category', [
                'maintenance',
                'electricity',
                'water',
                'salary',
                'renovation',
                'supplies',
                'event',
                'other',
            ])->default('other');

            $table->string('title');
            $table->decimal('amount', 10, 2);
            $table->date('expense_date');
            $table->string('paid_to')->nullable();
            $table->string('receipt_no')->nullable();

            $table->foreignId('season_id')
                ->nullable()
                ->constrained('masjid_seasons')
                ->nullOnDelete();

            $table->string('attachment')->nullable();
            $table->text('notes')->nullable();

            $table->foreignId('created_by')
                ->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')
                ->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['mosque_id', 'category']);
            $table->index(['mosque_id', 'expense_date']);
            $table->index(['mosque_id', 'season_id']);
            $table->index(['company_id', 'mosque_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('masjid_expenses');
    }
};