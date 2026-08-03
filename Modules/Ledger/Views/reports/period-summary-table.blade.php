@forelse ($rows as $row)
    <tr data-period="{{ $row->period }}" data-income="{{ $row->income }}" data-expense="{{ $row->expense }}">
        <td data-label="{{ __('Period') }}" class="ledger-cell-name">{{ $row->period }}</td>
        <td data-label="{{ __('Income') }}" class="text-end text-success">{{ formatCurrency($row->income) }}</td>
        <td data-label="{{ __('Expense') }}" class="text-end text-danger">{{ formatCurrency($row->expense) }}</td>
        <td data-label="{{ __('Net') }}" class="text-end {{ ($row->income - $row->expense) >= 0 ? 'text-success' : 'text-danger' }}">
            {{ formatCurrency($row->income - $row->expense) }}
        </td>
        <td data-label="{{ __('Debit') }}" class="text-end">{{ formatCurrency($row->debit) }}</td>
        <td data-label="{{ __('Credit') }}" class="text-end">{{ formatCurrency($row->credit) }}</td>
    </tr>
@empty
    <tr class="ledger-row-empty">
        <td colspan="6">
            @include('ledger::partials.empty-state', ['icon' => 'bi-bar-chart', 'title' => __('No data for this period')])
        </td>
    </tr>
@endforelse