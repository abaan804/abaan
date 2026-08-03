<?php

namespace App\Http\Controllers\Onboarding;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Services\SubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    public function showPackages(): View
    {
        $packages = Package::where('status', 'active')
            ->orderBy('sort_order')
            ->with('features')
            ->get();

        return view('onboarding.package', compact('packages'));
    }

    public function selectPackage(Request $request, SubscriptionService $subscriptionService): RedirectResponse
    {
        $request->validate([
            'package_id' => 'required|exists:packages,id',
        ]);

        $company = $request->user()->company;

        if (! $company) {
            return redirect()->route('register')->withErrors([
                'company' => 'No company found for this account.',
            ]);
        }

        if ($company->subscriptions()->exists()) {
            return redirect()->route('dashboard')
                ->with('success', 'You already have an active subscription.');
        }

        $package = Package::where('status', 'active')->findOrFail($request->package_id);

        $subscriptionService->startTrial($company, $package);

        return redirect()->route('dashboard')
            ->with('success', 'Your trial has started! Welcome to Abaan.');
    }
}