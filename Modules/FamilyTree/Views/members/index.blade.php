@extends('familytree::layouts.standalone')
@section('heading', __('Members') . ' — ' . $family->name)
@section('ft-content')

{{-- Filters --}}
<div class="card shadow-sm border-0 mb-3" style="border-radius:14px;">
    <div class="card-body">
        <div class="row g-2">
            <div class="col-12 col-md-4">
                <input type="text" id="member-search" class="form-control"
                       placeholder="{{ __('Search name, CNIC, mobile, occupation...') }}">
            </div>
            <div class="col-6 col-md-2">
                <select id="member-gender" class="form-select">
                    <option value="">{{ __('All Genders') }}</option>
                    <option value="male">{{ __('Male') }}</option>
                    <option value="female">{{ __('Female') }}</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <select id="member-life-status" class="form-select">
                    <option value="">{{ __('All') }}</option>
                    <option value="living">{{ __('Living') }}</option>
                    <option value="deceased">{{ __('Deceased') }}</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <select id="member-marital-status" class="form-select">
                    <option value="">{{ __('All Marital') }}</option>
                    <option value="married">{{ __('Married') }}</option>
                    <option value="unmarried">{{ __('Unmarried') }}</option>
                    <option value="divorced">{{ __('Divorced') }}</option>
                    <option value="widowed">{{ __('Widowed') }}</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                @can('familytree.manage-members')
                    <button type="button" class="btn btn-primary w-100" id="btn-add-member">
                        <i class="bi bi-person-plus"></i> {{ __('Add') }}
                    </button>
                @endcan
            </div>
        </div>
    </div>
</div>

{{-- Table --}}
<div class="card shadow-sm border-0" style="border-radius:14px;">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle ft-table">
            <thead>
                <tr>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Father') }}</th>
                    <th>{{ __('Gender') }}</th>
                    <th>{{ __('DOB / Age') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th class="text-end">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody id="members-table-body">
                <tr><td colspan="6" class="text-center py-5">
                    <div class="spinner-border spinner-border-sm" style="color:var(--ft-primary);"></div>
                </td></tr>
            </tbody>
        </table>
    </div>
</div>

{{-- Add/Edit Member Modal --}}
@can('familytree.manage-members')
<div class="modal fade" id="memberModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form id="member-form" enctype="multipart/form-data" novalidate>
                <input type="hidden" id="member-id">
                <div class="modal-header" style="background:var(--ft-primary);color:#fff;">
                    <h5 class="modal-title" id="memberModalTitle">{{ __('Add Member') }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="member-form-errors" class="alert alert-danger d-none"></div>

                    {{-- Tabs --}}
                    <ul class="nav nav-tabs mb-4" id="memberTabs">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-personal">
                                <i class="bi bi-person"></i> {{ __('Personal') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-family">
                                <i class="bi bi-diagram-2"></i> {{ __('Family Links') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-contact">
                                <i class="bi bi-telephone"></i> {{ __('Contact & Bio') }}
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content">
                        {{-- Tab 1: Personal --}}
                        <div class="tab-pane fade show active" id="tab-personal">
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label class="form-label">{{ __('Full Name') }} <span class="text-danger">*</span></label>
                                            <input type="text" name="full_name" id="m-full-name" class="form-control" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">{{ __('Gender') }} <span class="text-danger">*</span></label>
                                            <select name="gender" id="m-gender" class="form-select" required>
                                                <option value="male">{{ __('Male') }}</option>
                                                <option value="female">{{ __('Female') }}</option>
                                                <option value="other">{{ __('Other') }}</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">{{ __('Life Status') }}</label>
                                            <select name="life_status" id="m-life-status" class="form-select">
                                                <option value="living">{{ __('Living') }}</option>
                                                <option value="deceased">{{ __('Deceased') }}</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">{{ __('Date of Birth') }}</label>
                                            <input type="date" name="date_of_birth" id="m-dob" class="form-control">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">{{ __('Place of Birth') }}</label>
                                            <input type="text" name="place_of_birth" id="m-place-birth" class="form-control">
                                        </div>
                                        <div id="deceased-fields" class="col-12 d-none">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label">{{ __('Date of Death') }}</label>
                                                    <input type="date" name="date_of_death" id="m-dod" class="form-control">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">{{ __('Burial Place') }}</label>
                                                    <input type="text" name="burial_place" id="m-burial" class="form-control">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">{{ __('Marital Status') }}</label>
                                            <select name="marital_status" id="m-marital" class="form-select">
                                                <option value="unmarried">{{ __('Unmarried') }}</option>
                                                <option value="married">{{ __('Married') }}</option>
                                                <option value="divorced">{{ __('Divorced') }}</option>
                                                <option value="widowed">{{ __('Widowed') }}</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">{{ __('Blood Group') }}</label>
                                            <select name="blood_group" id="m-blood-group" class="form-select">
                                                <option value="">{{ __('Unknown') }}</option>
                                                @foreach(['A+','A-','B+','B-','O+','O-','AB+','AB-'] as $bg)
                                                    <option value="{{ $bg }}">{{ $bg }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">{{ __('CNIC') }}</label>
                                            <input type="text" name="cnic" id="m-cnic" class="form-control"
                                                   placeholder="00000-0000000-0">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">{{ __('Passport No.') }}</label>
                                            <input type="text" name="passport_number" id="m-passport" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">{{ __('Profile Photo') }}</label>
                                    <div class="text-center mb-3">
                                        <div id="m-photo-preview" class="rounded-circle mx-auto mb-2"
                                             style="width:100px;height:100px;overflow:hidden;background:#f0f0f0;display:flex;align-items:center;justify-content:center;">
                                            <i class="bi bi-person fs-1 text-muted"></i>
                                        </div>
                                    </div>
                                    <input type="file" name="profile_photo" id="m-photo"
                                           class="form-control" accept="image/*">
                                </div>
                            </div>
                        </div>

                        {{-- Tab 2: Family Links --}}
                        <div class="tab-pane fade" id="tab-family">
                            <div class="alert alert-info small">
                                <i class="bi bi-info-circle"></i>
                                {{ __('Link to existing members in this family. If the parent is not yet in the system, enter their name as text — you can link them later.') }}
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Father (linked member)') }}</label>
                                    <select name="father_id" id="m-father-id"
                                            class="form-select ft-member-select"
                                            data-placeholder="{{ __('— Not linked yet —') }}">
                                        <option value=""></option>
                                        @foreach (\Modules\FamilyTree\Models\FtMember::where('family_id', $family->id)->where('gender', 'male')->orderBy('full_name')->get() as $m)
                                            <option value="{{ $m->id }}">{{ $m->full_name }} - {{ $m->father?->full_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Father Name (text fallback)') }}</label>
                                    <input type="text" name="father_name_text" id="m-father-text"
                                           class="form-control" placeholder="{{ __('If not in system yet') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Mother (linked member)') }}</label>
                                    <select name="mother_id" id="m-mother-id"
                                            class="form-select ft-member-select"
                                            data-placeholder="{{ __('— Not linked yet —') }}">
                                        <option value=""></option>
                                        @foreach (\Modules\FamilyTree\Models\FtMember::where('family_id', $family->id)->where('gender', 'female')->orderBy('full_name')->get() as $m)
                                            <option value="{{ $m->id }}">{{ $m->full_name }} - {{ $m->father?->full_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Mother Name (text fallback)') }}</label>
                                    <input type="text" name="mother_name_text" id="m-mother-text"
                                           class="form-control" placeholder="{{ __('If not in system yet') }}">
                                </div>
                            </div>
                        </div>

                        {{-- Tab 3: Contact & Bio --}}
                        <div class="tab-pane fade" id="tab-contact">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Contact Number') }}</label>
                                    <input type="text" name="contact_number" id="m-contact" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('WhatsApp Number') }}</label>
                                    <input type="text" name="whatsapp_number" id="m-whatsapp" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Email') }}</label>
                                    <input type="email" name="email" id="m-email" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Occupation') }}</label>
                                    <input type="text" name="occupation" id="m-occupation" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Education') }}</label>
                                    <input type="text" name="education" id="m-education" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Religion') }}</label>
                                    <input type="text" name="religion" id="m-religion" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Nationality') }}</label>
                                    <input type="text" name="nationality" id="m-nationality" class="form-control">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">{{ __('Current Address') }}</label>
                                    <textarea name="current_address" id="m-current-address" rows="2" class="form-control"></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">{{ __('Permanent Address') }}</label>
                                    <textarea name="permanent_address" id="m-perm-address" rows="2" class="form-control"></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">{{ __('Other Details') }}</label>
                                    <textarea name="other_details" id="m-other" rows="2" class="form-control"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>{{-- /tab-content --}}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary" id="member-save-btn">
                        <span class="spinner-border spinner-border-sm d-none" id="member-spinner"></span>
                        {{ __('Save Member') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Delete Modal --}}
<div class="modal fade" id="deleteMemberModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">{{ __('Delete Member') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>{{ __('Delete') }} <strong id="delete-member-name"></strong>?</p>
                <p class="small text-danger">{{ __('Members who are linked as parents cannot be deleted. Unlink their children first.') }}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-danger" id="confirm-delete-member-btn">{{ __('Delete') }}</button>
            </div>
        </div>
    </div>
</div>
@endcan

@push('scripts')
<script>
(function () {
    const csrfToken   = document.querySelector('meta[name="csrf-token"]')?.content ?? '{{ csrf_token() }}';
    const tableUrl    = '{{ route("familytree.family.members.table", $family) }}';
    const storeUrl    = '{{ route("familytree.family.members.store", $family) }}';
    const tbody       = document.getElementById('members-table-body');
    const searchInput = document.getElementById('member-search');

    const memberModal = new bootstrap.Modal(document.getElementById('memberModal'));
    FtSelect2.onModal(document.getElementById('memberModal'));
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteMemberModal'));
    let deleteId = null, searchDebounce = null, currentPage = 1;

    // ── Filters ────────────────────────────────────────────────────────────────
    function buildUrl(page = 1) {
        const p = new URLSearchParams();
        if (searchInput.value) p.set('search', searchInput.value);
        const g = document.getElementById('member-gender').value;
        const l = document.getElementById('member-life-status').value;
        const m = document.getElementById('member-marital-status').value;
        if (g) p.set('gender', g);
        if (l) p.set('life_status', l);
        if (m) p.set('marital_status', m);
        p.set('page', page);
        return `${tableUrl}?${p.toString()}`;
    }

    function loadTable(page = 1) {
        currentPage = page;
        tbody.innerHTML = `<tr><td colspan="6" class="text-center py-5">
            <div class="spinner-border spinner-border-sm" style="color:var(--ft-primary);"></div></td></tr>`;
        fetch(buildUrl(page), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.text()).then(html => { tbody.innerHTML = html; });
    }

    tbody.addEventListener('click', e => {
        const link = e.target.closest('#members-pagination a');
        if (link) { e.preventDefault(); loadTable(new URL(link.href).searchParams.get('page') || 1); }
    });

    searchInput.addEventListener('input', () => {
        clearTimeout(searchDebounce);
        searchDebounce = setTimeout(() => loadTable(1), 400);
    });
    ['member-gender','member-life-status','member-marital-status'].forEach(id =>
        document.getElementById(id).addEventListener('change', () => loadTable(1))
    );

    // ── Life status toggle for deceased fields ─────────────────────────────────
    document.getElementById('m-life-status')?.addEventListener('change', function () {
        document.getElementById('deceased-fields').classList.toggle('d-none', this.value !== 'deceased');
    });

    // ── Photo preview ──────────────────────────────────────────────────────────
    document.getElementById('m-photo')?.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = e => {
            const preview = document.getElementById('m-photo-preview');
            preview.innerHTML = `<img src="${e.target.result}" style="width:100%;height:100%;object-fit:cover;">`;
        };
        reader.readAsDataURL(file);
    });

    // ── Add button ─────────────────────────────────────────────────────────────
    document.getElementById('btn-add-member')?.addEventListener('click', () => {
        document.getElementById('member-form').reset();
        document.getElementById('member-id').value = '';
        document.getElementById('memberModalTitle').textContent = '{{ __('Add Member') }}';
        document.getElementById('member-form-errors').classList.add('d-none');
        document.getElementById('m-photo-preview').innerHTML =
            `<i class="bi bi-person fs-1 text-muted"></i>`;
        document.getElementById('deceased-fields').classList.add('d-none');
        // Activate first tab
        bootstrap.Tab.getOrCreateInstance(document.querySelector('#memberTabs .nav-link')).show();
        memberModal.show();
    });

    // ── Edit ───────────────────────────────────────────────────────────────────
    tbody.addEventListener('click', e => {
        const editBtn = e.target.closest('.btn-edit-member');
        if (editBtn) {
            fetch(`/app/family-tree/{{ $family->id }}/members/${editBtn.dataset.id}/json`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(({ data: m }) => {
                document.getElementById('member-form').reset();
                document.getElementById('member-id').value           = m.id;
                document.getElementById('m-full-name').value         = m.full_name ?? '';
                document.getElementById('m-gender').value            = m.gender ?? 'male';
                document.getElementById('m-life-status').value       = m.life_status ?? 'living';
                document.getElementById('m-dob').value               = m.date_of_birth ?? '';
                document.getElementById('m-place-birth').value       = m.place_of_birth ?? '';
                document.getElementById('m-dod').value               = m.date_of_death ?? '';
                document.getElementById('m-burial').value            = m.burial_place ?? '';
                document.getElementById('m-marital').value           = m.marital_status ?? 'unmarried';
                document.getElementById('m-blood-group').value       = m.blood_group ?? '';
                document.getElementById('m-cnic').value              = m.cnic ?? '';
                document.getElementById('m-passport').value          = m.passport_number ?? '';
                $('#m-father-id').val(m.father_id ?? '').trigger('change');
                document.getElementById('m-father-text').value       = m.father_name_text ?? '';
                $('#m-mother-id').val(m.mother_id ?? '').trigger('change');
                document.getElementById('m-mother-text').value       = m.mother_name_text ?? '';
                document.getElementById('m-contact').value           = m.contact_number ?? '';
                document.getElementById('m-whatsapp').value          = m.whatsapp_number ?? '';
                document.getElementById('m-email').value             = m.email ?? '';
                document.getElementById('m-occupation').value        = m.occupation ?? '';
                document.getElementById('m-education').value         = m.education ?? '';
                document.getElementById('m-religion').value          = m.religion ?? '';
                document.getElementById('m-nationality').value       = m.nationality ?? '';
                document.getElementById('m-current-address').value   = m.current_address ?? '';
                document.getElementById('m-perm-address').value      = m.permanent_address ?? '';
                document.getElementById('m-other').value             = m.other_details ?? '';
                document.getElementById('deceased-fields').classList.toggle('d-none', m.life_status !== 'deceased');

                if (m.profile_photo) {
                    document.getElementById('m-photo-preview').innerHTML =
                        `<img src="/storage/${m.profile_photo}" style="width:100%;height:100%;object-fit:cover;">`;
                } else {
                    document.getElementById('m-photo-preview').innerHTML =
                        `<i class="bi bi-person fs-1 text-muted"></i>`;
                }

                document.getElementById('memberModalTitle').textContent = '{{ __('Edit Member') }}';
                document.getElementById('member-form-errors').classList.add('d-none');
                bootstrap.Tab.getOrCreateInstance(document.querySelector('#memberTabs .nav-link')).show();
                memberModal.show();
            });
        }

        const deleteBtn = e.target.closest('.btn-delete-member');
        if (deleteBtn) {
            deleteId = deleteBtn.dataset.id;
            document.getElementById('delete-member-name').textContent = deleteBtn.dataset.name;
            deleteModal.show();
        }
    });

    // ── Delete ─────────────────────────────────────────────────────────────────
    document.getElementById('confirm-delete-member-btn')?.addEventListener('click', () => {
        if (!deleteId) return;
        fetch(`/app/family-tree/{{ $family->id }}/members/${deleteId}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.json().then(b => ({ ok: r.ok, b })))
        .then(({ ok, b }) => {
            deleteModal.hide();
            if (ok && b.success) { FtToast.success(b.message); loadTable(currentPage); }
            else FtToast.error(b.message ?? '{{ __('Delete failed.') }}');
        });
    });

    // ── Save ───────────────────────────────────────────────────────────────────
    document.getElementById('member-form')?.addEventListener('submit', function (e) {
        e.preventDefault();
        const id  = document.getElementById('member-id').value;
        const url = id
            ? `/app/family-tree/{{ $family->id }}/members/${id}`
            : storeUrl;
         
        const fd = new FormData(this);
        if (id) fd.append('_method', 'PUT');

        const btn = document.getElementById('member-save-btn');
        const sp  = document.getElementById('member-spinner');
        btn.disabled = true; sp.classList.remove('d-none');

        fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            body: fd,
        })
        .then(r => r.json().then(b => ({ status: r.status, b })))
        .then(({ status, b }) => {
            btn.disabled = false; sp.classList.add('d-none');
            if (status === 422) {
                const eb = document.getElementById('member-form-errors');
                eb.textContent = Object.values(b.errors ?? {}).flat().join(' ');
                eb.classList.remove('d-none');
                return;
            }
            if (b.success) { memberModal.hide(); FtToast.success(b.message); loadTable(currentPage); }
        })
        .catch(() => { btn.disabled = false; sp.classList.add('d-none'); });
    });

    loadTable(1);
})();
</script>
@endpush
@endsection