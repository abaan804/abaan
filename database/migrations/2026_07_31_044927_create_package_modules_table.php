<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('package_modules', function (Blueprint $table) {
            $table->id();

            $table->foreignId('package_id')
                ->constrained('packages')
                ->cascadeOnDelete();

            $table->foreignId('module_definition_id')
                ->constrained('module_definitions')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(['package_id', 'module_definition_id'], 'package_module_unique');
            $table->index('package_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_modules');
    }
};