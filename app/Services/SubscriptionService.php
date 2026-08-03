<?php

namespace App\Services;

use App\Models\Company;
use App\Models\ModuleDefinition;
use App\Models\Package;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;

class SubscriptionService
{
    /**
     * Assign a package to a company.
     * If trial_days > 0 and company has never used trial → start trial.
     * Otherwise → start paid subscription immediately.
     */
    public function assignPackage(
        Company $company,
        Package $package,
        bool    $useTrial = true,
        int     $billingMonths = 1,
        float   $pricePaid = 0,
        ?string $notes = null
    ): Subscription {
        return DB::transaction(function () use (
            $company, $package, $useTrial, $billingMonths, $pricePaid, $notes
        ) {
            // Cancel any existing active subscriptions
            Subscription::where('company_id', $company->id)
                ->whereIn('status', ['trial', 'active'])
                ->update(['status' => 'cancelled']);

            $canTrial = $useTrial
                && $package->trial_days > 0
                && ! $company->trial_used;

            if ($canTrial) {
                $subscription = Subscription::create([
                    'company_id'        => $company->id,
                    'package_id'        => $package->id,
                    'status'            => Subscription::STATUS_TRIAL,
                    'trial_started_at'  => now(),
                    'trial_ends_at'     => now()->addDays($package->trial_days),
                    'price_paid'        => 0,
                    'billing_months'    => 0,
                    'notes'             => $notes,
                    'created_by'        => auth()->id(),
                ]);

                // Mark trial as used — permanently
                $company->update([
                    'trial_used'    => true,
                    'trial_used_at' => now(),
                ]);
            } else {
                // Paid subscription
                $starts = now();
                $ends   = now()->addMonths($billingMonths);

                $subscription = Subscription::create([
                    'company_id'     => $company->id,
                    'package_id'     => $package->id,
                    'status'         => Subscription::STATUS_ACTIVE,
                    'starts_at'      => $starts,
                    'ends_at'        => $ends,
                    'price_paid'     => $pricePaid ?: ($package->monthly_price * $billingMonths),
                    'billing_months' => $billingMonths,
                    'notes'          => $notes,
                    // 'created_by'     => auth()->id(),
                ]);
            }

            // Sync company modules from package
            $this->syncModules($company, $package);

            return $subscription;
        });
    }

    /**
     * Renew an expired subscription (paid — no trial).
     */
    public function renew(
        Company $company,
        Package $package,
        int     $billingMonths = 1,
        float   $pricePaid = 0,
        ?string $notes = null
    ): Subscription {
        return $this->assignPackage(
            $company, $package, false,
            $billingMonths, $pricePaid, $notes
        );
    }

    /**
     * Sync company_modules from package module definitions.
     * Removes modules not in the package, adds new ones.
     */
    public function syncModules(Company $company, Package $package): void
    {
        $moduleIds = $package->moduleDefinitions()->pluck('module_definitions.id');

        // Delete modules NOT in this package
        \App\Models\CompanyModule::where('company_id', $company->id)
            ->whereNotIn('module_definition_id', $moduleIds)
            ->delete();

        // Add modules that aren't already enabled
        foreach ($moduleIds as $moduleId) {
            \App\Models\CompanyModule::firstOrCreate([
                'company_id'          => $company->id,
                'module_definition_id'=> $moduleId,
            ], [
                'is_enabled' => true,
            ]);
        }
    }
}