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
        <td data-label="{{ __('Amount Due') }}">{{ formatCurrency($sm->amount_due) }}</td>
        <td data-label="{{ __('Amount Paid') }}" class="text-success">{{ formatCurrency($sm->amount_paid) }}</td>
        <td data-label="{{ __('Balance') }}" class="{{ $sm->balance() > 0 ? 'text-danger' : ($sm->isOverpaid() ? 'text-info' : 'text-success') }}">
            {{ formatCurrency(abs($sm->balance())) }}
            @if ($sm->isOverpaid()) <span class="badge bg-info ms-1">+</span> @endif
        </td>
        <td data-label="{{ __('Status') }}">
            <span class="badge mj-badge-{{ $sm->status }}">{{ ucfirst($sm->status) }}</span>
        </td>
        <td class="mj-cell-actions">
            <a href="{{ route('masjid.mosque.members.statement', [$mosque, $sm->member,'standalone' => 1]) }}" class="btn btn-sm btn-outline-primary" title="{{ __('Statement') }}">
                <i class="bi bi-journal-text"></i>
            </a>
            @if ($sm->status === 'pending')
                <button type="button" class="btn btn-sm btn-outline-danger btn-unassign-member" data-id="{{ $sm->id }}" title="{{ __('Remove from season') }}">
                    <i class="bi bi-person-dash"></i>
                </button>
            @endif
        </td>
    </tr>
@empty
    <tr class="mj-row-empty">
        <td colspan="6">
            @include('masjid::partials.empty-state', [
                'icon' => 'bi-people',
                'title' => __('No members assigned yet'),
                'description' => __('Use "Assign All" to add all active members, or "Assign Member" to add individually.'),
            ])
        </td>
    </tr>
@endforelse

@if ($assignments->hasPages())
    <tr class="mj-row-empty">
        <td colspan="6">
            <div id="season-members-pagination" class="d-flex justify-content-center py-2">{{ $assignments->links('pagination::bootstrap-5') }}</div>
        </td>
    </tr>
@endif