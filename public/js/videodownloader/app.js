/**
 * Video Downloader — Main JS
 * Handles: URL submission, metadata fetch, format selection,
 * download start, status polling, toasts.
 */

// ── Toast ──────────────────────────────────────────────────────────────────────
window.VdToast = (function () {
    function show(msg, type = 'success') {
        const container = document.getElementById('vd-toast-container');
        if (!container) return;
        const bg = {
            success: 'text-bg-success',
            error:   'text-bg-danger',
            warning: 'text-bg-warning',
            info:    'text-bg-primary',
        }[type] || 'text-bg-secondary';

        const el = document.createElement('div');
        el.className = `toast align-items-center ${bg} border-0`;
        el.setAttribute('role', 'alert');
        el.innerHTML = `<div class="d-flex">
            <div class="toast-body">${msg}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>`;
        container.appendChild(el);
        new bootstrap.Toast(el, { delay: 5000 }).show();
        el.addEventListener('hidden.bs.toast', () => el.remove());
    }

    return {
        success: m => show(m, 'success'),
        error:   m => show(m, 'error'),
        warning: m => show(m, 'warning'),
        info:    m => show(m, 'info'),
    };
})();

// ── New Download Flow ──────────────────────────────────────────────────────────
window.VdDownload = (function () {

    let selectedFormat  = null;
    let currentMetadata = null;
    let csrfToken       = null;

    function init(options = {}) {
        csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

        const form = document.getElementById('vd-url-form');
        if (form) {
            form.addEventListener('submit', onUrlSubmit);
        }

        const pasteBtn = document.getElementById('vd-paste-btn');
        if (pasteBtn) {
            pasteBtn.addEventListener('click', () => {
                navigator.clipboard.readText().then(text => {
                    const input = document.getElementById('vd-url-input');
                    if (input) { input.value = text.trim(); input.focus(); }
                }).catch(() => VdToast.warning('Clipboard access denied. Please paste manually.'));
            });
        }

        const startBtn = document.getElementById('vd-start-btn');
        if (startBtn) {
            startBtn.addEventListener('click', onStartDownload);
        }
    }

    // ── Step 1: Submit URL ─────────────────────────────────────────────────────
    function onUrlSubmit(e) {
        e.preventDefault();
        const url = document.getElementById('vd-url-input')?.value?.trim();
        if (!url) { VdToast.error('Please enter a video URL.'); return; }

        setStep('loading');

        fetch(window.VdRoutes?.fetchMetadata ?? '/app/video-downloader/fetch-metadata', {
            method: 'POST',
            headers: {
                'Content-Type':     'application/json',
                'X-CSRF-TOKEN':     csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept':           'application/json',
            },
            body: JSON.stringify({ url }),
        })
        .then(r => r.json().then(b => ({ status: r.status, b })))
        .then(({ status, b }) => {
            if (!b.success) {
                setStep('form');
                VdToast.error(b.message ?? 'Could not retrieve video information.');
                return;
            }
            currentMetadata = b.metadata;
            renderMetadata(b.metadata);
            renderFormats(b.formats, url);
            setStep('formats');
        })
        .catch(() => {
            setStep('form');
            VdToast.error('Request failed. Please check your connection and try again.');
        });
    }

    // ── Render Metadata ────────────────────────────────────────────────────────
    function renderMetadata(meta) {
        const el = document.getElementById('vd-metadata-section');
        if (!el) return;

        const thumb = meta.thumbnail
            ? `<img src="${meta.thumbnail}" class="vd-meta-thumb" alt="" onerror="this.parentElement.innerHTML='<div class=vd-meta-thumb-placeholder><i class=bi bi-play-circle-fill></i></div>'">`
            : `<div class="vd-meta-thumb-placeholder"><i class="bi bi-play-circle-fill"></i></div>`;

        const dur = meta.duration ? formatDuration(meta.duration) : '';
        const platIcon = meta.platform_icon || 'bi-globe';

        el.innerHTML = `
            <div class="vd-meta-card mb-3">
                ${thumb}
                <div class="vd-meta-body">
                    <div class="vd-meta-title">${escHtml(meta.title)}</div>
                    <div class="vd-meta-sub d-flex gap-3 flex-wrap">
                        <span><i class="bi ${platIcon}"></i> ${cap(meta.platform)}</span>
                        ${meta.uploader ? `<span><i class="bi bi-person"></i> ${escHtml(meta.uploader)}</span>` : ''}
                        ${dur ? `<span><i class="bi bi-clock"></i> ${dur}</span>` : ''}
                        ${meta.upload_date ? `<span><i class="bi bi-calendar3"></i> ${meta.upload_date}</span>` : ''}
                    </div>
                </div>
            </div>
        `;
    }

    // ── Render Format Buttons ──────────────────────────────────────────────────
    function renderFormats(formats, url) {
        selectedFormat = null;
        document.getElementById('vd-start-btn').disabled = true;
        document.getElementById('vd-selected-url').value  = url;

        const videoWrap = document.getElementById('vd-formats-video');
        const audioWrap = document.getElementById('vd-formats-audio');
        const audioSec  = document.getElementById('vd-audio-section');

        if (videoWrap) videoWrap.innerHTML = '';
        if (audioWrap) audioWrap.innerHTML = '';

        // Video formats
        (formats.video || []).forEach(f => {
            const btn = createFormatBtn(f, false);
            videoWrap?.appendChild(btn);
        });

        // Audio formats
        if (formats.audio?.length && audioSec) {
            audioSec.style.display = '';
            formats.audio.forEach(f => {
                const btn = createFormatBtn(f, true);
                audioWrap?.appendChild(btn);
            });
        } else if (audioSec) {
            audioSec.style.display = 'none';
        }

        if (!formats.video?.length && !formats.audio?.length) {
            videoWrap.innerHTML = '<p class="text-muted small">No downloadable formats found.</p>';
        }
    }

    function createFormatBtn(f, isAudio) {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'vd-format-btn mb-2' + (isAudio ? ' audio-format' : '');
        btn.dataset.formatId    = f.id;
        btn.dataset.quality     = f.quality || '';
        btn.dataset.ext         = f.ext || 'mp4';
        btn.dataset.isAudio     = isAudio ? '1' : '0';

        const size = f.filesize ? `<span class="text-muted small ms-auto">${formatBytes(f.filesize)}</span>` : '';
        const note = f.note ? `<span class="text-muted small">${escHtml(f.note)}</span>` : '';

        btn.innerHTML = `
            <div class="d-flex align-items-center gap-2">
                <i class="bi ${isAudio ? 'bi-music-note' : 'bi-camera-video'} text-muted"></i>
                <span class="quality-badge">${escHtml(f.quality || (isAudio ? 'Audio' : 'Video'))}</span>
                <span class="ext-badge">.${escHtml(f.ext)}</span>
                ${note}
                ${size}
            </div>
        `;

        btn.addEventListener('click', () => selectFormat(btn, f, isAudio));
        return btn;
    }

    function selectFormat(btn, f, isAudio) {
        // Deselect all
        document.querySelectorAll('.vd-format-btn').forEach(b => b.classList.remove('selected'));
        btn.classList.add('selected');

        selectedFormat = { ...f, is_audio_only: isAudio };

        document.getElementById('vd-selected-format-id').value    = f.id;
        document.getElementById('vd-selected-quality').value      = f.quality || '';
        document.getElementById('vd-selected-ext').value          = f.ext || 'mp4';
        document.getElementById('vd-selected-audio-only').value   = isAudio ? '1' : '0';
        document.getElementById('vd-start-btn').disabled = false;
    }

    // ── Step 2: Start Download ─────────────────────────────────────────────────
    function onStartDownload() {
        if (!selectedFormat) { VdToast.error('Please select a format first.'); return; }

        const startBtn = document.getElementById('vd-start-btn');
        startBtn.disabled = true;
        startBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Starting...';

        const payload = {
            url:           document.getElementById('vd-selected-url').value,
            format_id:     document.getElementById('vd-selected-format-id').value,
            quality:       document.getElementById('vd-selected-quality').value,
            format_ext:    document.getElementById('vd-selected-ext').value,
            is_audio_only: document.getElementById('vd-selected-audio-only').value === '1',
        };

        fetch(window.VdRoutes?.start ?? '/app/video-downloader/start', {
            method: 'POST',
            headers: {
                'Content-Type':     'application/json',
                'X-CSRF-TOKEN':     csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept':           'application/json',
            },
            body: JSON.stringify(payload),
        })
        .then(r => r.json().then(b => ({ status: r.status, b })))
        .then(({ status, b }) => {
            if (b.success) {
                VdToast.success(b.message);
                // Redirect to download show page
                setTimeout(() => { window.location.href = b.show_url; }, 800);
            } else {
                startBtn.disabled = false;
                startBtn.innerHTML = '<i class="bi bi-cloud-arrow-down"></i> Start Download';
                VdToast.error(b.message ?? 'Failed to start download.');
            }
        })
        .catch(() => {
            startBtn.disabled = false;
            startBtn.innerHTML = '<i class="bi bi-cloud-arrow-down"></i> Start Download';
            VdToast.error('Request failed.');
        });
    }

    // ── UI State ───────────────────────────────────────────────────────────────
    function setStep(step) {
        const form    = document.getElementById('vd-url-section');
        const loading = document.getElementById('vd-loading-section');
        const formats = document.getElementById('vd-formats-section');

        if (form)    form.style.display    = step === 'form'    ? '' : 'none';
        if (loading) loading.style.display = step === 'loading' ? '' : 'none';
        if (formats) formats.style.display = step === 'formats' ? '' : 'none';
    }

    // ── Helpers ────────────────────────────────────────────────────────────────
    function formatDuration(s) {
        const h = Math.floor(s / 3600);
        const m = Math.floor((s % 3600) / 60);
        const sec = s % 60;
        return h > 0
            ? `${h}:${String(m).padStart(2,'0')}:${String(sec).padStart(2,'0')}`
            : `${m}:${String(sec).padStart(2,'0')}`;
    }

    function formatBytes(b) {
        if (b < 1048576)    return (b / 1024).toFixed(1) + ' KB';
        if (b < 1073741824) return (b / 1048576).toFixed(1) + ' MB';
        return (b / 1073741824).toFixed(2) + ' GB';
    }

    function escHtml(str) {
        return String(str ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    }

    function cap(str) {
        return str ? str.charAt(0).toUpperCase() + str.slice(1) : '';
    }

    return { init };
})();