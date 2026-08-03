@extends($ledgerLayout)
@section('heading', __('Income & Expense / Profit-Loss'))
@section('ledger-content')

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <div class="row g-2">
            <div class="col-6 col-md-4">
                <label class="form-label small">{{ __('From') }}</label>
                <input type="date" id="ie-date-from" class="form-control" value="{{ now()->startOfMonth()->toDateString() }}">
            </div>
            <div class="col-6 col-md-4">
                <label class="form-label small">{{ __('To') }}</label>
                <input type="date" id="ie-date-to" class="form-control" value="{{ now()->toDateString() }}">
            </div>
            <div class="col-12 col-md-4 d-flex align-items-end">
                <a href="#" id="ie-download-pdf" class="btn btn-outline-danger w-100">
                    <i class="bi bi-file-earmark-pdf"></i> {{ __('Download PDF') }}
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-4">
        <div class="ledger-stat-card p-3">
            <div class="text-muted small">{{ __('Total Income') }}</div>
            <div class="h4 mb-0 text-success" id="ie-total-income">—</div>
        </div>
    </div>
    <div class="col-4">
        <div class="ledger-stat-card p-3">
            <div class="text-muted small">{{ __('Total Expense') }}</div>
            <div class="h4 mb-0 text-danger" id="ie-total-expense">—</div>
        </div>
    </div>
    <div class="col-4">
        <div class="ledger-stat-card p-3">
            <div class="text-muted small">{{ __('Net Profit / Loss') }}</div>
            <div class="h4 mb-0" id="ie-net-profit">—</div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white"><strong>{{ __('By Category') }}</strong></div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle ledger-responsive-table">
            <thead>
                <tr>
                    <th>{{ __('Category') }}</th>
                    <th class="text-end">{{ __('Income') }}</th>
                    <th class="text-end">{{ __('Expense') }}</th>
                </tr>
            </thead>
            <tbody id="ie-table-body">
                <tr><td colspan="3" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></td></tr>
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const tableUrl = '{{ route("ledger.reports.income-expense.table") }}';
    const tbody = document.getElementById('ie-table-body');
    const dateFrom = document.getElementById('ie-date-from');
    const dateTo = document.getElementById('ie-date-to');

    function load() {
        tbody.innerHTML = `<tr><td colspan="3" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></td></tr>`;
        const params = new URLSearchParams({ date_from: dateFrom.value, date_to: dateTo.value });
        fetch(`${tableUrl}?${params.toString()}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(res => res.text())
            .then(html => {
                tbody.innerHTML = html;

                const dataEl = document.getElementById('ie-totals-data');
                if (dataEl) {
                    const income = parseFloat(dataEl.dataset.ieIncome || 0);
                    const expense = parseFloat(dataEl.dataset.ieExpense || 0);
                    const profit = parseFloat(dataEl.dataset.ieProfit || 0);

                    document.getElementById('ie-total-income').textContent = formatMoney(income);
                    document.getElementById('ie-total-expense').textContent = formatMoney(expense);

                    const netProfitEl = document.getElementById('ie-net-profit');
                    netProfitEl.textContent = formatMoney(Math.abs(profit)) + (profit < 0 ? ' ({{ __('Loss') }})' : ' ({{ __('Profit') }})');
                    netProfitEl.className = 'h4 mb-0 ' + (profit >= 0 ? 'text-success' : 'text-danger');
                }
            })
            .catch(() => LedgerToast.error('{{ __('Failed to load report.') }}'));
    }

    function formatMoney(value) {
        const num = parseFloat(value || 0);
        return '{{ setting('currency_position', 'before') }}' === 'after'
            ? num.toFixed(2) + ' {{ setting('currency_symbol', '$') }}'
            : '{{ setting('currency_symbol', '$') }}' + num.toFixed(2);
    }

    [dateFrom, dateTo].forEach(el => el.addEventListener('change', load));
    load();

   document.getElementById('ie-download-pdf').addEventListener('click', function (e) {
    e.preventDefault();

        const params = new URLSearchParams({
            date_from: dateFrom.value,
            date_to: dateTo.value
        });

        window.open(
            `{{ route('ledger.reports.income-expense.pdf') }}?${params.toString()}`,
            '_blank'
        );
    });
})();


</script>
@endpush
@endsection