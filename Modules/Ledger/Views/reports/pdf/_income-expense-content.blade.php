<table class="data-table">
    <thead>
        <tr>
            <th>{{ __('Category') }}</th>
            <th class="text-end">{{ __('Income') }}</th>
            <th class="text-end">{{ __('Expense') }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($byCategory as $name => $amounts)
            <tr>
                <td>{{ $name }}</td>
                <td class="text-end text-success">{{ $amounts['income'] > 0 ? formatCurrency($amounts['income']) : '—' }}</td>
                <td class="text-end text-danger">{{ $amounts['expense'] > 0 ? formatCurrency($amounts['expense']) : '—' }}</td>
            </tr>
        @empty
            <tr><td colspan="3" style="text-align:center;">{{ __('No income/expense transactions in this period') }}</td></tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr>
            <td>{{ __('Total') }}</td>
            <td class="text-end text-success">{{ formatCurrency($totals['income']) }}</td>
            <td class="text-end text-danger">{{ formatCurrency($totals['expense']) }}</td>
        </tr>
        <tr>
            <td>{{ __('Net Profit / Loss') }}</td>
            <td colspan="2" class="text-end {{ $totals['profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                {{ formatCurrency(abs($totals['profit'])) }} ({{ $totals['profit'] >= 0 ? __('Profit') : __('Loss') }})
            </td>
        </tr>
    </tfoot>
</table>