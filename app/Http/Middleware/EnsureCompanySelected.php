<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCompanySelected
{
    /**
     * Ensures a logged-in (non-super-admin) user has a company attached.
     * Super admins are exempt since they aren't tied to a single company.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->is_super_admin && ! $user->company_id) {
            return redirect()->route('register')->withErrors([
                'company' => 'No company is associated with your account. Please contact support.',
            ]);
        }

        return $next($request);
    }
}