@forelse ($documents as $doc)
    <tr>
        <td data-label="{{ __('Member') }}" class="ft-cell-name">
            @if ($doc->member)
                <a href="{{ route('familytree.family.members.show', [$family, $doc->member]) }}"
                   class="text-decoration-none fw-semibold">{{ $doc->member->full_name }}</a>
            @else
                <span class="text-muted">—</span>
            @endif
        </td>
        <td data-label="{{ __('Title') }}">{{ $doc->title }}</td>
        <td data-label="{{ __('Type') }}">
            <span class="badge bg-light text-dark border small">{{ $doc->type_display }}</span>
        </td>
        <td data-label="{{ __('Size') }}">{{ $doc->formatted_size }}</td>
        <td data-label="{{ __('Uploaded') }}">{{ formatDate($doc->created_at) }}</td>
        <td class="ft-cell-actions">
            @if ($doc->is_previewable)
                <a href="{{ $doc->url }}" target="_blank"
                   class="btn btn-sm btn-outline-secondary" title="{{ __('Preview') }}">
                    <i class="bi bi-eye"></i>
                </a>
            @endif
            <a href="{{ route('familytree.family.documents.download', [$family, $doc]) }}"
               class="btn btn-sm btn-outline-primary" title="{{ __('Download') }}">
                <i class="bi bi-download"></i>
            </a>
            @can('familytree.manage-documents')
                <button type="button" class="btn btn-sm btn-outline-danger btn-delete-doc"
                        data-id="{{ $doc->id }}">
                    <i class="bi bi-trash"></i>
                </button>
            @endcan
        </td>
    </tr>
@empty
    <tr class="ft-row-empty">
        <td colspan="6">
            @include('familytree::partials.empty-state', [
                'icon'        => 'bi-folder2-open',
                'title'       => __('No documents uploaded'),
                'description' => __('Upload CNIC, birth certificates, passports, or other important documents.'),
            ])
        </td>
    </tr>
@endforelse

@if ($documents->hasPages())
    <tr class="ft-row-empty">
        <td colspan="6">
            <div id="documents-pagination" class="d-flex justify-content-center py-2">
                {{ $documents->links() }}
            </div>
        </td>
    </tr>
@endif