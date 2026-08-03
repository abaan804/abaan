@forelse ($transactions as $tx)
    <tr data-transaction-id="{{ $tx->id }}">
        <td data-label="{{ __('Date') }}">{{ formatDate($tx->transaction_date) }}</td>
        <td data-label="{{ __('Type') }}">
            @php
                $label = '';

                if ($tx->customer) {
                    $label = $tx->type === 'debit' ? __('He Paid') : __('He Owe');
                } elseif ($tx->supplier) {
                    $label = $tx->type === 'debit' ? __('You Owe') : __('You Paid');
                }
            @endphp

            <span class="badge ledger-badge-{{ $tx->type }}">
                {{ ucfirst(str_replace('_', ' ', $tx->type)) }}

                @if($label)
                    ({{ $label }})
                @endif
            </span>
        </td>
        <td data-label="{{ __('Party') }}" class="ledger-cell-name">
            {{ $tx->customer?->name ?? $tx->supplier?->name ?? '—' }}

            @if($tx->customer)
                <span class="badge bg-primary ms-1">{{ __('Customer') }}</span>
            @elseif($tx->supplier)
                <span class="badge bg-success ms-1">{{ __('Supplier') }}</span>
            @endif
        </td>
        {{--<td data-label="{{ __('Category') }}">{{ $tx->category?->name ?? '—' }}</td>--}}
        <td data-label="{{ __('Method') }}">{{ $tx->paymentMethod?->name ?? '—' }}</td>
        <td data-label="{{ __('Amount') }}" class="fw-semibold">{{ formatCurrency($tx->amount) }}</td>
        <td class="ledger-cell-actions">
        <button
            type="button"
            class="btn btn-sm btn-outline-info btn-view-transaction"
            data-bs-toggle="modal"
            data-bs-target="#transactionViewModal"
            data-reference="{{ $tx->reference_no }}"
            data-category="{{ $tx->category?->name }}"
            data-method="{{ $tx->paymentMethod?->name }}"
            data-notes="{{ $tx->notes }}"
            data-attachments='@json($tx->attachments)'
        >
            <i class="bi bi-eye"></i>
        </button>

        <button type="button" class="btn btn-sm btn-outline-primary btn-edit-transaction" data-id="{{ $tx->id }}">
            <i class="bi bi-pencil"></i>
        </button>

        <button type="button" class="btn btn-sm btn-outline-danger btn-delete-transaction"
                data-id="{{ $tx->id }}"
                data-name="{{ formatCurrency($tx->amount) }}">
            <i class="bi bi-trash"></i>
        </button>
    </td>
    </tr>
@empty
    <tr class="ledger-row-empty">
        <td colspan="7">
            @include('ledger::partials.empty-state', [
                'icon' => 'bi-arrow-left-right',
                'title' => __('No transactions found'),
                'description' => __('Record your first transaction to get started.'),
            ])
        </td>
    </tr>
@endforelse
@if ($transactions->hasPages())
    <tr>
        <td colspan="7">
            <div id="transactions-pagination">
                 {{ $transactions->links('pagination::bootstrap-5') }}
            </div>
        </td>
    </tr>
@endif
