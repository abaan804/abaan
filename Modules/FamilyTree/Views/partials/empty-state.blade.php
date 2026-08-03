<div class="text-center py-5" style="color:#9ca3af;">
    <i class="bi {{ $icon ?? 'bi-inbox' }}" style="font-size:2.8rem;"></i>
    <h6 class="mt-3 mb-1">{{ $title }}</h6>
    @if (! empty($description))
        <p class="small mb-0">{{ $description }}</p>
    @endif
    @if (! empty($actionLabel) && ! empty($actionUrl))
        <a href="{{ $actionUrl }}" class="btn btn-primary btn-sm mt-3">{{ $actionLabel }}</a>
    @endif
</div>