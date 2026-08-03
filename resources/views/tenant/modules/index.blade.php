<x-app-layout>
    @section('title', 'Modules')

    <h3 class="mb-4">{{ __('Modules') }}</h3>

    <div class="row g-4">
        @foreach ($modules as $module)
            @php
                $cm = $assigned->get($module->id);
                $isEnabled = $cm?->is_enabled ?? false;
                $isPendingRequest = in_array($module->id, $pendingRequests);
            @endphp
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm {{ $isEnabled ? 'border-success' : '' }}">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <i class="bi {{ $module->icon ?? 'bi-puzzle' }} fs-2 text-primary"></i>
                            @if ($isEnabled)
                                <span class="badge bg-success">{{ __('Active') }}</span>
                            @elseif ($module->status === 'coming_soon')
                                <span class="badge bg-warning">{{ __('Coming Soon') }}</span>
                            @elseif ($module->status === 'disabled')
                                <span class="badge bg-secondary">{{ __('Unavailable') }}</span>
                            @else
                                <span class="badge bg-light text-dark border">{{ __('Not Enabled') }}</span>
                            @endif
                        </div>

                        <h5 class="card-title">{{ $module->translated('name') }}</h5>
                        <p class="text-muted small flex-grow-1">
                            {{ $module->translated('description') ?? __('No description available.') }}
                        </p>

                        @can('manage company subscription')
                            @if (! $isEnabled && $module->status === 'active')
                                @if ($isPendingRequest)
                                    <button class="btn btn-outline-secondary w-100" disabled>
                                        <i class="bi bi-clock-history"></i> {{ __('Request Pending') }}
                                    </button>
                                @else
                                    <form method="POST" action="{{ route('modules.request', $module) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-primary w-100">
                                            {{ __('Request This Module') }}
                                        </button>
                                    </form>
                                @endif
                            @elseif ($module->status !== 'active' && ! $isEnabled)
                                <button class="btn btn-outline-secondary w-100" disabled>{{ __('Not Yet Available') }}</button>
                            @endif
                        @endcan
                                                @if ($isEnabled )
                            <a href="{{ route($module->key, ['standalone' => 1]) }}" target="_blank" class="btn btn-sm btn-outline-primary w-100 mt-2">
                                <i class="bi bi-box-arrow-up-right"></i> {{ $module->name_en }}
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</x-app-layout>