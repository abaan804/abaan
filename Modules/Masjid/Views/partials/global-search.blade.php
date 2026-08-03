@if (isset($mosque))
<div class="position-relative" style="max-width: 420px; width: 100%;">
    <div class="input-group">
        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
        <input type="text" id="mj-global-search" class="form-control border-start-0"
               placeholder="{{ __('Search members, seasons, payments...') }}" autocomplete="off">
    </div>
    <div id="mj-search-results" class="list-group position-absolute w-100 shadow-sm d-none"
         style="z-index:1050; max-height:400px; overflow-y:auto; top:100%; margin-top:4px;"></div>
</div>

<script>
(function () {
    const input = document.getElementById('mj-global-search');
    if (!input) return;
    const resultsBox = document.getElementById('mj-search-results');
    let timer = null;

    function hide() { resultsBox.classList.add('d-none'); resultsBox.innerHTML = ''; }

    input.addEventListener('input', function () {
        clearTimeout(timer);
        const q = this.value.trim();
        if (q.length < 2) { hide(); return; }

        timer = setTimeout(() => {
            fetch(`{{ route('masjid.mosque.search', $mosque) }}?q=${encodeURIComponent(q)}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.json())
                .then(({ results }) => {
                    if (!results.length) {
                        resultsBox.innerHTML = `<div class="list-group-item text-muted small">{{ __('No results found.') }}</div>`;
                    } else {
                        let html = ''; let lastGroup = null;
                        results.forEach(r => {
                            if (r.group !== lastGroup) {
                                html += `<div class="list-group-item bg-light fw-semibold small py-1">${r.group}</div>`;
                                lastGroup = r.group;
                            }
                            html += `<a href="${r.url}?standalone=1" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                                <i class="bi ${r.icon} text-muted"></i>
                                <div><div>${r.title}</div><div class="text-muted small">${r.subtitle}</div></div>
                            </a>`;
                        });
                        resultsBox.innerHTML = html;
                    }
                    resultsBox.classList.remove('d-none');
                })
                .catch(hide);
        }, 350);
    });

    document.addEventListener('click', e => {
        if (!e.target.closest('#mj-global-search') && !e.target.closest('#mj-search-results')) hide();
    });

    input.addEventListener('keydown', e => { if (e.key === 'Escape') hide(); });
})();
</script>
@endif