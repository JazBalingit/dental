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
