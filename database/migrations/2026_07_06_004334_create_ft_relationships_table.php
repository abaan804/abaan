<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ft_relationships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();

            // The member who HAS the relationship
            $table->foreignId('member_id')->constrained('ft_members')->cascadeOnDelete();

            // The member they are related to
            $table->foreignId('related_member_id')->constrained('ft_members')->cascadeOnDelete();

            // Only non-derivable relationships are stored here.
            // All biological/legal relationships derivable from
            // father_id/mother_id and ft_marriages are computed, not stored.
            $table->enum('relationship_type', [
                'adoptive_father',
                'adoptive_mother',
                'step_father',
                'step_mother',
                'guardian',
                'foster_child',
                'custom',
            ]);

            // Used when relationship_type = 'custom'
            $table->string('label')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            // One member can only have one explicit record of the same
            // relationship type to another member
            $table->unique(
                ['member_id', 'related_member_id', 'relationship_type'],
                'unique_relationship'
            );

            $table->index(['company_id', 'member_id']);
            $table->index(['company_id', 'related_member_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ft_relationships');
    }
};