<table class="data-table">
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
    <tbody>
        @forelse ($rows as $row)
            @php $tx = $row['transaction']; @endphp
            <tr>
                <td>{{ formatDate($tx->transaction_date) }}</td>
                <td>{{ ucfirst(str_replace('_', ' ', $tx->type)) }}</td>
                <td>{{ $tx->customer?->name ?? $tx->supplier?->name ?? '—' }}</td>
                <td>{{ $tx->paymentMethod?->name ?? '—' }}</td>
                <td class="text-end text-success">{{ $row['inflow'] > 0 ? formatCurrency($row['inflow']) : '—' }}</td>
                <td class="text-end text-danger">{{ $row['outflow'] > 0 ? formatCurrency($row['outflow']) : '—' }}</td>
                <td class="text-end">{{ formatCurrency($row['running_balance']) }}</td>
            </tr>
        @empty
            <tr><td colspan="7" style="text-align:center;">{{ __('No transactions in this period') }}</td></tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr>
            <td colspan="4" class="text-end">{{ __('Totals') }}</td>
            <td class="text-end text-success">{{ formatCurrency($totals['inflow']) }}</td>
            <td class="text-end text-danger">{{ formatCurrency($totals['outflow']) }}</td>
            <td class="text-end">{{ formatCurrency($totals['closing']) }}</td>
        </tr>
    </tfoot>
</table>