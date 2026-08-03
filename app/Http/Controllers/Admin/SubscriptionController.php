<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Package;
use App\Models\Subscription;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class SubscriptionController extends Controller
{
    public function __construct(protected SubscriptionService $subscriptionService)
    {
    }

    public function index(): View
    {
        $subscriptions = Subscription::with(['company', 'package', 'createdBy'])
            ->latest()
            ->paginate(20);

        return view('admin.subscriptions.index', compact('subscriptions'));
    }

    public function create(): View
    {
        $companies = Company::where('status', 'active')->orderBy('name')->get();
        $packages  = Package::active()->orderBy('sort_order')->with('moduleDefinitions')->get();

        return view('admin.subscriptions.create', compact('companies', 'packages'));
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'company_id'     => 'required|exists:companies,id',
            'package_id'     => 'required|exists:packages,id',
            'use_trial'      => 'nullable|boolean',
            'billing_months' => 'required_if:use_trial,0|integer|min:1|max:24',
            'price_paid'     => 'nullable|numeric|min:0',
            'notes'          => 'nullable|string',
        ]);

        $company = Company::findOrFail($data['company_id']);
        $package = Package::findOrFail($data['package_id']);
        $useTrial = $request->boolean('use_trial');

        // Validate trial eligibility
        if ($useTrial) {
            if ($company->trial_used) {
                return back()->withErrors([
                    'use_trial' => __('This company has already used their trial period and cannot trial again.')
                ])->withInput();
            }
            if (! $package->has_trial) {
                return back()->withErrors([
                    'use_trial' => __('This package does not have a trial period.')
                ])->withInput();
            }
        }

        $this->subscriptionService->assignPackage(
            company:       $company,
            package:       $package,
            useTrial:      $useTrial,
            billingMonths: (int) ($data['billing_months'] ?? 1),
            pricePaid:     (float) ($data['price_paid'] ?? 0),
            notes:         $data['notes'] ?? null,
        );

        return redirect()->route('admin.subscriptions.index')
            ->with('success', $useTrial
                ? __(':company subscription started with :days day trial.', ['company' => $company->name, 'days' => $package->trial_days])
                : __(':company subscription activated for :months month(s).', ['company' => $company->name, 'months' => $data['billing_months']])
            );
    }

    /**
     * Renew an existing subscription from admin side.
     */
    public function renew(Request $request, Subscription $subscription): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'package_id'     => 'required|exists:packages,id',
            'billing_months' => 'required|integer|min:1|max:24',
            'price_paid'     => 'nullable|numeric|min:0',
            'notes'          => 'nullable|string',
        ]);

        $company = $subscription->company;
        $package = Package::findOrFail($data['package_id']);

        $this->subscriptionService->renew(
            company:       $company,
            package:       $package,
            billingMonths: (int) $data['billing_months'],
            pricePaid:     (float) ($data['price_paid'] ?? $package->monthly_price * $data['billing_months']),
            notes:         $data['notes'] ?? null,
        );

        return redirect()->route('admin.subscriptions.index')
            ->with('success', __('Subscription renewed for :months month(s).', ['months' => $data['billing_months']]));
    }

    /**
     * Package info JSON — for dynamic form update when admin selects a package.
     */
    public function packageInfo(Package $package): \Illuminate\Http\JsonResponse
    {
        $package->load('moduleDefinitions');
        return response()->json([
            'monthly_price' => $package->monthly_price,
            'trial_days'    => $package->trial_days,
            'has_trial'     => $package->has_trial,
            'modules'       => $package->moduleDefinitions->map(fn ($m) => [
                'name' => $m->name_en,
                'icon' => $m->icon,
            ]),
        ]);
    }

    public function show(Subscription $subscription): View
    {
        $subscription->load(['company', 'package.features', 'transactions']);

        return view('admin.subscriptions.show', compact('subscription'));
    }

    public function updateStatus(Request $request, Subscription $subscription): RedirectResponse
    {
        $request->validate([
            'status' => 'required|in:trial,active,past_due,cancelled,expired',
        ]);

        $subscription->update([
            'status' => $request->status,
            'cancelled_at' => $request->status === 'cancelled' ? now() : $subscription->cancelled_at,
        ]);

        return back()->with('success', 'Subscription status updated.');
    }
}