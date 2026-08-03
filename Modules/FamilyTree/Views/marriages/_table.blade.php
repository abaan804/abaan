@forelse ($marriages as $marriage)
    <tr>
        <td data-label="{{ __('Husband') }}" class="ft-cell-name">
            @if ($marriage->husband)
                <a href="{{ route('familytree.family.members.show', [$family, $marriage->husband]) }}"
                   class="text-decoration-none fw-semibold" style="color:var(--ft-primary);">
                    {{ $marriage->husband->full_name }}
                </a>
            @else
                <span class="text-muted">—</span>
            @endif
        </td>
        <td data-label="{{ __('Wife') }}" class="ft-cell-name">
            @if ($marriage->wife)
                <a href="{{ route('familytree.family.members.show', [$family, $marriage->wife]) }}"
                   class="text-decoration-none fw-semibold" style="color:var(--ft-female);">
                    {{ $marriage->wife->full_name }}
                </a>
            @else
                <span class="text-muted">—</span>
            @endif
        </td>
        <td data-label="{{ __('Date') }}">
            {{ $marriage->marriage_date ? formatDate($marriage->marriage_date) : '—' }}
        </td>
        <td data-label="{{ __('Type') }}">
            <span class="badge bg-light text-dark border">{{ ucfirst($marriage->marriage_type) }}</span>
        </td>
        <td data-label="{{ __('Status') }}">
            <span class="badge bg-{{ $marriage->status === 'active' ? 'success' : ($marriage->status === 'divorced' ? 'danger' : 'secondary') }}">
                {{ ucfirst($marriage->status) }}
            </span>
            @if ($marriage->divorce_date)
                <div class="text-muted small">{{ formatDate($marriage->divorce_date) }}</div>
            @endif
        </td>
        <td class="ft-cell-actions">
            @can('familytree.manage-relationships')
                <button type="button" class="btn btn-sm btn-outline-secondary btn-edit-marriage"
                        data-id="{{ $marriage->id }}">
                    <i class="bi bi-pencil"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger btn-delete-marriage"
                        data-id="{{ $marriage->id }}">
                    <i class="bi bi-trash"></i>
                </button>
            @endcan
        </td>
    </tr>
@empty
    <tr class="ft-row-empty">
        <td colspan="6">
            @include('familytree::partials.empty-state', [
                'icon'        => 'bi-heart',
                'title'       => __('No marriage records yet'),
                'description' => __('Record a marriage to link spouses in the family tree.'),
            ])
        </td>
    </tr>
@endforelse