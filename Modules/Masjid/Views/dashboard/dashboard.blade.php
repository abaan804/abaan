@extends($masjidLayout ?? 'masjid::layouts.app')
@section('heading', __('Dashboard'))
@section('masjid-content')

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="mj-stat-card p-3 h-100">
            <div class="icon-wrap mb-2" style="background:rgba(27,107,69,.1);">
                <i class="bi bi-people" style="color:var(--mj-primary);"></i>
            </div>
            <div class="text-muted small">{{ __('Total Members') }}</div>
            <div class="h4 mb-0">{{ $mosque->members()->count() }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="mj-stat-card p-3 h-100">
            <div class="icon-wrap mb-2" style="background:rgba(201,168,76,.1);">
                <i class="bi bi-cash-coin" style="color:var(--mj-gold);"></i>
            </div>
            <div class="text-muted small">{{ __('Total Collected') }}</div>
            <div class="h4 mb-0 text-success">{{ formatCurrency($totals['total_collected']) }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="mj-stat-card p-3 h-100">
            <div class="icon-wrap mb-2" style="background:rgba(220,38,38,.1);">
                <i class="bi bi-exclamation-circle" style="color:var(--mj-danger);"></i>
            </div>
            <div class="text-muted small">{{ __('Outstanding') }}</div>
            <div class="h4 mb-0 text-danger">{{ formatCurrency($totals['total_outstanding']) }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="mj-stat-card p-3 h-100">
            <div class="icon-wrap mb-2" style="background:rgba(27,107,69,.1);">
                <i class="bi bi-calendar-check" style="color:var(--mj-primary);"></i>
            </div>
            <div class="text-muted small">{{ __("Today's Collection") }}</div>
            <div class="h4 mb-0">{{ formatCurrency($todayCollection) }}</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="mj-stat-card p-3">
            <div class="text-muted small">{{ __('Total Due') }}</div>
            <div class="h5 mb-0">{{ formatCurrency($totals['total_due']) }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="mj-stat-card p-3">
            <div class="text-muted small">{{ __('Month Collection') }}</div>
            <div class="h5 mb-0 text-success">{{ formatCurrency($monthCollection) }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="mj-stat-card p-3">
            <div class="text-muted small">{{ __('Pending Members') }}</div>
            <div class="h5 mb-0 text-warning">{{ $totals['pending_count'] }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="mj-stat-card p-3">
            <div class="text-muted small">{{ __('Overpaid Members') }}</div>
            <div class="h5 mb-0 text-info">{{ $totals['overpaid_count'] }}</div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-12 col-lg-7">
        <div class="card shadow-sm border-0" style="border-radius:14px;">
            <div class="card-header bg-white border-0 pb-0"><strong>{{ __('Monthly Collection') }} {{ now()->year }}</strong></div>
            <div class="card-body"><canvas id="monthlyChart" height="220"></canvas></div>
        </div>
    </div>
    <div class="col-12 col-lg-5">
        <div class="card shadow-sm border-0 h-100" style="border-radius:14px;">
            <div class="card-header bg-white border-0 pb-0"><strong>{{ __('Active Seasons') }}</strong></div>
            <div class="card-body">
                @forelse ($activeSeasons as $season)
                    <div class="d-flex justify-content-between align-items-start py-2 {{ ! $loop->last ? 'border-bottom' : '' }}">
                        <div>
                            <div class="fw-semibold small">{{ $season->name }}</div>
                            <div class="text-muted small">{{ $season->seasonMembers()->count() }} {{ __('members') }}</div>
                        </div>
                        <span class="badge" style="background:rgba(27,107,69,.1);color:var(--mj-primary);">{{ ucfirst($season->frequency) }}</span>
                    </div>
                @empty
                    <p class="text-muted small mb-0">{{ __('No active seasons.') }}</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-8">
        <div class="card shadow-sm border-0" style="border-radius:14px;">
            <div class="card-header bg-white border-0 d-flex justify-content-between pb-0">
                <strong>{{ __('Recent Payments') }}</strong>
                <a href="{{ route('masjid.mosque.payments.index', [$mosque,'standalone' => 1]) }}" class="small text-decoration-none">{{ __('View all') }}</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle mj-responsive-table">
                    <thead>
                        <tr>
                            <th>{{ __('Member') }}</th>
                            <th>{{ __('Season') }}</th>
                            <th>{{ __('Method') }}</th>
                            <th class="text-end">{{ __('Amount') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentPayments as $payment)
                            <tr>
                                <td data-label="{{ __('Member') }}" class="mj-cell-name">{{ $payment->member?->name ?? '—' }}</td>
                                <td data-label="{{ __('Season') }}">{{ $payment->season?->name ?? '—' }}</td>
                                <td data-label="{{ __('Method') }}">{{ ucfirst($payment->payment_method) }}</td>
                                <td data-label="{{ __('Amount') }}" class="text-end fw-semibold text-success">{{ formatCurrency($payment->amount_paid) }}</td>
                            </tr>
                        @empty
                            <tr class="mj-row-empty">
                                <td colspan="4">
                                    @include('masjid::partials.empty-state', ['icon' => 'bi-cash', 'title' => __('No payments yet')])
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-4">
        <div class="card shadow-sm border-0 h-100" style="border-radius:14px;">
            <div class="card-header bg-white border-0 pb-0">
                <strong>{{ __('Pending Members') }}</strong>
            </div>
            <div class="card-body">
                @forelse ($pendingMembers as $sm)
                    <div class="d-flex justify-content-between align-items-center py-2 {{ ! $loop->last ? 'border-bottom' : '' }}">
                        <div>
                            <div class="small fw-semibold">{{ $sm->member?->name ?? '—' }}</div>
                            <div class="text-muted small">{{ $sm->season?->name ?? '—' }}</div>
                        </div>
                        <span class="badge mj-badge-{{ $sm->status }}">{{ formatCurrency($sm->balance()) }}</span>
                    </div>
                @empty
                    <p class="text-muted small text-center mb-0">{{ __('All members are settled up!') }}</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {
    const ctx = document.getElementById('monthlyChart');
    if (!ctx) return;

    const monthlyData = @json($monthlyChart);
    const labels = [];
    const data = [];
    const allMonths = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

    allMonths.forEach((label, i) => {
        const found = monthlyData.find(r => parseInt(r.month) === i + 1);
        labels.push(label);
        data.push(found ? parseFloat(found.total) : 0);
    });

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: '{{ __('Collections') }}',
                data,
                backgroundColor: 'rgba(27,107,69,.7)',
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } }
        }
    });
})();
</script>
@endpush
@endsection