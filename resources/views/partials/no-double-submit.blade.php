{{--
    No double clicks, no spamming — on every page (included from
    partials.flash-toasts, which every page with a form already has).

    1. A second click on the SAME button / link / checkbox / chip within
       half a second is ignored (a worn mouse often sends two clicks for one
       press; it also covers JavaScript buttons that aren't forms).
    2. A form can only be sent once: after the first submit its buttons are
       locked and show "Please wait…". They unlock after 8 seconds in case
       the page didn't navigate away (a download, a validation stop), and
       when coming back with the browser's Back button.
       Opt out with data-allow-resubmit on the <form>.
--}}
<script>
    (function () {
        if (window.__noDoubleSubmit) return;
        window.__noDoubleSubmit = true;

        var CLICK_GAP_MS = 500;
        var UNLOCK_MS = 8000;
        var CLICKABLE = 'button, a, [role="button"], input[type="checkbox"], input[type="radio"], input[type="submit"], input[type="button"], label, .book-time-chip, .who-option';

        // 1. Ignore a repeat click on the same element.
        document.addEventListener('click', function (e) {
            var el = e.target.closest && e.target.closest(CLICKABLE);
            if (!el) return;
            var now = Date.now();
            var last = parseInt(el.dataset.lastClick || '0', 10);
            if (now - last < CLICK_GAP_MS) {
                e.preventDefault();
                e.stopImmediatePropagation();
                return;
            }
            el.dataset.lastClick = String(now);
        }, true);

        // 2. Send each form only once. Runs last (bubble phase) so validation
        //    or "are you sure?" handlers can still cancel the submit first.
        document.addEventListener('submit', function (e) {
            var form = e.target;
            if (!(form instanceof HTMLFormElement) || form.hasAttribute('data-allow-resubmit') || form.target === '_blank') return;

            if (form.dataset.submitting === '1') {
                e.preventDefault();
                return;
            }
            if (e.defaultPrevented) return;

            form.dataset.submitting = '1';
            var buttons = Array.prototype.slice.call(form.querySelectorAll('button[type="submit"], button:not([type]), input[type="submit"]'));
            if (e.submitter && buttons.indexOf(e.submitter) === -1) buttons.push(e.submitter);

            // Disable on the next tick, so the clicked button's own name/value is still sent.
            setTimeout(function () {
                buttons.forEach(function (b) {
                    if (b.disabled) return; // already disabled for its own reason — leave it alone
                    b.disabled = true;
                    b.dataset.ndsLocked = '1';
                    if (b === e.submitter && b.tagName === 'BUTTON' && !b.dataset.originalHtml) {
                        b.dataset.originalHtml = b.innerHTML;
                        b.innerHTML = '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span> Please wait…';
                    }
                });
            }, 0);

            setTimeout(function () { unlock(form); }, UNLOCK_MS);
        });

        function unlock(form) {
            form.dataset.submitting = '';
            form.querySelectorAll('[data-original-html]').forEach(function (b) {
                b.innerHTML = b.dataset.originalHtml;
                delete b.dataset.originalHtml;
            });
            form.querySelectorAll('[data-nds-locked]').forEach(function (b) {
                b.disabled = false;
                delete b.dataset.ndsLocked;
            });
        }

        // Back button restores the page from cache with the buttons still locked.
        window.addEventListener('pageshow', function (e) {
            if (e.persisted) document.querySelectorAll('form[data-submitting="1"]').forEach(unlock);
        });
    })();
</script>
