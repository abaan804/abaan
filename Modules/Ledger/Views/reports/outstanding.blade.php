@extends($ledgerLayout)
@section('heading', __('Outstanding Receivables / Payables'))
@section('ledger-content')

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <div class="row g-2">
            <div class="col-6 col-md-4">
                <select id="out-type" class="form-select">
                    <option value="receivables">{{ __('Outstanding Receivables (Customers)') }}</option>
                    <option value="payables">{{ __('Outstanding Payables (Suppliers)') }}</option>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <a href="#" id="out-download-pdf" class="btn btn-outline-danger w-100">
                    <i class="bi bi-file-earmark-pdf"></i> {{ __('Download PDF') }}
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-6">
        <div class="ledger-stat-card p-3">
            <div class="text-muted small" id="out-label">{{ __('Total Outstanding') }}</div>
            <div class="h4 mb-0" id="out-total">—</div>
        </div>
    </div>
    <div class="col-6">
        <div class="ledger-stat-card p-3">
            <div class="text-muted small">{{ __('Parties with Balance') }}</div>
            <div class="h4 mb-0" id="out-count">—</div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle ledger-responsive-table">
            <thead>
                <tr>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Mobile') }}</th>
                    <th class="text-end">{{ __('Balance') }}</th>
                    <th class="text-end">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody id="out-table-body">
                <tr><td colspan="4" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></td></tr>
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const tableUrl = '{{ route("ledger.reports.outstanding.table") }}';
    const tbody = document.getElementById('out-table-body');
    const typeSelect = document.getElementById('out-type');

    function load() {
        tbody.innerHTML = `<tr><td colspan="4" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></td></tr>`;
        fetch(`${tableUrl}?type=${typeSelect.value}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(res => res.text())
            .then(html => {
                tbody.innerHTML = html;
                const dataEl = document.getElementById('out-totals-data');
                document.getElementById('out-label').textContent = typeSelect.value === 'payables'
                    ? '{{ __('Total Payable') }}' : '{{ __('Total Receivable') }}';
                if (dataEl) {
                    const total = parseFloat(dataEl.dataset.outTotal || 0);
                    document.getElementById('out-total').textContent = formatMoney(total);
                    document.getElementById('out-count').textContent = dataEl.dataset.outCount;
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

    typeSelect.addEventListener('change', load);

    document.getElementById('out-download-pdf').addEventListener('click', function (e) {
        e.preventDefault();
        window.location.href = `{{ route('ledger.reports.outstanding.pdf') }}?type=${typeSelect.value}`;
    });

    load();
})();
</script>
@endpush
@endsection