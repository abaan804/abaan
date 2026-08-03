<div data-out-total="{{ $rows->sum('balance') }}" data-out-count="{{ $rows->count() }}" class="d-none" id="out-totals-data"></div>

@forelse ($rows as $row)
    <tr>
        <td data-label="{{ __('Name') }}" class="ledger-cell-name">
            <a href="{{ route($type === 'payables' ? 'ledger.suppliers.show' : 'ledger.customers.show', $row['party']) }}" class="text-decoration-none">
                {{ $row['party']->name }}
            </a>
        </td>
        <td data-label="{{ __('Mobile') }}">{{ $row['party']->mobile ?? '—' }}</td>
        <td data-label="{{ __('Balance') }}" class="text-end fw-semibold {{ $type === 'payables' ? 'text-danger' : 'text-success' }}">
            {{ formatCurrency($row['balance']) }}
        </td>
        <td class="ledger-cell-actions">
            <a href="{{ route('ledger.reminders.index') }}" class="btn btn-sm btn-outline-secondary" title="{{ __('Set a reminder for this party from the Reminders page') }}">
                <i class="bi bi-bell"></i>
            </a>
        </td>
    </tr>
@empty
    <tr class="ledger-row-empty">
        <td colspan="4">
            @include('ledger::partials.empty-state', [
                'icon' => 'bi-check-circle',
                'title' => __('No outstanding balances'),
                'description' => __('Everyone is settled up.'),
            ])
        </td>
    </tr>
@endforelse