<div style="text-align:center; padding: 2.5rem 1rem; color: #6B7280;">
    <i class="bi {{ $icon ?? 'bi-inbox' }}" style="font-size: 2.5rem;"></i>
    <h6 class="mt-3">{{ $title }}</h6>
    @if (! empty($description))
        <p class="small text-muted">{{ $description }}</p>
    @endif
    @if (! empty($actionLabel) && ! empty($actionUrl))
        <a href="{{ $actionUrl }}" class="btn btn-primary btn-sm mt-2">{{ $actionLabel }}</a>
    @endif
</div>