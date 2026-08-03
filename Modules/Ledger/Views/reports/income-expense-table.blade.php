<div data-ie-income="{{ $totals['income'] }}" data-ie-expense="{{ $totals['expense'] }}" data-ie-profit="{{ $totals['profit'] }}" class="d-none" id="ie-totals-data"></div>

@forelse ($byCategory as $name => $amounts)
    <tr>
        <td data-label="{{ __('Category') }}" class="ledger-cell-name">{{ $name }}</td>
        <td data-label="{{ __('Income') }}" class="text-end text-success">{{ $amounts['income'] > 0 ? formatCurrency($amounts['income']) : '—' }}</td>
        <td data-label="{{ __('Expense') }}" class="text-end text-danger">{{ $amounts['expense'] > 0 ? formatCurrency($amounts['expense']) : '—' }}</td>
    </tr>
@empty
    <tr class="ledger-row-empty">
        <td colspan="3">
            @include('ledger::partials.empty-state', ['icon' => 'bi-graph-up', 'title' => __('No income/expense transactions in this period')])
        </td>
    </tr>
@endforelse

<tr class="fw-bold border-top">
    <td data-label="">{{ __('Total') }}</td>
    <td class="text-end text-success" data-label="{{ __('Income') }}">{{ formatCurrency($totals['income']) }}</td>
    <td class="text-end text-danger" data-label="{{ __('Expense') }}">{{ formatCurrency($totals['expense']) }}</td>
</tr>