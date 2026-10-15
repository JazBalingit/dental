{{--
    Caps how many services one appointment can cover (Service::MAX_PER_APPOINTMENT).
    Works on every service checklist (patient booking, follow-up and walk-in):
    once the limit is ticked, the remaining options grey out with a short
    note; unticking one frees them again. The server enforces the same cap.
    Safe to include more than once per page.
--}}
<script>
(function () {
    if (window.__serviceLimit) return;
    window.__serviceLimit = true;

    var MAX = {{ \App\Models\Service::MAX_PER_APPOINTMENT }};
    var OPTION = '.book-slot-service-option, .wi-slot-service-option, .wk-service-option';

    // The checklist an option belongs to (one dropdown, or the walk-in list).
    function groupOf(input) {
        return input.closest('.dropdown-menu') || input.closest('[data-service-group]') || input.closest('form') || document.body;
    }

    function refresh(group) {
        var boxes = Array.from(group.querySelectorAll(OPTION));
        if (!boxes.length) return;
        var full = boxes.filter(function (b) { return b.checked; }).length >= MAX;
        boxes.forEach(function (b) {
            var off = full && !b.checked;
            b.disabled = off;
            var row = b.closest('label');
            if (row) { row.style.opacity = off ? '.45' : ''; row.style.cursor = off ? 'not-allowed' : ''; }
        });

        var note = group.querySelector('.service-limit-note');
        if (!note) {
            note = document.createElement('div');
            note.className = 'service-limit-note col-12';
            note.style.cssText = 'font-size:.74rem;color:#64748b;padding:.35rem .6rem .15rem;';
            group.appendChild(note);
        }
        note.textContent = full
            ? 'You\'ve picked the maximum of ' + MAX + ' services for one appointment. Untick one to choose another.'
            : 'Up to ' + MAX + ' services per appointment.';
        note.style.color = full ? '#92400e' : '#64748b';
    }

    document.addEventListener('change', function (e) {
        if (!e.target.matches(OPTION)) return;
        var group = groupOf(e.target);
        // Safety net (e.g. a restored draft): never let more than MAX stay ticked.
        var checked = Array.from(group.querySelectorAll(OPTION)).filter(function (b) { return b.checked; });
        if (checked.length > MAX && e.target.checked) {
            e.target.checked = false;
            e.target.dispatchEvent(new Event('change', { bubbles: true }));
            return;
        }
        refresh(group);
    }, true);

    function refreshAll(root) {
        var seen = new Set();
        (root || document).querySelectorAll(OPTION).forEach(function (b) {
            var g = groupOf(b);
            if (!seen.has(g)) { seen.add(g); refresh(g); }
        });
    }

    // Checklists get reset when a day window reopens, so re-sync after that.
    document.addEventListener('shown.bs.modal', function (e) { refreshAll(e.target); });
    document.addEventListener('shown.bs.dropdown', function (e) { refreshAll(e.target.parentElement); });
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', function () { refreshAll(); });
    else refreshAll();
})();
</script>
