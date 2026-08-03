<div data-total="{{ formatCurrency($total) }}"
     data-count="{{ $donations->total() }}"
     class="d-none" id="don-totals-data"></div>

@forelse ($donations as $donation)
    <tr>
        <td data-label="{{ __('Type') }}">
            <span class="badge {{ $donation->type === 'named' ? 'bg-primary' : 'bg-secondary' }}">
                <i class="bi bi-{{ $donation->type === 'named' ? 'person-check' : 'incognito' }}"></i>
                {{ $donation->type === 'named' ? __('Named') : __('Anonymous') }}
            </span>
        </td>
        <td data-label="{{ __('Donor') }}" class="fw-semibold">
            {{ $donation->donor_display_name }}
            @if ($donation->donor_mobile)
                <div class="text-muted small">{{ $donation->donor_mobile }}</div>
            @endif
        </td>
        <td data-label="{{ __('Purpose') }}">
            {{ $donation->purpose ?? '—' }}
            @if ($donation->season)
                <div class="text-muted small">{{ $donation->season->name }}</div>
            @endif
        </td>
        <td data-label="{{ __('Season') }}">
            {{ $donation->season?->name ?? '—' }}
        </td>
        <td data-label="{{ __('Date') }}">
            {{ formatDate($donation->donation_date) }}
        </td>
        <td data-label="{{ __('Amount') }}" class="text-end fw-bold text-success">
            {{ formatCurrency($donation->amount) }}
        </td>
        {{-- REPLACE the actions td --}}
        <td class="text-end mj-cell-actions">
            {{-- Slip button — only for named donations --}}
            @if ($donation->type === 'named')
                <a href="{{ route('masjid.mosque.donations.slip', [$mosque, $donation]) }}"
                target="_blank"
                class="btn btn-sm btn-outline-success"
                title="{{ __('Print Slip') }}">
                    <i class="bi bi-receipt"></i>
                </a>
            @endif
            @can('masjid.manage-payments')
                <button type="button" class="btn btn-sm btn-outline-secondary btn-edit-donation"
                        data-id="{{ $donation->id }}">
                    <i class="bi bi-pencil"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger btn-delete-donation"
                        data-id="{{ $donation->id }}"
                        data-amount="{{ formatCurrency($donation->amount) }}">
                    <i class="bi bi-trash"></i>
                </button>
            @endcan
        </td>
    </tr>
@empty
    <tr>
        <td colspan="7" class="text-center py-5 text-muted">
            <i class="bi bi-gift" style="font-size:2rem;"></i>
            <p class="mt-2 mb-0">{{ __('No donations recorded yet.') }}</p>
        </td>
    </tr>
@endforelse

@if ($donations->hasPages())
    <tr>
        <td colspan="7">
            <div id="don-pagination" class="d-flex justify-content-center py-2">
                {{ $donations->links() }}
            </div>
        </td>
    </tr>
@endif