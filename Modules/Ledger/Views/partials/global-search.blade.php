<div class="position-relative" style="max-width: 420px; width: 100%;">
    <div class="input-group">
        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
        <input type="text" id="ledger-global-search" class="form-control border-start-0"
               placeholder="{{ __('Search customers, suppliers, transactions...') }}" autocomplete="off">
    </div>
    <div id="ledger-search-results" class="list-group position-absolute w-100 shadow-sm d-none" style="z-index: 1050; max-height: 400px; overflow-y: auto; top: 100%; margin-top: 4px;"></div>
</div>

<script>
(function () {
    const input = document.getElementById('ledger-global-search');
    const resultsBox = document.getElementById('ledger-search-results');
    let debounceTimer = null;

    function hideResults() {
        resultsBox.classList.add('d-none');
        resultsBox.innerHTML = '';
    }

    function renderResults(results) {
        if (results.length === 0) {
            resultsBox.innerHTML = `<div class="list-group-item text-muted small">{{ __('No results found.') }}</div>`;
            resultsBox.classList.remove('d-none');
            return;
        }

        let html = '';
        let lastGroup = null;
        results.forEach(r => {
            if (r.group !== lastGroup) {
                html += `<div class="list-group-item bg-light fw-semibold small py-1">${r.group}</div>`;
                lastGroup = r.group;
            }
            html += `
                <a href="${r.url}?standalone=1" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                    <i class="bi ${r.icon} text-muted"></i>
                    <div>
                        <div>${r.title}</div>
                        <div class="text-muted small">${r.subtitle}</div>
                    </div>
                </a>`;
        });
        resultsBox.innerHTML = html;
        resultsBox.classList.remove('d-none');
    }

    input.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        const q = this.value.trim();

        if (q.length < 2) {
            hideResults();
            return;
        }

        debounceTimer = setTimeout(() => {
            fetch(`{{ route('ledger.search') }}?q=${encodeURIComponent(q)}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(res => res.json())
                .then(({ results }) => renderResults(results))
                .catch(() => hideResults());
        }, 350);
    });

    document.addEventListener('click', function (e) {
        if (!e.target.closest('#ledger-global-search') && !e.target.closest('#ledger-search-results')) {
            hideResults();
        }
    });

    input.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') hideResults();
    });
})();
</script>