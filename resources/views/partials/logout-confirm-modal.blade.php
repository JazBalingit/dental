{{--
    "Log out?" confirmation for every account type — super admin, admin,
    staff, patient. Any logout <form> opts in with a `data-logout-form`
    attribute; its submit is held until the user confirms here.

    Included from each place that renders a logout form (the sidebar
    account badges, the patient navbar menu, the setup and verify-email
    screens). @once keeps it to one copy even when a page renders two of
    those (e.g. the patient sidebar + navbar). The modal is moved to <body>
    on load: it's often included inside the sidebar, which is a transformed
    offcanvas on phones — a position:fixed modal inside a transformed
    ancestor would be trapped in that box instead of covering the screen.
--}}
@once
<div class="modal fade" id="logoutConfirmModal" tabindex="-1" aria-labelledby="logoutConfirmTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-semibold" id="logoutConfirmTitle"><i class="bi bi-box-arrow-right me-2"></i>Log out?</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p class="mb-0 text-muted-2">Are you sure you want to log out? You'll need to sign in again to get back to your account.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="logoutConfirmBtn"><i class="bi bi-box-arrow-right me-1"></i> Log Out</button>
      </div>
    </div>
  </div>
</div>

<script>
  (function () {
    var modalEl = document.getElementById('logoutConfirmModal');
    if (!modalEl) return;
    document.body.appendChild(modalEl);

    var pendingForm = null;

    // Created lazily: Bootstrap's <script> may come after this partial.
    function modal() { return bootstrap.Modal.getOrCreateInstance(modalEl); }

    // Capture phase: runs before partials.no-double-submit's bubble-phase
    // listener, so that one sees the submit as cancelled and doesn't lock
    // the Log Out button with "Please wait…" if the user backs out.
    document.addEventListener('submit', function (e) {
      var form = e.target;
      if (!(form instanceof HTMLFormElement) || !form.hasAttribute('data-logout-form')) return;
      if (form.dataset.logoutConfirmed) return;
      e.preventDefault();
      pendingForm = form;
      // Close the account dropdown/sidebar sheet it came from first.
      var openMenu = form.closest('.dropdown-menu.show');
      if (openMenu && openMenu.previousElementSibling && window.bootstrap) {
        bootstrap.Dropdown.getOrCreateInstance(openMenu.previousElementSibling).hide();
      }
      modal().show();
    }, true);

    document.getElementById('logoutConfirmBtn').addEventListener('click', function () {
      if (!pendingForm) return;
      this.disabled = true;
      pendingForm.dataset.logoutConfirmed = '1';
      // Native submit() doesn't fire 'submit' again, so no loop.
      pendingForm.submit();
    });

    modalEl.addEventListener('hidden.bs.modal', function () { pendingForm = null; });
  })();
</script>
@endonce
