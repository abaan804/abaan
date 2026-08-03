<div class="position-relative" style="max-width:420px;width:100%;">
    <div class="input-group">
        <span class="input-group-text bg-white border-end-0">
            <i class="bi bi-search text-muted"></i>
        </span>
        <input type="text" id="ft-global-search" class="form-control border-start-0"
               placeholder="{{ __('Search members, events...') }}" autocomplete="off">
    </div>
    <div id="ft-search-results" class="list-group position-absolute w-100 shadow-sm d-none"
         style="z-index:1050;max-height:400px;overflow-y:auto;top:100%;margin-top:4px;"></div>
</div>

<script>
(function () {
    const input = document.getElementById('ft-global-search');
    if (!input) return;
    const box = document.getElementById('ft-search-results');
    let timer = null;

    function hide() { box.classList.add('d-none'); box.innerHTML = ''; }

    input.addEventListener('input', function () {
        clearTimeout(timer);
        const q = this.value.trim();
        @if(isset($family))
            const treeUrl = "{{ route('familytree.family.tree.index', $family) }}";
        @endif
        if (q.length < 2) { hide(); return; }

        timer = setTimeout(() => {
            fetch(`{{ route('familytree.global-search') }}?q=${encodeURIComponent(q)}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(({ data }) => {
                const members = data?.members ?? [];
                if (!members.length) {
                    box.innerHTML = `<div class="list-group-item text-muted small">{{ __('No results found.') }}</div>`;
                } else {
                    let html = `<div class="list-group-item bg-light fw-semibold small py-1">{{ __('Members') }}</div>`;
                    members.forEach(r => {
                        html += `<a href="${treeUrl}?root=${r.id}&highlight=${r.id}" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                            <i class="bi bi-person-vcard text-muted"></i>
                            <div>
                                <div>${r.full_name}</div>
                                <div class="text-muted small">${r.father.full_name}</div>
                            </div>
                        </a>`;
                    });
                    box.innerHTML = html;
                }
                box.classList.remove('d-none');
            })
            .catch(hide);
        }, 350);
    });

    document.addEventListener('click', e => {
        if (!e.target.closest('#ft-global-search') && !e.target.closest('#ft-search-results')) hide();
    });

    input.addEventListener('keydown', e => { if (e.key === 'Escape') hide(); });
})();
</script>