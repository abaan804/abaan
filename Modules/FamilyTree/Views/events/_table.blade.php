@forelse ($events as $event)
    <tr>
        <td data-label="{{ __('Member') }}" class="ft-cell-name">
            @if ($event->member)
                <a href="{{ route('familytree.family.members.show', [$family, $event->member]) }}"
                   class="text-decoration-none fw-semibold">{{ $event->member->full_name }}</a>
            @else
                <span class="text-muted">—</span>
            @endif
        </td>
        <td data-label="{{ __('Event') }}">
            <span class="badge bg-light text-dark border">{{ $event->display_title }}</span>
        </td>
        <td data-label="{{ __('Date') }}">{{ formatDate($event->event_date) }}</td>
        <td data-label="{{ __('Location') }}">{{ $event->location ?? '—' }}</td>
        <td data-label="{{ __('Media') }}">
            @if ($event->media->isNotEmpty())
                <div class="d-flex gap-1 flex-wrap">
                    @foreach ($event->images->take(2) as $img)
                        <a href="{{ $img->url }}" target="_blank">
                            <img src="{{ $img->url }}" class="rounded"
                                 style="width:32px;height:32px;object-fit:cover;">
                        </a>
                    @endforeach
                    @if ($event->media->count() > 2)
                        <span class="badge bg-secondary">+{{ $event->media->count() - 2 }}</span>
                    @endif
                </div>
            @else
                <span class="text-muted small">—</span>
            @endif
        </td>
        <td class="ft-cell-actions">
            @can('familytree.manage-events')
                <button type="button" class="btn btn-sm btn-outline-secondary btn-edit-event"
                        data-id="{{ $event->id }}">
                    <i class="bi bi-pencil"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger btn-delete-event"
                        data-id="{{ $event->id }}"
                        data-name="{{ $event->display_title }}">
                    <i class="bi bi-trash"></i>
                </button>
            @endcan
        </td>
    </tr>
@empty
    <tr class="ft-row-empty">
        <td colspan="6">
            @include('familytree::partials.empty-state', [
                'icon'        => 'bi-calendar-x',
                'title'       => __('No events recorded'),
                'description' => __('Add life events like births, graduations, marriages to the timeline.'),
            ])
        </td>
    </tr>
@endforelse

@if ($events->hasPages())
    <tr class="ft-row-empty">
        <td colspan="6">
            <div id="events-pagination" class="d-flex justify-content-center py-2">
                {{ $events->links() }}
            </div>
        </td>
    </tr>
@endif