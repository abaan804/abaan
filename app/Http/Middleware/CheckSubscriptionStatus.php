<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscriptionStatus
{
    // public function handle(Request $request, Closure $next): Response
    // {
    //     $user = $request->user();

    //     if ($user && ! $user->is_super_admin) {

    //         // Deactivated/suspended user — log out immediately, even mid-session.
    //         if (in_array($user->status, ['inactive', 'suspended'])) {
    //             Auth::logout();
    //             $request->session()->invalidate();
    //             $request->session()->regenerateToken();

    //             return redirect()->route('account-inactive');
    //         }

    //         if ($user->company_id) {
    //             $company = $user->company;

    //             if ($company && $company->status === 'suspended') {
    //                 Auth::logout();
    //                 $request->session()->invalidate();
    //                 $request->session()->regenerateToken();

    //                 return redirect()->route('suspended');
    //             }

    //             $hasSubscription = $company->subscriptions()->exists();

    //             if (! $hasSubscription && ! $request->routeIs('onboarding.*')) {
    //                 return redirect()->route('onboarding.package');
    //             }
    //         }
    //     }

    //     return $next($request);
    // }
       /**
     * Routes the expired company CAN still access.
     */
    protected array $allowedRoutes = [
        'subscription.renew',
        'subscription.renew.submit',
        'subscription.expired',
        'logout',
        'profile.edit',
        'profile.update',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->hasRole('super-admin')) {
            return $next($request);
        }

        $company = $user->company;

        if (! $company) {
            return redirect()->route('subscription.expired');
        }

        // Get the current active subscription
        $subscription = $company->subscriptions()
            ->with('package')
            ->whereIn('status', ['trial', 'active'])
            ->latest()
            ->first();

        // No subscription at all
        if (! $subscription) {
            return $this->denyAccess($request, 'no_subscription');
        }

        // Check if trial has expired (status is still 'trial' but date passed)
        if ($subscription->isTrialExpired()) {
            // Auto-update status to expired
            $subscription->update(['status' => 'expired']);
            return $this->denyAccess($request, 'trial_expired');
        }

        // Check if paid subscription has expired
        if ($subscription->isExpired()) {
            $subscription->update(['status' => 'expired']);
            return $this->denyAccess($request, 'subscription_expired');
        }

        // Access is allowed — share subscription data with views
        view()->share('currentSubscription', $subscription);
        view()->share('subscriptionDaysRemaining', $subscription->days_remaining);
        view()->share('subscriptionIsTrial', $subscription->status === 'trial');

        return $next($request);
    }

    protected function denyAccess(Request $request, string $reason): Response
    {
        // Allow whitelisted routes even when expired
        $currentRoute = $request->route()?->getName();
        if ($currentRoute && in_array($currentRoute, $this->allowedRoutes)) {
            return response()->redirectToRoute($currentRoute); // let it pass
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Your subscription has expired. Please renew to continue.',
                'reason'  => $reason,
            ], 403);
        }

        return redirect()->route('subscription.expired')->with('reason', $reason);
    }
}