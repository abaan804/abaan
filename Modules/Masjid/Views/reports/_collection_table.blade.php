<div data-total="{{ formatCurrency($total) }}" data-count="{{ $payments->count() }}" class="d-none" id="col-totals-data"></div>

@forelse ($payments as $payment)
    <tr>
        <td data-label="{{ __('Receipt') }}"><code class="small">{{ $payment->receipt_no ?? '—' }}</code></td>
        <td data-label="{{ __('Member') }}" class="mj-cell-name">{{ $payment->member?->name ?? '—' }}</td>
        <td data-label="{{ __('Season') }}">{{ $payment->season?->name ?? '—' }}</td>
        <td data-label="{{ __('Date') }}">{{ formatDate($payment->payment_date) }}</td>
        <td data-label="{{ __('Method') }}">{{ ucfirst($payment->payment_method) }}</td>
        <td data-label="{{ __('Received By') }}">{{ $payment->receivedBy?->name ?? '—' }}</td>
        <td data-label="{{ __('Amount') }}" class="text-end fw-semibold text-success">{{ formatCurrency($payment->amount_paid) }}</td>
    </tr>
@empty
    <tr class="mj-row-empty">
        <td colspan="7">
            @include('masjid::partials.empty-state', ['icon' => 'bi-cash', 'title' => __('No payments in this period')])
        </td>
    </tr>
@endforelse

@if ($payments->isNotEmpty())
    <tr class="fw-bold border-top">
        <td colspan="6" class="text-end" data-label="">{{ __('Total') }}</td>
        <td class="text-end text-success" data-label="{{ __('Total') }}">{{ formatCurrency($total) }}</td>
    </tr>
@endif