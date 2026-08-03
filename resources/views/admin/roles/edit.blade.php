<x-admin-layout>
    @section('title', 'Edit Role')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">{{ __('Edit Role') }}: {{ $role->name }}</h3>
        <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> {{ __('Back') }}
        </a>
    </div>

    <form method="POST" action="{{ route('admin.roles.update', $role) }}">
        @method('PUT')
        @include('admin.roles._form')
    </form>
</x-admin-layout>