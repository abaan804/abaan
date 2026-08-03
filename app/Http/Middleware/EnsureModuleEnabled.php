<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureModuleEnabled
{
    /**
     * Usage: ->middleware('module.enabled:ledger')
     */
    public function handle(Request $request, Closure $next, string $moduleKey): Response
    {
        $user = $request->user();

        if ($user->is_super_admin) {
            return $next($request);
        }

        $company = $user->company;

        abort_if(! $company, 403, 'No company associated with this account.');

        $enabled = $company->companyModules()
            ->whereHas('moduleDefinition', fn ($q) => $q->where('key', $moduleKey))
            ->where('is_enabled', true)
            ->exists();

        abort_unless($enabled, 403, 'This module is not enabled for your company.');

        return $next($request);
    }
}