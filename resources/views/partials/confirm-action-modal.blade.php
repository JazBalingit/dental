{{--
    Reusable confirmation modal for any state-changing action that shouldn't
    fire on a single misclick (archive/unarchive, delete, etc.). One copy per
    page — every trigger button just needs data-bs-toggle="modal"
    data-bs-target="#confirmActionModal" plus these data attributes:

      data-action-url    (required) — the form's POST target
      data-title         — modal heading (default "Are you sure?")
      data-message       — modal body text (default "This action cannot be undone.")
      data-confirm-label — submit button text (default "Confirm")
      data-confirm-class — submit button class, e.g. "btn-pill-archive" or "btn-pill-cancel" (default "btn-pill-archive")
      data-method        — "DELETE" to spoof via @method (default: plain POST)
--}}
<div class="modal fade" id="confirmActionModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-semibold" id="confirmActionTitle">Are you sure?</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body pt-2">
        <p class="mb-0 text-muted-2" id="confirmActionMessage">This action cannot be undone.</p>
      </div>
      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-pill btn-pill-cancel" data-bs-dismiss="modal">Cancel</button>
        <form method="POST" action="" id="confirmActionForm" class="d-inline">
          @csrf
          <span id="confirmActionMethodField"></span>
          <button type="submit" class="btn btn-pill btn-pill-archive" id="confirmActionSubmit">Confirm</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
  // A form re-render after a validation error re-opens its modal by giving
  // it the "show" class + inline display:block directly in the Blade markup
  // (so it's visible on first paint, no flash of a closed modal). But
  // Bootstrap's own Modal instance is never told about that — its internal
  // _isShown flag only flips to true inside its own show(), so the X/Cancel
  // buttons' data-bs-dismiss="modal" call hide(), which just no-ops because
  // Bootstrap doesn't think anything is open. Re-run the real show() for any
  // modal that's already visible so Bootstrap's state (and its own backdrop)
  // catches up, and dismiss buttons start working immediately.
  (function () {
    function reopen() {
      if (typeof bootstrap === 'undefined') return;
      document.querySelectorAll('.modal.show').forEach(function (el) {
        bootstrap.Modal.getOrCreateInstance(el).show();
      });
    }
    // This partial is sometimes included before the Bootstrap <script> tag
    // (order varies per page), so wait for DOMContentLoaded — by then every
    // synchronous script earlier in the document, bootstrap.bundle.min.js
    // included, has already run.
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', reopen);
    } else {
      reopen();
    }
  })();

  (function () {
    var modalEl = document.getElementById('confirmActionModal');
    if (!modalEl) return;

    modalEl.addEventListener('show.bs.modal', function (event) {
      var trigger = event.relatedTarget;
      if (!trigger) return;

      var form = document.getElementById('confirmActionForm');
      form.action = trigger.getAttribute('data-action-url') || '#';

      document.getElementById('confirmActionTitle').textContent = trigger.getAttribute('data-title') || 'Are you sure?';
      document.getElementById('confirmActionMessage').textContent = trigger.getAttribute('data-message') || 'This action cannot be undone.';

      var submitBtn = document.getElementById('confirmActionSubmit');
      submitBtn.textContent = trigger.getAttribute('data-confirm-label') || 'Confirm';
      submitBtn.className = 'btn btn-pill ' + (trigger.getAttribute('data-confirm-class') || 'btn-pill-archive');

      var methodField = document.getElementById('confirmActionMethodField');
      var method = trigger.getAttribute('data-method');
      methodField.innerHTML = (method && method.toUpperCase() !== 'POST')
        ? '<input type="hidden" name="_method" value="' + method.toUpperCase() + '">'
        : '';
    });
  })();
</script>

{{--
    Generic yes/no modal for confirming an action that isn't a simple
    data-attribute POST (an already-built form submit, or a branch inside
    other JS). Usage:

      if (!(await appConfirm({ message: 'Delete this?' }))) return;

    Or, to gate an existing <form> submit with zero JS of your own, add:
      <form data-confirm-title="..." data-confirm-message="...">
--}}
<div class="modal fade" id="appConfirmModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-semibold" id="appConfirmTitle">Are you sure?</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body pt-2">
        <p class="mb-0 text-muted-2" id="appConfirmMessage">This action cannot be undone.</p>
      </div>
      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-pill btn-pill-cancel" data-bs-dismiss="modal" id="appConfirmCancelBtn">Cancel</button>
        <button type="button" class="btn btn-pill btn-pill-archive" id="appConfirmOkBtn">Confirm</button>
      </div>
    </div>
  </div>
</div>

<script>
  (function () {
    var modalEl = document.getElementById('appConfirmModal');
    if (!modalEl) return;

    var titleEl = document.getElementById('appConfirmTitle');
    var messageEl = document.getElementById('appConfirmMessage');
    var okBtn = document.getElementById('appConfirmOkBtn');
    var resolveFn = null;
    var bsModal = null;

    // Constructed lazily (not at parse time) so this partial works no
    // matter whether it's included before or after the Bootstrap <script>.
    function getModal() {
      if (!bsModal) bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
      return bsModal;
    }

    function settle(result) {
      if (resolveFn) { var r = resolveFn; resolveFn = null; r(result); }
    }

    okBtn.addEventListener('click', function () {
      getModal().hide();
      settle(true);
    });
    modalEl.addEventListener('hidden.bs.modal', function () {
      settle(false);
    });

    window.appConfirm = function (options) {
      options = options || {};
      titleEl.textContent = options.title || 'Are you sure?';
      messageEl.textContent = options.message || 'This action cannot be undone.';
      okBtn.textContent = options.confirmLabel || 'Confirm';
      okBtn.className = 'btn btn-pill ' + (options.confirmClass || 'btn-pill-archive');
      return new Promise(function (resolve) {
        resolveFn = resolve;
        getModal().show();
      });
    };

    // Any form can opt into a confirm-before-submit gate purely via markup:
    // <form data-confirm-title="..." data-confirm-message="...">
    document.addEventListener('submit', function (e) {
      var form = e.target;
      if (!(form instanceof HTMLFormElement)) return;
      if (!form.hasAttribute('data-confirm-message') && !form.hasAttribute('data-confirm-title')) return;
      if (form.dataset.confirmed) { delete form.dataset.confirmed; return; }

      e.preventDefault();
      window.appConfirm({
        title: form.getAttribute('data-confirm-title'),
        message: form.getAttribute('data-confirm-message'),
        confirmLabel: form.getAttribute('data-confirm-label'),
        confirmClass: form.getAttribute('data-confirm-class'),
      }).then(function (ok) {
        if (!ok) return;
        form.dataset.confirmed = '1';
        // form.submit() (native call) does not re-dispatch the 'submit'
        // event, so this cannot loop back into this same listener.
        form.submit();
      });
    });
  })();
</script>
