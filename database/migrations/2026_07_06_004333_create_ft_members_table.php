<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ft_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('family_id')->constrained('ft_families')->cascadeOnDelete();

            // ── Identity ──────────────────────────────────────────────────
            $table->string('full_name');
            $table->enum('gender', ['male', 'female', 'other'])->default('male');
            $table->date('date_of_birth')->nullable();
            $table->string('place_of_birth')->nullable();
            $table->date('date_of_death')->nullable();
            $table->string('burial_place')->nullable();
            $table->enum('life_status', ['living', 'deceased'])->default('living');

            // ── Parent links — the structural backbone of the tree ────────
            // Nullable self-referential FKs.
            // father_id always points to a male member.
            // mother_id always points to a female member.
            // Siblings are derived (same father_id OR same mother_id).
            // Added after table creation via separate statements (self-ref constraint).
            $table->unsignedBigInteger('father_id')->nullable();
            $table->unsignedBigInteger('mother_id')->nullable();

            // ── Fallback display names when parent not yet in system ──────
            // Once father_id / mother_id is set, these become redundant
            // but are retained for display fallback.
            $table->string('father_name_text')->nullable();
            $table->string('mother_name_text')->nullable();

            // ── Contact & Legal ───────────────────────────────────────────
            $table->string('cnic')->nullable();
            $table->string('passport_number')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('whatsapp_number')->nullable();
            $table->string('email')->nullable();
            $table->text('current_address')->nullable();
            $table->text('permanent_address')->nullable();

            // ── Biographical ──────────────────────────────────────────────
            $table->string('occupation')->nullable();
            $table->string('education')->nullable();
            $table->string('blood_group')->nullable();
            $table->string('religion')->nullable();
            $table->string('nationality')->nullable();
            $table->enum('marital_status', ['married', 'unmarried', 'divorced', 'widowed'])
                ->default('unmarried');

            // ── Media ─────────────────────────────────────────────────────
            $table->string('profile_photo')->nullable();
            $table->text('other_details')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['company_id', 'family_id']);
            $table->index(['company_id', 'father_id']);
            $table->index(['company_id', 'mother_id']);
            $table->index(['company_id', 'full_name']);
            $table->index(['company_id', 'life_status']);
            $table->index(['company_id', 'gender']);
            $table->index(['company_id', 'date_of_birth']);
            $table->index(['company_id', 'cnic']);
        });

        // Self-referential FK constraints added after table creation
        // MySQL requires the table to exist before referencing itself
        Schema::table('ft_members', function (Blueprint $table) {
            $table->foreign('father_id')
                ->references('id')->on('ft_members')
                ->nullOnDelete();
            $table->foreign('mother_id')
                ->references('id')->on('ft_members')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ft_members', function (Blueprint $table) {
            $table->dropForeign(['father_id']);
            $table->dropForeign(['mother_id']);
        });
        Schema::dropIfExists('ft_members');
    }
};