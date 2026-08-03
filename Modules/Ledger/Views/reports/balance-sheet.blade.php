@extends($ledgerLayout)
@section('heading', __('Balance Sheet'))
@section('ledger-content')

<div class="alert alert-warning">
    <i class="bi bi-info-circle"></i>
    {{ __('This is a simplified snapshot based on cash flow and outstanding balances only — it does not include fixed assets, equity, or capital accounts, and should not be treated as a complete accounting balance sheet.') }}
</div>

<div class="row g-4">
    <div class="col-12 col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white"><strong>{{ __('Assets') }}</strong></div>
            <div class="card-body">
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span>{{ __('Cash & Bank Balance') }}</span>
                    <span class="fw-semibold">{{ formatCurrency($sheet['cash']) }}</span>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span>{{ __('Receivables (You Owe Customer)') }}</span>
                    <span class="fw-semibold text-success">{{ formatCurrency($sheet['receivables']) }}</span>
                </div>
                <div class="d-flex justify-content-between py-2 fw-bold mt-2">
                    <span>{{ __('Total Assets') }}</span>
                    <span>{{ formatCurrency($sheet['total_assets']) }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white"><strong>{{ __('Liabilities') }}</strong></div>
            <div class="card-body">
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span>{{ __('Payables (Suppliers Owe You)') }}</span>
                    <span class="fw-semibold text-danger">{{ formatCurrency($sheet['payables']) }}</span>
                </div>
                <div class="d-flex justify-content-between py-2 fw-bold mt-2">
                    <span>{{ __('Total Liabilities') }}</span>
                    <span>{{ formatCurrency($sheet['total_liabilities']) }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="ledger-stat-card p-4 text-center">
            <div class="text-muted small">{{ __('Net Position (Assets − Liabilities)') }}</div>
            <div class="h2 mb-0 {{ $sheet['net_position'] >= 0 ? 'text-success' : 'text-danger' }}">
                {{ formatCurrency(abs($sheet['net_position'])) }}
            </div>
        </div>
    </div>
</div>

@endsection