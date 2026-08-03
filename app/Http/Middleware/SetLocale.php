<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    protected array $supportedLocales = ['en', 'ur', 'ar'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->session()->get('locale');

        if (! $locale && $request->user()) {
            $locale = $request->user()->locale;
        }

        if (! $locale || ! in_array($locale, $this->supportedLocales)) {
            $locale = config('app.locale', 'en');
        }

        app()->setLocale($locale);
        $request->session()->put('locale', $locale);

        return $next($request);
    }

    public static function direction(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        return in_array($locale, ['ur', 'ar']) ? 'rtl' : 'ltr';
    }
}