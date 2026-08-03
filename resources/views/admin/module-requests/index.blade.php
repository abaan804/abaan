<x-admin-layout>
    @section('title', 'Module Requests')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">{{ __('Module Requests') }}</h3>
        @if ($pendingCount > 0)
            <span class="badge bg-danger">{{ $pendingCount }} {{ __('pending') }}</span>
        @endif
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.module-requests.index') }}" class="row g-2">
                <div class="col-12 col-md-3">
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="pending" {{ request('status', 'pending') === 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>{{ __('Approved') }}</option>
                        <option value="declined" {{ request('status') === 'declined' ? 'selected' : '' }}>{{ __('Declined') }}</option>
                    </select>
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
                        <th>{{ __('Module') }}</th>
                        <th>{{ __('Requested By') }}</th>
                        <th>{{ __('Date') }}</th>
                        <th class="text-end">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($requests as $req)
                        <tr>
                            <td><a href="{{ route('admin.companies.show', $req->company) }}">{{ $req->company->name }}</a></td>
                            <td>{{ $req->moduleDefinition->translated('name') }}</td>
                            <td>{{ $req->requestedBy?->name ?? '—' }}</td>
                            <td>{{ formatDate($req->created_at) }}</td>
                            <td class="text-end">
                                @if ($req->status === 'pending')
                                    <form method="POST" action="{{ route('admin.module-requests.approve', $req) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-success">{{ __('Approve') }}</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.module-requests.decline', $req) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('Decline') }}</button>
                                    </form>
                                @else
                                    <span class="badge bg-{{ $req->status === 'approved' ? 'success' : 'secondary' }}">
                                        {{ ucfirst($req->status) }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">{{ __('No requests found.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($requests->hasPages())
            <div class="card-footer bg-white">{{ $requests->links() }}</div>
        @endif
    </div>
</x-admin-layout>