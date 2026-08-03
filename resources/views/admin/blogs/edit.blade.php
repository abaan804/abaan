<x-admin-layout>
    @section('title', 'Edit Blog Post')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">{{ __('Edit Post') }}: {{ $blog->title_en }}</h3>
        <a href="{{ route('admin.blogs.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> {{ __('Back') }}</a>
    </div>
    <form method="POST" action="{{ route('admin.blogs.update', $blog) }}" enctype="multipart/form-data">
        @method('PUT')
        @include('admin.blogs._form')
    </form>
</x-admin-layout>