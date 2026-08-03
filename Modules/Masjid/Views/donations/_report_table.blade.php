<div data-total="{{ formatCurrency($total) }}"
     data-count="{{ $donations->total() }}"
     class="d-none" id="rp-totals-data"></div>

@forelse ($donations as $donation)
    <tr>
        <td data-label="{{ __('Type') }}">
            <span class="badge {{ $donation->type === 'named' ? 'bg-primary' : 'bg-secondary' }}">
                {{ $donation->type === 'named' ? __('Named') : __('Anonymous') }}
            </span>
        </td>
        <td data-label="{{ __('Donor') }}" class="fw-semibold">
            {{ $donation->donor_display_name }}
            @if ($donation->donor_mobile)
                <div class="text-muted small">{{ $donation->donor_mobile }}</div>
            @endif
        </td>
        <td data-label="{{ __('Purpose') }}">{{ $donation->purpose ?? '—' }}</td>
        <td data-label="{{ __('Receipt') }}" class="small text-muted">
            {{ $donation->receipt_no ?? '—' }}
        </td>
        <td data-label="{{ __('Season') }}">{{ $donation->season?->name ?? '—' }}</td>
        <td data-label="{{ __('Date') }}">{{ formatDate($donation->donation_date) }}</td>
        <td data-label="{{ __('Amount') }}" class="text-end fw-bold text-success">
            {{ formatCurrency($donation->amount) }}
        </td>
        <td class="text-end">
            @if ($donation->type === 'named')
                <a href="{{ route('masjid.mosque.donations.slip', [$mosque, $donation]) }}"
                   target="_blank"
                   class="btn btn-sm btn-outline-primary"
                   title="{{ __('View / Print Slip') }}">
                    <i class="bi bi-receipt"></i>
                </a>
                <a href="{{ route('masjid.mosque.donations.slip.pdf', [$mosque, $donation]) }}"
                   class="btn btn-sm btn-outline-danger"
                   title="{{ __('Download PDF Slip') }}">
                    <i class="bi bi-file-earmark-pdf"></i>
                </a>
            @else
                <span class="text-muted small">—</span>
            @endif
        </td>
    </tr>
@empty
    <tr>
        <td colspan="8" class="text-center py-5 text-muted">
            <i class="bi bi-gift" style="font-size:2rem;"></i>
            <p class="mt-2 mb-0">{{ __('No donations match the filters.') }}</p>
        </td>
    </tr>
@endforelse

@if ($donations->hasPages())
    <tr>
        <td colspan="8">
            <div id="rp-pagination" class="d-flex justify-content-center py-2">
                {{ $donations->links() }}
            </div>
        </td>
    </tr>
@endif