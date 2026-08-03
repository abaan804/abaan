<div class="ft-subnav card border-0 shadow-sm p-2">
    <nav class="nav flex-column gap-1">
        <a href="{{ route('familytree.index',['standalone'=>1]) }}"
           class="nav-link {{ request()->routeIs('familytree.index') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> {{ __('Dashboard') }}
        </a>

        <div class="nav-section">{{ __('Families') }}</div>
        @can('familytree.manage-families')
            <a href="{{ route('familytree.families.index',['standalone'=>1]) }}"
               class="nav-link {{ request()->routeIs('familytree.families.*') ? 'active' : '' }}">
                <i class="bi bi-houses"></i> {{ __('All Families') }}
            </a>
        @endcan

        @if (isset($family))
            <div class="nav-section">{{ $family->name }}</div>

            @can('familytree.manage-members')
                <a href="{{ route('familytree.family.members.index', [$family,'standalone'=>1]) }}"
                   class="nav-link {{ request()->routeIs('familytree.family.members.*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i> {{ __('Members') }}
                </a>
            @endcan

            @can('familytree.view-tree')
                <a href="{{ route('familytree.family.tree.index', [$family,'standalone'=>1]) }}"
                   class="nav-link {{ request()->routeIs('familytree.family.tree.*') ? 'active' : '' }}">
                    <i class="bi bi-diagram-3"></i> {{ __('Family Tree') }}
                </a>
            @endcan

            @can('familytree.manage-relationships')
                <a href="{{ route('familytree.family.marriages.index', [$family,'standalone'=>1]) }}"
                   class="nav-link {{ request()->routeIs('familytree.family.marriages.*') ? 'active' : '' }}">
                    <i class="bi bi-heart"></i> {{ __('Marriages') }}
                </a>
            @endcan

            @can('familytree.manage-events')
                <a href="{{ route('familytree.family.events.index', [$family,'standalone'=>1]) }}"
                   class="nav-link {{ request()->routeIs('familytree.family.events.*') ? 'active' : '' }}">
                    <i class="bi bi-calendar-event"></i> {{ __('Events') }}
                </a>
            @endcan

            @can('familytree.manage-documents')
                <a href="{{ route('familytree.family.documents.index', [$family,'standalone'=>1]) }}"
                   class="nav-link {{ request()->routeIs('familytree.family.documents.*') ? 'active' : '' }}">
                    <i class="bi bi-folder2-open"></i> {{ __('Documents') }}
                </a>
            @endcan

            <div class="nav-section">{{ __('Reports & Tools') }}</div>

            @can('familytree.view-reports')
                <a href="{{ route('familytree.family.reports.index', [$family,'standalone'=>1]) }}"
                   class="nav-link {{ request()->routeIs('familytree.family.reports.*') ? 'active' : '' }}">
                    <i class="bi bi-bar-chart"></i> {{ __('Reports') }}
                </a>
            @endcan

            <a href="{{ route('familytree.family.notifications.index', [$family,'standalone'=>1]) }}"
               class="nav-link {{ request()->routeIs('familytree.family.notifications.*') ? 'active' : '' }}">
                <i class="bi bi-bell"></i> {{ __('Notifications') }}
            </a>

            <hr class="my-2">
        @endif

        <a href="{{ route('familytree.global-search') }}?standalone=1"
           class="nav-link {{ request()->routeIs('familytree.global-search') ? 'active' : '' }}">
            <i class="bi bi-search"></i> {{ __('Global Search') }}
        </a>
    </nav>
</div>