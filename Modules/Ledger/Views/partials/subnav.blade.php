<div class="ledger-subnav card border-0 shadow-sm p-2">
    <nav class="nav flex-column gap-1">
        @can('easykhata.view-dashboard')
            <a href="{{ route('ledger.dashboard',['standalone' => 1]) }}" class="nav-link {{ request()->routeIs('ledger.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> {{ __('Dashboard') }}
            </a>
        @endcan
        @can('easykhata.manage-customers')
            <a href="{{ route('ledger.customers.index', ['standalone' => 1]) }}" class="nav-link {{ request()->routeIs('ledger.customers.*') ? 'active' : '' }}">
                <i class="bi bi-people"></i> {{ __('Customers') }}
            </a>
        @endcan
        @can('easykhata.manage-suppliers')
            <a href="{{ route('ledger.suppliers.index', ['standalone' => 1]) }}" class="nav-link {{ request()->routeIs('ledger.suppliers.*') ? 'active' : '' }}">
                <i class="bi bi-truck"></i> {{ __('Suppliers') }}
            </a>
        @endcan
        @can('easykhata.manage-transactions')
            <a href="{{ route('ledger.transactions.index', ['standalone' => 1]) }}" class="nav-link {{ request()->routeIs('ledger.transactions.*') ? 'active' : '' }}">
                <i class="bi bi-arrow-left-right"></i> {{ __('Transactions') }}
            </a>
        @endcan
        @can('easykhata.manage-reminders')
            <a href="{{ route('ledger.reminders.index', ['standalone' => 1]) }}" class="nav-link {{ request()->routeIs('ledger.reminders.*') ? 'active' : '' }}">
                <i class="bi bi-bell"></i> {{ __('Reminders') }}
            </a>
        @endcan
        @can('easykhata.manage-categories')
            <a href="{{ route('ledger.categories.index', ['standalone' => 1]) }}" class="nav-link {{ request()->routeIs('ledger.categories.*') ? 'active' : '' }}">
                <i class="bi bi-tags"></i> {{ __('Categories') }}
            </a>
            <a href="{{ route('ledger.payment-methods.index', ['standalone' => 1]) }}" class="nav-link {{ request()->routeIs('ledger.payment-methods.*') ? 'active' : '' }}">
                <i class="bi bi-credit-card"></i> {{ __('Payment Methods') }}
            </a>
        @endcan
        @can('easykhata.view-reports')
            <hr class="my-2">
            <span class="px-3 text-muted small text-uppercase">{{ __('Reports') }}</span>
            <a href="{{ route('ledger.reports.index', ['standalone' => 1]) }}" class="nav-link text-muted">
                <i class="bi bi-bar-chart"></i> {{ __('Reports') }}
            </a>
        @endcan
    </nav>
</div>