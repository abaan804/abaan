/**
 * Family Tree Manager — Interactive Tree JS
 * Handles: Treant.js rendering, pan/zoom, hover cards,
 * mode switching (full / descendant / ancestor / path finder).
 */
window.FtTree = (function () {

    // ── State ──────────────────────────────────────────────────────────────────
    let cfg         = {};
    let treant      = null;
    let currentMode = 'full';
    let scale       = 1;
    let panX        = 0, panY = 0;
    let isPanning   = false;
    let panStartX   = 0, panStartY = 0, panStartTX = 0, panStartTY = 0;
    let hoverTimer  = null;
    let activeMemberId = null;

    // ── DOM references ─────────────────────────────────────────────────────────
    let container, inner, loading, empty, hoverCard, pathPanel, pathSteps;

    // ── Initialise ─────────────────────────────────────────────────────────────
    function init(options) {
        cfg = options;

        container  = document.getElementById('ft-tree-container');
        inner      = document.getElementById('ft-tree-inner');
        loading    = document.getElementById('ft-tree-loading');
        empty      = document.getElementById('ft-tree-empty');
        hoverCard  = document.getElementById('ft-hover-card');
        pathPanel  = document.getElementById('ft-path-panel');
        pathSteps  = document.getElementById('ft-path-steps');

        bindModeButtons();
        bindZoomControls();
        bindPanEvents();
        bindTouchEvents();
        bindToolbarControls();
        bindHoverCard();

        // If no preselect — just load full tree
        // Preselect is handled externally after Select2 inits (see tree/index.blade.php)
        if (!cfg.preselect) {
            switchMode('full');
        }
        // If preselect IS provided (old path), handle it
        else {
            switchMode('descendant');
        }
    }

    // ── Mode switching ─────────────────────────────────────────────────────────
    function switchMode(mode) {
        currentMode = mode;

        ['full','descendant','ancestor','path'].forEach(m => {
            const btn = document.getElementById('btn-mode-' + m);
            if (btn) {
                btn.classList.toggle('btn-primary', m === mode);
                btn.classList.toggle('btn-outline-primary', m !== mode);
            }
        });

        const ctrl      = document.getElementById('ctrl-root-member');
        const ctrlPath  = document.getElementById('ctrl-path-members');
        const ctrlDepth = document.getElementById('ctrl-depth');

        if (ctrl)      ctrl.classList.toggle('d-none', !['descendant','ancestor'].includes(mode));
        if (ctrlPath)  ctrlPath.classList.toggle('d-none', mode !== 'path');
        if (ctrlDepth) ctrlDepth.classList.toggle('d-none', mode === 'path');
        if (pathPanel) pathPanel.style.display = mode === 'path' ? '' : 'none';

        if (mode === 'full') {
            loadTree(cfg.urlFull + '?depth=' + getDepth());
        } else if (mode === 'descendant' || mode === 'ancestor') {
            const memberId = document.getElementById('select-root-member')?.value;
            if (memberId) {
                const url = (mode === 'descendant' ? cfg.urlDescendant : cfg.urlAncestor)
                    .replace('__ID__', memberId) + '?depth=' + getDepth();
                loadTree(url);
            } else {
                showEmpty();
            }
        } else if (mode === 'path') {
            showEmpty();
        }
    }

    function bindModeButtons() {
        ['full','descendant','ancestor','path'].forEach(m => {
            document.getElementById('btn-mode-' + m)?.addEventListener('click', () => switchMode(m));
        });
    }

    // ── Tree loading ───────────────────────────────────────────────────────────
    function loadTree(url) {
        showLoading();
        inner.innerHTML = '';
        if (treant) { try { treant.tree.destroy(); } catch(e) {} treant = null; }

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(json => {
                hideLoading();
                if (!json.success || !json.data?.nodeStructure) { showEmpty(); return; }
                renderTreant(json.data);
            })
            .catch(() => { hideLoading(); showEmpty(); });
    }

    // ── Treant rendering ───────────────────────────────────────────────────────
    function renderTreant(data) {
    data.chart.container        = '#ft-tree-inner';
    data.chart.rootOrientation  = 'NORTH';
    data.chart.levelSeparation  = 70;
    data.chart.siblingSeparation = 40;
    data.chart.subTeeSeparation  = 50;
    data.chart.padding           = 20;
    data.chart.connectors = {
        type: 'bCurve',
        style: { stroke: '#b8c9d9', 'stroke-width': 2.5, 'fill': 'none' }
    };
    data.chart.node = {
        HTMLclass: 'ft-member-node',
        collapsable: true,
    };
    data.chart.animateOnInit = false;

    // If a highlight member is set, mark their node before rendering
    if (cfg.highlight) {
        markHighlightNode(data.nodeStructure, parseInt(cfg.highlight));
    }

    transformNodes(data.nodeStructure);

    try {
        treant = new Treant(data);

        setTimeout(() => {
            fitToViewport();
            updateMinimap();

            // After render: scroll to & pulse the highlighted node
            if (cfg.highlight) {
                scrollToMember(cfg.highlight);
            }
        }, 400);
    } catch(e) {
        console.error('Treant render error:', e);
        showEmpty();
    }
    }

    /**
     * Recursively walk the nodeStructure and add ft-node-highlight
     * to the node whose data-member-id matches.
     */
    function markHighlightNode(node, memberId) {
        if (!node) return;
        if (node.pseudo) {
            if (node.children) node.children.forEach(c => markHighlightNode(c, memberId));
            return;
        }

        const nodeId = parseInt(node['data-member-id']);
        if (nodeId === memberId) {
            // Inject highlight into existing HTMLclass
            const existing = node.HTMLclass || '';
            if (!existing.includes('ft-node-highlight')) {
                node.HTMLclass = existing + ' ft-node-highlight';
            }
        }

        if (node.children) {
            node.children.forEach(c => markHighlightNode(c, memberId));
        }
    }

    /**
     * After Treant renders, find the highlighted node in the DOM,
     * add a pulse animation, and pan/center the viewport to it.
     */
    function scrollToMember(memberId) {
        // Treant renders inner HTML into .ft-member-node-inner divs
        const nodeEl = inner.querySelector(
            `.ft-member-node-inner[data-member-id="${memberId}"]`
        );

        if (!nodeEl) return;

        // Add a visible pulse ring to draw attention
        nodeEl.style.outline        = '3px solid var(--ft-gold)';
        nodeEl.style.outlineOffset  = '4px';
        nodeEl.style.borderRadius   = '14px';
        nodeEl.style.transition     = 'outline .3s ease';

        // Also add class to the wrapping Treant node
        const treantNode = nodeEl.closest('.node');
        if (treantNode) {
            treantNode.classList.add('ft-node-highlight');
        }

        // Pulse animation — flash 3 times then settle
        let pulseCount = 0;
        const pulseInterval = setInterval(() => {
            nodeEl.style.outline = pulseCount % 2 === 0
                ? '3px solid var(--ft-gold)'
                : '3px solid transparent';
            pulseCount++;
            if (pulseCount >= 6) {
                clearInterval(pulseInterval);
                nodeEl.style.outline       = '3px solid var(--ft-gold)';
                nodeEl.style.outlineOffset = '4px';
            }
        }, 300);

        // Center the viewport on this node
        const cRect  = container.getBoundingClientRect();
        const nRect  = nodeEl.getBoundingClientRect();

        // Calculate where the node is in the inner coordinate space
        const innerRect = inner.getBoundingClientRect();
        const nodeOffsetX = (nRect.left - innerRect.left) / scale;
        const nodeOffsetY = (nRect.top  - innerRect.top)  / scale;

        // Pan so node is centered in the container
        panX = (cRect.width  / 2) - (nodeOffsetX * scale) - (nRect.width  / 2);
        panY = (cRect.height / 2) - (nodeOffsetY * scale) - (nRect.height / 2) - 40;

        applyTransform();
        updateMinimap();
    }

    /**
     * Recursively transforms the nodeStructure to use our custom HTML node template.
     * Treant renders node.innerHTML if it's provided.
     */
    function transformNodes(node) {
        if (!node) return;

        // Skip pseudo (couple connector) nodes
        if (node.pseudo) {
            node.innerHTML = '<div class="ft-couple-connector"></div>';
            if (node.children) node.children.forEach(transformNodes);
            return;
        }

        const data = node.text || {};
        const name = data.name || '';
        const fatherName = data.title || '';
        const age = data.contact || '';
        const occupation = data.desc || '';
        const img = node.image || `{{ asset('images/familytree/default-male.png') }}`;
        const memberId = node['data-member-id'] || '';
        const gender = node['data-gender'] || 'male';
        const lifeStatus = node['data-life-status'] || 'living';
        const isDeceased = lifeStatus === 'deceased';
        const isHighlight = (node.HTMLclass || '').includes('ft-node-highlight');
        const isFemale = gender === 'female';

        // Build the custom HTML
        node.innerHTML = `
            <div class="ft-member-node-inner"
                 data-member-id="${memberId}"
                 data-gender="${gender}"
                 data-life-status="${lifeStatus}">
                <img src="${img}"
                     alt="${name}"
                     onerror="this.src='{{ asset('images/familytree/default-male.png') }}'">
                <div class="ft-node-name">
                    ${name}
                    ${isDeceased ? '<span class="ft-node-deceased-mark"></span>' : ''}
                </div>
                ${fatherName ? `<div class="ft-node-sub">${fatherName}</div>` : ''}
                ${age ? `<div class="ft-node-sub">${age}</div>` : ''}
            </div>
        `;

        // Add extra CSS classes
        node.HTMLclass = [
            'ft-member-node',
            isFemale ? 'ft-node-female' : 'ft-node-male',
            isDeceased ? 'ft-node-deceased' : '',
            isHighlight ? 'ft-node-highlight' : '',
        ].filter(Boolean).join(' ');

        // Remove the text object so Treant doesn't render its own default
        node.text = {};

        if (node.children) node.children.forEach(transformNodes);
    }

    // ── Pan & Zoom ─────────────────────────────────────────────────────────────
    function applyTransform() {
        inner.style.transform = `translate(${panX}px, ${panY}px) scale(${scale})`;
        updateMinimap();
    }

    function bindZoomControls() {
        document.getElementById('btn-zoom-in')?.addEventListener('click', () => zoom(0.15));
        document.getElementById('btn-zoom-out')?.addEventListener('click', () => zoom(-0.15));
        document.getElementById('btn-zoom-reset')?.addEventListener('click', resetZoom);
        document.getElementById('btn-fit')?.addEventListener('click', fitToViewport);
        document.getElementById('btn-center')?.addEventListener('click', centerTree);

        // Mouse wheel zoom
        container.addEventListener('wheel', e => {
            e.preventDefault();
            zoom(e.deltaY < 0 ? 0.1 : -0.1, e.clientX, e.clientY);
        }, { passive: false });
    }

    function zoom(delta, clientX, clientY) {
        const newScale = Math.max(0.2, Math.min(2.5, scale + delta));
        if (newScale === scale) return;

        if (clientX !== undefined && clientY !== undefined) {
            const rect = container.getBoundingClientRect();
            const mouseX = clientX - rect.left;
            const mouseY = clientY - rect.top;
            panX = mouseX - (mouseX - panX) * (newScale / scale);
            panY = mouseY - (mouseY - panY) * (newScale / scale);
        }

        scale = newScale;
        applyTransform();
    }

    function resetZoom() {
        scale = 1;
        centerTree();
    }

    function centerTree() {
        const rect = inner.getBoundingClientRect();
        const cRect = container.getBoundingClientRect();
        panX = (cRect.width  - rect.width  * scale) / 2;
        panY = (cRect.height - rect.height * scale) / 2;
        applyTransform();
    }

    function fitToViewport() {
        if (!inner.children.length) return;
        const cRect = container.getBoundingClientRect();
        const naturalW = inner.scrollWidth;
        const naturalH = inner.scrollHeight;
        if (!naturalW || !naturalH) return;

        const newScale = Math.min(
            (cRect.width  - 40) / naturalW,
            (cRect.height - 40) / naturalH,
            1 // never scale above 100%
        );
        scale = Math.max(0.2, newScale);
        panX  = (cRect.width  - naturalW * scale) / 2;
        panY  = 20;
        applyTransform();
    }

    function bindPanEvents() {
        container.addEventListener('mousedown', e => {
            if (e.target.closest('.ft-member-node-inner') || e.target.closest('.ft-zoom-controls')) return;
            isPanning  = true;
            panStartX  = e.clientX;
            panStartY  = e.clientY;
            panStartTX = panX;
            panStartTY = panY;
            container.style.cursor = 'grabbing';
        });

        document.addEventListener('mousemove', e => {
            if (!isPanning) return;
            panX = panStartTX + (e.clientX - panStartX);
            panY = panStartTY + (e.clientY - panStartY);
            applyTransform();
        });

        document.addEventListener('mouseup', () => {
            isPanning = false;
            container.style.cursor = '';
        });
    }

    function bindTouchEvents() {
        let lastDist = 0;
        let touchPanX = 0, touchPanY = 0;
        let touches = [];

        container.addEventListener('touchstart', e => {
            touches = Array.from(e.touches);
            if (touches.length === 2) {
                lastDist = Math.hypot(
                    touches[0].clientX - touches[1].clientX,
                    touches[0].clientY - touches[1].clientY
                );
            } else if (touches.length === 1) {
                touchPanX = touches[0].clientX;
                touchPanY = touches[0].clientY;
                panStartTX = panX;
                panStartTY = panY;
            }
        }, { passive: true });

        container.addEventListener('touchmove', e => {
            e.preventDefault();
            touches = Array.from(e.touches);

            if (touches.length === 2) {
                // Pinch-to-zoom
                const dist = Math.hypot(
                    touches[0].clientX - touches[1].clientX,
                    touches[0].clientY - touches[1].clientY
                );
                const midX = (touches[0].clientX + touches[1].clientX) / 2;
                const midY = (touches[0].clientY + touches[1].clientY) / 2;
                zoom((dist - lastDist) * 0.005, midX, midY);
                lastDist = dist;
            } else if (touches.length === 1) {
                // Pan
                panX = panStartTX + (touches[0].clientX - touchPanX);
                panY = panStartTY + (touches[0].clientY - touchPanY);
                applyTransform();
            }
        }, { passive: false });

        container.addEventListener('touchend', () => { touches = []; });
    }

    // ── Toolbar controls ───────────────────────────────────────────────────────
    function bindToolbarControls() {
        // Root member change triggers reload
        document.getElementById('select-root-member')?.addEventListener('change', function () {
            if (!this.value) { showEmpty(); return; }
            switchMode(currentMode);
        });

        // Depth change triggers reload
        document.getElementById('select-depth')?.addEventListener('change', () => {
            if (currentMode !== 'path') switchMode(currentMode);
        });

        // Path finder
        document.getElementById('btn-find-path')?.addEventListener('click', findRelationshipPath);
    }

    function getDepth() {
        return document.getElementById('select-depth')?.value || 5;
    }

    // ── Relationship Path Finder ───────────────────────────────────────────────
    function findRelationshipPath() {
        const aId = document.getElementById('select-path-a')?.value;
        const bId = document.getElementById('select-path-b')?.value;

        if (!aId || !bId || aId === bId) {
            FtToast.error("{{ __('Please select two different members.') }}");
            return;
        }

        pathPanel.style.display = 'block';
        pathSteps.innerHTML = `<div class="text-center py-3">
            <div class="spinner-border spinner-border-sm" style="color:var(--ft-primary);"></div>
        </div>`;
        document.getElementById('ft-path-description')?.classList.add('d-none');
        document.getElementById('ft-path-no-connection')?.classList.add('d-none');

        fetch(`${cfg.urlPath}?member_a_id=${aId}&member_b_id=${bId}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(json => {
            if (!json.success) { pathSteps.innerHTML = ''; return; }

            if (!json.connected || !json.path?.length) {
                pathSteps.innerHTML = '';
                document.getElementById('ft-path-no-connection')?.classList.remove('d-none');
                return;
            }

            let html = '';
            json.path.forEach((step, i) => {
                const photoEl = step.photo
                    ? `<img src="${step.photo}" class="ft-path-avatar" alt="">`
                    : `<div class="ft-path-avatar-placeholder">
                           <i class="bi bi-person" style="color:var(--ft-primary);"></i>
                       </div>`;

                html += `<div class="ft-path-step">
                    ${photoEl}
                    <div>
                        <div class="fw-semibold small">${step.name}</div>
                        ${step.label ? `<div class="text-muted small">${step.label}</div>` : ''}
                    </div>
                </div>`;

                // Arrow between steps
                if (i < json.path.length - 1) {
                    html += `<div style="padding-left:10px;color:#9ca3af;font-size:.75rem;margin:2px 0;">
                        <i class="bi bi-arrow-down"></i>
                    </div>`;
                }
            });

            pathSteps.innerHTML = html;

            if (json.description) {
                const descEl = document.getElementById('ft-path-description');
                if (descEl) { descEl.textContent = json.description; descEl.classList.remove('d-none'); }
            }
        })
        .catch(() => {
            pathSteps.innerHTML = `
                <p class="text-danger small">{{ __('Failed to find path.') }}</p>
            `;        
        });
    }

    // ── Hover Card ─────────────────────────────────────────────────────────────
    function bindHoverCard() {
        // Delegate click/hover on dynamically rendered tree nodes
        inner.addEventListener('mouseover', e => {
            const node = e.target.closest('.ft-member-node-inner');
            
            if (!node) return;
           
            const memberId = node.dataset.memberId;
            if (!memberId || memberId === activeMemberId) return;

            clearTimeout(hoverTimer);
            hoverTimer = setTimeout(() => loadHoverCard(memberId, e), 400);
        });

        inner.addEventListener('mouseout', e => {
            if (!e.target.closest('.ft-member-node-inner') && !e.target.closest('#ft-hover-card')) {
                clearTimeout(hoverTimer);
                scheduleHideCard();
            }
        });

        hoverCard.addEventListener('mouseenter', () => clearTimeout(hoverTimer));
        hoverCard.addEventListener('mouseleave', () => scheduleHideCard());

        // Click on node: navigate to profile
        inner.addEventListener('click', e => {
            const node = e.target.closest('.ft-member-node-inner');
            if (!node) return;
            const memberId = node.dataset.memberId;
            if (memberId) {
                const url = cfg.urlProfile.replace('__ID__', memberId);
                // If it's a quick tap (not a pan), navigate
                window.location.href = url;
            }
        });

        // Hover card "Set Root" button
        document.getElementById('ft-hc-set-root')?.addEventListener('click', () => {
            if (!activeMemberId) return;
            hideHoverCard();
            document.getElementById('select-root-member').value = activeMemberId;
            switchMode('descendant');
        });
    }

    function loadHoverCard(memberId, event) {
        activeMemberId = memberId;
       
        const url = cfg.urlCard.replace('__ID__', memberId);
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(json => {
                if (!json.success || activeMemberId !== memberId) return;
                populateHoverCard(json.data);
                positionHoverCard(event);
                hoverCard.style.display = 'block';
            })
            .catch(() => {});
    }

    function populateHoverCard(data) {
        
        const photo = data.photo || `{{ asset('images/familytree/default-male.png') }}`;
        
        document.getElementById('ft-hc-photo').src = photo;
       
        document.getElementById('ft-hc-name').textContent = data.full_name || '—';
       
        // document.getElementById('ft-hc-sub').textContent = data.father_name
        //         ? (window.ftLang.sonOf + ' ' + data.father_name)
        //         : (data.occupation || '');
        //    alert('test');
        document.getElementById('ft-hc-sub').textContent = data.father_name
            ? ((window.ftLang?.sonOf || 'Son of') + ' ' + data.father_name)
            : (data.occupation || '');
        document.getElementById('ft-hc-age').textContent        = data.age ? (data.age + ' yrs') : '—';
        document.getElementById('ft-hc-dob').textContent        = data.date_of_birth || '—';
        document.getElementById('ft-hc-status').textContent     = data.life_status ? capitalize(data.life_status) : '—';
        document.getElementById('ft-hc-marital').textContent    = data.marital_status ? capitalize(data.marital_status) : '—';
        document.getElementById('ft-hc-occupation').textContent = data.occupation || '—';
        document.getElementById('ft-hc-contact').textContent    = data.contact_number || '—';
        
        const eventRow = document.getElementById('ft-hc-event-row');
        if (data.recent_event) {
            document.getElementById('ft-hc-event').textContent = `${data.recent_event.title} (${data.recent_event.date})`;
            eventRow.classList.remove('d-none');
        } else {
            eventRow.classList.add('d-none');
        }

        // Header color by gender
        const header = hoverCard.querySelector('.ft-hc-header');
        if (header) {
            header.style.background = data.gender === 'female' ? 'var(--ft-female)' : 'var(--ft-primary)';
        }

        // Profile link
        const profileLink = document.getElementById('ft-hc-profile-link');
        if (profileLink) profileLink.href = cfg.urlProfile.replace('__ID__', data.id);

        // Set root button shows only in descendant mode
        const setRootBtn = document.getElementById('ft-hc-set-root');
        if (setRootBtn) setRootBtn.style.display = currentMode === 'descendant' ? '' : 'none';
    }

    function positionHoverCard(event) {
        const cardW = 270, cardH = 320;
        const vw = window.innerWidth, vh = window.innerHeight;
        let x = event.clientX + 16;
        let y = event.clientY - 20;
        if (x + cardW > vw) x = event.clientX - cardW - 16;
        if (y + cardH > vh) y = vh - cardH - 10;
        hoverCard.style.left = x + 'px';
        hoverCard.style.top  = y + 'px';
    }

    let hideCardTimer = null;
    function scheduleHideCard() {
        clearTimeout(hideCardTimer);
        hideCardTimer = setTimeout(hideHoverCard, 250);
    }

    function hideHoverCard() {
        hoverCard.style.display = 'none';
        activeMemberId = null;
    }

    // ── Mini-map ───────────────────────────────────────────────────────────────
    function updateMinimap() {
        const canvas = document.getElementById('ft-minimap');
        if (!canvas || !canvas.getContext) return;
        const ctx = canvas.getContext('2d');
        const cW = canvas.width, cH = canvas.height;

        ctx.clearRect(0, 0, cW, cH);

        // Draw a simplified representation of the tree area
        const innerW = inner.scrollWidth  || 1;
        const innerH = inner.scrollHeight || 1;
        const cRect  = container.getBoundingClientRect();
        const scaleX = cW / innerW;
        const scaleY = cH / innerH;

        // Draw tree bounding box
        ctx.fillStyle = 'rgba(26,82,118,.08)';
        ctx.fillRect(0, 0, cW, cH);

        // Viewport indicator
        const vpX = (-panX / scale) * scaleX;
        const vpY = (-panY / scale) * scaleY;
        const vpW = (cRect.width  / scale) * scaleX;
        const vpH = (cRect.height / scale) * scaleY;

        ctx.strokeStyle = 'rgba(26,82,118,.6)';
        ctx.lineWidth = 1.5;
        ctx.strokeRect(vpX, vpY, vpW, vpH);

        ctx.fillStyle = 'rgba(26,82,118,.15)';
        ctx.fillRect(vpX, vpY, vpW, vpH);

        // Draw simplified node dots
        const nodes = inner.querySelectorAll('.ft-member-node-inner');
        nodes.forEach(node => {
            const nr = node.getBoundingClientRect();
            const cr = inner.getBoundingClientRect();
            const nx = ((nr.left - cr.left) / innerW) * cW;
            const ny = ((nr.top  - cr.top)  / innerH) * cH;
            const female = node.dataset.gender === 'female';
            ctx.fillStyle = female ? 'rgba(142,68,173,.6)' : 'rgba(26,82,118,.6)';
            ctx.beginPath();
            ctx.arc(nx, ny, 3, 0, Math.PI * 2);
            ctx.fill();
        });
    }

    // ── UI helpers ─────────────────────────────────────────────────────────────
    function showLoading() {
        loading.style.display = 'flex';
        empty.style.display   = 'none';
    }
    function hideLoading() {
        loading.style.display = 'none';
    }
    function showEmpty() {
        loading.style.display = 'none';
        empty.style.display   = 'flex';
    }
    function capitalize(s) {
        return s ? s.charAt(0).toUpperCase() + s.slice(1) : s;
    }

    
    // ── Public API ─────────────────────────────────────────────────────────────
    return {
        init,
        switchDescendant: () => switchMode('descendant'),
        switchFull:       () => switchMode('full'),
        switchAncestor:   () => switchMode('ancestor'),
    };
})();