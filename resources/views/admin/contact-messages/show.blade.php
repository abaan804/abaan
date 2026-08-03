<x-admin-layout>
    @section('title', 'Message Details')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">{{ $message->subject ?: __('(No subject)') }}</h3>
        <a href="{{ route('admin.contact-messages.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> {{ __('Back') }}
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <dl class="row mb-0 small">
                        <dt class="col-3 text-muted">{{ __('From') }}</dt>
                        <dd class="col-9">{{ $message->name }} &lt;{{ $message->email }}&gt;</dd>

                        @if ($message->phone)
                            <dt class="col-3 text-muted">{{ __('Phone') }}</dt>
                            <dd class="col-9">{{ $message->phone }}</dd>
                        @endif

                        <dt class="col-3 text-muted">{{ __('Received') }}</dt>
                        <dd class="col-9">{{ formatDateTime($message->created_at) }}</dd>
                    </dl>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white"><strong>{{ __('Message') }}</strong></div>
                <div class="card-body">
                    <p class="mb-0" style="white-space: pre-wrap;">{{ $message->message }}</p>
                </div>
            </div>

            <div class="d-flex gap-2">
                <a href="mailto:{{ $message->email }}?subject=RE: {{ $message->subject ?: 'Your message' }}" class="btn btn-primary">
                    <i class="bi bi-reply"></i> {{ __('Reply by Email') }}
                </a>
                <form method="POST" action="{{ route('admin.contact-messages.mark-unread', $message) }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary">
                        <i class="bi bi-envelope"></i> {{ __('Mark as Unread') }}
                    </button>
                </form>
                <form method="POST" action="{{ route('admin.contact-messages.destroy', $message) }}"
                      onsubmit="return confirm('{{ __('Delete this message?') }}');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger">
                        <i class="bi bi-trash"></i> {{ __('Delete') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>