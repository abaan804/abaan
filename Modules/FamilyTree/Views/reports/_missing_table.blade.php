<div data-count="{{ $rows->count() }}" class="d-none" id="r-count-data"></div>

@forelse ($rows as $row)
    <tr>
        <td data-label="{{ __('Name') }}" class="ft-cell-name">
            <a href="{{ route('familytree.family.members.show', [$family, $row['member']]) }}"
               class="text-decoration-none fw-semibold">{{ $row['member']->full_name }}</a>
        </td>
        <td data-label="{{ __('Gender') }}">
            <span class="ft-badge-{{ $row['member']->gender }}">{{ ucfirst($row['member']->gender) }}</span>
        </td>
        <td data-label="{{ __('Missing') }}">
            <div class="d-flex flex-wrap gap-1">
                @foreach (array_filter($row['missing']) as $field)
                    <span class="badge bg-warning text-dark">{{ $field }}</span>
                @endforeach
            </div>
        </td>
        <td class="ft-cell-actions">
            <a href="{{ route('familytree.family.members.index', $family) }}?edit={{ $row['member']->id }}"
               class="btn btn-sm btn-outline-primary">
                <i class="bi bi-pencil"></i> {{ __('Edit') }}
            </a>
        </td>
    </tr>
@empty
    <tr class="ft-row-empty">
        <td colspan="4">
            @include('familytree::partials.empty-state', [
                'icon' => 'bi-check-circle',
                'title' => __('All records are complete!'),
                'description' => __('No members with missing information.'),
            ])
        </td>
    </tr>
@endforelse