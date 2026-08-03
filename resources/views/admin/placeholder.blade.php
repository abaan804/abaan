<x-admin-layout>
    @section('title', $label)
    <div class="text-center py-5">
        <i class="bi bi-cone-striped" style="font-size: 3rem;"></i>
        <h4 class="mt-3">{{ $label }}</h4>
        <p class="text-muted">{{ __('This section is coming up in a later step.') }}</p>
    </div>
</x-admin-layout>