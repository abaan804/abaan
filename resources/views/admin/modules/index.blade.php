<x-admin-layout>
    @section('title', 'Modules')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">{{ __('Modules') }}</h3>
        <a href="{{ route('admin.modules.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> {{ __('New Module') }}
        </a>
    </div>

    <div class="row g-3">
        @forelse ($modules as $module)
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <i class="bi {{ $module->icon ?? 'bi-puzzle' }} fs-2 text-primary"></i>
                            <span class="badge bg-{{ $module->status === 'active' ? 'success' : ($module->status === 'coming_soon' ? 'warning' : 'secondary') }}">
                                {{ ucfirst(str_replace('_', ' ', $module->status)) }}
                            </span>
                        </div>
                        <h5 class="card-title">{{ $module->name_en }}</h5>
                        <p class="text-muted small">{{ $module->description_en ?? __('No description.') }}</p>
                        <p class="text-muted small mb-3">
                            <i class="bi bi-building"></i> {{ $module->company_modules_count }} {{ __('companies assigned') }}
                        </p>

                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.modules.edit', $module) }}" class="btn btn-sm btn-outline-primary flex-grow-1">
                                <i class="bi bi-pencil"></i> {{ __('Edit') }}
                            </a>
                            <form method="POST" action="{{ route('admin.modules.destroy', $module) }}"
                                  onsubmit="return confirm('{{ __('Delete this module?') }}');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info text-center">{{ __('No modules registered yet.') }}</div>
            </div>
        @endforelse
    </div>
</x-admin-layout>