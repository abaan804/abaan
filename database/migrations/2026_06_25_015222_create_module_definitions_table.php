<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('module_definitions', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // ledger, pos, school, clinic, hr, crm

            $table->string('name_en');
            $table->string('name_ur')->nullable();
            $table->string('name_ar')->nullable();

            $table->text('description_en')->nullable();
            $table->text('description_ur')->nullable();
            $table->text('description_ar')->nullable();

            $table->string('icon')->nullable(); // bootstrap icon class
            $table->enum('status', ['active', 'coming_soon', 'disabled'])->default('coming_soon');
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_definitions');
    }
};