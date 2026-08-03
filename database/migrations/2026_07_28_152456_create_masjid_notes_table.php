<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('masjid_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('mosque_id')->constrained('masjid_mosques')->cascadeOnDelete();

            $table->enum('type', ['season', 'general'])->default('general');
            $table->foreignId('season_id')
                ->nullable()
                ->constrained('masjid_seasons')
                ->nullOnDelete();

            $table->string('title');
            $table->text('content');
            $table->enum('color', [
                'default', 'warning', 'danger', 'success', 'info'
            ])->default('default');
            $table->boolean('is_pinned')->default(false);

            $table->foreignId('created_by')
                ->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')
                ->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['mosque_id', 'type']);
            $table->index(['mosque_id', 'is_pinned']);
            $table->index(['mosque_id', 'season_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('masjid_notes');
    }
};