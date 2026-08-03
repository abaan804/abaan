<div id="ft-toast-container" class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index:1080;"></div>
<script>
window.FtToast = (function () {
    function show(msg, type = 'success') {
        const c = document.getElementById('ft-toast-container');
        const bg = type === 'success' ? 'text-bg-success' : (type === 'error' ? 'text-bg-danger' : 'text-bg-secondary');
        const el = document.createElement('div');
        el.className = `toast align-items-center ${bg} border-0`;
        el.setAttribute('role', 'alert');
        el.innerHTML = `<div class="d-flex"><div class="toast-body">${msg}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>`;
        c.appendChild(el);
        new bootstrap.Toast(el, { delay: 4500 }).show();
        el.addEventListener('hidden.bs.toast', () => el.remove());
    }
    return { success: m => show(m,'success'), error: m => show(m,'error'), info: m => show(m,'info') };
})();
</script>