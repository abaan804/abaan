<x-admin-layout>
    @section('title', 'Companies')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">{{ __('Companies') }}</h3>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.companies.index') }}" class="row g-2">
                <div class="col-12 col-md-5">
                    <input type="text" name="search" value="{{ request('search') }}"
                           class="form-control" placeholder="{{ __('Search by name, email, or slug') }}">
                </div>
                <div class="col-12 col-md-3">
                    <select name="status" class="form-select">
                        <option value="">{{ __('All Statuses') }}</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                        <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>{{ __('Suspended') }}</option>
                    </select>
                </div>
                <div class="col-12 col-md-2">
                    <button type="submit" class="btn btn-primary w-100">{{ __('Filter') }}</button>
                </div>
                <div class="col-12 col-md-2">
                    <a href="{{ route('admin.companies.index') }}" class="btn btn-outline-secondary w-100">{{ __('Reset') }}</a>
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
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Users') }}</th>
                        <th>{{ __('Current Subscription') }}</th>
                        <th>{{ __('Registered') }}</th>
                        <th class="text-end">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($companies as $company)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $company->name }}</div>
                                <div class="text-muted small">{{ $company->email }}</div>
                            </td>
                            <td>
                                <span class="badge bg-{{ $company->status === 'active' ? 'success' : ($company->status === 'pending' ? 'warning' : 'danger') }}">
                                    {{ ucfirst($company->status) }}
                                </span>
                            </td>
                            <td>{{ $company->users_count }}</td>
                            <td>
                                @php $sub = $company->subscriptions->first(); @endphp
                                @if ($sub)
                                    <span class="badge bg-light text-dark border">{{ ucfirst($sub->status) }}</span>
                                @else
                                    <span class="text-muted small">{{ __('No subscription') }}</span>
                                @endif
                            </td>
                            <td>{{ formatDate($company->created_at) }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.companies.show', $company) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> {{ __('View') }}
                                </a>
                                @if ($company->status === 'suspended')
                                    <form method="POST" action="{{ route('admin.companies.activate', $company) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-success">{{ __('Activate') }}</button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('admin.companies.suspend', $company) }}" class="d-inline"
                                          onsubmit="return confirm('{{ __('Suspend this company? Their users will be unable to access the platform.') }}');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('Suspend') }}</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">{{ __('No companies found.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($companies->hasPages())
            <div class="card-footer bg-white">
                {{ $companies->links() }}
            </div>
        @endif
    </div>
</x-admin-layout>