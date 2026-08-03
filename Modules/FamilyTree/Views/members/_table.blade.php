@forelse ($members as $member)
    <tr>
        <td data-label="{{ __('Name') }}" class="ft-cell-name">
            <div class="d-flex align-items-center gap-2">
                @if ($member->profile_photo)
                    <img src="{{ asset('storage/' . $member->profile_photo) }}"
                         class="ft-avatar ft-avatar-{{ $member->gender }}"
                         style="width:34px;height:34px;">
                @else
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width:34px;height:34px;background:{{ $member->gender === 'female' ? 'rgba(142,68,173,.1)' : 'rgba(26,82,118,.1)' }};">
                        <i class="bi bi-person" style="color:{{ $member->gender === 'female' ? 'var(--ft-female)' : 'var(--ft-primary)' }};"></i>
                    </div>
                @endif
                <div>
                    <a href="{{ route('familytree.family.members.show', [$family, $member,'standalone'=>1]) }}"
                       class="text-decoration-none fw-semibold">
                        {{ $member->full_name }}
                        @if ($member->life_status === 'deceased')
                            <span class="text-muted"></span>
                        @endif
                    </a>
                    @if ($member->occupation)
                        <div class="text-muted small">{{ $member->occupation }}</div>
                    @endif
                </div>
            </div>
        </td>
        <td data-label="{{ __('Father') }}">{{ $member->father_display_name }}</td>
        <td data-label="{{ __('Gender') }}">
            <span class="ft-badge-{{ $member->gender }}">{{ ucfirst($member->gender) }}</span>
        </td>
        <td data-label="{{ __('DOB / Age') }}">
            @if ($member->date_of_birth)
                <div>{{ formatDate($member->date_of_birth) }}</div>
                <div class="text-muted small">{{ $member->age }} {{ __('yrs') }}</div>
            @else
                <span class="text-muted">—</span>
            @endif
        </td>
        <td data-label="{{ __('Status') }}">
            <span class="ft-badge-{{ $member->life_status }}">{{ ucfirst($member->life_status) }}</span>
            <span class="d-block small text-muted mt-1">{{ ucfirst($member->marital_status) }}</span>
        </td>
        <td class="ft-cell-actions">
            <a href="{{ route('familytree.family.members.show', [$family, $member,'standalone'=>1]) }}"
               class="btn btn-sm btn-outline-primary" title="{{ __('Profile') }}">
                <i class="bi bi-person-vcard"></i>
            </a>
            <a href="{{ route('familytree.family.tree.index', $family) }}?root={{ $member->id }}&highlight={{ $member->id }}"
            class="btn btn-sm btn-outline-secondary" title="{{ __('View in Tree') }}">
                <i class="bi bi-diagram-3"></i>
            </a>
            @can('familytree.manage-members')
                <button type="button" class="btn btn-sm btn-outline-secondary btn-edit-member"
                        data-id="{{ $member->id }}" title="{{ __('Edit') }}">
                    <i class="bi bi-pencil"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger btn-delete-member"
                        data-id="{{ $member->id }}" data-name="{{ $member->full_name }}"
                        title="{{ __('Delete') }}">
                    <i class="bi bi-trash"></i>
                </button>
            @endcan
        </td>
    </tr>
@empty
    <tr class="ft-row-empty">
        <td colspan="6">
            @include('familytree::partials.empty-state', [
                'icon'        => 'bi-people',
                'title'       => __('No members found'),
                'description' => __('Add your first family member to begin building the tree.'),
            ])
        </td>
    </tr>
@endforelse

@if ($members->hasPages())
    <tr class="ft-row-empty">
        <td colspan="6">
            <div id="members-pagination" class="d-flex justify-content-center py-2">
                {{ $members->links('pagination::bootstrap-5') }}
            </div>
        </td>
    </tr>
@endif