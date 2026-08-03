<div class="masjid-subnav card border-0 shadow-sm p-2">
    @if (isset($mosque))
        <nav class="nav flex-column gap-1">
            <a href="{{ route('masjid.mosque.dashboard', [$mosque,'standalone' => 1]) }}" class="nav-link {{ request()->routeIs('masjid.mosque.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> {{ __('Dashboard') }}
            </a>

            <div class="nav-section">{{ __('Management') }}</div>
            @can('masjid.manage-members')
                <a href="{{ route('masjid.mosque.members.index', [$mosque,'standalone' => 1]) }}" class="nav-link {{ request()->routeIs('masjid.mosque.members.*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i> {{ __('Members') }}
                </a>
            @endcan
            @can('masjid.manage-seasons')
                <a href="{{ route('masjid.mosque.seasons.index', [$mosque,'standalone' => 1]) }}" class="nav-link {{ request()->routeIs('masjid.mosque.seasons.*') ? 'active' : '' }}">
                    <i class="bi bi-calendar3"></i> {{ __('Seasons') }}
                </a>
            @endcan
            @can('masjid.manage-payments')
                <a href="{{ route('masjid.mosque.payments.index', [$mosque,'standalone' => 1]) }}" class="nav-link {{ request()->routeIs('masjid.mosque.payments.*') ? 'active' : '' }}">
                    <i class="bi bi-cash-coin"></i> {{ __('Payments') }}
                </a>
            @endcan
            <div class="nav-section">{{ __('Finance') }}</div>

            <a href="{{ route('masjid.mosque.donations.index',[$mosque,'standalone' => 1]) }}"
            class="nav-link {{ request()->routeIs('masjid.mosque.donations.index*') ? 'active' : '' }}">
                <i class="bi bi-gift"></i> {{ __('Donations') }}
            </a>

            <a href="{{ route('masjid.mosque.expenses.index', [$mosque,'standalone' => 1]) }}"
            class="nav-link {{ request()->routeIs('masjid.mosque.expenses.*') ? 'active' : '' }}">
                <i class="bi bi-cash-stack"></i> {{ __('Expenses') }}
            </a>

            <a href="{{ route('masjid.mosque.financial.index', $mosque) }}"
            class="nav-link {{ request()->routeIs('masjid.mosque.financial.*') ? 'active' : '' }}">
                <i class="bi bi-calculator"></i> {{ __('Financial Summary') }}
            </a>

            <a href="{{ route('masjid.mosque.donations.report', [$mosque,'standalone' => 1]) }}"
            class="nav-link {{ request()->routeIs('masjid.mosque.donations.report*') ? 'active' : '' }}">
                <i class="bi bi-bar-chart-line"></i> {{ __('Donation Report') }}
            </a>

            <div class="nav-section">{{ __('Notes') }}</div>

            <a href="{{ route('masjid.mosque.notes.index', [$mosque,'standalone' => 1]) }}"
            class="nav-link {{ request()->routeIs('masjid.mosque.notes.*') ? 'active' : '' }}">
                <i class="bi bi-sticky"></i> {{ __('Notes') }}
            </a>
            <div class="nav-section">{{ __('Reports & Tools') }}</div>
            @can('masjid.view-reports')
                <a href="{{ route('masjid.mosque.reports.index', [$mosque,'standalone' => 1]) }}" class="nav-link {{ request()->routeIs('masjid.mosque.reports.*') ? 'active' : '' }}">
                    <i class="bi bi-bar-chart"></i> {{ __('Reports') }}
                </a>
            @endcan
            @can('masjid.send-notifications')
                <a href="{{ route('masjid.mosque.notifications.index', [$mosque,'standalone' => 1]) }}" class="nav-link {{ request()->routeIs('masjid.mosque.notifications.*') ? 'active' : '' }}">
                    <i class="bi bi-bell"></i> {{ __('Notifications') }}
                </a>
            @endcan

            <div class="nav-section">{{ __('Configure') }}</div>
            @can('masjid.manage-mosque-profile')
                <a href="{{ route('masjid.mosque.profile.edit', [$mosque,'standalone' => 1]) }}" class="nav-link {{ request()->routeIs('masjid.mosque.profile.*') ? 'active' : '' }}">
                    <i class="bi bi-building"></i> {{ __('Mosque Profile') }}
                </a>
            @endcan
            @can('masjid.manage-settings')
                <a href="{{ route('masjid.mosque.settings.edit', [$mosque,'standalone' => 1]) }}" class="nav-link {{ request()->routeIs('masjid.mosque.settings.*') ? 'active' : '' }}">
                    <i class="bi bi-gear"></i> {{ __('Settings') }}
                </a>
            @endcan

            <hr class="my-2">
            <a href="{{ route('masjid.index',['standalone' => 1]) }}" class="nav-link">
                <i class="bi bi-grid"></i> {{ __('All Mosques') }}
            </a>

            @can('masjid.manage-settings')
                <a href="{{ route('masjid.mosque.backups.index', [$mosque,'standalone' => 1]) }}" class="nav-link {{ request()->routeIs('masjid.mosque.backups.*') ? 'active' : '' }}">
                    <i class="bi bi-database-down"></i> {{ __('Backups') }}
                </a>
            @endcan
        </nav>
    @else
        <nav class="nav flex-column gap-1">
            <a href="{{ route('masjid.index',['standalone' => 1]) }}" class="nav-link {{ request()->routeIs('masjid.index') ? 'active' : '' }}">
                <i class="bi bi-grid"></i> {{ __('All Mosques') }}
            </a>
        </nav>
    @endif
</div>