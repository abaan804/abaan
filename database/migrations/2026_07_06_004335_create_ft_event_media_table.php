<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ft_event_media', function (Blueprint $table) {
            $table->id();

            // Cascade: deleting an event removes its media
            $table->foreignId('event_id')->constrained('ft_events')->cascadeOnDelete();

            $table->string('file_path');
            $table->string('original_name');
            $table->string('mime_type')->nullable();
            $table->enum('file_type', ['image', 'document', 'video'])->default('image');
            $table->unsignedBigInteger('size')->nullable();
            $table->string('caption')->nullable();

            $table->timestamps();

            $table->index(['event_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ft_event_media');
    }
};