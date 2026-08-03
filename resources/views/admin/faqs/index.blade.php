<x-admin-layout>
    @section('title', 'FAQ')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">{{ __('FAQs') }}</h3>
        <a href="{{ route('admin.faqs.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> {{ __('New FAQ') }}</a>
    </div>
    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>{{ __('Question') }}</th>
                        <th>{{ __('Category') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th class="text-end">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($faqs as $faq)
                        <tr>
                            <td>{{ $faq->question_en }}</td>
                            <td>{{ $faq->category ?? '—' }}</td>
                            <td>
                                <span class="badge bg-{{ $faq->is_active ? 'success' : 'secondary' }}">
                                    {{ $faq->is_active ? __('Active') : __('Hidden') }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.faqs.edit', $faq) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                <form method="POST" action="{{ route('admin.faqs.destroy', $faq) }}" class="d-inline" onsubmit="return confirm('{{ __('Delete this FAQ?') }}');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">{{ __('No FAQs yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($faqs->hasPages())<div class="card-footer bg-white">{{ $faqs->links() }}</div>@endif
    </div>
</x-admin-layout>