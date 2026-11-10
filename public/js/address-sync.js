// Combines the split "Street / Barangay / City / Province" address inputs
// (class="addr-part") into the single hidden `address` field each form
// actually submits — same convention as the signup wizard. Scoped per
// <form> via closest() so it works even when a page has several such
// forms/modals at once (e.g. one edit modal per row).
(function () {
    // Always saved as "Street, Barangay, City, Province" — by field name, not
    // page position, since ph-address.js shows them Province → … → Street.
    var ORDER = ['addr_street', 'addr_barangay', 'addr_city', 'addr_province'];
    function rank(el) {
        for (var i = 0; i < ORDER.length; i++) if ((el.name || '').slice(-ORDER[i].length) === ORDER[i]) return i;
        return ORDER.length;
    }

    function sync(form) {
        var hidden = form.querySelector('input[name="address"]');
        if (!hidden) return;
        var parts = Array.prototype.slice.call(form.querySelectorAll('.addr-part'))
            .sort(function (a, b) { return rank(a) - rank(b); })
            .map(function (el) { return el.value.trim(); })
            .filter(Boolean);
        hidden.value = parts.join(', ');
    }

    document.addEventListener('input', function (e) {
        if (!e.target.classList || !e.target.classList.contains('addr-part')) return;
        var form = e.target.closest('form');
        if (form) sync(form);
    });

    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (form instanceof HTMLFormElement && form.querySelector('.addr-part')) sync(form);
    }, true);

    function syncAll() {
        document.querySelectorAll('form').forEach(function (form) {
            if (form.querySelector('.addr-part')) sync(form);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', syncAll);
    } else {
        syncAll();
    }

    // Modals rendered per-row are already in the DOM at load time, but
    // re-sync on show in case a modal's fields were populated dynamically.
    document.addEventListener('shown.bs.modal', function (e) { syncAll(); });
})();
