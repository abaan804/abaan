@extends($ledgerLayout)
@section('heading', __('Cash Book'))
@section('ledger-content')

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <div class="row g-2">
            <div class="col-6 col-md-3">
                <label class="form-label small">{{ __('From') }}</label>
                <input type="date" id="cb-date-from" class="form-control" value="{{ now()->startOfMonth()->toDateString() }}">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small">{{ __('To') }}</label>
                <input type="date" id="cb-date-to" class="form-control" value="{{ now()->toDateString() }}">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small">{{ __('Payment Method') }}</label>
                <select id="cb-payment-method" class="form-select">
                    <option value="">{{ __('All Methods') }}</option>
                    @foreach ($paymentMethods as $pm)
                        <option value="{{ $pm->id }}">{{ $pm->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-3 d-flex align-items-end">
                <a href="#" id="cb-download-pdf" class="btn btn-outline-danger w-100">
                    <i class="bi bi-file-earmark-pdf"></i> {{ __('Download PDF') }}
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3" id="cb-totals">
    <div class="col-4">
        <div class="ledger-stat-card p-3">
            <div class="text-muted small">{{ __('Total Inflow') }}</div>
            <div class="h5 mb-0 text-success" id="cb-total-inflow">—</div>
        </div>
    </div>
    <div class="col-4">
        <div class="ledger-stat-card p-3">
            <div class="text-muted small">{{ __('Total Outflow') }}</div>
            <div class="h5 mb-0 text-danger" id="cb-total-outflow">—</div>
        </div>
    </div>
    <div class="col-4">
        <div class="ledger-stat-card p-3">
            <div class="text-muted small">{{ __('Closing Balance') }}</div>
            <div class="h5 mb-0" id="cb-closing-balance">—</div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle ledger-responsive-table">
            <thead>
                <tr>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Type') }}</th>
                    <th>{{ __('Party') }}</th>
                    <th>{{ __('Method') }}</th>
                    <th class="text-end">{{ __('In') }}</th>
                    <th class="text-end">{{ __('Out') }}</th>
                    <th class="text-end">{{ __('Balance') }}</th>
                </tr>
            </thead>
            <tbody id="cb-table-body">
                <tr><td colspan="7" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></td></tr>
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const tableUrl = '{{ route("ledger.reports.cash-book.table") }}';
    const tbody = document.getElementById('cb-table-body');
    const dateFrom = document.getElementById('cb-date-from');
    const dateTo = document.getElementById('cb-date-to');
    const paymentMethod = document.getElementById('cb-payment-method');

    function load() {
        tbody.innerHTML = `<tr><td colspan="7" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></td></tr>`;
        const params = new URLSearchParams({
            date_from: dateFrom.value,
            date_to: dateTo.value,
            payment_method_id: paymentMethod.value,
        });
        fetch(`${tableUrl}?${params.toString()}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(res => res.text())
            .then(html => {
                tbody.innerHTML = html;

                const dataEl = document.getElementById('cb-totals-data');
                if (dataEl) {
                    document.getElementById('cb-total-inflow').textContent = formatMoney(dataEl.dataset.cbInflow);
                    document.getElementById('cb-total-outflow').textContent = formatMoney(dataEl.dataset.cbOutflow);
                    document.getElementById('cb-closing-balance').textContent = formatMoney(dataEl.dataset.cbClosing);
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

    [dateFrom, dateTo, paymentMethod].forEach(el => el.addEventListener('change', load));

    load();

    document.getElementById('cb-download-pdf').addEventListener('click', function (e) {
        e.preventDefault();
        const params = new URLSearchParams({
            date_from: dateFrom.value,
            date_to: dateTo.value,
            payment_method_id: paymentMethod.value,
        });
        window.open(
            `{{ route('ledger.reports.cash-book.pdf') }}?${params.toString()}`,
            '_blank'
        );
    });
})();


</script>
@endpush
@endsection