<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_metas', function (Blueprint $table) {
            $table->id();

            $table->string('seoable_type');
            $table->unsignedBigInteger('seoable_id');

            $table->string('meta_title_en')->nullable();
            $table->string('meta_title_ur')->nullable();
            $table->string('meta_title_ar')->nullable();

            $table->text('meta_description_en')->nullable();
            $table->text('meta_description_ur')->nullable();
            $table->text('meta_description_ar')->nullable();

            $table->string('keywords_en')->nullable();
            $table->string('keywords_ur')->nullable();
            $table->string('keywords_ar')->nullable();

            $table->string('og_image')->nullable();

            $table->timestamps();

            $table->index(['seoable_type', 'seoable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_metas');
    }
};