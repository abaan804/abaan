<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('masjid_seasons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('mosque_id')->constrained('masjid_mosques')->cascadeOnDelete();

            $table->string('name');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('contribution_amount', 12, 2);
            $table->text('description')->nullable();
            $table->enum('frequency', ['monthly', 'quarterly', 'seasonal', 'yearly', 'custom'])->default('seasonal');
            $table->enum('status', ['active', 'inactive', 'completed'])->default('active');
            $table->boolean('auto_assign')->default(true);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'mosque_id', 'status']);
            $table->index(['company_id', 'mosque_id', 'start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('masjid_seasons');
    }
};