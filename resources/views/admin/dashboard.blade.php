<x-admin-layout>
    @section('title', 'Dashboard')
 
    <h3 class="mb-4">{{ __('Platform Overview') }}</h3>

    <div class="row g-3 mb-4">
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">{{ __('Total Companies') }}</div>
                    <div class="h3 mb-0">{{ $stats['total_companies'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">{{ __('Active Companies') }}</div>
                    <div class="h3 mb-0 text-success">{{ $stats['active_companies'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">{{ __('Pending Companies') }}</div>
                    <div class="h3 mb-0 text-warning">{{ $stats['pending_companies'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">{{ __('Suspended Companies') }}</div>
                    <div class="h3 mb-0 text-danger">{{ $stats['suspended_companies'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">{{ __('Trial Subscriptions') }}</div>
                    <div class="h3 mb-0">{{ $stats['trial_subscriptions'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">{{ __('Active Subscriptions') }}</div>
                    <div class="h3 mb-0 text-success">{{ $stats['active_subscriptions'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">{{ __('Total Users') }}</div>
                    <div class="h3 mb-0">{{ $stats['total_users'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">{{ __('Total Revenue') }}</div>
                    <div class="h3 mb-0">{{ formatCurrency($stats['revenue_total']) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <strong>{{ __('Recent Companies') }}</strong>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Registered') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentCompanies as $company)
                        <tr>
                            <td>{{ $company->name }}</td>
                            <td>
                                <span class="badge bg-{{ $company->status === 'active' ? 'success' : ($company->status === 'pending' ? 'warning' : 'danger') }}">
                                    {{ ucfirst($company->status) }}
                                </span>
                            </td>
                            <td>{{ $company->created_at->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-4">{{ __('No companies yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-admin-layout>