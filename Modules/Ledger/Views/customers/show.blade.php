@extends($ledgerLayout)
@section('heading', $customer->name)
@section('ledger-content')

<div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
    <div class="d-flex align-items-center gap-3">
        @if ($customer->photo)
            <img src="{{ asset('storage/' . $customer->photo) }}" class="rounded-circle" style="width:56px;height:56px;object-fit:cover;">
        @else
            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center" style="width:56px;height:56px;">
                <i class="bi bi-person fs-3 text-muted"></i>
            </div>
        @endif
        <div>
            <h4 class="mb-0">{{ $customer->name }}</h4>
            <div class="text-muted small">
                {{ $customer->mobile ?? '—' }} @if($customer->city) · {{ $customer->city }} @endif
            </div>
        </div>
    </div>
     <div class="d-flex gap-2">
        <a href="{{ route('ledger.customers.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> {{ __('Back') }}
        </a>
        <a href="{{ route('ledger.customers.statement.pdf', $customer) }}" class="btn btn-outline-danger" target="_blank"> 
            <i class="bi bi-file-earmark-pdf"></i> {{ __('Download PDF') }}
        </a>
        @can('easykhata.manage-transactions')
            <a href="{{ route('ledger.transactions.index', ['customer_id' => $customer->id]) }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> {{ __('New Transaction') }}
            </a>
        @endcan
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="ledger-stat-card p-3 h-100">
            <div class="text-muted small">{{ __('Current Balance') }}</div>
            <div class="h4 mb-0 {{ $balance > 0 ? 'text-danger' : ($balance < 0 ? 'text-success' : '') }}">
                {{ formatCurrency(abs($balance)) }}
            </div>
            <div class="small text-muted">
                {{ $balance > 0 ? __('Receivable (You Owes)') : ($balance < 0 ? __('Credit (you owe them)') : __('Settled')) }}
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="ledger-stat-card p-3 h-100">
            <div class="text-muted small">{{ __('Opening Balance') }}</div>
            <div class="h5 mb-0">{{ formatCurrency($customer->opening_balance) }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="ledger-stat-card p-3 h-100">
            <div class="text-muted small">{{ __('Total Debit') }} ({{ __('Amount Paid') }})</div>
            <div class="h5 mb-0 text-success">{{ formatCurrency($totalDebit) }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="ledger-stat-card p-3 h-100">
            <div class="text-muted small">{{ __('Total Credit') }} ({{ __('Amount Owe') }})</div>
            <div class="h5 mb-0 text-danger ">{{ formatCurrency($totalCredit) }}</div>
        </div>
    </div>
</div>

@if ($customer->notes)
    <div class="alert alert-light border mb-4">
        <i class="bi bi-sticky"></i> {{ $customer->notes }}
    </div>
@endif

<div class="card shadow-sm">
    <div class="card-header bg-white"><strong>{{ __('Transaction History') }}</strong></div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle ledger-responsive-table">
            <thead>
                <tr>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Type') }}</th>
                    <th>{{ __('Reference') }}</th>
                    <th>{{ __('Method') }}</th>
                    <th class="text-end">{{ __('Debit') }}</th>
                    <th class="text-end">{{ __('Credit') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($transactions as $tx)
                    <tr>
                        <td data-label="{{ __('Date') }}">{{ formatDate($tx->transaction_date) }}</td>
                        <td data-label="{{ __('Type') }}">
                            <span class="badge ledger-badge-{{ $tx->type }}">
                                {{ ucfirst(str_replace('_', ' ', $tx->type)) }}
                                ({{ $tx->type === 'debit' ? __('He Paid') : __('He Owe') }})
                            </span>
                        </td>
                        <td data-label="{{ __('Reference') }}">{{ $tx->reference_no ?? '—' }}</td>
                        <td data-label="{{ __('Method') }}">{{ $tx->paymentMethod?->name ?? '—' }}</td>
                        <td data-label="{{ __('Debit') }}" class="text-end text-danger">
                            {{ in_array($tx->type, ['debit', 'opening_balance']) ? formatCurrency($tx->amount) : '—' }}
                        </td>
                        <td data-label="{{ __('Credit') }}" class="text-end text-success">
                            {{ $tx->type === 'credit' ? formatCurrency($tx->amount) : '—' }}
                        </td>
                    </tr>
                @empty
                    <tr class="ledger-row-empty">
                        <td colspan="6">
                            @include('ledger::partials.empty-state', [
                                'icon' => 'bi-receipt',
                                'title' => __('No transactions yet for this customer'),
                            ])
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($transactions->hasPages())
        <div class="card-footer bg-white">{{ $transactions->links() }}</div>
    @endif
</div>

@endsection