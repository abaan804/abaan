<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('masjid_season_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('mosque_id')->constrained('masjid_mosques')->cascadeOnDelete();
            $table->foreignId('season_id')->constrained('masjid_seasons')->cascadeOnDelete();
            $table->foreignId('member_id')->constrained('masjid_members')->cascadeOnDelete();

            $table->decimal('amount_due', 12, 2);
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->enum('status', ['pending', 'partial', 'paid', 'overpaid'])->default('pending');

            $table->timestamps();

            $table->unique(
                ['season_id', 'member_id'],
                'msm_season_member_unique'
            );

            $table->index(
                ['company_id', 'mosque_id', 'season_id', 'status'],
                'msm_cmp_mos_season_status_idx'
            );

            $table->index(
                ['company_id', 'mosque_id', 'member_id'],
                'msm_cmp_mos_member_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('masjid_season_members');
    }
};