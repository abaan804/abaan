<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Services\SubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BillingController extends Controller
{
    public function index(Request $request): View
    {
        $company = $request->user()->company;
        $subscription = $company->activeSubscription();

        $packages = Package::where('status', 'active')->with('features')->orderBy('sort_order')->get();

        $transactions = $company->transactions()->latest()->take(15)->get();

        $daysLeft = null;
        if ($subscription?->status === 'trial' && $subscription->trial_ends_at) {
            $daysLeft = max(0, now()->diffInDays($subscription->trial_ends_at, false));
        } elseif ($subscription?->ends_at) {
            $daysLeft = max(0, now()->diffInDays($subscription->ends_at, false));
        }

        $history = $company->subscriptions()->with('package')->latest()->get();

        return view('tenant.billing.index', compact('company', 'subscription', 'packages', 'transactions', 'daysLeft', 'history'));
    }

    public function changePlan(Request $request, SubscriptionService $subscriptionService): RedirectResponse
    {
        abort_unless($request->user()->can('manage company subscription'), 403);

        $request->validate([
            'package_id' => 'required|exists:packages,id',
        ]);

        $company = $request->user()->company;
        $package = Package::where('status', 'active')->findOrFail($request->package_id);

        $currentSubscription = $company->activeSubscription();
        if ($currentSubscription && $currentSubscription->package_id === $package->id) {
            return back()->with('error', __('You are already on this plan.'));
        }

        $subscriptionService->changePlan($company, $package);

        return back()->with('success', __('Your plan has been updated to :package.', ['package' => $package->translated('name')]));
    }
}