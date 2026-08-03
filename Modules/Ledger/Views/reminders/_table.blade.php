@forelse ($reminders as $reminder)
    <tr data-reminder-id="{{ $reminder->id }}">
        <td data-label="{{ __('Title') }}" class="ledger-cell-name">{{ $reminder->title }}</td>
        <td data-label="{{ __('Party') }}">{{ $reminder->customer?->name ?? $reminder->supplier?->name ?? '—' }}</td>
        <td data-label="{{ __('Due Date') }}">
            {{ formatDate($reminder->due_date) }}
            @if ($reminder->status === 'pending' && \Carbon\Carbon::parse($reminder->due_date)->isPast())
                <span class="badge bg-danger ms-1">{{ __('Overdue') }}</span>
            @endif
        </td>
        <td data-label="{{ __('Amount') }}">{{ $reminder->amount ? formatCurrency($reminder->amount) : '—' }}</td>
        <td data-label="{{ __('Channel') }}">
            <span class="badge bg-light text-dark border">{{ ucfirst(str_replace('_', ' ', $reminder->channel)) }}</span>
        </td>
        <td data-label="{{ __('Status') }}">
            <span class="badge bg-{{ $reminder->status === 'pending' ? 'warning' : ($reminder->status === 'sent' ? 'info' : 'secondary') }}">
                {{ ucfirst($reminder->status) }}
            </span>
        </td>
        <td class="ledger-cell-actions">
            @if ($reminder->status === 'pending')
                <button type="button" class="btn btn-sm btn-outline-secondary btn-dismiss-reminder" data-id="{{ $reminder->id }}">
                    <i class="bi bi-check-lg"></i> {{ __('Dismiss') }}
                </button>
            @endif
            <button type="button" class="btn btn-sm btn-outline-danger btn-delete-reminder" data-id="{{ $reminder->id }}" data-name="{{ $reminder->title }}">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    </tr>
@empty
    <tr class="ledger-row-empty">
        <td colspan="7">
            @include('ledger::partials.empty-state', [
                'icon' => 'bi-bell',
                'title' => __('No reminders found'),
                'description' => __('Create a reminder to follow up on a payment due date.'),
            ])
        </td>
    </tr>
@endforelse

@if ($reminders->hasPages())
    <tr class="ledger-row-empty">
        <td colspan="7">
            <div class="d-flex justify-content-center py-2" id="reminders-pagination">{{ $reminders->links() }}</div>
        </td>
    </tr>
@endif