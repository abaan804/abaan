<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('package_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained('packages')->cascadeOnDelete();

            $table->string('feature_key'); // e.g. "max_users", "max_storage_gb"
            $table->string('feature_label_en');
            $table->string('feature_label_ur')->nullable();
            $table->string('feature_label_ar')->nullable();

            $table->string('value')->nullable(); // e.g. "10", "unlimited", "5GB"
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_features');
    }
};