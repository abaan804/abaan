<div class="ledger-empty-state">
    <i class="bi {{ $icon ?? 'bi-inbox' }}" style="font-size: 2.5rem;"></i>
    <h6 class="mt-3">{{ $title }}</h6>
    @if (! empty($description))
        <p class="text-muted small">{{ $description }}</p>
    @endif
    @if (! empty($actionLabel) && ! empty($actionUrl))
        <a href="{{ $actionUrl }}" class="btn btn-primary btn-sm mt-2">{{ $actionLabel }}</a>
    @endif
</div>