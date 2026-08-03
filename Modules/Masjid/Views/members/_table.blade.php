@forelse ($members as $member)
    <tr>
        <td data-label="{{ __('Name') }}" class="mj-cell-name">
            <div class="d-flex align-items-center gap-2">
                @if ($member->photo)
                    <img src="{{ asset('storage/' . $member->photo) }}" class="rounded-circle" style="width:30px;height:30px;object-fit:cover;">
                @else
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:30px;height:30px;background:rgba(27,107,69,.1);">
                        <i class="bi bi-person" style="color:var(--mj-primary);font-size:.9rem;"></i>
                    </div>
                @endif
                <a href="{{ route('masjid.mosque.members.statement', [$mosque, $member]) }}" class="text-decoration-none fw-semibold">{{ $member->name }}</a>
            </div>
            @if ($member->father_name)
                <div class="text-muted small">{{ __('S/O') }} {{ $member->father_name }}</div>
            @endif
        </td>
        <td data-label="{{ __('Mobile') }}">{{ $member->mobile }}</td>
        <td data-label="{{ __('Joining') }}">{{ formatDate($member->joining_date) }}</td>
        <td data-label="{{ __('Status') }}">
            <span class="badge bg-{{ $member->status === 'active' ? 'success' : 'secondary' }}">
                {{ ucfirst($member->status) }}
            </span>
        </td>
        <td class="mj-cell-actions">
            <a href="{{ route('masjid.mosque.members.statement', [$mosque, $member,'standalone' => 1]) }}" class="btn btn-sm btn-outline-primary" title="{{ __('Statement') }}">
                <i class="bi bi-journal-text"></i>
            </a>
            <button type="button" class="btn btn-sm btn-outline-secondary btn-edit-member" data-id="{{ $member->id }}" title="{{ __('Edit') }}">
                <i class="bi bi-pencil"></i>
            </button>
            <button type="button" class="btn btn-sm btn-outline-danger btn-delete-member" data-id="{{ $member->id }}" data-name="{{ $member->name }}" title="{{ __('Delete') }}">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    </tr>
@empty
    <tr class="mj-row-empty">
        <td colspan="5">
            @include('masjid::partials.empty-state', [
                'icon' => 'bi-people',
                'title' => __('No members found'),
                'description' => __('Add your first mosque member to get started.'),
            ])
        </td>
    </tr>
@endforelse

@if ($members->hasPages())
    <tr class="mj-row-empty">
        <td colspan="5">
            <div id="members-pagination" class="d-flex justify-content-center py-2">
                {{ $members->links('pagination::bootstrap-5') }}
            </div>
        </td>
    </tr>
@endif