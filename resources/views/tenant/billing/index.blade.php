<x-app-layout>
    @section('title', 'Subscription & Billing')

    <h3 class="mb-4">{{ __('Subscription & Billing') }}</h3>

    @if ($subscription?->status === 'trial' && $daysLeft !== null)
        <div class="alert alert-info">
            <i class="bi bi-hourglass-split"></i>
            {{ __('Your free trial ends in :days days.', ['days' => $daysLeft]) }}
        </div>
    @endif

    <div class="row g-4 mb-4">
        <div class="col-12 col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">{{ __('Current Plan') }}</div>
                    <div class="h4">{{ $subscription?->package?->translated('name') ?? __('No active plan') }}</div>
                    @if ($subscription)
                        <span class="badge bg-{{ $subscription->status === 'active' ? 'success' : ($subscription->status === 'trial' ? 'info' : 'secondary') }}">
                            {{ ucfirst($subscription->status) }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">{{ __('Started') }}</div>
                    <div class="h5">{{ formatDate($subscription?->starts_at) }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">{{ __('Renews / Ends') }}</div>
                    <div class="h5">{{ formatDate($subscription?->ends_at) ?: __('No end date set') }}</div>
                </div>
            </div>
        </div>
    </div>

    @can('manage company subscription')
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white"><strong>{{ __('Available Plans') }}</strong></div>
            <div class="card-body">
                <div class="row g-4">
                    @foreach ($packages as $package)
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="card h-100 {{ $subscription?->package_id === $package->id ? 'border-primary' : '' }}">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title">{{ $package->translated('name') }}</h5>
                                    <p class="text-muted small">{{ $package->translated('description') }}</p>
                                    <div class="my-2">
                                        <span class="h4">{{ formatCurrency($package->price_monthly) }}</span>
                                        <span class="text-muted">/{{ __('mo') }}</span>
                                    </div>
                                    <ul class="list-unstyled small mb-4">
                                        @foreach ($package->features as $feature)
                                            <li class="mb-1">
                                                <i class="bi bi-check-circle text-success"></i>
                                                {{ $feature->feature_label_en }}: {{ $feature->value }}
                                            </li>
                                        @endforeach
                                    </ul>
                                    <div class="mt-auto">
                                        @if ($subscription?->package_id === $package->id)
                                            <button class="btn btn-outline-secondary w-100" disabled>{{ __('Current Plan') }}</button>
                                        @else
                                            <form method="POST" action="{{ route('billing.change-plan') }}"
                                                  onsubmit="return confirm('{{ __('Switch to this plan now?') }}');">
                                                @csrf
                                                <input type="hidden" name="package_id" value="{{ $package->id }}">
                                                <button type="submit" class="btn btn-primary w-100">{{ __('Switch to this Plan') }}</button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <p class="text-muted small mt-3 mb-0">
                    {{ __('Plan changes take effect immediately. No payment gateway is connected yet — billing is currently managed manually by our team.') }}
                </p>
            </div>
        </div>
    @endcan

    <div class="row g-4">
        <div class="col-12 col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white"><strong>{{ __('Plan History') }}</strong></div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('Package') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Started') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($history as $sub)
                                <tr>
                                    <td>{{ $sub->package?->translated('name') ?? '—' }}</td>
                                    <td><span class="badge bg-light text-dark border">{{ ucfirst($sub->status) }}</span></td>
                                    <td>{{ formatDate($sub->starts_at) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted py-3">{{ __('No history yet.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white"><strong>{{ __('Transaction History') }}</strong></div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('Amount') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Date') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($transactions as $tx)
                                <tr>
                                    <td>{{ formatCurrency($tx->amount) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $tx->status === 'success' ? 'success' : ($tx->status === 'pending' ? 'warning' : 'danger') }}">
                                            {{ ucfirst($tx->status) }}
                                        </span>
                                    </td>
                                    <td>{{ formatDate($tx->created_at) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted py-3">{{ __('No transactions yet.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>