@extends($ledgerLayout)
@section('heading', __('Reports'))
@section('ledger-content')

<div class="row g-3">
    @php
        $reports = [
            ['icon' => 'bi-journal-text', 'title' => __('Cash Book'), 'desc' => __('All cash in/out with running balance'), 'route' => 'ledger.reports.cash-book'],
            ['icon' => 'bi-graph-up-arrow', 'title' => __('Income & Expense / P&L'), 'desc' => __('Profit and loss breakdown by category'), 'route' => 'ledger.reports.income-expense'],
            ['icon' => 'bi-people', 'title' => __('Customer Ledger'), 'desc' => __('Per-customer statement — visit a customer profile'), 'route' => 'ledger.customers.index'],
            ['icon' => 'bi-truck', 'title' => __('Supplier Ledger'), 'desc' => __('Per-supplier statement — visit a supplier profile'), 'route' => 'ledger.suppliers.index'],
            ['icon' => 'bi-exclamation-circle', 'title' => __('Outstanding Receivables/Payables'), 'desc' => __('Who owes you and who you owe right now'), 'route' => 'ledger.reports.outstanding'],
            ['icon' => 'bi-calendar3', 'title' => __('Period Summary'), 'desc' => __('Daily, weekly, monthly, or yearly breakdown'), 'route' => 'ledger.reports.period-summary'],
            ['icon' => 'bi-clipboard-data', 'title' => __('Balance Sheet'), 'desc' => __('Simplified assets vs liabilities snapshot'), 'route' => 'ledger.reports.balance-sheet'],
                    ];
    @endphp

    @foreach ($reports as $report)
        <div class="col-12 col-md-6 col-lg-4">
            <a href="{{ route($report['route'], ['standalone' => 1]) }}" class="text-decoration-none">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <i class="bi {{ $report['icon'] }} fs-2 text-primary"></i>
                        <h5 class="mt-3" style="color: var(--lg-primary, #2563EB);">{{ $report['title'] }}</h5>
                        <p class="text-muted small mb-0">{{ $report['desc'] }}</p>
                    </div>
                </div>
            </a>
        </div>
    @endforeach

</div>

@endsection