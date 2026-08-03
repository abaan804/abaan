<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('masjid_donations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('mosque_id')->constrained('masjid_mosques')->cascadeOnDelete();

            $table->enum('type', ['named', 'anonymous'])->default('named');

            // Named donor fields
            $table->string('donor_name')->nullable();
            $table->string('donor_mobile')->nullable();
            $table->string('donor_address')->nullable();

            // Common fields
            $table->decimal('amount', 10, 2);
            $table->date('donation_date');
            $table->string('day_description')->nullable()
                ->comment('e.g. Friday, Jumma, Eid ul Fitr');
            $table->string('purpose')->nullable();
            $table->foreignId('season_id')
                ->nullable()
                ->constrained('masjid_seasons')
                ->nullOnDelete();
            $table->string('receipt_no')->nullable();
            $table->text('notes')->nullable();

            $table->foreignId('received_by')
                ->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')
                ->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')
                ->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['mosque_id', 'type']);
            $table->index(['mosque_id', 'donation_date']);
            $table->index(['mosque_id', 'season_id']);
            $table->index(['company_id', 'mosque_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('masjid_donations');
    }
};