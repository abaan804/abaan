<x-admin-layout>
    @section('title', 'Edit Module')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">{{ __('Edit Module') }}: {{ $module->name_en }}</h3>
        <a href="{{ route('admin.modules.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> {{ __('Back') }}
        </a>
    </div>

    <form method="POST" action="{{ route('admin.modules.update', $module) }}">
        @method('PUT')
        @include('admin.modules._form')
    </form>
</x-admin-layout>