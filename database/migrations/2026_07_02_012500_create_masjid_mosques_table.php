<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('masjid_mosques', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();

            $table->string('village_name');
            $table->string('mosque_name');
            $table->string('scholar_name')->nullable();
            $table->string('scholar_contact')->nullable();
            $table->string('scholar_email')->nullable();
            $table->string('committee_name')->nullable();
            $table->string('mosque_contact')->nullable();

            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->string('country')->default('Pakistan');
            $table->string('postal_code')->nullable();
            $table->string('map_link')->nullable();
            $table->text('description')->nullable();

            $table->string('logo')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('masjid_mosques');
    }
};