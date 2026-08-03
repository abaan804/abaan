<x-admin-layout>
    @section('title', 'New Blog Post')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">{{ __('New Blog Post') }}</h3>
        <a href="{{ route('admin.blogs.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> {{ __('Back') }}</a>
    </div>
    <form method="POST" action="{{ route('admin.blogs.store') }}" enctype="multipart/form-data">
        @include('admin.blogs._form')
    </form>
</x-admin-layout>