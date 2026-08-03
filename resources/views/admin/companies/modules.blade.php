<x-admin-layout>
    @section('title', 'Company Modules')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">{{ __('Modules for') }}: {{ $company->name }}</h3>
        <a href="{{ route('admin.companies.show', $company) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> {{ __('Back to Company') }}
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.companies.modules.update', $company) }}">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    @foreach ($modules as $module)
                        @php $cm = $assigned->get($module->id); @endphp
                        <div class="col-12 col-md-6">
                            <div class="border rounded p-3 d-flex justify-content-between align-items-center {{ $module->status !== 'active' ? 'opacity-50' : '' }}">
                                <div>
                                    <i class="bi {{ $module->icon ?? 'bi-puzzle' }} me-2"></i>
                                    <strong>{{ $module->translated('name') }}</strong>
                                    @if ($module->status !== 'active')
                                        <span class="badge bg-secondary ms-2">{{ ucfirst(str_replace('_', ' ', $module->status)) }}</span>
                                    @endif
                                </div>
                                <div class="form-check form-switch mb-0">
                                    <input type="checkbox" name="modules[]" value="{{ $module->id }}"
                                           class="form-check-input" {{ $cm?->is_enabled ? 'checked' : '' }}
                                           {{ $module->status !== 'active' ? 'disabled' : '' }}>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <button type="submit" class="btn btn-primary mt-4">{{ __('Save Module Assignments') }}</button>
                <p class="text-muted small mt-2 mb-0">
                    {{ __('Only Active modules can be assigned. Coming Soon and Disabled modules are shown for visibility only.') }}
                </p>
            </form>
        </div>
    </div>
</x-admin-layout>