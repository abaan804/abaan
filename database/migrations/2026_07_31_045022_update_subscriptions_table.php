<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            // Status: trial → active → expired → cancelled
            if (! Schema::hasColumn('subscriptions', 'status')) {
                $table->enum('status', [
                    'trial',
                    'active',
                    'expired',
                    'cancelled',
                ])->default('trial')->after('company_id');
            }

            // Trial tracking
            if (! Schema::hasColumn('subscriptions', 'trial_started_at')) {
                $table->timestamp('trial_started_at')->nullable()->after('status');
            }
            if (! Schema::hasColumn('subscriptions', 'trial_ends_at')) {
                $table->timestamp('trial_ends_at')->nullable()->after('trial_started_at');
            }

            // Paid subscription window
            if (! Schema::hasColumn('subscriptions', 'starts_at')) {
                $table->timestamp('starts_at')->nullable()->after('trial_ends_at');
            }
            if (! Schema::hasColumn('subscriptions', 'ends_at')) {
                $table->timestamp('ends_at')->nullable()->after('starts_at');
            }

            // Monthly billing fields
            if (! Schema::hasColumn('subscriptions', 'price_paid')) {
                $table->decimal('price_paid', 10, 2)->nullable()->after('ends_at');
            }
            if (! Schema::hasColumn('subscriptions', 'billing_months')) {
                $table->unsignedTinyInteger('billing_months')->default(1)->after('price_paid');
            }
            if (! Schema::hasColumn('subscriptions', 'notes')) {
                $table->text('notes')->nullable()->after('billing_months');
            }

            // $table->index(['company_id', 'status']);
            $table->index(['company_id', 'ends_at']);
        });

        // Track whether a company has ever used a trial
        // This is on the companies table — permanent flag
        Schema::table('companies', function (Blueprint $table) {
            if (! Schema::hasColumn('companies', 'trial_used')) {
                $table->boolean('trial_used')->default(false)
                    ->comment('Once true, company can never use trial again')
                    ->after('status');
            }
            if (! Schema::hasColumn('companies', 'trial_used_at')) {
                $table->timestamp('trial_used_at')->nullable()->after('trial_used');
            }
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn([
                'status', 'trial_started_at', 'trial_ends_at',
                'starts_at', 'ends_at', 'price_paid', 'billing_months', 'notes',
            ]);
        });
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['trial_used', 'trial_used_at']);
        });
    }
};