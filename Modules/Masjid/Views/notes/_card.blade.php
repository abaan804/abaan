@php
    $colorMap = [
        'default' => ['bg' => '#fffdf0', 'border' => '#e5e7eb'],
        'warning' => ['bg' => '#fffbeb', 'border' => '#f59e0b'],
        'danger'  => ['bg' => '#fef2f2', 'border' => '#ef4444'],
        'success' => ['bg' => '#f0fdf4', 'border' => '#22c55e'],
        'info'    => ['bg' => '#eff6ff', 'border' => '#3b82f6'],
    ];
    $c = $colorMap[$note->color] ?? $colorMap['default'];
@endphp
<div class="col-12 col-md-6">
    <div class="card border-0 shadow-sm h-100"
         style="border-radius:12px;background:{{ $c['bg'] }};border-left:4px solid {{ $c['border'] }} !important;">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <h6 class="fw-bold mb-0">
                    @if ($note->is_pinned)
                        <i class="bi bi-pin-angle-fill text-warning me-1"></i>
                    @endif
                    {{ $note->title }}
                </h6>
                <div class="d-flex gap-1 flex-shrink-0">
                    <button type="button" class="btn btn-xs btn-sm btn-outline-secondary btn-pin-note"
                            data-id="{{ $note->id }}"
                            title="{{ $note->is_pinned ? __('Unpin') : __('Pin') }}">
                        <i class="bi bi-pin{{ $note->is_pinned ? '-angle-fill' : '' }}"></i>
                    </button>
                    <button type="button" class="btn btn-xs btn-sm btn-outline-secondary btn-edit-note"
                            data-id="{{ $note->id }}">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button type="button" class="btn btn-xs btn-sm btn-outline-danger btn-delete-note"
                            data-id="{{ $note->id }}">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
            <p class="small mb-2" style="white-space:pre-wrap;">{{ $note->content }}</p>
            <div class="d-flex gap-2 flex-wrap">
                <span class="badge bg-light text-dark border small">
                    {{ $note->type === 'season' ? __('Season Note') : __('General') }}
                </span>
                @if ($note->season)
                    <span class="badge bg-light text-dark border small">
                        <i class="bi bi-calendar3"></i> {{ $note->season->name }}
                    </span>
                @endif
                <span class="text-muted small ms-auto">{{ $note->updated_at->format('d M Y') }}</span>
            </div>
        </div>
    </div>
</div>