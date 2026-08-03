<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('company_id')
                ->nullable()
                ->after('id')
                ->constrained('companies')
                ->nullOnDelete();

            $table->boolean('is_super_admin')->default(false)->after('company_id');
            $table->string('locale', 5)->default('en')->after('is_super_admin');
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active')->after('locale');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_id');
            $table->dropColumn(['is_super_admin', 'locale', 'status']);
            $table->dropSoftDeletes();
        });
    }
};