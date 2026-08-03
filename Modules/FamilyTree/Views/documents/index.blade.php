@extends($familyTreeLayout ?? 'familytree::layouts.app')
@section('heading', __('Documents') . ' — ' . $family->name)
@section('ft-content')

<div class="card shadow-sm border-0 mb-3" style="border-radius:14px;">
    <div class="card-body">
        <div class="row g-2">
            <div class="col-md-4">
                <select name="member_id" id="doc-member"
                    class="form-select ft-member-select"
                    data-placeholder="{{ __('— Select Member —') }}" required>
                <option value=""></option>
                @foreach ($members as $m)
                    <option value="{{ $m->id }}">{{ $m->full_name }}</option>
                @endforeach
            </select>
            </div>
            <div class="col-md-4">
                <select id="doc-type-filter" class="form-select">
                    <option value="">{{ __('All Types') }}</option>
                    @foreach (\Modules\FamilyTree\Models\FtDocument::TYPE_LABELS as $key => $label)
                        <option value="{{ $key }}">{{ __($label) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                @can('familytree.manage-documents')
                    <button type="button" class="btn btn-primary w-100" id="btn-upload-doc">
                        <i class="bi bi-upload"></i> {{ __('Upload Document') }}
                    </button>
                @endcan
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0" style="border-radius:14px;">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle ft-table">
            <thead>
                <tr>
                    <th>{{ __('Member') }}</th>
                    <th>{{ __('Title') }}</th>
                    <th>{{ __('Type') }}</th>
                    <th>{{ __('Size') }}</th>
                    <th>{{ __('Uploaded') }}</th>
                    <th class="text-end">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody id="documents-table-body">
                <tr><td colspan="6" class="text-center py-5">
                    <div class="spinner-border spinner-border-sm" style="color:var(--ft-primary);"></div>
                </td></tr>
            </tbody>
        </table>
    </div>
</div>

@can('familytree.manage-documents')
<div class="modal fade" id="documentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="document-form" enctype="multipart/form-data" novalidate>
                <div class="modal-header" style="background:var(--ft-primary);color:#fff;">
                    <h5 class="modal-title">{{ __('Upload Document') }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="document-form-errors" class="alert alert-danger d-none"></div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Member') }} <span class="text-danger">*</span></label>
                        <select id="doc-member-filter"
                            class="form-select ft-member-select"
                            data-placeholder="{{ __('All Members') }}" name="member_id  ">
                        <option value=""></option>
                        @foreach ($members as $m)
                            <option value="{{ $m->id }}">{{ $m->full_name }}</option>
                        @endforeach
                    </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Document Type') }} <span class="text-danger">*</span></label>
                        <select name="document_type" id="doc-type" class="form-select" required>
                            @foreach (\Modules\FamilyTree\Models\FtDocument::TYPE_LABELS as $key => $label)
                                <option value="{{ $key }}">{{ __($label) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Title') }} <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="doc-title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('File') }} <span class="text-danger">*</span></label>
                        <input type="file" name="file" id="doc-file" class="form-control"
                               accept="image/*,.pdf,.doc,.docx,.xlsx" required>
                        <div class="form-text">{{ __('Max 10MB. Images, PDF, Word, Excel accepted.') }}</div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">{{ __('Notes') }}</label>
                        <textarea name="notes" id="doc-notes" rows="2" class="form-control"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary" id="document-save-btn">
                        <span class="spinner-border spinner-border-sm d-none" id="document-spinner"></span>
                        {{ __('Upload') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

@push('scripts')
<script>
(function () {
    const csrfToken    = document.querySelector('meta[name="csrf-token"]')?.content ?? '{{ csrf_token() }}';
    const tableUrl     = '{{ route("familytree.family.documents.table", $family) }}';
    const storeUrl     = '{{ route("familytree.family.documents.store", $family) }}';
    const tbody        = document.getElementById('documents-table-body');
    const memberFilter = document.getElementById('doc-member-filter');
    const typeFilter   = document.getElementById('doc-type-filter');

    const docModal = document.getElementById('documentModal')
        ? new bootstrap.Modal(document.getElementById('documentModal'))
        : null;
    FtSelect2.onModal(document.getElementById('documentModal'));
    $(document).ready(function () {
        FtSelect2.init(document.querySelector('.card.shadow-sm.border-0.mb-3'));
    });
    let currentPage = 1;

    function loadTable(page = 1) {
        currentPage = page;
        tbody.innerHTML = `<tr><td colspan="6" class="text-center py-5">
            <div class="spinner-border spinner-border-sm" style="color:var(--ft-primary);"></div></td></tr>`;
        const p = new URLSearchParams();
        if (memberFilter.value) p.set('member_id', memberFilter.value);
        if (typeFilter.value)   p.set('document_type', typeFilter.value);
        p.set('page', page);
        fetch(`${tableUrl}?${p.toString()}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.text()).then(html => { tbody.innerHTML = html; });
    }

    tbody.addEventListener('click', e => {
        const link = e.target.closest('#documents-pagination a');
        if (link) { e.preventDefault(); loadTable(new URL(link.href).searchParams.get('page') || 1); }

        const deleteBtn = e.target.closest('.btn-delete-doc');
        if (deleteBtn) {
            if (!confirm('{{ __('Delete this document?') }}')) return;
            fetch(`/app/family-tree/{{ $family->id }}/documents/${deleteBtn.dataset.id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            .then(r => r.json().then(b => ({ ok: r.ok, b })))
            .then(({ ok, b }) => {
                if (ok && b.success) { FtToast.success(b.message); loadTable(currentPage); }
                else FtToast.error(b.message ?? '{{ __('Delete failed.') }}');
            });
        }
    });

    [memberFilter, typeFilter].forEach(el => el.addEventListener('change', () => loadTable(1)));

    document.getElementById('btn-upload-doc')?.addEventListener('click', () => {
        document.getElementById('document-form').reset();
        document.getElementById('document-form-errors').classList.add('d-none');
        docModal?.show();
    });

    document.getElementById('document-form')?.addEventListener('submit', function (e) {
        e.preventDefault();
        const btn = document.getElementById('document-save-btn');
        const sp  = document.getElementById('document-spinner');
        btn.disabled = true; sp.classList.remove('d-none');

        fetch(storeUrl, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            body: new FormData(this),
        })
        .then(r => r.json().then(b => ({ status: r.status, b })))
        .then(({ status, b }) => {
            btn.disabled = false; sp.classList.add('d-none');
            if (status === 422) {
                const eb = document.getElementById('document-form-errors');
                eb.textContent = Object.values(b.errors ?? {}).flat().join(' ');
                eb.classList.remove('d-none');
                return;
            }
            if (b.success) { docModal?.hide(); FtToast.success(b.message); loadTable(currentPage); }
        })
        .catch(() => { btn.disabled = false; sp.classList.add('d-none'); });
    });

    loadTable(1);
})();
</script>
@endpush
@endsection