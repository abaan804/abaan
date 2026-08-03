{{-- @extends($familyTreeLayout ?? 'familytree::layouts.app') --}}
@extends('familytree::layouts.standalone')
@section('heading', __('Family Tree') . ' — ' . $family->name)
@section('ft-content')

@push('styles')
    <link rel="stylesheet" href="{{ asset('vendor/treant/Treant.css') }}">
    <style>
        /* ── Tree Controls ─────────────────────────────────────────── */
        .ft-tree-toolbar {
            background: #fff;
            border: 1px solid var(--ft-border);
            border-radius: 14px;
            padding: .75rem 1rem;
            margin-bottom: 1rem;
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
            align-items: center;
        }
        .ft-tree-toolbar .divider {
            width: 1px; background: var(--ft-border); height: 28px; margin: 0 .25rem;
        }
        #ft-tree-container {
            min-height: 560px;
            background: radial-gradient(circle at 20% 30%, #f0f4ff 0%, #f8f9fa 100%);
            position: relative;
            overflow: hidden;
            border-radius: 14px;
            border: 1px solid var(--ft-border);
            touch-action: none;
        }
        #ft-tree-inner {
            position: absolute;
            transform-origin: 0 0;
            cursor: grab;
        }
        #ft-tree-inner:active { cursor: grabbing; }

        /* ── Loading overlay ────────────────────────────────────────── */
        #ft-tree-loading {
            position: absolute; inset: 0;
            display: flex; align-items: center; justify-content: center;
            background: rgba(248,249,250,.85);
            z-index: 10; border-radius: 14px;
        }

        /* ── Empty state overlay ────────────────────────────────────── */
        #ft-tree-empty {
            position: absolute; inset: 0;
            display: none; align-items: center; justify-content: center;
            flex-direction: column; color: #9ca3af;
        }

        /* ── Zoom controls ──────────────────────────────────────────── */
        .ft-zoom-controls {
            position: absolute; bottom: 1rem; right: 1rem;
            display: flex; flex-direction: column; gap: .3rem; z-index: 5;
        }
        .ft-zoom-controls button {
            width: 36px; height: 36px; border-radius: 8px;
            background: #fff; border: 1px solid var(--ft-border);
            display: flex; align-items: center; justify-content: center;
            font-size: .95rem; cursor: pointer;
            box-shadow: 0 2px 6px rgba(0,0,0,.08);
        }
        .ft-zoom-controls button:hover { background: var(--ft-primary); color: #fff; }

        /* ── Mini-map ───────────────────────────────────────────────── */
        #ft-minimap {
            position: absolute; bottom: 1rem; left: 1rem;
            width: 140px; height: 90px;
            background: rgba(255,255,255,.9);
            border: 1px solid var(--ft-border);
            border-radius: 8px; overflow: hidden; z-index: 5;
            display: none;
        }
        @media (min-width: 992px) { #ft-minimap { display: block; } }

        /* ── Hover Card ─────────────────────────────────────────────── */
        #ft-hover-card {
            position: fixed; z-index: 1060;
            width: 270px;
            background: #fff;
            border: 1px solid var(--ft-border);
            border-radius: 14px;
            box-shadow: 0 8px 32px rgba(0,0,0,.14);
            display: none; padding: 0; overflow: hidden;
        }
        .ft-hc-header {
            padding: 12px 14px;
            background: var(--ft-primary); color: #fff;
        }
        .ft-hc-name { font-weight: 700; font-size: .95rem; }
        .ft-hc-sub  { font-size: .78rem; opacity: .85; }
        .ft-hc-body { padding: 10px 14px; }
        .ft-hc-row  {
            display: flex; justify-content: space-between;
            padding: 3px 0; border-bottom: 1px solid #f5f5f5;
            font-size: .8rem;
        }
        .ft-hc-label { color: #9ca3af; }
        .ft-hc-footer {
            padding: 8px 14px; background: #f8f9fa;
            border-top: 1px solid var(--ft-border);
            display: flex; gap: .5rem;
        }

        /* ── Treant node overrides ──────────────────────────────────── */
        .node { cursor: pointer; }
        .ft-member-node {
            background: #fff;
            border: 2px solid var(--ft-primary);
            border-radius: 12px;
            padding: 8px 10px;
            min-width: 120px; max-width: 155px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(26,82,118,.1);
            transition: box-shadow .2s, transform .15s;
            user-select: none;
        }
        .ft-member-node:hover {
            box-shadow: 0 6px 20px rgba(26,82,118,.22);
            transform: translateY(-2px);
        }
        .ft-member-node.ft-node-female { border-color: var(--ft-female); }
        .ft-member-node.ft-node-deceased { opacity: .6; border-style: dashed; }
        .ft-member-node.ft-node-highlight {
            border-color: var(--ft-gold); border-width: 3px;
            box-shadow: 0 0 0 4px rgba(212,172,13,.2);
        }
        .ft-member-node img {
            width: 48px; height: 48px;
            border-radius: 50%; object-fit: cover;
            border: 2px solid #e5e7eb; margin-bottom: 5px;
            display: block; margin-left: auto; margin-right: auto;
        }
        .ft-node-name  { font-weight: 700; font-size: .82rem; line-height: 1.2; color: #1a252f; }
        .ft-node-sub   { font-size: .7rem; color: #9ca3af; margin-top: 2px; }
        .ft-node-deceased-mark { color: #566573; }

        /* ── Relationship path ──────────────────────────────────────── */
        #ft-path-panel {
            background: #fff; border: 1px solid var(--ft-border);
            border-radius: 14px; padding: 1rem; margin-top: 1rem;
            display: none;
        }
        .ft-path-step {
            display: flex; align-items: center; gap: .75rem; padding: .5rem 0;
        }
        .ft-path-step:not(:last-child)::after {
            content: ''; display: block;
            width: 2px; height: 16px;
            background: var(--ft-border);
            margin-left: 19px;
        }
        .ft-path-avatar {
            width: 38px; height: 38px; border-radius: 50%;
            object-fit: cover; border: 2px solid var(--ft-border);
            flex-shrink: 0;
        }
        .ft-path-avatar-placeholder {
            width: 38px; height: 38px; border-radius: 50%;
            background: rgba(26,82,118,.1); flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
        }
    </style>
@endpush

{{-- ── Toolbar ───────────────────────────────────────────────────────────── --}}
<div class="ft-tree-toolbar">

    {{-- Mode --}}
    <div class="btn-group btn-group-sm" role="group">
        <button type="button" class="btn btn-primary" id="btn-mode-full" data-mode="full">
            <i class="bi bi-diagram-3"></i>
            <span class="d-none d-md-inline ms-1">{{ __('Full Tree') }}</span>
        </button>
        <button type="button" class="btn btn-outline-primary" id="btn-mode-descendant" data-mode="descendant">
            <i class="bi bi-arrow-down-circle"></i>
            <span class="d-none d-md-inline ms-1">{{ __('Descendants') }}</span>
        </button>
        <button type="button" class="btn btn-outline-primary" id="btn-mode-ancestor" data-mode="ancestor">
            <i class="bi bi-arrow-up-circle"></i>
            <span class="d-none d-md-inline ms-1">{{ __('Ancestors') }}</span>
        </button>
        <button type="button" class="btn btn-outline-primary" id="btn-mode-path" data-mode="path">
            <i class="bi bi-arrow-left-right"></i>
            <span class="d-none d-md-inline ms-1">{{ __('Relationship') }}</span>
        </button>
    </div>

    <div class="divider d-none d-md-block"></div>

    {{-- Root member selector (for descendant/ancestor modes) --}}
    <div id="ctrl-root-member" class="d-none">
        <select id="select-root-member"
            class="form-select form-select-sm ft-member-select"
            style="min-width:220px;"
            data-placeholder="{{ __('Select root member...') }}">
            <option value=""></option>
            @foreach ($members as $m)
                <option value="{{ $m->id }}">{{ $m->full_name }}</option>
            @endforeach
        </select>
    </div>

    {{-- Path finder selectors --}}
    <div id="ctrl-path-members" class="d-none d-flex gap-2 flex-wrap">
        <select id="select-path-a"
                class="form-select form-select-sm ft-member-select"
                style="min-width:200px;"
                data-placeholder="{{ __('From member...') }}">
            <option value=""></option>
            @foreach ($members as $m)
                <option value="{{ $m->id }}">{{ $m->full_name }} - {{ $m->father?->full_name }}</option>
            @endforeach
        </select>
        <select id="select-path-b"
                class="form-select form-select-sm ft-member-select"
                style="min-width:200px;"
                data-placeholder="{{ __('To member...') }}">
            <option value=""></option>
            @foreach ($members as $m)
                <option value="{{ $m->id }}">{{ $m->full_name }}- {{ $m->father?->full_name }}</option>
            @endforeach
        </select>
        <button type="button" class="btn btn-primary btn-sm" id="btn-find-path">
            <i class="bi bi-search"></i> {{ __('Find') }}
        </button>
    </div>

    <div class="divider d-none d-md-block"></div>

    {{-- Depth --}}
    <div class="d-flex align-items-center gap-2" id="ctrl-depth">
        <label class="small text-muted mb-0">{{ __('Depth') }}</label>
        <select id="select-depth" class="form-select form-select-sm" style="width:70px;">
            <option value="3">3</option>
            <option value="4">4</option>
            <option value="5" selected>5</option>
            <option value="6">6</option>
            <option value="8">8</option>
        </select>
    </div>

    <div class="ms-auto d-flex gap-2">
        <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-fit">
            <i class="bi bi-fullscreen"></i>
        </button>
        <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-center">
            <i class="bi bi-arrows-move"></i>
        </button>
        <button type="button" class="btn btn-outline-secondary btn-sm d-lg-none" id="btn-toggle-minimap">
            <i class="bi bi-map"></i>
        </button>
    </div>
</div>

{{-- ── Tree container ────────────────────────────────────────────────────── --}}
<div id="ft-tree-container" style="height: calc(100vh - 340px); min-height: 480px;">

    <div id="ft-tree-loading">
        <div class="text-center">
            <div class="spinner-border mb-2" style="color:var(--ft-primary);"></div>
            <div class="small text-muted">{{ __('Building tree...') }}</div>
        </div>
    </div>

    <div id="ft-tree-empty" style="display:none;">
        <i class="bi bi-diagram-3" style="font-size:3rem;color:#d1d5db;"></i>
        <p class="mt-3 text-muted">{{ __('No members to display.') }}</p>
        <a href="{{ route('familytree.family.members.index', [$family, 'standalone' =>1]) }}" class="btn btn-primary btn-sm">
            {{ __('Add Members') }}
        </a>
    </div>

    <div id="ft-tree-inner"></div>

    {{-- Zoom controls --}}
    <div class="ft-zoom-controls">
        <button id="btn-zoom-in"  title="{{ __('Zoom In') }}"><i class="bi bi-plus"></i></button>
        <button id="btn-zoom-reset" title="{{ __('Reset') }}"><i class="bi bi-1-circle"></i></button>
        <button id="btn-zoom-out" title="{{ __('Zoom Out') }}"><i class="bi bi-dash"></i></button>
    </div>

    {{-- Mini-map --}}
    <canvas id="ft-minimap" width="140" height="90"></canvas>
</div>

{{-- ── Relationship Path Panel ────────────────────────────────────────────── --}}
<div id="ft-path-panel">
    <h6 class="mb-3"><i class="bi bi-diagram-2"></i> {{ __('Relationship Path') }}</h6>
    <div id="ft-path-steps"></div>
    <div id="ft-path-description" class="alert alert-info mt-3 small mb-0 d-none"></div>
    <div id="ft-path-no-connection" class="alert alert-warning mt-3 small mb-0 d-none">
        {{ __('These members are not connected in the family tree.') }}
    </div>
</div>

{{-- ── Hover Card ─────────────────────────────────────────────────────────── --}}
<div id="ft-hover-card">
    <div class="ft-hc-header">
        <div class="d-flex align-items-center gap-2">
            <img id="ft-hc-photo" src="" alt="" class="rounded-circle" style="width:36px;height:36px;object-fit:cover;border:2px solid rgba(255,255,255,.4);">
            <div>
                <div class="ft-hc-name" id="ft-hc-name"></div>
                <div class="ft-hc-sub"  id="ft-hc-sub"></div>
            </div>
        </div>
    </div>
    <div class="ft-hc-body">
        <div class="ft-hc-row"><span class="ft-hc-label">{{ __('Age') }}</span><span id="ft-hc-age">—</span></div>
        <div class="ft-hc-row"><span class="ft-hc-label">{{ __('DOB') }}</span><span id="ft-hc-dob">—</span></div>
        <div class="ft-hc-row"><span class="ft-hc-label">{{ __('Status') }}</span><span id="ft-hc-status">—</span></div>
        <div class="ft-hc-row"><span class="ft-hc-label">{{ __('Married') }}</span><span id="ft-hc-marital">—</span></div>
        <div class="ft-hc-row"><span class="ft-hc-label">{{ __('Occupation') }}</span><span id="ft-hc-occupation">—</span></div>
        <div class="ft-hc-row"><span class="ft-hc-label">{{ __('Contact') }}</span><span id="ft-hc-contact">—</span></div>
        <div id="ft-hc-event-row" class="ft-hc-row d-none">
            <span class="ft-hc-label">{{ __('Last Event') }}</span>
            <span id="ft-hc-event">—</span>
        </div>
    </div>
    <div class="ft-hc-footer">
        <a id="ft-hc-profile-link" href="#" class="btn btn-primary btn-sm flex-grow-1">
            <i class="bi bi-person-vcard"></i> {{ __('Profile') }}
        </a>
        <button type="button" class="btn btn-outline-secondary btn-sm" id="ft-hc-set-root"
                title="{{ __('View descendants from here') }}">
            <i class="bi bi-arrow-down-circle"></i>
        </button>
    </div>
</div>

@push('scripts')
<script src="{{ asset('vendor/treant/vendor/raphael.js') }}"></script>
<script src="{{ asset('vendor/treant/Treant.js') }}"></script>
<script src="{{ asset('js/familytree/tree.js') }}"></script>
<script>
// Store preselect before Select2 overrides the select value
window._ftPreselect  = {{ request('root')      ? (int)request('root')      : 'null' }};
window._ftHighlight  = {{ request('highlight') ? (int)request('highlight') : 'null' }};

window.FtTree.init({
    familyId     : {{ $family->id }},
    rootMembers  : @json($roots->pluck('id')),
    urlFull      : '{{ route("familytree.family.tree.data.full", $family) }}',
    urlDescendant: '{{ route("familytree.family.tree.data.descendant", [$family, '__ID__']) }}',
    urlAncestor  : '{{ route("familytree.family.tree.data.ancestor", [$family, '__ID__']) }}',
    urlCard      : '{{ route("familytree.family.tree.member.card", [$family, '__ID__']) }}',
    urlPath      : '{{ route("familytree.family.relationships.path", $family) }}',
    urlProfile   : '{{ route("familytree.family.members.show", [$family, '__ID__']) }}',
    csrfToken    : document.querySelector('meta[name="csrf-token"]').content,
    preselect    : null,        // handled below after Select2 inits
    highlight    : window._ftHighlight,
    labels: {
        loading   : '{{ __('Building tree...') }}',
        noMembers : '{{ __('No members to display.') }}',
        cardError : '{{ __('Could not load member details.') }}',
        children  : '{{ __('children') }}',
    }
});

// Initialize Select2 AFTER FtTree.init() — then safely set the preselect value
$(document).ready(function () {
    setTimeout(function () {

        // Init Select2 on all tree toolbar selects
        FtSelect2.init(document.querySelector('.ft-tree-toolbar'));

        if (window._ftPreselect) {
            // Set the root member dropdown to the linked member
            $('#select-root-member').val(window._ftPreselect).trigger('change');

            // Switch to descendant mode — shows member + their descendants
            // The highlight will visually mark which member was clicked
            window.FtTree.switchDescendant();
        } else {
            // No preselect — load full tree normally
            window.FtTree.switchFull();
        }

    }, 150);
});
</script>
@endpush

@endsection