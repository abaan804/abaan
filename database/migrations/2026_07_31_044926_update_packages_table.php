<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            // Rename/add price columns
            if (! Schema::hasColumn('packages', 'monthly_price')) {
                $table->decimal('monthly_price', 10, 2)->default(0)->after('name_ar');
            }
            if (! Schema::hasColumn('packages', 'trial_days')) {
                $table->unsignedSmallInteger('trial_days')->default(0)
                    ->comment('0 = no trial for this package')
                    ->after('monthly_price');
            }
            if (! Schema::hasColumn('packages', 'description')) {
                $table->text('description')->nullable()->after('trial_days');
            }
            if (! Schema::hasColumn('packages', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('description');
            }
            if (! Schema::hasColumn('packages', 'max_users')) {
                $table->unsignedSmallInteger('max_users')->nullable()
                    ->comment('null = unlimited')
                    ->after('is_active');
            }
            if (! Schema::hasColumn('packages', 'sort_order')) {
                $table->unsignedSmallInteger('sort_order')->default(0)->after('max_users');
            }
        });
    }

    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn(['monthly_price', 'trial_days', 'description', 'is_active', 'max_users', 'sort_order']);
        });
    }
};