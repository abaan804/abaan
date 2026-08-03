@extends('layouts.admin')
@section('title', __('Review Renewal Request'))

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0">{{ __('Review Renewal Request') }} #{{ $renewalRequest->id }}</h4>
            <span class="badge {{ $renewalRequest->status_badge }} fs-6 mt-1">
                {{ $renewalRequest->status_label }}
            </span>
        </div>
        <a href="{{ route('admin.renewal-requests.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> {{ __('Back') }}
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row g-4">
        {{-- LEFT: Request Details --}}
        <div class="col-12 col-lg-7">

            {{-- Company + Request Info --}}
            <div class="card border-0 shadow-sm mb-4" style="border-radius:14px;">
                <div class="card-header bg-white border-0">
                    <strong>{{ __('Request Details') }}</strong>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="text-muted small">{{ __('Company') }}</div>
                            <div class="fw-bold fs-5">{{ $renewalRequest->company->name }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">{{ __('Submitted By') }}</div>
                            <div class="fw-semibold">{{ $renewalRequest->submittedBy->name }}</div>
                            <div class="text-muted small">{{ $renewalRequest->submittedBy->email }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">{{ __('Package Requested') }}</div>
                            <div class="fw-semibold">{{ $renewalRequest->package->name }}</div>
                            <div class="text-muted small">
                                {{ setting('currency_symbol','Rs.') }}{{ $renewalRequest->package->formatted_price }} / {{ __('month') }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">{{ __('Duration Requested') }}</div>
                            <div class="fw-semibold">
                                {{ $renewalRequest->billing_months }} {{ __('month(s)') }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">{{ __('Amount') }}</div>
                            <div class="fw-bold text-success fs-5">
                                {{ setting('currency_symbol','Rs.') }}{{ number_format($renewalRequest->amount, 2) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">{{ __('Payment Method') }}</div>
                            <div class="fw-semibold">{{ $renewalRequest->payment_method ?? '—' }}</div>
                        </div>
                        @if ($renewalRequest->transaction_id)
                            <div class="col-md-6">
                                <div class="text-muted small">{{ __('Transaction ID') }}</div>
                                <div class="fw-semibold font-monospace">{{ $renewalRequest->transaction_id }}</div>
                            </div>
                        @endif
                        <div class="col-md-6">
                            <div class="text-muted small">{{ __('Submitted On') }}</div>
                            <div class="fw-semibold">{{ $renewalRequest->created_at->format('d M Y, h:i A') }}</div>
                        </div>
                        @if ($renewalRequest->note)
                            <div class="col-12">
                                <div class="text-muted small">{{ __('Company Note') }}</div>
                                <div class="p-2 bg-light rounded small mt-1">{{ $renewalRequest->note }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Included Modules --}}
            <div class="card border-0 shadow-sm mb-4" style="border-radius:14px;">
                <div class="card-header bg-white border-0">
                    <strong>{{ __('Package Modules') }}</strong>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        @foreach ($renewalRequest->package->moduleDefinitions as $m)
                            <span class="badge bg-light text-dark border p-2">
                                <i class="bi {{ $m->icon }}"></i> {{ $m->name_en }}
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Company Subscription History --}}
            @if ($renewalRequest->company->subscriptions->isNotEmpty())
                <div class="card border-0 shadow-sm mb-4" style="border-radius:14px;">
                    <div class="card-header bg-white border-0">
                        <strong>{{ __('Recent Subscription History') }}</strong>
                    </div>
                    <div class="card-body p-0">
                        @foreach ($renewalRequest->company->subscriptions as $sub)
                            <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                                <div class="small">
                                    <span class="fw-semibold">{{ $sub->package?->name ?? '—' }}</span>
                                    <span class="text-muted ms-2">
                                        {{ $sub->starts_at?->format('d M Y') ?? $sub->trial_started_at?->format('d M Y') }}
                                    </span>
                                </div>
                                <span class="badge {{ $sub->status_badge }}">{{ ucfirst($sub->status) }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Review actions --}}
            @if ($renewalRequest->isPending())
                <div class="row g-3">
                    {{-- APPROVE --}}
                    <div class="col-12 col-md-7">
                        <div class="card border-success border-0 shadow-sm" style="border-radius:14px;">
                            <div class="card-header bg-white border-0">
                                <strong class="text-success">
                                    <i class="bi bi-check-circle"></i> {{ __('Approve & Activate') }}
                                </strong>
                            </div>
                            <div class="card-body">
                                <form method="POST"
                                      action="{{ route('admin.renewal-requests.approve', $renewalRequest) }}"
                                      onsubmit="return confirm('{{ __('Approve this renewal and activate the subscription?') }}')">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label small fw-semibold">{{ __('Confirm Billing Months') }}</label>
                                        <select name="billing_months" class="form-select form-select-sm" required>
                                            @foreach ([1 => '1 Month', 2 => '2 Months', 3 => '3 Months', 6 => '6 Months', 12 => '12 Months'] as $m => $label)
                                                <option value="{{ $m }}"
                                                        {{ $renewalRequest->billing_months == $m ? 'selected' : '' }}>
                                                    {{ __($label) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-semibold">{{ __('Confirm Amount Received') }}</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">{{ setting('currency_symbol','Rs.') }}</span>
                                            <input type="number" name="price_paid" step="0.01"
                                                   value="{{ $renewalRequest->amount }}"
                                                   class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-semibold">{{ __('Admin Note') }} <span class="text-muted">({{ __('optional') }})</span></label>
                                        <textarea name="admin_note" rows="2" class="form-control form-control-sm"
                                                  placeholder="{{ __('Payment verified via...') }}"></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="bi bi-check2-circle"></i> {{ __('Approve & Activate') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- REJECT --}}
                    <div class="col-12 col-md-5">
                        <div class="card border-0 shadow-sm h-100" style="border-radius:14px;">
                            <div class="card-header bg-white border-0">
                                <strong class="text-danger">
                                    <i class="bi bi-x-circle"></i> {{ __('Reject') }}
                                </strong>
                            </div>
                            <div class="card-body">
                                <form method="POST"
                                      action="{{ route('admin.renewal-requests.reject', $renewalRequest) }}"
                                      onsubmit="return confirm('{{ __('Reject this renewal request?') }}')">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label small fw-semibold">
                                            {{ __('Reason for Rejection') }} <span class="text-danger">*</span>
                                        </label>
                                        <textarea name="admin_note" rows="4"
                                                  class="form-control form-control-sm"
                                                  placeholder="{{ __('e.g. Payment amount does not match, unclear screenshot...') }}"
                                                  required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-danger w-100">
                                        <i class="bi bi-x-lg"></i> {{ __('Reject Request') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            @else
                {{-- Reviewed info --}}
                <div class="card border-0 shadow-sm" style="border-radius:14px;">
                    <div class="card-header bg-white border-0">
                        <strong>{{ __('Review Decision') }}</strong>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">{{ __('Decision') }}</span>
                            <span class="badge {{ $renewalRequest->status_badge }} fs-6">
                                {{ $renewalRequest->status_label }}
                            </span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">{{ __('Reviewed By') }}</span>
                            <span class="fw-semibold">{{ $renewalRequest->reviewedBy?->name ?? '—' }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">{{ __('Reviewed At') }}</span>
                            <span class="fw-semibold">{{ $renewalRequest->reviewed_at?->format('d M Y, H:i') }}</span>
                        </div>
                        @if ($renewalRequest->admin_note)
                            <div class="mt-3">
                                <div class="text-muted small">{{ __('Admin Note') }}</div>
                                <div class="p-2 bg-light rounded small mt-1">
                                    {{ $renewalRequest->admin_note }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

        </div>

        {{-- RIGHT: Payment Screenshot --}}
        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm" style="border-radius:14px;position:sticky;top:1rem;">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <strong>{{ __('Payment Screenshot') }}</strong>
                    <a href="{{ route('admin.renewal-requests.screenshot', $renewalRequest) }}"
                       target="_blank"
                       class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-arrow-up-right-square"></i> {{ __('Open Full Size') }}
                    </a>
                </div>
                <div class="card-body p-2">
                    @php
                        $ext = strtolower(pathinfo($renewalRequest->payment_screenshot, PATHINFO_EXTENSION));
                    @endphp
                    @if ($ext === 'pdf')
                        <div class="text-center py-5">
                            <i class="bi bi-file-earmark-pdf text-danger" style="font-size:4rem;"></i>
                            <p class="mt-2 text-muted">{{ __('PDF Document') }}</p>
                            <a href="{{ route('admin.renewal-requests.screenshot', $renewalRequest) }}"
                               target="_blank" class="btn btn-outline-danger">
                                <i class="bi bi-eye"></i> {{ __('View PDF') }}
                            </a>
                        </div>
                    @else
                        <a href="{{ route('admin.renewal-requests.screenshot', $renewalRequest) }}"
                           target="_blank">
                            <img src="{{ route('admin.renewal-requests.screenshot', $renewalRequest) }}"
                                 class="img-fluid rounded"
                                 style="width:100%;border:1px solid #e5e7eb;"
                                 alt="{{ __('Payment Screenshot') }}">
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection