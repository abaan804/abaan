<x-admin-layout>
    @section('title', 'Edit Package')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">{{ __('Edit Package') }}: {{ $package->name_en }}</h3>
        <a href="{{ route('admin.packages.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> {{ __('Back') }}
        </a>
    </div>

    <form method="POST" action="{{ route('admin.packages.update', $package) }}">
        @method('PUT')
        @include('admin.packages._form')
    </form>
</x-admin-layout>