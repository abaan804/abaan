<div class="vd-subnav card border-0 shadow-sm p-2">
    <nav class="nav flex-column gap-1">
        <a href="{{ route('videodownloader.index') }}"
           class="nav-link {{ request()->routeIs('videodownloader.index') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> {{ __('Dashboard') }}
        </a>

        <div class="vd-nav-section">{{ __('Downloads') }}</div>

        @can('videodownloader.create-download')
            <a href="{{ route('videodownloader.download.create') }}"
               class="nav-link {{ request()->routeIs('videodownloader.download.create') ? 'active' : '' }}">
                <i class="bi bi-cloud-arrow-down"></i> {{ __('New Download') }}
            </a>
        @endcan

        @can('videodownloader.view-history')
            <a href="{{ route('videodownloader.history.index') }}"
               class="nav-link {{ request()->routeIs('videodownloader.history.*') ? 'active' : '' }}">
                <i class="bi bi-clock-history"></i> {{ __('History') }}
            </a>
        @endcan

        <div class="vd-nav-section">{{ __('Reports & Settings') }}</div>

        @can('videodownloader.view-reports')
            <a href="{{ route('videodownloader.reports.index') }}"
               class="nav-link {{ request()->routeIs('videodownloader.reports.*') ? 'active' : '' }}">
                <i class="bi bi-bar-chart"></i> {{ __('Reports') }}
            </a>
        @endcan

        @can('videodownloader.manage-settings')
            <a href="{{ route('videodownloader.settings.index') }}"
               class="nav-link {{ request()->routeIs('videodownloader.settings.*') ? 'active' : '' }}">
                <i class="bi bi-gear"></i> {{ __('Settings') }}
            </a>
        @endcan
    </nav>
</div>