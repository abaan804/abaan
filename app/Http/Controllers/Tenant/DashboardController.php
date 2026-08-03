<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $company = $request->user()->company;
        $company->load(['companyModules.moduleDefinition']);

        $subscription = $company->activeSubscription();

        $stats = [
            'total_users' => $company->users()->count(),
            'enabled_modules' => $company->companyModules->where('is_enabled', true)->count(),
            'days_left' => null,
        ];

        if ($subscription?->trial_ends_at && $subscription->status === 'trial') {
            $stats['days_left'] = max(0, now()->diffInDays($subscription->trial_ends_at, false));
        } elseif ($subscription?->ends_at) {
            $stats['days_left'] = max(0, now()->diffInDays($subscription->ends_at, false));
        }

        return view('tenant.dashboard', compact('company', 'subscription', 'stats'));
    }
}