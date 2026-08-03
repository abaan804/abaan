<div class="text-center py-5">
    <div class="mb-3">
        <i class="bi {{ $icon ?? 'bi-inbox' }} text-muted" style="font-size: 3rem;"></i>
    </div>

    <h5 class="fw-semibold mb-2">
        {{ $title ?? __('Nothing here yet') }}
    </h5>

    @if(!empty($description))
        <p class="text-muted mb-0">
            {{ $description }}
        </p>
    @endif

    @isset($action)
        <div class="mt-4">
            {!! $action !!}
        </div>
    @endisset
</div>