@forelse ($seasons as $season)
    <tr>
        <td data-label="{{ __('Season') }}" class="mj-cell-name">
            <div class="fw-semibold">{{ $season->name }}</div>
            <div class="text-muted small">
                <i class="bi bi-people"></i> {{ $season->seasonMembers()->count() }} {{ __('members') }}
                <span class="ms-2 badge bg-light text-dark border">{{ ucfirst($season->frequency) }}</span>
            </div>
        </td>
        <td data-label="{{ __('Period') }}">
            {{ formatDate($season->start_date) }} — {{ formatDate($season->end_date) }}
        </td>
        <td data-label="{{ __('Amount') }}">{{ formatCurrency($season->contribution_amount) }}</td>
        <td data-label="{{ __('Status') }}">
            <span class="badge bg-{{ $season->status === 'active' ? 'success' : ($season->status === 'completed' ? 'secondary' : 'warning') }}">
                {{ ucfirst($season->status) }}
            </span>
        </td>
        <td class="mj-cell-actions">
            <a href="{{ route('masjid.mosque.seasons.members', [$mosque, $season,'standalone' => 1] ) }}" class="btn btn-sm btn-outline-primary" title="{{ __('Manage Members') }}">
                <i class="bi bi-people"></i>
            </a>
            <a href="{{ route('masjid.mosque.reports.season', [$mosque, $season,'standalone' => 1]) }}" class="btn btn-sm btn-outline-secondary" title="{{ __('Report') }}">
                <i class="bi bi-bar-chart"></i>
            </a>
            <a href="{{ route('masjid.mosque.seasons.blank-list', [$mosque, $season]) }}"
            class="btn btn-sm btn-outline-success"
            title="{{ __('Download Blank Collection List') }}" target="_blank">
                <i class="bi bi-file-earmark-ruled"></i>
            </a>
            <button type="button" class="btn btn-sm btn-outline-secondary btn-edit-season" data-id="{{ $season->id }}" title="{{ __('Edit') }}">
                <i class="bi bi-pencil"></i>
            </button>
            <button type="button" class="btn btn-sm btn-outline-danger btn-delete-season" data-id="{{ $season->id }}" data-name="{{ $season->name }}" title="{{ __('Delete') }}">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    </tr>
@empty
    <tr class="mj-row-empty">
        <td colspan="5">
            @include('masjid::partials.empty-state', [
                'icon' => 'bi-calendar-x',
                'title' => __('No seasons yet'),
                'description' => __('Create a contribution season and assign members to it.'),
            ])
        </td>
    </tr>
@endforelse

@if ($seasons->hasPages())
    <tr class="mj-row-empty">
        <td colspan="5">
            <div id="seasons-pagination" class="d-flex justify-content-center py-2">{{ $seasons->links('pagination::bootstrap-5') }}</div>
        </td>
    </tr>
@endif