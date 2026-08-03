<div data-total="{{ formatCurrency($payments->sum('amount_paid')) }}" data-count="{{ $payments->total() }}" class="d-none" id="pay-totals-data"></div>

@forelse ($payments as $payment)
    <tr>
        <td data-label="{{ __('Receipt') }}">
            <code class="small">{{ $payment->receipt_no ?? '—' }}</code>
        </td>
        <td data-label="{{ __('Member') }}" class="mj-cell-name">
            <a href="{{ route('masjid.mosque.members.statement', [$mosque, $payment->member, 'standalone' => 1]) }}" class="text-decoration-none fw-semibold">
                {{ $payment->member?->name ?? '—' }}
            </a>
        </td>
        <td data-label="{{ __('Season') }}">
            <span class="small">{{ $payment->season?->name ?? '—' }}</span>
        </td>
        <td data-label="{{ __('Date') }}">{{ formatDate($payment->payment_date) }}</td>
        <td data-label="{{ __('Method') }}">
            <span class="badge bg-light text-dark border">{{ ucfirst($payment->payment_method) }}</span>
        </td>
        <td data-label="{{ __('Amount') }}" class="text-end fw-semibold text-success">
            {{ formatCurrency($payment->amount_paid) }}
        </td>
        <td class="mj-cell-actions">
            @if ($payment->attachments->isNotEmpty())
                <span class="badge bg-light text-dark border" title="{{ __('Has attachments') }}">
                    <i class="bi bi-paperclip"></i>{{ $payment->attachments->count() }}
                </span>
            @endif
            <button type="button" class="btn btn-sm btn-outline-secondary btn-edit-payment" data-id="{{ $payment->id }}" title="{{ __('Edit') }}">
                <i class="bi bi-pencil"></i>
            </button>
            <button type="button" class="btn btn-sm btn-outline-danger btn-delete-payment" data-id="{{ $payment->id }}" data-amount="{{ formatCurrency($payment->amount_paid) }}" title="{{ __('Delete') }}">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    </tr>
@empty
    <tr class="mj-row-empty">
        <td colspan="7">
            @include('masjid::partials.empty-state', [
                'icon' => 'bi-cash-coin',
                'title' => __('No payments recorded'),
                'description' => __('Click "Record" to add your first payment.'),
            ])
        </td>
    </tr>
@endforelse

@if ($payments->hasPages())
    <tr class="mj-row-empty">
        <td colspan="7">
            <div id="payments-pagination" class="d-flex justify-content-center py-2">{{ $payments->links() }}</div>
        </td>
    </tr>
@endif