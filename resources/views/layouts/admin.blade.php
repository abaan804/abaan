<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ \App\Http\Middleware\SetLocale::direction() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Abaan') }} Admin - @yield('title', 'Dashboard')</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap{{ \App\Http\Middleware\SetLocale::direction() === 'rtl' ? '.rtl' : '' }}.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/fonts.css') }}">
    @stack('styles')

    <style>
        .admin-sidebar .nav-link { color: #adb5bd; padding: .6rem 1rem; border-radius: .375rem; }
        .admin-sidebar .nav-link:hover { color: #fff; background: rgba(255,255,255,.08); }
        .admin-sidebar .nav-link.active { color: #fff; background: rgba(255,255,255,.15); }
        .admin-sidebar .nav-link i { width: 1.25rem; }
    </style>
</head>
<body>
    <div class="d-flex">
        <aside class="admin-sidebar bg-dark text-white p-3" style="width: 260px; min-height: 100vh;">
            <h5 class="mb-4 px-2">
                <i class="bi bi-shield-lock"></i> {{ config('app.name', 'Abaan') }} Admin
            </h5>

            <nav class="nav flex-column gap-1">
                <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i> {{ __('Dashboard') }}
                </a>
                <a href="{{ route('admin.contact-messages.index') }}" class="nav-link d-flex justify-content-between align-items-center {{ request()->routeIs('admin.contact-messages.*') ? 'active' : '' }}">
                    <span><i class="bi bi-envelope"></i> {{ __('Messages') }}</span>
                    @php $unread = \App\Models\ContactMessage::where('is_read', false)->count(); @endphp
                    @if ($unread > 0)
                        <span class="badge bg-danger rounded-pill">{{ $unread }}</span>
                    @endif
                </a>
                <a href="{{ route('admin.companies.index') }}" class="nav-link {{ request()->routeIs('admin.companies.*') ? 'active' : '' }}">
                    <i class="bi bi-building"></i> {{ __('Companies') }}
                </a>
                <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i> {{ __('Users') }}
                </a>
                <a href="{{ route('admin.packages.index') }}" class="nav-link {{ request()->routeIs('admin.packages.*') ? 'active' : '' }}">
                    <i class="bi bi-box-seam"></i> {{ __('Packages') }}
                </a>
                <a href="{{ route('admin.renewal-requests.index') }}"
                class="nav-link {{ request()->routeIs('admin.renewal-requests.*') ? 'active' : '' }}">
                    <i class="bi bi-arrow-repeat"></i>
                    {{ __('Renewal Requests') }}
                    @php $pendingCount = \App\Models\RenewalRequest::pending()->count(); @endphp
                    @if ($pendingCount > 0)
                        <span class="badge bg-danger rounded-pill ms-auto">{{ $pendingCount }}</span>
                    @endif
                </a>
                <a href="{{ route('admin.subscriptions.index') }}" class="nav-link {{ request()->routeIs('admin.subscriptions.*') ? 'active' : '' }}">
                    <i class="bi bi-credit-card"></i> {{ __('Subscriptions') }}
                </a>
                <a href="{{ route('admin.trial-settings.index') }}" class="nav-link {{ request()->routeIs('admin.trial-settings.*') ? 'active' : '' }}">
                    <i class="bi bi-hourglass-split"></i> {{ __('Trial Settings') }}
                </a>
                <a href="{{ route('admin.modules.index') }}" class="nav-link {{ request()->routeIs('admin.modules.*') ? 'active' : '' }}">
                    <i class="bi bi-puzzle"></i> {{ __('Modules') }}
                </a>
                <a href="{{ route('admin.module-requests.index') }}" class="nav-link d-flex justify-content-between align-items-center {{ request()->routeIs('admin.module-requests.*') ? 'active' : '' }}">
                    <span><i class="bi bi-inbox"></i> {{ __('Module Requests') }}</span>
                    @php $pendingReq = \App\Models\ModuleRequest::where('status', 'pending')->count(); @endphp
                    @if ($pendingReq > 0)
                        <span class="badge bg-danger rounded-pill">{{ $pendingReq }}</span>
                    @endif
                </a>
                <a href="{{ route('admin.content.index') }}" class="nav-link {{ request()->routeIs('admin.content.*') ? 'active' : '' }}">
                    <i class="bi bi-file-text"></i> {{ __('Website Content') }}
                </a>
                <a href="{{ route('admin.blogs.index') }}" class="nav-link {{ request()->routeIs('admin.blogs.*') ? 'active' : '' }}">
                    <i class="bi bi-journal-text"></i> {{ __('Blog') }}
                </a>
                <a href="{{ route('admin.faqs.index') }}" class="nav-link {{ request()->routeIs('admin.faqs.*') ? 'active' : '' }}">
                    <i class="bi bi-question-circle"></i> {{ __('FAQ') }}
                </a>
                <a href="{{ route('admin.roles.index') }}" class="nav-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                    <i class="bi bi-person-badge"></i> {{ __('Roles & Permissions') }}
                </a>
                <a href="{{ route('admin.settings.index') }}" class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                    <i class="bi bi-gear"></i> {{ __('System Settings') }}
                </a>
                <a href="{{ route('admin.settings.notifications.index') }}" class="nav-link {{ request()->routeIs('admin.settings.notifications.*') ? 'active' : '' }}">
                    <i class="bi bi-bell"></i> {{ __('Notifications') }}
                </a>
                <a href="{{ route('admin.backups.index') }}" class="nav-link {{ request()->routeIs('admin.backups.*') ? 'active' : '' }}">
                    <i class="bi bi-hdd-stack"></i> {{ __('Backups') }}
                </a>
                <hr class="border-secondary my-2">

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
                <div class="d-flex align-items-center gap-2">
                    <x-language-switcher />
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> {{ auth()->user()->name ?? '' }}
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person"></i> {{ __('Profile') }}</a></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item"><i class="bi bi-box-arrow-right"></i> {{ __('Logout') }}</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
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