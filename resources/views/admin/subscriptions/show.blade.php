<x-admin-layout>
    @section('title', 'Subscription Details')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">{{ __('Subscription') }} #{{ $subscription->id }}</h3>
        <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> {{ __('Back') }}
        </a>
    </div>

    <div class="row g-4">
        <div class="col-12 col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white"><strong>{{ __('Details') }}</strong></div>
                <div class="card-body">
                    <dl class="row mb-0 small">
                        <dt class="col-5 text-muted">{{ __('Company') }}</dt>
                        <dd class="col-7">
                            <a href="{{ route('admin.companies.show', $subscription->company) }}">{{ $subscription->company?->name }}</a>
                        </dd>
                        <dt class="col-5 text-muted">{{ __('Package') }}</dt>
                        <dd class="col-7">{{ $subscription->package?->translated('name') }}</dd>
                        <dt class="col-5 text-muted">{{ __('Gateway') }}</dt>
                        <dd class="col-7">{{ $subscription->gateway ? ucfirst($subscription->gateway) : __('Manual / None') }}</dd>
                        <dt class="col-5 text-muted">{{ __('Started') }}</dt>
                        <dd class="col-7">{{ formatDate($subscription->starts_at) }}</dd>
                        <dt class="col-5 text-muted">{{ __('Trial Ends') }}</dt>
                        <dd class="col-7">{{ formatDate($subscription->trial_ends_at) }}</dd>
                        <dt class="col-5 text-muted">{{ __('Ends') }}</dt>
                        <dd class="col-7">{{ formatDate($subscription->ends_at) }}</dd>
                        <dt class="col-5 text-muted">{{ __('Cancelled') }}</dt>
                        <dd class="col-7">{{ formatDate($subscription->cancelled_at) }}</dd>
                    </dl>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-white"><strong>{{ __('Override Status') }}</strong></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.subscriptions.update-status', $subscription) }}">
                        @csrf
                        @method('PATCH')
                        <select name="status" class="form-select mb-2">
                            @foreach (['trial', 'active', 'past_due', 'cancelled', 'expired'] as $status)
                                <option value="{{ $status }}" {{ $subscription->status === $status ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $status)) }}
                                </option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-warning w-100">{{ __('Update Status') }}</button>
                        <p class="text-muted small mt-2 mb-0">
                            {{ __('Manual override for support cases. Use with caution — this does not affect any payment gateway.') }}
                        </p>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white"><strong>{{ __('Transaction History') }}</strong></div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('Amount') }}</th>
                                <th>{{ __('Gateway') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Date') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($subscription->transactions as $tx)
                                <tr>
                                    <td>{{ formatCurrency($tx->amount) }}</td>
                                    <td>{{ ucfirst($tx->gateway) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $tx->status === 'success' ? 'success' : ($tx->status === 'pending' ? 'warning' : 'danger') }}">
                                            {{ ucfirst($tx->status) }}
                                        </span>
                                    </td>
                                    <td>{{ formatDate($tx->created_at) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-3">{{ __('No transactions yet.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>