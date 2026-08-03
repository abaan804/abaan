<x-app-layout>
    @section('title', 'Dashboard')

    <h3 class="mb-4">{{ __('Welcome back') }}, {{ auth()->user()->name }}</h3>

    @if ($subscription && $subscription->status === 'trial' && $stats['days_left'] !== null)
        <div class="alert alert-info d-flex justify-content-between align-items-center">
            <div>
                <i class="bi bi-hourglass-split"></i>
                {{ __('You are on a free trial.') }}
                <strong>{{ $stats['days_left'] }} {{ __('days left') }}.</strong>
            </div>
            <a href="{{ route('billing.index') }}" class="btn btn-sm btn-primary">{{ __('Upgrade Now') }}</a>
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">{{ __('Current Plan') }}</div>
                    <div class="h4 mb-0">{{ $subscription?->package?->translated('name') ?? __('No Plan') }}</div>
                    @if ($subscription)
                        <span class="badge bg-{{ $subscription->status === 'active' ? 'success' : ($subscription->status === 'trial' ? 'info' : 'secondary') }} mt-2">
                            {{ ucfirst($subscription->status) }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">{{ __('Team Members') }}</div>
                    <div class="h4 mb-0">{{ $stats['total_users'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">{{ __('Enabled Modules') }}</div>
                    <div class="h4 mb-0">{{ $stats['enabled_modules'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white"><strong>{{ __('Your Modules') }}</strong></div>
        <div class="card-body">
            <div class="row g-3">
                @forelse ($company->companyModules as $cm)
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="border rounded p-3 d-flex justify-content-between align-items-center {{ ! $cm->is_enabled ? 'opacity-50' : '' }}">
                            <div>
                                <i class="bi {{ $cm->moduleDefinition->icon ?? 'bi-puzzle' }} me-2"></i>
                                {{ $cm->moduleDefinition->translated('name') }}
                            </div>
                            <span class="badge bg-{{ $cm->is_enabled ? 'success' : 'secondary' }}">
                                {{ $cm->is_enabled ? __('Active') : __('Disabled') }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-muted text-center py-3">
                        {{ __('No modules assigned yet. Contact support to get started.') }}
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>