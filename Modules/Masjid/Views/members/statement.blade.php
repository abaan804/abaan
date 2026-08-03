@extends($masjidLayout ?? 'masjid::layouts.app')
@section('heading', $member->name)
@section('masjid-content')

<div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
    <div class="d-flex align-items-center gap-3">
        @if ($member->photo)
            <img src="{{ asset('storage/' . $member->photo) }}" class="rounded-circle" style="width:54px;height:54px;object-fit:cover;">
        @else
            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:54px;height:54px;background:rgba(27,107,69,.12);">
                <i class="bi bi-person fs-3" style="color:var(--mj-primary);"></i>
            </div>
        @endif
        <div>
            <h4 class="mb-0">{{ $member->name }}</h4>
            @if ($member->father_name)
                <div class="text-muted small">{{ __('S/O') }} {{ $member->father_name }}</div>
            @endif
            <div class="text-muted small">{{ $member->mobile }}</div>
        </div>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('masjid.mosque.members.index', [$mosque, $member,'standalone' => 1]) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> {{ __('Back') }}
        </a>
        <a href="{{ route('masjid.mosque.members.statement.pdf', [$mosque, $member,'standalone' => 1]) }}" class="btn btn-outline-danger" target="_blank">
            <i class="bi bi-file-earmark-pdf"></i> {{ __('Download PDF') }}
        </a>
        @can('masjid.manage-payments')
            <a href="{{ route('masjid.mosque.payments.index', [$mosque,'standalone' => 1]) }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> {{ __('Record Payment') }}
            </a>
        @endcan
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="mj-stat-card p-3 h-100">
            <div class="text-muted small">{{ __('Total Due') }}</div>
            <div class="h4 mb-0">{{ formatCurrency($statement['total_due']) }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="mj-stat-card p-3 h-100">
            <div class="text-muted small">{{ __('Total Paid') }}</div>
            <div class="h4 mb-0 text-success">{{ formatCurrency($statement['total_paid']) }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="mj-stat-card p-3 h-100">
            <div class="text-muted small">{{ __('Balance') }}</div>
            @php $balance = $statement['balance']; @endphp
            <div class="h4 mb-0 {{ $balance > 0 ? 'text-danger' : ($balance < 0 ? 'text-info' : 'text-success') }}">
                {{ formatCurrency(abs($balance)) }}
            </div>
            <div class="small text-muted">
                {{ $balance > 0 ? __('Outstanding') : ($balance < 0 ? __('Overpaid') : __('Settled')) }}
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="mj-stat-card p-3 h-100">
            <div class="text-muted small">{{ __('Seasons') }}</div>
            <div class="h4 mb-0">{{ $statement['assignments']->count() }}</div>
        </div>
    </div>
</div>

@if ($member->notes)
    <div class="alert alert-light border mb-4">
        <i class="bi bi-sticky"></i> {{ $member->notes }}
    </div>
@endif

@forelse ($statement['assignments'] as $sm)
    <div class="card shadow-sm border-0 mb-3" style="border-radius:14px;">
        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
            <div>
                <strong>{{ $sm->season->name }}</strong>
                <span class="ms-2 badge mj-badge-{{ $sm->status }}">{{ ucfirst($sm->status) }}</span>
            </div>
            <div class="text-end">
                <div class="small text-muted">{{ __('Due') }}: {{ formatCurrency($sm->amount_due) }}</div>
                <div class="small text-muted">{{ __('Paid') }}: <span class="text-success">{{ formatCurrency($sm->amount_paid) }}</span></div>
                @if ($sm->balance() > 0)
                    <div class="small text-danger">{{ __('Remaining') }}: {{ formatCurrency($sm->balance()) }}</div>
                @elseif ($sm->isOverpaid())
                    <div class="small text-info">{{ __('Excess') }}: {{ formatCurrency(abs($sm->balance())) }}</div>
                @endif
            </div>
        </div>
        @if ($sm->payments->isNotEmpty())
            <div class="table-responsive">
                <table class="table table-sm mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Receipt') }}</th>
                            <th>{{ __('Method') }}</th>
                            <th>{{ __('Received By') }}</th>
                            <th class="text-end">{{ __('Amount') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sm->payments as $pay)
                            <tr>
                                <td>{{ formatDate($pay->payment_date) }}</td>
                                <td><code>{{ $pay->receipt_no ?? '—' }}</code></td>
                                <td>{{ ucfirst($pay->payment_method) }}</td>
                                <td>{{ $pay->receivedBy?->name ?? '—' }}</td>
                                <td class="text-end text-success fw-semibold">{{ formatCurrency($pay->amount_paid) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="card-body py-2 text-muted small">{{ __('No payments recorded for this season yet.') }}</div>
        @endif
    </div>
@empty
    @include('masjid::partials.empty-state', [
        'icon' => 'bi-calendar-x',
        'title' => __('No seasons assigned'),
        'description' => __('This member has not been assigned to any contribution season yet.'),
    ])
@endforelse

@endsection