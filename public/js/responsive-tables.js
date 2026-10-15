/*
 * Responsive admin tables (.table-soft).
 *
 * A table stays a normal table while it fits its box. When it doesn't (a
 * phone, a tablet, or a wide table like Staff Accounts on a laptop), it
 * gets .rt-stack and mobile.css §16 shows every row as a card of
 * "LABEL  value" lines — labels taken from the <th> headers — instead of a
 * table you have to scroll sideways. Re-checked on resize and whenever a
 * tab or modal is shown (a table in a hidden tab measures 0 wide).
 *
 * Each cell's content is wrapped once in <span class="rt-val">, which is
 * display:contents in table mode (no visual change) and the right-hand
 * value block in card mode.
 */
(function () {
    if (window.__responsiveTables) return;
    window.__responsiveTables = true;

    function prepare(table) {
        if (table.dataset.rtReady) return;
        table.dataset.rtReady = '1';

        var labels = Array.prototype.map.call(table.querySelectorAll('thead th'), function (th) {
            return th.textContent.replace(/\s+/g, ' ').trim();
        });

        table.querySelectorAll('tbody tr').forEach(function (tr) {
            var col = 0;
            Array.prototype.forEach.call(tr.children, function (td) {
                var span = parseInt(td.getAttribute('colspan') || '1', 10);
                if (span === 1 && labels[col] !== undefined && !td.hasAttribute('data-label')) {
                    td.setAttribute('data-label', labels[col]);
                }
                if (!td.querySelector(':scope > .rt-val')) {
                    var wrap = document.createElement('span');
                    wrap.className = 'rt-val';
                    while (td.firstChild) wrap.appendChild(td.firstChild);
                    td.appendChild(wrap);
                }
                col += span;
            });
        });
    }

    function fit(table) {
        var box = table.parentElement;
        if (!box || box.clientWidth === 0) return; // hidden tab/modal — checked again when shown
        table.classList.remove('rt-stack');
        var tooWide = table.scrollWidth > box.clientWidth + 1;
        table.classList.toggle('rt-stack', tooWide);
    }

    function run() {
        document.querySelectorAll('table.table-soft').forEach(function (t) {
            prepare(t);
            fit(t);
        });
    }

    var timer = null;
    function later() {
        clearTimeout(timer);
        timer = setTimeout(run, 120);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', run);
    } else {
        run();
    }
    window.addEventListener('load', run);
    window.addEventListener('resize', later);
    document.addEventListener('shown.bs.tab', later);
    document.addEventListener('shown.bs.modal', later);
    document.addEventListener('click', function (e) {
        // Configuration's own settings-tab switcher (not Bootstrap tabs).
        if (e.target.closest && e.target.closest('[data-settings-tab]')) later();
    });
})();
