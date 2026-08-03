<div data-count="{{ $events->count() }}" class="d-none" id="r-count-data"></div>

@forelse ($events as $event)
    <tr>
        <td data-label="{{ __('Member') }}" class="ft-cell-name">
            {{ $event->member?->full_name ?? '—' }}
        </td>
        <td data-label="{{ __('Event') }}">
            <span class="badge bg-light text-dark border">{{ $event->display_title }}</span>
        </td>
        <td data-label="{{ __('Date') }}">{{ formatDate($event->event_date) }}</td>
        <td data-label="{{ __('Location') }}">{{ $event->location ?? '—' }}</td>
        <td data-label="{{ __('Description') }}" class="small text-muted">
            {{ \Illuminate\Support\Str::limit($event->description ?? '', 80) }}
        </td>
    </tr>
@empty
    <tr class="ft-row-empty">
        <td colspan="5">
            @include('familytree::partials.empty-state', [
                'icon' => 'bi-calendar-x', 'title' => __('No events found'),
            ])
        </td>
    </tr>
@endforelse