/**
 * Family Tree Manager — Select2 Utility
 *
 * Usage:
 *   FtSelect2.init(container)   — initialize all .ft-member-select inside container
 *   FtSelect2.destroy(container) — destroy before modal hide to avoid memory leaks
 *   FtSelect2.onModal(modalEl)   — wire show/hide events for a Bootstrap modal
 *
 * Key detail: Select2 inside Bootstrap 5 modals requires dropdownParent
 * set to the modal element — otherwise the dropdown renders behind the modal
 * overlay and is invisible/unclickable. This utility handles that automatically
 * by reading the closest .modal ancestor of each select.
 */
window.FtSelect2 = (function () {

    /**
     * Initialize Select2 on all .ft-member-select elements inside a container.
     * Container can be a modal element, a form, or document.body.
     */
    function init(container) {
        const parent = container instanceof Element ? container : document;
        const selects = parent.querySelectorAll('.ft-member-select');

        selects.forEach(function (el) {
            const $el    = $(el);
            const $modal = $el.closest('.modal');

            // Destroy if already initialized (safe re-init on modal reopen)
            if ($el.hasClass('select2-hidden-accessible')) {
                $el.select2('destroy');
            }

            const options = {
                theme: 'bootstrap-5',
                width: '100%',
                allowClear: true,
                placeholder: el.dataset.placeholder || el.options[0]?.text || '— Select —',
                language: {
                    noResults: function () {
                        return window.FtSelect2Labels?.noResults ?? 'No results found';
                    },
                    searching: function () {
                        return window.FtSelect2Labels?.searching ?? 'Searching…';
                    },
                },
            };

            // Critical: set dropdownParent when inside a modal
            if ($modal.length) {
                options.dropdownParent = $modal;
            }

            $el.select2(options);
        });
    }

    /**
     * Destroy all Select2 instances inside a container.
     * Call this on modal hide to avoid ghost dropdowns and event leaks.
     */
    function destroy(container) {
        const parent = container instanceof Element ? container : document;
        parent.querySelectorAll('.ft-member-select.select2-hidden-accessible').forEach(function (el) {
            $(el).select2('destroy');
        });
    }

    /**
     * Wire a Bootstrap 5 modal element for automatic Select2 init/destroy.
     * Call once per modal, after the modal exists in the DOM.
     */
    function onModal(modalEl) {
        if (!modalEl) return;

        // Re-init on every show so fresh options are picked up after form reset
        modalEl.addEventListener('shown.bs.modal', function () {
            init(modalEl);
        });

        // Destroy on hide to free memory and prevent stale dropdowns
        modalEl.addEventListener('hide.bs.modal', function () {
            destroy(modalEl);
        });
    }

    /**
     * Wire multiple modals at once.
     * Usage: FtSelect2.onModals(['#memberModal', '#marriageModal'])
     */
    function onModals(selectors) {
        selectors.forEach(function (sel) {
            onModal(document.querySelector(sel));
        });
    }

    return { init, destroy, onModal, onModals };

})();