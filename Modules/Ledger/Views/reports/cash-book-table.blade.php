<div data-cb-inflow="{{ $totals['inflow'] }}" data-cb-outflow="{{ $totals['outflow'] }}" data-cb-closing="{{ $totals['closing'] }}" class="d-none" id="cb-totals-data"></div>

@forelse ($rows as $row)
    @php $tx = $row['transaction']; @endphp
    <tr>
        <td data-label="{{ __('Date') }}">{{ formatDate($tx->transaction_date) }}</td>
        <td data-label="{{ __('Type') }}"><span class="badge ledger-badge-{{ $tx->type }}">{{ ucfirst(str_replace('_', ' ', $tx->type)) }}</span></td>
        <td data-label="{{ __('Party') }}">{{ $tx->customer?->name ?? $tx->supplier?->name ?? '—' }}</td>
        <td data-label="{{ __('Method') }}">{{ $tx->paymentMethod?->name ?? '—' }}</td>
        <td data-label="{{ __('In') }}" class="text-end text-success">{{ $row['inflow'] > 0 ? formatCurrency($row['inflow']) : '—' }}</td>
        <td data-label="{{ __('Out') }}" class="text-end text-danger">{{ $row['outflow'] > 0 ? formatCurrency($row['outflow']) : '—' }}</td>
        <td data-label="{{ __('Balance') }}" class="text-end fw-semibold">{{ formatCurrency($row['running_balance']) }}</td>
    </tr>
@empty
    <tr class="ledger-row-empty">
        <td colspan="7">
            @include('ledger::partials.empty-state', ['icon' => 'bi-journal-text', 'title' => __('No transactions in this period')])
        </td>
    </tr>
@endforelse

<tr class="fw-bold border-top">
    <td colspan="4" class="text-end" data-label="">{{ __('Totals') }}</td>
    <td class="text-end text-success" data-label="{{ __('In') }}">{{ formatCurrency($totals['inflow']) }}</td>
    <td class="text-end text-danger" data-label="{{ __('Out') }}">{{ formatCurrency($totals['outflow']) }}</td>
    <td class="text-end" data-label="{{ __('Balance') }}">{{ formatCurrency($totals['closing']) }}</td>
</tr>