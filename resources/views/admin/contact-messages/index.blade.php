<x-admin-layout>
    @section('title', 'Contact Messages')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">{{ __('Contact Messages') }}</h3>
        @if ($unreadCount > 0)
            <span class="badge bg-danger">{{ $unreadCount }} {{ __('unread') }}</span>
        @endif
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.contact-messages.index') }}" class="row g-2">
                <div class="col-12 col-md-5">
                    <input type="text" name="search" value="{{ request('search') }}"
                           class="form-control" placeholder="{{ __('Search by name, email, or subject') }}">
                </div>
                <div class="col-12 col-md-3">
                    <select name="filter" class="form-select">
                        <option value="">{{ __('All Messages') }}</option>
                        <option value="unread" {{ request('filter') === 'unread' ? 'selected' : '' }}>{{ __('Unread Only') }}</option>
                    </select>
                </div>
                <div class="col-12 col-md-2">
                    <button type="submit" class="btn btn-primary w-100">{{ __('Filter') }}</button>
                </div>
                <div class="col-12 col-md-2">
                    <a href="{{ route('admin.contact-messages.index') }}" class="btn btn-outline-secondary w-100">{{ __('Reset') }}</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th></th>
                        <th>{{ __('From') }}</th>
                        <th>{{ __('Subject') }}</th>
                        <th>{{ __('Received') }}</th>
                        <th class="text-end">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($messages as $message)
                        <tr class="{{ ! $message->is_read ? 'fw-semibold' : '' }}">
                            <td>
                                @if (! $message->is_read)
                                    <span class="badge bg-danger rounded-circle" style="width: 8px; height: 8px; padding: 0;"></span>
                                @endif
                            </td>
                            <td>
                                <div>{{ $message->name }}</div>
                                <div class="text-muted small fw-normal">{{ $message->email }}</div>
                            </td>
                            <td>{{ $message->subject ?: __('(No subject)') }}</td>
                            <td class="fw-normal">{{ formatDate($message->created_at) }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.contact-messages.show', $message) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> {{ __('View') }}
                                </a>
                                <form method="POST" action="{{ route('admin.contact-messages.destroy', $message) }}" class="d-inline"
                                      onsubmit="return confirm('{{ __('Delete this message?') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">{{ __('No messages yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($messages->hasPages())
            <div class="card-footer bg-white">{{ $messages->links() }}</div>
        @endif
    </div>
</x-admin-layout>