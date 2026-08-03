<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_contents', function (Blueprint $table) {
            $table->id();
            $table->string('page_key');    // home, about, pricing, features, contact, footer
            $table->string('section_key'); // hero, intro, cta, footer_column_1, etc.

            $table->string('title_en')->nullable();
            $table->string('title_ur')->nullable();
            $table->string('title_ar')->nullable();

            $table->longText('content_en')->nullable();
            $table->longText('content_ur')->nullable();
            $table->longText('content_ar')->nullable();

            $table->string('image')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->unique(['page_key', 'section_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_contents');
    }
};