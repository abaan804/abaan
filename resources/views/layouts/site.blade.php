<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ \App\Http\Middleware\SetLocale::direction() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', setting('site_name', 'Abaan')) - {{ setting('site_name', 'Abaan') }}</title>
    <meta name="description" content="@yield('meta_description', __('One platform for every part of your business.'))">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap{{ \App\Http\Middleware\SetLocale::direction() === 'rtl' ? '.rtl' : '' }}.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/fonts.css') }}">
    <link rel="stylesheet" href="{{ asset('css/site-theme.css') }}">
    @stack('styles')
</head>
<body class="site-body">

    <nav class="navbar navbar-expand-lg py-3" style="background-color: var(--paper);">
        <div class="container">
            <a class="navbar-brand font-display fs-4" href="{{ route('home') }}">{{ setting('site_name', 'Abaan') }}</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#siteNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="siteNav">
                <ul class="navbar-nav mx-auto gap-1">
                    <li class="nav-item"><a class="nav-link" href="{{ route('home') }}">{{ __('Home') }}</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('about') }}">{{ __('About') }}</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('solutions') }}">{{ __('Solutions') }}</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('pricing') }}">{{ __('Pricing') }}</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('blog.index') }}">{{ __('Blog') }}</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('contact') }}">{{ __('Contact') }}</a></li>
                </ul>
                <div class="d-flex align-items-center gap-2 mt-3 mt-lg-0">
                    <x-language-switcher />
                    @auth
                        <a href="{{ auth()->user()->isSuperAdmin() ? route('admin.dashboard') : route('dashboard') }}" class="btn btn-ink-outline btn-sm">{{ __('Dashboard') }}</a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-ink-outline btn-sm">{{ __('Log in') }}</a>
                        <a href="{{ route('register') }}" class="btn btn-amber btn-sm">{{ __('Start Free Trial') }}</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <main>
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    <footer class="site-hairline mt-5 pt-5 pb-4" style="background-color: var(--paper);">
        <div class="container">
            <div class="row g-4">
                <div class="col-12 col-md-4">
                    <div class="font-display fs-5 mb-2">{{ setting('site_name', 'Abaan') }}</div>
                    <p class="small text-muted">{{ __('One platform for every part of your business — ledger, POS, HR, and more, in one place.') }}</p>
                </div>
                <div class="col-6 col-md-2">
                    <div class="eyebrow mb-2">{{ __('Company') }}</div>
                    <ul class="list-unstyled small">
                        <li class="mb-2"><a href="{{ route('about') }}" class="text-decoration-none text-muted">{{ __('About') }}</a></li>
                        <li class="mb-2"><a href="{{ route('contact') }}" class="text-decoration-none text-muted">{{ __('Contact') }}</a></li>
                        <li class="mb-2"><a href="{{ route('blog.index') }}" class="text-decoration-none text-muted">{{ __('Blog') }}</a></li>
                    </ul>
                </div>
                <div class="col-6 col-md-2">
                    <div class="eyebrow mb-2">{{ __('Product') }}</div>
                    <ul class="list-unstyled small">
                        <li class="mb-2"><a href="{{ route('features') }}" class="text-decoration-none text-muted">{{ __('Features') }}</a></li>
                        <li class="mb-2"><a href="{{ route('pricing') }}" class="text-decoration-none text-muted">{{ __('Pricing') }}</a></li>
                        <li class="mb-2"><a href="{{ route('faq') }}" class="text-decoration-none text-muted">{{ __('FAQ') }}</a></li>
                    </ul>
                </div>
                <div class="col-6 col-md-2">
                    <div class="eyebrow mb-2">{{ __('Legal') }}</div>
                    <ul class="list-unstyled small">
                        <li class="mb-2"><a href="{{ route('privacy') }}" class="text-decoration-none text-muted">{{ __('Privacy Policy') }}</a></li>
                        <li class="mb-2"><a href="{{ route('terms') }}" class="text-decoration-none text-muted">{{ __('Terms') }}</a></li>
                    </ul>
                </div>
            </div>
            <div class="site-hairline mt-4 pt-3 d-flex justify-content-between flex-wrap small text-muted">
                <span>&copy; {{ date('Y') }} {{ setting('site_name', 'Abaan') }}. {{ __('All rights reserved.') }}</span>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>