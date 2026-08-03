@forelse ($families as $family)
    <div class="col-12 col-md-6 col-lg-4" id="family-card-{{ $family->id }}">
        <div class="card shadow-sm border-0 h-100" style="border-radius:14px;">
            <div class="card-body">

                {{-- Header row --}}
                <div class="d-flex align-items-center gap-3 mb-3">
                    @if ($family->photo)
                        <img src="{{ asset('storage/' . $family->photo) }}"
                             class="ft-avatar" style="width:52px;height:52px;">
                    @else
                        <div class="rounded-circle d-flex align-items-center justify-content-center"
                             style="width:52px;height:52px;background:rgba(26,82,118,.1);">
                            <i class="bi bi-houses fs-4" style="color:var(--ft-primary);"></i>
                        </div>
                    @endif
                    <div>
                        <h6 class="mb-0 fw-bold">{{ $family->name }}</h6>
                        <div class="text-muted small">
                            @if ($family->village) {{ $family->village }}@if($family->city), @endif @endif
                            {{ $family->city ?? '' }}
                        </div>
                    </div>
                </div>

                {{-- Stats row --}}
                <div class="d-flex gap-3 mb-3">
                    <div class="text-center">
                        <div class="fw-bold">{{ $family->members_count }}</div>
                        <div class="text-muted small">{{ __('Members') }}</div>
                    </div>
                    @if ($family->district)
                        <div class="text-muted small mt-1">
                            <i class="bi bi-geo-alt"></i> {{ $family->district }}
                        </div>
                    @endif
                    <div class="ms-auto">
                        <span class="badge bg-{{ $family->status === 'active' ? 'success' : 'secondary' }}">
                            {{ ucfirst($family->status) }}
                        </span>
                    </div>
                </div>

                @if ($family->description)
                    <p class="small text-muted mb-3" style="line-clamp:2;-webkit-line-clamp:2;overflow:hidden;display:-webkit-box;-webkit-box-orient:vertical;">
                        {{ $family->description }}
                    </p>
                @endif

                {{-- Actions --}}
                <div class="d-flex gap-2">
                    <a href="{{ route('familytree.family.members.index', [$family,'standalone'=>1]) }}"
                       class="btn btn-primary btn-sm flex-grow-1">
                        <i class="bi bi-people"></i> {{ __('Open') }}
                    </a>
                    <a href="{{ route('familytree.family.tree.index', [$family,'standalone'=>1]) }}"
                       class="btn btn-outline-primary btn-sm" title="{{ __('View Tree') }}">
                        <i class="bi bi-diagram-3"></i>
                    </a>
                    @can('familytree.manage-families')
                        <button type="button" class="btn btn-outline-secondary btn-sm btn-edit-family"
                                data-id="{{ $family->id }}" title="{{ __('Edit') }}">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-sm btn-delete-family"
                                data-id="{{ $family->id }}" data-name="{{ $family->name }}"
                                title="{{ __('Delete') }}">
                            <i class="bi bi-trash"></i>
                        </button>
                    @endcan
                </div>
            </div>
        </div>
    </div>
@empty
    <div class="col-12">
        @include('familytree::partials.empty-state', [
            'icon'        => 'bi-houses',
            'title'       => __('No families found'),
            'description' => __('Add your first family to start building your family tree.'),
        ])
    </div>
@endforelse