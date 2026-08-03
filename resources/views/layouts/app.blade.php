<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ \App\Http\Middleware\SetLocale::direction() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Abaan') }} - @yield('title', 'Dashboard')</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap{{ \App\Http\Middleware\SetLocale::direction() === 'rtl' ? '.rtl' : '' }}.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/fonts.css') }}">
    @stack('styles')

    <style>
        .tenant-sidebar .nav-link { color: #495057; padding: .6rem 1rem; border-radius: .375rem; }
        .tenant-sidebar .nav-link:hover { background: #f1f3f5; }
        .tenant-sidebar .nav-link.active { color: #fff; background: var(--bs-primary, #0d6efd); }
        .tenant-sidebar .nav-link i { width: 1.25rem; }
    </style>
</head>
<body>
    <div class="d-flex">
        <aside class="tenant-sidebar bg-white border-end p-3" style="width: 260px; min-height: 100vh;">
            <a href="{{ route('dashboard') }}" class="d-block mb-4 px-2 fw-bold fs-5 text-decoration-none">
                {{ config('app.name', 'Abaan') }}
            </a>

            <nav class="nav flex-column gap-1">
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i> {{ __('Dashboard') }}
                </a>
                <a href="{{ route('company.edit') }}" class="nav-link {{ request()->routeIs('company.*') ? 'active' : '' }}">
                    <i class="bi bi-building"></i> {{ __('Company Profile') }}
                </a>
                <a href="{{ route('team.index') }}" class="nav-link {{ request()->routeIs('team.*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i> {{ __('Team') }}
                </a>
                <a href="{{ route('billing.index') }}" class="nav-link {{ request()->routeIs('billing.*') ? 'active' : '' }}">
                    <i class="bi bi-credit-card"></i> {{ __('Subscription & Billing') }}
                </a>
                <a href="{{ route('modules.index') }}" class="nav-link {{ request()->routeIs('modules.*') ? 'active' : '' }}">
                    <i class="bi bi-puzzle"></i> {{ __('Modules') }}
                </a>
                
                <hr class="my-2">

                <a href="{{ route('profile.edit') }}" class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                    <i class="bi bi-person"></i> {{ __('Profile') }}
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="nav-link border-0 bg-transparent w-100 text-start">
                        <i class="bi bi-box-arrow-right"></i> {{ __('Logout') }}
                    </button>
                </form>
            </nav>
        </aside>

        <div class="flex-grow-1">
            <nav class="navbar navbar-light bg-white border-bottom px-3 d-flex justify-content-end">
                <x-language-switcher />
            </nav>
            <main class="container-fluid py-4">
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                {{ $slot ?? '' }}
                @yield('content')
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>