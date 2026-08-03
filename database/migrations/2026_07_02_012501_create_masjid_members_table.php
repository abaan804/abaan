<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('masjid_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('mosque_id')->constrained('masjid_mosques')->cascadeOnDelete();

            $table->string('name');
            $table->string('father_name')->nullable();
            $table->string('cnic')->nullable();
            $table->string('mobile');
            $table->string('whatsapp')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('occupation')->nullable();
            $table->date('joining_date');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->text('notes')->nullable();
            $table->string('photo')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'mosque_id', 'status']);
            $table->index(['company_id', 'mosque_id', 'name']);
            $table->index(['company_id', 'mobile']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('masjid_members');
    }
};