<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ft_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('family_id')->constrained('ft_families')->cascadeOnDelete();
            $table->foreignId('member_id')->constrained('ft_members')->cascadeOnDelete();

            $table->enum('event_type', [
                'birth',
                'bismillah',
                'school_admission',
                'graduation',
                'hifz',
                'marriage',
                'job_started',
                'business_started',
                'migration',
                'house_purchased',
                'award',
                'retirement',
                'death',
                'custom',
            ]);

            // Used when event_type = 'custom'
            $table->string('event_title')->nullable();
            $table->date('event_date');
            $table->string('location')->nullable();
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'member_id', 'event_date']);
            $table->index(['company_id', 'family_id', 'event_date']);
            $table->index(['company_id', 'event_type']);
            $table->index(['company_id', 'event_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ft_events');
    }
};