<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ft_marriages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();

            // Both members must exist — no cascade delete since
            // marriages are historically significant records.
            // Soft delete the marriage when a member is soft deleted.
            $table->foreignId('husband_id')->constrained('ft_members')->cascadeOnDelete();
            $table->foreignId('wife_id')->constrained('ft_members')->cascadeOnDelete();

            $table->date('marriage_date')->nullable();
            $table->string('marriage_place')->nullable();
            $table->enum('marriage_type', ['nikah', 'civil', 'other'])->default('nikah');

            // Active = current marriage
            // Divorced = ended by divorce
            // Widowed = ended by death of spouse
            $table->enum('status', ['active', 'divorced', 'widowed'])->default('active');
            $table->date('divorce_date')->nullable();
            $table->text('notes')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['company_id', 'husband_id']);
            $table->index(['company_id', 'wife_id']);
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'marriage_date']);

            // Prevent exact duplicate marriage records
            // Note: NOT a unique on (husband_id, wife_id) alone since
            // polygamous marriages mean one husband can have multiple wives,
            // and a divorced couple could theoretically remarry (different date).
            $table->unique(['husband_id', 'wife_id', 'marriage_date'], 'unique_marriage');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ft_marriages');
    }
};