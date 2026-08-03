 
{{-- @extends($masjidLayout ?? 'masjid::layouts.app') --}}
@extends('masjid::layouts.standalone')
@section('heading', __('Financial Summary'))
@section('masjid-content')

{{-- Filters --}}
<div class="card shadow-sm border-0 mb-4" style="border-radius:14px;">
    <div class="card-body">
        <form method="GET" action="{{ route('masjid.mosque.financial.index', $mosque) }}">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small">{{ __('Season') }}</label>
                    <select name="season_id" class="form-select">
                        <option value="">{{ __('All Seasons') }}</option>
                        @foreach ($seasons as $s)
                            <option value="{{ $s->id }}" {{ $seasonId == $s->id ? 'selected' : '' }}>
                                {{ $s->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">{{ __('From Date') }}</label>
                    <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">{{ __('To Date') }}</label>
                    <input type="date" name="date_to" value="{{ $dateTo }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-funnel"></i> {{ __('Apply') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@if ($selectedSeason)
    <div class="alert alert-info border-0 mb-4">
        <i class="bi bi-calendar3"></i>
        {{ __('Showing financial data for:') }} <strong>{{ $selectedSeason->name }}</strong>
        ({{ formatDate($selectedSeason->start_date) }} — {{ formatDate($selectedSeason->end_date) }})
    </div>
@endif

{{-- Financial Summary Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="mj-stat-card p-3 text-center h-100"
             style="border-left:4px solid var(--mj-primary);">
            <div class="small text-muted mb-1">
                <i class="bi bi-people"></i> {{ __('Season Collections') }}
            </div>
            <div class="h4 mb-0" style="color:var(--mj-primary);">
                {{ formatCurrency($totalPayments) }}
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="mj-stat-card p-3 text-center h-100"
             style="border-left:4px solid #059669;">
            <div class="small text-muted mb-1">
                <i class="bi bi-gift"></i> {{ __('Donations') }}
            </div>
            <div class="h4 mb-0 text-success">{{ formatCurrency($totalDonations) }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="mj-stat-card p-3 text-center h-100"
             style="border-left:4px solid #dc2626;">
            <div class="small text-muted mb-1">
                <i class="bi bi-cash-stack"></i> {{ __('Total Expenses') }}
            </div>
            <div class="h4 mb-0 text-danger">{{ formatCurrency($totalExpenses) }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="mj-stat-card p-3 text-center h-100"
             style="border-left:4px solid {{ $netBalance >= 0 ? '#059669' : '#dc2626' }};">
            <div class="small text-muted mb-1">
                <i class="bi bi-calculator"></i> {{ __('Net Balance') }}
            </div>
            <div class="h4 mb-0 {{ $netBalance >= 0 ? 'text-success' : 'text-danger' }}">
                {{ formatCurrency(abs($netBalance)) }}
                @if ($netBalance < 0) <small class="text-danger">({{ __('Deficit') }})</small> @endif
            </div>
        </div>
    </div>
</div>

{{-- Formula Display --}}
<div class="card border-0 shadow-sm mb-4" style="border-radius:14px;">
    <div class="card-body">
        <h6 class="fw-bold mb-3">{{ __('Balance Calculation') }}</h6>
        <div class="d-flex align-items-center gap-2 flex-wrap" style="font-size:1.05rem;">
            <span class="badge bg-primary p-2 fs-6">{{ formatCurrency($totalPayments) }}</span>
            <span class="text-muted fw-bold">{{ __('(Season Collections)') }}</span>
            <span class="fw-bold fs-5 mx-1">+</span>
            <span class="badge bg-success p-2 fs-6">{{ formatCurrency($totalDonations) }}</span>
            <span class="text-muted fw-bold">{{ __('(Donations)') }}</span>
            <span class="fw-bold fs-5 mx-1">−</span>
            <span class="badge bg-danger p-2 fs-6">{{ formatCurrency($totalExpenses) }}</span>
            <span class="text-muted fw-bold">{{ __('(Expenses)') }}</span>
            <span class="fw-bold fs-5 mx-1">=</span>
            <span class="badge p-2 fs-6 {{ $netBalance >= 0 ? 'bg-success' : 'bg-danger' }}">
                {{ formatCurrency($netBalance) }}
            </span>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    {{-- Donation Breakdown --}}
    <div class="col-12 col-md-6">
        <div class="card border-0 shadow-sm h-100" style="border-radius:14px;">
            <div class="card-header bg-white border-0">
                <strong><i class="bi bi-gift text-success"></i> {{ __('Donation Breakdown') }}</strong>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted"><i class="bi bi-person-check"></i> {{ __('Named Donors') }}</span>
                    <span class="fw-semibold text-success">{{ formatCurrency($namedDonations) }}</span>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted"><i class="bi bi-incognito"></i> {{ __('Anonymous') }}</span>
                    <span class="fw-semibold text-success">{{ formatCurrency($anonymousDonations) }}</span>
                </div>
                <div class="d-flex justify-content-between py-2 fw-bold">
                    <span>{{ __('Total') }}</span>
                    <span class="text-success">{{ formatCurrency($totalDonations) }}</span>
                </div>
                <div class="mt-3">
                    <a href="{{ route('masjid.mosque.donations.index', $mosque) }}"
                       class="btn btn-sm btn-outline-success w-100">
                        {{ __('View All Donations') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Expense Breakdown --}}
    <div class="col-12 col-md-6">
        <div class="card border-0 shadow-sm h-100" style="border-radius:14px;">
            <div class="card-header bg-white border-0">
                <strong><i class="bi bi-cash-stack text-danger"></i> {{ __('Expense Breakdown') }}</strong>
            </div>
            <div class="card-body">
                @forelse ($expenseByCategory as $cat)
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted small">
                            <i class="bi {{ \Modules\Masjid\Models\MasjidExpense::CATEGORY_ICONS[$cat->category] ?? 'bi-three-dots' }}"></i>
                            {{ __(\Modules\Masjid\Models\MasjidExpense::CATEGORIES[$cat->category] ?? $cat->category) }}
                        </span>
                        <span class="fw-semibold text-danger">{{ formatCurrency($cat->total) }}</span>
                    </div>
                @empty
                    <p class="text-muted small text-center py-3">{{ __('No expenses recorded.') }}</p>
                @endforelse
                <div class="d-flex justify-content-between py-2 fw-bold border-top mt-1">
                    <span>{{ __('Total') }}</span>
                    <span class="text-danger">{{ formatCurrency($totalExpenses) }}</span>
                </div>
                <div class="mt-3">
                    <a href="{{ route('masjid.mosque.expenses.index', $mosque) }}"
                       class="btn btn-sm btn-outline-danger w-100">
                        {{ __('View All Expenses') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Recent Transactions --}}
<div class="row g-4">
    <div class="col-12 col-md-4">
        <div class="card border-0 shadow-sm" style="border-radius:14px;">
            <div class="card-header bg-white border-0 small fw-bold">
                <i class="bi bi-people" style="color:var(--mj-primary);"></i>
                {{ __('Recent Payments') }}
            </div>
            <div class="card-body p-0">
                @forelse ($recentPayments as $pay)
                    <div class="d-flex justify-content-between px-3 py-2 border-bottom">
                        <div class="small">
                            <div class="fw-semibold">{{ $pay->member?->name ?? '—' }}</div>
                            <div class="text-muted">{{ formatDate($pay->payment_date) }}</div>
                        </div>
                        <span class="text-success fw-bold small">{{ formatCurrency($pay->amount_paid) }}</span>
                    </div>
                @empty
                    <p class="text-muted small text-center py-3 mb-0">{{ __('None') }}</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-12 col-md-4">
        <div class="card border-0 shadow-sm" style="border-radius:14px;">
            <div class="card-header bg-white border-0 small fw-bold">
                <i class="bi bi-gift text-success"></i>
                {{ __('Recent Donations') }}
            </div>
            <div class="card-body p-0">
                @forelse ($recentDonations as $don)
                    <div class="d-flex justify-content-between px-3 py-2 border-bottom">
                        <div class="small">
                            <div class="fw-semibold">{{ $don->donor_display_name }}</div>
                            <div class="text-muted">{{ formatDate($don->donation_date) }}</div>
                        </div>
                        <span class="text-success fw-bold small">{{ formatCurrency($don->amount) }}</span>
                    </div>
                @empty
                    <p class="text-muted small text-center py-3 mb-0">{{ __('None') }}</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-12 col-md-4">
        <div class="card border-0 shadow-sm" style="border-radius:14px;">
            <div class="card-header bg-white border-0 small fw-bold">
                <i class="bi bi-cash-stack text-danger"></i>
                {{ __('Recent Expenses') }}
            </div>
            <div class="card-body p-0">
                @forelse ($recentExpenses as $exp)
                    <div class="d-flex justify-content-between px-3 py-2 border-bottom">
                        <div class="small">
                            <div class="fw-semibold">{{ $exp->title }}</div>
                            <div class="text-muted">{{ formatDate($exp->expense_date) }}</div>
                        </div>
                        <span class="text-danger fw-bold small">{{ formatCurrency($exp->amount) }}</span>
                    </div>
                @empty
                    <p class="text-muted small text-center py-3 mb-0">{{ __('None') }}</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

@endsection