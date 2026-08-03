@extends($masjidLayout ?? 'masjid::layouts.app')
@section('heading', $season->name)
@section('masjid-content')

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h4> {{ $season->name }}</h4>
    <div class="text-muted small">
        {{ formatDate($season->start_date) }} — {{ formatDate($season->end_date) }}
        &nbsp;·&nbsp; {{ formatCurrency($season->contribution_amount) }} {{ __('per member') }}
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('masjid.mosque.reports.index', [$mosque,'standalone' => 1]) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> {{ __('Reports') }}
        </a>
        <a href="{{ route('masjid.mosque.reports.season.pdf', [$mosque, $season]) }}" class="btn btn-outline-danger" target="_blank">
            <i class="bi bi-file-earmark-pdf"></i> {{ __('Download PDF') }}
        </a>
        <a href="{{ route('masjid.mosque.reports.season.export', [$mosque, $season, 'xlsx']) }}" class="btn btn-outline-success">
            <i class="bi bi-file-earmark-excel"></i> {{ __('Excel') }}
        </a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="mj-stat-card p-3">
            <div class="text-muted small">{{ __('Total Members') }}</div>
            <div class="h4 mb-0">{{ $summary['total_members'] }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="mj-stat-card p-3">
            <div class="text-muted small">{{ __('Total Due') }}</div>
            <div class="h4 mb-0">{{ formatCurrency($summary['total_due']) }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="mj-stat-card p-3">
            <div class="text-muted small">{{ __('Total Collected') }}</div>
            <div class="h4 mb-0 text-success">{{ formatCurrency($summary['total_paid']) }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="mj-stat-card p-3">
            <div class="text-muted small">{{ __('Outstanding') }}</div>
            <div class="h4 mb-0 text-danger">{{ formatCurrency($summary['total_outstanding']) }}</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    @foreach (['pending' => 'warning', 'partial' => 'info', 'paid' => 'success', 'overpaid' => 'purple'] as $status => $color)
        <div class="col-6 col-md-3">
            <div class="mj-stat-card p-3 text-center">
                <span class="badge mj-badge-{{ $status }} fs-6">{{ $summary[$status] }}</span>
                <div class="text-muted small mt-1">{{ ucfirst($status) }}</div>
            </div>
        </div>
    @endforeach
</div>

<div class="card shadow-sm border-0" style="border-radius:14px;">
    <div class="card-header bg-white border-0"><strong>{{ __('Member Breakdown') }}</strong></div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle mj-responsive-table">
            <thead>
                <tr>
                    <th>{{ __('Member') }}</th>
                    <th>{{ __('Amount Due') }}</th>
                    <th>{{ __('Amount Paid') }}</th>
                    <th>{{ __('Balance') }}</th>
                    <th>{{ __('Status') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($assignments as $sm)
                    <tr>
                        <td data-label="{{ __('Member') }}" class="mj-cell-name">
                            <a href="{{ route('masjid.mosque.members.statement', [$mosque, $sm->member,'standalone' => 1]) }}" class="text-decoration-none fw-semibold">
                                {{ $sm->member?->name ?? '—' }}
                            </a>
                        </td>
                        <td data-label="{{ __('Due') }}">{{ formatCurrency($sm->amount_due) }}</td>
                        <td data-label="{{ __('Paid') }}" class="text-success">{{ formatCurrency($sm->amount_paid) }}</td>
                        <td data-label="{{ __('Balance') }}" class="{{ $sm->balance() > 0 ? 'text-danger' : ($sm->isOverpaid() ? 'text-info' : 'text-success') }}">
                            {{ formatCurrency(abs($sm->balance())) }}
                        </td>
                        <td data-label="{{ __('Status') }}">
                            <span class="badge mj-badge-{{ $sm->status }}">{{ ucfirst($sm->status) }}</span>
                        </td>
                    </tr>
                @empty
                    <tr class="mj-row-empty">
                        <td colspan="5">
                            @include('masjid::partials.empty-state', ['icon' => 'bi-people', 'title' => __('No members assigned')])
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection