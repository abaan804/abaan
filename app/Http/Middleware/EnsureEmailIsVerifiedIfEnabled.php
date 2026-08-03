<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailIsVerifiedIfEnabled
{
    /**
     * Replaces Laravel's default 'verified' middleware.
     * Only blocks access if the Super Admin has enabled email verification
     * via the system setting 'email_verification_enabled'.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $enforced = Setting::getValue('email_verification_enabled', false);

        if (! $enforced) {
            return $next($request);
        }

        $user = $request->user();

        if ($user && method_exists($user, 'hasVerifiedEmail') && ! $user->hasVerifiedEmail()) {
            return $request->expectsJson()
                ? abort(409, 'Your email address is not verified.')
                : redirect()->route('verification.notice');
        }

        return $next($request);
    }
}