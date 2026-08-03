<x-admin-layout>
    @section('title', 'Edit FAQ')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">{{ __('Edit FAQ') }}</h3>
        <a href="{{ route('admin.faqs.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> {{ __('Back') }}</a>
    </div>
    <form method="POST" action="{{ route('admin.faqs.update', $faq) }}">
        @method('PUT')
        @include('admin.faqs._form')
    </form>
</x-admin-layout>