@extends('layouts.admin')
@section('title', __('Renewal Requests'))

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0">{{ __('Subscription Renewal Requests') }}</h4>
            @if ($pendingCount > 0)
                <span class="badge bg-danger fs-6 ms-2">
                    {{ $pendingCount }} {{ __('pending') }}
                </span>
            @endif
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Status Filter Tabs --}}
    <div class="d-flex gap-2 mb-4 border-bottom pb-3">
        @foreach (['pending' => __('Pending'), 'approved' => __('Approved'), 'rejected' => __('Rejected'), 'all' => __('All')] as $val => $label)
            <a href="{{ request()->fullUrlWithQuery(['status' => $val]) }}"
               class="btn btn-sm {{ $status === $val ? 'btn-primary' : 'btn-outline-secondary' }}">
                {{ $label }}
                @if ($val === 'pending' && $pendingCount > 0)
                    <span class="badge bg-danger ms-1">{{ $pendingCount }}</span>
                @endif
            </a>
        @endforeach
    </div>

    <div class="card border-0 shadow-sm" style="border-radius:14px;">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('Company') }}</th>
                        <th>{{ __('Package') }}</th>
                        <th>{{ __('Duration') }}</th>
                        <th>{{ __('Amount') }}</th>
                        <th>{{ __('Payment Method') }}</th>
                        <th>{{ __('Screenshot') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Submitted') }}</th>
                        <th class="text-end">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($requests as $rr)
                        <tr>
                            <td class="text-muted small">{{ $rr->id }}</td>
                            <td>
                                <div class="fw-semibold">{{ $rr->company->name }}</div>
                                <div class="text-muted small">{{ $rr->submittedBy->name }}</div>
                            </td>
                            <td>{{ $rr->package->name }}</td>
                            <td>{{ $rr->billing_months }} {{ __('mo.') }}</td>
                            <td class="fw-bold text-success">
                                {{ setting('currency_symbol','Rs.') }}{{ number_format($rr->amount, 2) }}
                            </td>
                            <td>{{ $rr->payment_method ?? '—' }}</td>
                            <td>
                                <a href="{{ route('admin.renewal-requests.screenshot', $rr) }}"
                                   target="_blank"
                                   class="btn btn-sm btn-outline-primary"
                                   title="{{ __('View Screenshot') }}">
                                    <i class="bi bi-image"></i>
                                </a>
                            </td>
                            <td>
                                <span class="badge {{ $rr->status_badge }}">
                                    {{ $rr->status_label }}
                                </span>
                            </td>
                            <td class="small text-muted">{{ $rr->created_at->format('d M Y, H:i') }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.renewal-requests.show', $rr) }}"
                                   class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-eye"></i> {{ __('Review') }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                {{ __('No renewal requests found.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($requests->hasPages())
            <div class="p-3">{{ $requests->links() }}</div>
        @endif
    </div>
</div>
@endsection