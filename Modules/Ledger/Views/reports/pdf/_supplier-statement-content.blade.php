<table style="width:100%; margin-bottom: 14px;">
    <tr>
        <td style="width:33%;">
            <strong>{{ __('Current Balance') }}:</strong><br>
            <span style="font-size:13px; color:{{ $balance > 0 ? '#DC2626' : ($balance < 0 ? '#16A34A' : '#1e293b') }};">
                {{ formatCurrency(abs($balance)) }}
                {{ $balance > 0 ? '('.__('Payable').')' : ($balance < 0 ? '('.__('Credit').')' : '') }}
            </span>
        </td>
        <td style="width:33%;">
            <strong>{{ __('Opening Balance') }}:</strong><br>
            {{ formatCurrency($supplier->opening_balance) }}
        </td>
        <td style="width:34%;">
            <strong>{{ __('Total Debit') }} / {{ __('Credit') }}:</strong><br>
            {{ formatCurrency($totalDebit) }} / {{ formatCurrency($totalCredit) }}
        </td>
    </tr>
</table>

<table class="data-table">
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
        @php $running = $supplier->opening_balance; @endphp
        @forelse ($transactions as $tx)
            @php
                $debit = $tx->type === 'debit' ? $tx->amount : 0;
                $credit = in_array($tx->type, ['credit', 'opening_balance']) ? $tx->amount : 0;
                $running += $credit - $debit;
            @endphp
            <tr>
                <td>{{ formatDate($tx->transaction_date) }}</td>
                <td>{{ ucfirst(str_replace('_', ' ', $tx->type)) }}</td>
                <td>{{ $tx->reference_no ?? '—' }}</td>
                <td>{{ $tx->paymentMethod?->name ?? '—' }}</td>
                <td class="text-end text-success">{{ $debit > 0 ? formatCurrency($debit) : '—' }}</td>
                <td class="text-end text-danger">{{ $credit > 0 ? formatCurrency($credit) : '—' }}</td>
            </tr>
        @empty
            <tr><td colspan="6" style="text-align:center;">{{ __('No transactions yet') }}</td></tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr>
            <td colspan="4" class="text-end">{{ __('Closing Balance') }}</td>
            <td colspan="2" class="text-end">{{ formatCurrency($running) }}</td>
        </tr>
    </tfoot>
</table>