<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ \App\Http\Middleware\SetLocale::direction() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Abaan') }} - @yield('title', 'Welcome')</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap{{ \App\Http\Middleware\SetLocale::direction() === 'rtl' ? '.rtl' : '' }}.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/fonts.css') }}">
    @stack('styles')
</head>
<body class="bg-light">

    <div class="container-fluid d-flex flex-column min-vh-100">
        <div class="d-flex justify-content-end p-3">
            <x-language-switcher />
        </div>

        <div class="flex-grow-1 d-flex align-items-center justify-content-center">
            <div class="card shadow-sm" style="max-width: 480px; width: 100%;">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <a href="{{ url('/') }}" class="h4 text-decoration-none fw-bold">{{ config('app.name', 'Abaan') }}</a>
                    </div>
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>