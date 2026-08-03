<div data-total="{{ formatCurrency($assignments->sum(fn($a) => $a->balance())) }}" data-count="{{ $assignments->count() }}" class="d-none" id="out-totals-data"></div>

@forelse ($assignments as $sm)
    <tr>
        <td data-label="{{ __('Member') }}" class="mj-cell-name">
            <a href="{{ route('masjid.mosque.members.statement', [$mosque, $sm->member]) }}" class="text-decoration-none fw-semibold">
                {{ $sm->member?->name ?? '—' }}
            </a>
            @if ($sm->member?->mobile)
                <div class="text-muted small">{{ $sm->member->mobile }}</div>
            @endif
        </td>
        <td data-label="{{ __('Season') }}">{{ $sm->season?->name ?? '—' }}</td>
        <td data-label="{{ __('Due') }}">{{ formatCurrency($sm->amount_due) }}</td>
        <td data-label="{{ __('Paid') }}" class="text-success">{{ formatCurrency($sm->amount_paid) }}</td>
        <td data-label="{{ __('Balance') }}" class="{{ $sm->balance() > 0 ? 'text-danger' : 'text-info' }} fw-semibold">
            {{ formatCurrency(abs($sm->balance())) }}
        </td>
        <td data-label="{{ __('Status') }}">
            <span class="badge mj-badge-{{ $sm->status }}">{{ ucfirst($sm->status) }}</span>
        </td>
    </tr>
@empty
    <tr class="mj-row-empty">
        <td colspan="6">
            @include('masjid::partials.empty-state', [
                'icon' => 'bi-check-circle',
                'title' => __('No outstanding balances'),
                'description' => __('All members are settled up.'),
            ])
        </td>
    </tr>
@endforelse