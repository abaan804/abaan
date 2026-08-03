<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ft_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('family_id')->nullable()->constrained('ft_families')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            // e.g. "member.created", "marriage.linked", "tree.viewed"
            $table->string('action');

            // Polymorphic-style subject tracking without full polymorphism overhead
            $table->string('subject_type')->nullable(); // e.g. "FtMember", "FtMarriage"
            $table->unsignedBigInteger('subject_id')->nullable();

            $table->json('properties')->nullable();

            // Immutable audit record — no updated_at
            $table->timestamp('created_at')->nullable();

            $table->index(['company_id', 'family_id', 'created_at']);
            $table->index(['company_id', 'action']);
            $table->index(['subject_type', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ft_activity_logs');
    }
};