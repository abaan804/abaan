@extends($ledgerLayout)
@section('heading', __('Period Summary'))
@section('ledger-content')

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <div class="row g-2">
            <div class="col-6 col-md-3">
                <label class="form-label small">{{ __('Group By') }}</label>
                <select id="ps-grain" class="form-select">
                    <option value="daily">{{ __('Daily') }}</option>
                    <option value="weekly">{{ __('Weekly') }}</option>
                    <option value="monthly" selected>{{ __('Monthly') }}</option>
                    <option value="yearly">{{ __('Yearly') }}</option>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small">{{ __('From') }}</label>
                <input type="date" id="ps-date-from" class="form-control" value="{{ now()->subMonths(6)->startOfMonth()->toDateString() }}">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small">{{ __('To') }}</label>
                <input type="date" id="ps-date-to" class="form-control" value="{{ now()->toDateString() }}">
            </div>
        </div>
    </div>
</div>



<div class="card shadow-sm  mb-3">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle ledger-responsive-table">
            <thead>
                <tr>
                    <th>{{ __('Period') }}</th>
                    <th class="text-end">{{ __('Income') }}</th>
                    <th class="text-end">{{ __('Expense') }}</th>
                    <th class="text-end">{{ __('Net') }}</th>
                    <th class="text-end">{{ __('Debit') }}</th>
                    <th class="text-end">{{ __('Credit') }}</th>
                </tr>
            </thead>
            <tbody id="ps-table-body">
                <tr><td colspan="6" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></td></tr>
            </tbody>
        </table>
    </div>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <canvas id="periodChart" height="220"></canvas>
    </div>
</div>
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {
    const tableUrl = '{{ route("ledger.reports.period-summary.table") }}';
    const tbody = document.getElementById('ps-table-body');
    const grain = document.getElementById('ps-grain');
    const dateFrom = document.getElementById('ps-date-from');
    const dateTo = document.getElementById('ps-date-to');
    let chart = null;

    function load() {
        tbody.innerHTML = `<tr><td colspan="6" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></td></tr>`;
        const params = new URLSearchParams({ grain: grain.value, date_from: dateFrom.value, date_to: dateTo.value });
        fetch(`${tableUrl}?${params.toString()}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(res => res.text())
            .then(html => { tbody.innerHTML = html; renderChart(); })
            .catch(() => LedgerToast.error('{{ __('Failed to load report.') }}'));
    }

    function renderChart() {
        const labels = [];
        const income = [];
        const expense = [];
        document.querySelectorAll('#ps-table-body tr[data-period]').forEach(row => {
            labels.push(row.dataset.period);
            income.push(parseFloat(row.dataset.income));
            expense.push(parseFloat(row.dataset.expense));
        });

        if (chart) chart.destroy();
        chart = new Chart(document.getElementById('periodChart'), {
            type: 'bar',
            data: {
                labels,
                datasets: [
                    { label: '{{ __('Income') }}', data: income, backgroundColor: '#16A34A' },
                    { label: '{{ __('Expense') }}', data: expense, backgroundColor: '#DC2626' },
                ],
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } }, scales: { y: { beginAtZero: true } } },
        });
    }

    [grain, dateFrom, dateTo].forEach(el => el.addEventListener('change', load));
    load();
})();
</script>
@endpush
@endsection