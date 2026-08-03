window.LedgerToast = (function () {
    function ensureContainer() {
        let container = document.getElementById('ledger-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'ledger-toast-container';
            container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
            container.style.zIndex = 1080;
            document.body.appendChild(container);
        }
        return container;
    }

    function show(message, type = 'success') {
        const container = ensureContainer();
        const bg = type === 'success' ? 'text-bg-success' : (type === 'error' ? 'text-bg-danger' : 'text-bg-secondary');

        const toastEl = document.createElement('div');
        toastEl.className = `toast align-items-center ${bg} border-0`;
        toastEl.setAttribute('role', 'alert');
        toastEl.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>`;

        container.appendChild(toastEl);
        const toast = new bootstrap.Toast(toastEl, { delay: 4000 });
        toast.show();
        toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
    }

    return {
        success: (msg) => show(msg, 'success'),
        error: (msg) => show(msg, 'error'),
    };
})();