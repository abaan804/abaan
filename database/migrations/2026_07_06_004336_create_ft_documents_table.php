<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ft_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('member_id')->constrained('ft_members')->cascadeOnDelete();

            $table->enum('document_type', [
                'cnic',
                'birth_certificate',
                'marriage_certificate',
                'educational',
                'passport',
                'photo',
                'other',
            ]);

            $table->string('title');
            $table->string('file_path');
            $table->string('original_name');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->text('notes')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'member_id', 'document_type']);
            $table->index(['company_id', 'document_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ft_documents');
    }
};