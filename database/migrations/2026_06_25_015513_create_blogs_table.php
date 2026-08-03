<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blogs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('title_en');
            $table->string('title_ur')->nullable();
            $table->string('title_ar')->nullable();

            $table->string('slug')->unique();

            $table->text('excerpt_en')->nullable();
            $table->text('excerpt_ur')->nullable();
            $table->text('excerpt_ar')->nullable();

            $table->longText('content_en')->nullable();
            $table->longText('content_ur')->nullable();
            $table->longText('content_ar')->nullable();

            $table->string('featured_image')->nullable();
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->timestamp('published_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blogs');
    }
};