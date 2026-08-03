<x-admin-layout>
    @section('title', 'Subscriptions')

    <h3 class="mb-4">{{ __('Subscriptions') }}</h3>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.subscriptions.index') }}" class="row g-2">
                <div class="col-12 col-md-5">
                    <input type="text" name="search" value="{{ request('search') }}"
                           class="form-control" placeholder="{{ __('Search by company name') }}">
                </div>
                <div class="col-12 col-md-3">
                    <select name="status" class="form-select">
                        <option value="">{{ __('All Statuses') }}</option>
                        @foreach (['trial', 'active', 'past_due', 'cancelled', 'expired'] as $status)
                            <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $status)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-2">
                    <button type="submit" class="btn btn-primary w-100">{{ __('Filter') }}</button>
                </div>
                <div class="col-12 col-md-2">
                    <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-outline-secondary w-100">{{ __('Reset') }}</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>{{ __('Company') }}</th>
                        <th>{{ __('Package') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Started') }}</th>
                        <th>{{ __('Trial Ends') }}</th>
                        <th>{{ __('Ends') }}</th>
                        <th class="text-end">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($subscriptions as $sub)
                        <tr>
                            <td>{{ $sub->company?->name ?? '—' }}</td>
                            <td>{{ app()->getLocale() == 'ar'
                                ? ($sub->package?->name_ar ?? '—')
                                : ($sub->package?->name_en ?? '—') }}</td>
                            <td>
                                @php
                                    $statusColors = ['trial' => 'info', 'active' => 'success', 'past_due' => 'warning', 'cancelled' => 'secondary', 'expired' => 'danger'];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$sub->status] ?? 'secondary' }}">
                                    {{ ucfirst(str_replace('_', ' ', $sub->status)) }}
                                </span>
                            </td>
                            <td>{{ formatDate($sub->starts_at) }}</td>
                            <td>{{ formatDate($sub->trial_ends_at) }}</td>
                            <td>{{ formatDate($sub->ends_at) }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.subscriptions.show', $sub) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> {{ __('View') }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">{{ __('No subscriptions found.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($subscriptions->hasPages())
            <div class="card-footer bg-white">{{ $subscriptions->links() }}</div>
        @endif
    </div>
</x-admin-layout>