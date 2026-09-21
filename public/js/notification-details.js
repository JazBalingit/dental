// Clicking a booked / approved / completed appointment notification opens the
// shared #notifDetailModal with that appointment's details (fetched as an HTML
// fragment), and quietly marks the notification read. The notifications list
// stays open underneath, so closing the details drops you back into it.
(function () {
    var modalEl = document.getElementById('notifDetailModal');
    if (!modalEl) return;

    var bodyEl = document.getElementById('notifDetailBody');
    var subtitleEl = document.getElementById('notifDetailSubtitle');
    var csrf = (document.getElementById('notifDetailCsrf') || {}).value || '';
    var loadToken = 0;

    // Bootstrap only formally supports one modal at a time. The details modal
    // deliberately sits on top of the (still open) notifications modal, so lift
    // it and its backdrop above everything else.
    modalEl.addEventListener('show.bs.modal', function () {
        modalEl.style.zIndex = 1090;
    });
    modalEl.addEventListener('shown.bs.modal', function () {
        var backdrops = document.querySelectorAll('.modal-backdrop');
        if (backdrops.length) backdrops[backdrops.length - 1].style.zIndex = 1085;
    });
    // Closing the top modal makes Bootstrap unlock page scroll even though the
    // notifications modal underneath is still open — put the lock back.
    modalEl.addEventListener('hidden.bs.modal', function () {
        if (document.querySelector('.modal.show')) {
            document.body.classList.add('modal-open');
            document.body.style.overflow = 'hidden';
        }
    });

    function markRead(card) {
        var url = card.getAttribute('data-notif-read-url');
        if (!url) return;
        card.setAttribute('data-notif-read-url', '');
        card.style.background = '';
        fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        }).catch(function () {});
    }

    function open(card) {
        var token = ++loadToken;
        var title = card.querySelector('.notif-text strong');

        subtitleEl.textContent = title ? title.textContent.trim() : '';
        bodyEl.innerHTML = '<div class="text-center text-muted py-4"><div class="spinner-border spinner-border-sm me-2" role="status"></div>Loading appointment details…</div>';
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
        markRead(card);

        fetch(card.getAttribute('data-notif-details-url'), {
            headers: { 'Accept': 'text/html', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        })
            .then(function (r) {
                if (!r.ok) throw new Error('HTTP ' + r.status);
                return r.text();
            })
            .then(function (html) {
                if (token === loadToken) bodyEl.innerHTML = html;
            })
            .catch(function () {
                if (token === loadToken) {
                    bodyEl.innerHTML = '<div class="alert alert-danger mb-0">The appointment details could not be loaded. Please try again.</div>';
                }
            });
    }

    document.addEventListener('click', function (e) {
        var card = e.target.closest('[data-notif-details-url]');
        if (card) open(card);
    });

    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Enter' && e.key !== ' ') return;
        var card = e.target.closest && e.target.closest('[data-notif-details-url]');
        if (card && e.target === card) {
            e.preventDefault();
            open(card);
        }
    });
})();
