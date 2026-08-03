@extends('familytree::layouts.standalone')
@section('heading', $member->full_name)
@section('ft-content')

{{-- Profile Hero --}}
<div class="ft-profile-hero mb-4">
    <div class="d-flex align-items-center gap-4 flex-wrap">
        @if ($member->profile_photo)
            <img src="{{ asset('storage/' . $member->profile_photo) }}"
                 class="ft-avatar" style="width:90px;height:90px;border-color:rgba(255,255,255,.5);">
        @else
            <div class="rounded-circle d-flex align-items-center justify-content-center"
                 style="width:90px;height:90px;background:rgba(255,255,255,.15);border:3px solid rgba(255,255,255,.4);">
                <i class="bi bi-person" style="font-size:2.5rem;color:#fff;"></i>
            </div>
        @endif
        <div class="flex-grow-1">
            <h3 class="mb-1">
                {{ $member->full_name }}
                @if ($member->life_status === 'deceased') <span style="opacity:.7;"></span> @endif
            </h3>
            <div style="opacity:.85;" class="small">
                {{ __('S/O') }} {{ $member->father_display_name }}
                @if ($member->date_of_birth)
                    &nbsp;·&nbsp; {{ formatDate($member->date_of_birth) }}
                    @if ($member->age !== null) ({{ $member->age }} {{ __('yrs') }}) @endif
                @endif
                @if ($member->occupation) &nbsp;·&nbsp; {{ $member->occupation }} @endif
            </div>
            <div class="mt-2 d-flex gap-2 flex-wrap">
                <span class="badge bg-light text-dark">{{ ucfirst($member->gender) }}</span>
                <span class="badge bg-{{ $member->life_status === 'living' ? 'success' : 'secondary' }}">
                    {{ ucfirst($member->life_status) }}
                </span>
                <span class="badge bg-light text-dark">{{ ucfirst($member->marital_status) }}</span>
                @if ($member->blood_group)
                    <span class="badge bg-danger">{{ $member->blood_group }}</span>
                @endif
            </div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('familytree.family.members.index', [$family,'standalone'=>1]) }}"
               class="btn btn-outline-light btn-sm"><i class="bi bi-arrow-left"></i> {{ __('Back') }}</a>
            <a href="{{ route('familytree.family.tree.index',$family) }}?root={{ $member->id }}&highlight={{ $member->id }}"
               class="btn btn-outline-light btn-sm"><i class="bi bi-diagram-3"></i> {{ __('Tree') }}</a>
            @can('familytree.manage-members')
                <button type="button" class="btn btn-warning btn-sm" id="btn-edit-this-member">
                    <i class="bi bi-pencil"></i> {{ __('Edit') }}
                </button>
            @endcan
        </div>
    </div>
</div>

<div class="row g-4">

    {{-- Left Column --}}
    <div class="col-12 col-lg-4">

        {{-- Personal Info --}}
        <div class="card shadow-sm border-0 mb-4" style="border-radius:14px;">
            <div class="card-header bg-white border-0"><strong>{{ __('Personal Information') }}</strong></div>
            <div class="card-body">
                @php
                    $info = [
                        __('CNIC')         => $member->cnic,
                        __('Passport')     => $member->passport_number,
                        __('Contact')      => $member->contact_number,
                        __('WhatsApp')     => $member->whatsapp_number,
                        __('Email')        => $member->email,
                        __('Education')    => $member->education,
                        __('Religion')     => $member->religion,
                        __('Nationality')  => $member->nationality,
                        __('Place of Birth') => $member->place_of_birth,
                    ];
                    if ($member->life_status === 'deceased') {
                        $info[__('Date of Death')]  = $member->date_of_death ? formatDate($member->date_of_death) : null;
                        $info[__('Burial Place')]   = $member->burial_place;
                    }
                @endphp
                @foreach ($info as $label => $value)
                    @if ($value)
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted small">{{ $label }}</span>
                            <span class="small fw-semibold text-end">{{ $value }}</span>
                        </div>
                    @endif
                @endforeach
                @if ($member->current_address)
                    <div class="py-2 border-bottom">
                        <div class="text-muted small">{{ __('Current Address') }}</div>
                        <div class="small mt-1">{{ $member->current_address }}</div>
                    </div>
                @endif
                @if ($member->other_details)
                    <div class="py-2">
                        <div class="text-muted small">{{ __('Other Details') }}</div>
                        <div class="small mt-1">{{ $member->other_details }}</div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Documents --}}
        <div class="card shadow-sm border-0" style="border-radius:14px;">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                <strong>{{ __('Documents') }}</strong>
                @can('familytree.manage-documents')
                    <a href="{{ route('familytree.family.documents.index', $family) }}"
                       class="btn btn-sm btn-outline-primary">{{ __('Manage') }}</a>
                @endcan
            </div>
            <div class="card-body">
                @forelse ($member->documents as $doc)
                    <div class="d-flex justify-content-between align-items-center py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div>
                            <div class="small fw-semibold">{{ $doc->title }}</div>
                            <div class="text-muted small">{{ $doc->type_display }}</div>
                        </div>
                        <div class="d-flex gap-1">
                            @if ($doc->is_previewable)
                                <a href="{{ $doc->url }}" target="_blank"
                                   class="btn btn-xs btn-outline-secondary btn-sm">
                                    <i class="bi bi-eye"></i>
                                </a>
                            @endif
                            <a href="{{ route('familytree.family.documents.download', [$family, $doc]) }}"
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-download"></i>
                            </a>
                        </div>
                    </div>
                @empty
                    <p class="text-muted small text-center mb-0">{{ __('No documents uploaded.') }}</p>
                @endforelse
            </div>
        </div>

    </div>

    {{-- Right Column --}}
    <div class="col-12 col-lg-8">

        {{-- Relationships Summary --}}
        <div class="card shadow-sm border-0 mb-4" style="border-radius:14px;">
            <div class="card-header bg-white border-0"><strong>{{ __('Family Relationships') }}</strong></div>
            <div class="card-body">
                <div class="row g-3">
                    {{-- Parents --}}
                    <div class="col-6 col-md-3">
                        <div class="ft-stat-card p-3 text-center h-100">
                            <div class="text-muted small">{{ __('Father') }}</div>
                            @if ($summary['father'])
                                <a href="{{ route('familytree.family.members.show', [$family, $summary['father']]) }}"
                                   class="d-block fw-semibold small text-decoration-none mt-1">
                                    {{ $summary['father']->full_name }}
                                </a>
                            @else
                                <div class="text-muted small mt-1">{{ $member->father_display_name }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="ft-stat-card p-3 text-center h-100">
                            <div class="text-muted small">{{ __('Mother') }}</div>
                            @if ($summary['mother'])
                                <a href="{{ route('familytree.family.members.show', [$family, $summary['mother']]) }}"
                                   class="d-block fw-semibold small text-decoration-none mt-1">
                                    {{ $summary['mother']->full_name }}
                                </a>
                            @else
                                <div class="text-muted small mt-1">{{ $member->mother_display_name }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="ft-stat-card p-3 text-center h-100">
                            <div class="text-muted small">{{ __('Spouses') }}</div>
                            <div class="h4 mb-0">{{ $summary['spouses']->count() }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="ft-stat-card p-3 text-center h-100">
                            <div class="text-muted small">{{ __('Children') }}</div>
                            <div class="h4 mb-0">{{ $summary['children']->count() }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="ft-stat-card p-3 text-center h-100">
                            <div class="text-muted small">{{ __('Brothers') }}</div>
                            <div class="h4 mb-0">{{ $summary['brothers']->count() }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="ft-stat-card p-3 text-center h-100">
                            <div class="text-muted small">{{ __('Sisters') }}</div>
                            <div class="h4 mb-0">{{ $summary['sisters']->count() }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="ft-stat-card p-3 text-center h-100">
                            <div class="text-muted small">{{ __('Grandparents') }}</div>
                            <div class="h4 mb-0">{{ count($summary['grandparents']) }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="ft-stat-card p-3 text-center h-100">
                            <div class="text-muted small">{{ __('Cousins') }}</div>
                            <div class="h4 mb-0">{{ $summary['cousins']->count() }}</div>
                        </div>
                    </div>
                </div>

                {{-- Spouses detail --}}
                @if ($summary['spouses']->isNotEmpty())
                    <h6 class="mt-4 mb-2 text-muted small fw-bold">{{ __('SPOUSES') }}</h6>
                    @foreach ($summary['spouses'] as $spouse)
                        <div class="d-flex align-items-center gap-2 py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                            @if ($spouse->profile_photo)
                                <img src="{{ asset('storage/' . $spouse->profile_photo) }}"
                                     class="ft-avatar" style="width:32px;height:32px;">
                            @else
                                <div class="rounded-circle d-flex align-items-center justify-content-center"
                                     style="width:32px;height:32px;background:rgba(142,68,173,.1);">
                                    <i class="bi bi-person" style="color:var(--ft-female);font-size:.85rem;"></i>
                                </div>
                            @endif
                            <a href="{{ route('familytree.family.members.show', [$family, $spouse]) }}"
                               class="text-decoration-none fw-semibold small">{{ $spouse->full_name }}</a>
                        </div>
                    @endforeach
                @endif

                {{-- Children detail --}}
                @if ($summary['children']->isNotEmpty())
                    <h6 class="mt-4 mb-2 text-muted small fw-bold">{{ __('CHILDREN') }}</h6>
                    <div class="row g-2">
                        @foreach ($summary['children'] as $child)
                            <div class="col-6 col-md-4">
                                <a href="{{ route('familytree.family.members.show', [$family, $child]) }}"
                                   class="d-flex align-items-center gap-2 text-decoration-none p-2 rounded border">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                         style="width:30px;height:30px;background:{{ $child->gender === 'female' ? 'rgba(142,68,173,.1)' : 'rgba(26,82,118,.1)' }};">
                                        <i class="bi bi-person" style="color:{{ $child->gender === 'female' ? 'var(--ft-female)' : 'var(--ft-primary)' }};font-size:.8rem;"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold small">{{ $child->full_name }}</div>
                                        @if ($child->age !== null)
                                            <div class="text-muted small">{{ $child->age }} {{ __('yrs') }}</div>
                                        @endif
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Grandparents --}}
                @if (!empty($summary['grandparents']))
                    <h6 class="mt-4 mb-2 text-muted small fw-bold">{{ __('GRANDPARENTS') }}</h6>
                    @foreach ($summary['grandparents'] as $entry)
                        <div class="d-flex justify-content-between py-1 border-bottom">
                            <span class="text-muted small">{{ $entry['label'] }}</span>
                            <a href="{{ route('familytree.family.members.show', [$family, $entry['member']]) }}"
                               class="small text-decoration-none fw-semibold">{{ $entry['member']->full_name }}</a>
                        </div>
                    @endforeach
                @endif

                {{-- Uncles & Aunts --}}
                @if (!empty($summary['uncles_aunts']))
                    <h6 class="mt-4 mb-2 text-muted small fw-bold">{{ __('UNCLES & AUNTS') }}</h6>
                    @foreach ($summary['uncles_aunts'] as $entry)
                        <div class="d-flex justify-content-between py-1 border-bottom">
                            <span class="text-muted small">{{ $entry['label'] }}</span>
                            <a href="{{ route('familytree.family.members.show', [$family, $entry['member']]) }}"
                               class="small text-decoration-none fw-semibold">{{ $entry['member']->full_name }}</a>
                        </div>
                    @endforeach
                @endif

            </div>
        </div>

        {{-- Life Events Timeline --}}
        <div class="card shadow-sm border-0 mb-4" style="border-radius:14px;">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                <strong>{{ __('Life Events') }}</strong>
                @can('familytree.manage-events')
                    <a href="{{ route('familytree.family.events.index', $family) }}"
                       class="btn btn-sm btn-outline-primary">{{ __('Manage') }}</a>
                @endcan
            </div>
            <div class="card-body">
                @if ($member->events->isNotEmpty())
                    <div class="ft-timeline">
                        @foreach ($member->events->where('status','active') as $event)
                            <div class="ft-timeline-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="fw-semibold small">{{ $event->display_title }}</div>
                                        @if ($event->location)
                                            <div class="text-muted small">
                                                <i class="bi bi-geo-alt"></i> {{ $event->location }}
                                            </div>
                                        @endif
                                        @if ($event->description)
                                            <div class="text-muted small mt-1">{{ $event->description }}</div>
                                        @endif
                                    </div>
                                    <span class="text-muted small text-nowrap ms-2">{{ formatDate($event->event_date) }}</span>
                                </div>
                                @if ($event->images->isNotEmpty())
                                    <div class="d-flex gap-2 mt-2 flex-wrap">
                                        @foreach ($event->images->take(3) as $img)
                                            <a href="{{ $img->url }}" target="_blank">
                                                <img src="{{ $img->url }}" class="rounded"
                                                     style="width:60px;height:60px;object-fit:cover;">
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted small text-center mb-0">{{ __('No events recorded for this member.') }}</p>
                @endif
            </div>
        </div>

        {{-- Siblings --}}
        @if ($summary['brothers']->isNotEmpty() || $summary['sisters']->isNotEmpty())
            <div class="card shadow-sm border-0" style="border-radius:14px;">
                <div class="card-header bg-white border-0">
                    <strong>{{ __('Brothers & Sisters') }}</strong>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        @foreach ($summary['brothers']->merge($summary['sisters'])->sortBy('date_of_birth') as $sibling)
                            <div class="col-6 col-md-4">
                                <a href="{{ route('familytree.family.members.show', [$family, $sibling]) }}"
                                   class="d-flex align-items-center gap-2 text-decoration-none p-2 rounded border">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                         style="width:30px;height:30px;background:{{ $sibling->gender === 'female' ? 'rgba(142,68,173,.1)' : 'rgba(26,82,118,.1)' }};">
                                        <i class="bi bi-person" style="color:{{ $sibling->gender === 'female' ? 'var(--ft-female)' : 'var(--ft-primary)' }};font-size:.8rem;"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold small">{{ $sibling->full_name }}</div>
                                        <div class="text-muted small">{{ ucfirst($sibling->gender) }}</div>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

    </div>
</div>

@push('scripts')
<script>
(function () {
    // Quick-edit: clicking the Edit button on profile opens the
    // members list edit modal if the user navigates back,
    // OR fires a deep-link back to the list with the edit param.
    document.getElementById('btn-edit-this-member')?.addEventListener('click', () => {
        window.location.href = '{{ route("familytree.family.members.index", $family) }}?edit={{ $member->id }}';
    });
})();
</script>
@endpush
@endsection