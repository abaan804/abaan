<div data-total="{{ formatCurrency($total) }}"
     data-count="{{ $expenses->total() }}"
     class="d-none" id="exp-totals-data"></div>

@forelse ($expenses as $expense)
    <tr>
        <td data-label="{{ __('Category') }}">
            <span class="badge bg-light text-dark border">
                <i class="bi {{ $expense->category_icon }}"></i>
                {{ $expense->category_label }}
            </span>
        </td>
        <td data-label="{{ __('Title') }}" class="fw-semibold">
            {{ $expense->title }}
            @if ($expense->receipt_no)
                <div class="text-muted small">{{ __('Receipt') }}: {{ $expense->receipt_no }}</div>
            @endif
        </td>
        <td data-label="{{ __('Paid To') }}">{{ $expense->paid_to ?? '—' }}</td>
        <td data-label="{{ __('Season') }}">{{ $expense->season?->name ?? '—' }}</td>
        <td data-label="{{ __('Date') }}">{{ formatDate($expense->expense_date) }}</td>
        <td data-label="{{ __('Amount') }}" class="text-end fw-bold text-danger">
            {{ formatCurrency($expense->amount) }}
        </td>
        <td class="text-end mj-cell-actions">
            @if ($expense->attachment)
                <a href="{{ $expense->attachment_url }}" target="_blank"
                   class="btn btn-sm btn-outline-secondary" title="{{ __('View') }}">
                    <i class="bi bi-paperclip"></i>
                </a>
            @endif
            @can('masjid.manage-payments')
                <button type="button" class="btn btn-sm btn-outline-secondary btn-edit-expense"
                        data-id="{{ $expense->id }}">
                    <i class="bi bi-pencil"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger btn-delete-expense"
                        data-id="{{ $expense->id }}"
                        data-title="{{ $expense->title }}">
                    <i class="bi bi-trash"></i>
                </button>
            @endcan
        </td>
    </tr>
@empty
    <tr>
        <td colspan="7" class="text-center py-5 text-muted">
            <i class="bi bi-cash-stack" style="font-size:2rem;"></i>
            <p class="mt-2 mb-0">{{ __('No expenses recorded yet.') }}</p>
        </td>
    </tr>
@endforelse

@if ($expenses->hasPages())
    <tr>
        <td colspan="7">
            <div id="exp-pagination" class="d-flex justify-content-center py-2">
                {{ $expenses->links() }}
            </div>
        </td>
    </tr>
@endif