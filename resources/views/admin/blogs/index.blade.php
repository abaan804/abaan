<x-admin-layout>
    @section('title', 'Blog')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">{{ __('Blog Posts') }}</h3>
        <a href="{{ route('admin.blogs.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> {{ __('New Post') }}
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>{{ __('Title') }}</th>
                        <th>{{ __('Author') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Published') }}</th>
                        <th class="text-end">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($blogs as $blog)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $blog->title_en }}</div>
                                <div class="text-muted small">{{ $blog->slug }}</div>
                            </td>
                            <td>{{ $blog->author?->name ?? '—' }}</td>
                            <td>
                                <span class="badge bg-{{ $blog->status === 'published' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($blog->status) }}
                                </span>
                            </td>
                            <td>{{ formatDate($blog->published_at) }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.blogs.edit', $blog) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.blogs.destroy', $blog) }}" class="d-inline"
                                      onsubmit="return confirm('{{ __('Delete this post?') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">{{ __('No blog posts yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($blogs->hasPages())
            <div class="card-footer bg-white">{{ $blogs->links() }}</div>
        @endif
    </div>
</x-admin-layout>