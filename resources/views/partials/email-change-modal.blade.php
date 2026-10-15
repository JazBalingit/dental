{{--
    "Change email" — one copy per page. Any button with data-email-change
    opens it:

      data-email-change                 (required)
      data-user-id="{{ $id }}"          account to change (omit = your own)
      data-current-email="{{ $email }}" shown as the current address
      data-account-label="Dr. Jane Cruz" optional, shown in the subtitle

    Step 1: type the new email → a 6-digit code is sent to it.
    Step 2: enter the code → the email changes and the page reloads.
    Backed by EmailChangeController (JSON). If the trigger sits inside
    another open modal, that one is closed first and reopened on Cancel.
--}}
<div class="modal fade" id="emailChangeModal" tabindex="-1" aria-labelledby="emailChangeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <h5 class="modal-title fw-semibold" id="emailChangeModalLabel"><i class="bi bi-envelope-at"></i> Change Email</h5>
          <div class="small text-muted" id="emailChangeSubtitle">We'll send a verification code to the new address.</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Current email</label>
          <div class="input-icon"><i class="bi bi-envelope"></i><input type="text" class="form-control" id="emailChangeCurrent" disabled></div>
        </div>

        {{-- Step 1 --}}
        <div id="emailChangeStep1">
          <label class="form-label" for="emailChangeNew">New email</label>
          <div class="input-icon"><i class="bi bi-envelope-plus"></i><input type="email" class="form-control" id="emailChangeNew"
              maxlength="150" placeholder="name@example.com" autocomplete="email"></div>
        </div>

        {{-- Step 2 --}}
        <div id="emailChangeStep2" hidden>
          <p class="small text-muted-2 mb-2" id="emailChangeSentMsg"></p>
          <label class="form-label" for="emailChangeCode">Verification code</label>
          <input type="text" class="form-control text-center" id="emailChangeCode" maxlength="6" inputmode="numeric"
            pattern="[0-9]*" placeholder="••••••" autocomplete="one-time-code" style="letter-spacing:4px;">
          <div class="d-flex justify-content-between align-items-center mt-2 small">
            <button type="button" class="btn btn-link p-0 small" id="emailChangeEditBtn">Use a different email</button>
            <button type="button" class="btn btn-link p-0 small" id="emailChangeResendBtn" disabled>Resend code</button>
          </div>
        </div>

        <div class="field-error mt-2" id="emailChangeError" hidden><i class="bi bi-exclamation-circle-fill"></i> <span></span></div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-brand px-3" id="emailChangeSendBtn"><i class="bi bi-send me-1"></i> Send Code</button>
        <button type="button" class="btn btn-brand px-3" id="emailChangeVerifyBtn" hidden><i class="bi bi-check2-circle me-1"></i> Verify &amp; Change</button>
      </div>
    </div>
  </div>
</div>

<script>
  (function () {
    var modalEl = document.getElementById('emailChangeModal');
    if (!modalEl || modalEl.dataset.ready) return;
    modalEl.dataset.ready = '1';

    var sendUrl = @json(route('emailChange.send'));
    var verifyUrl = @json(route('emailChange.verify'));
    var csrf = @json(csrf_token());

    var $ = function (id) { return document.getElementById(id); };
    var step1 = $('emailChangeStep1'), step2 = $('emailChangeStep2');
    var currentInput = $('emailChangeCurrent'), newInput = $('emailChangeNew'), codeInput = $('emailChangeCode');
    var sendBtn = $('emailChangeSendBtn'), verifyBtn = $('emailChangeVerifyBtn');
    var resendBtn = $('emailChangeResendBtn'), editBtn = $('emailChangeEditBtn');
    var errorBox = $('emailChangeError'), sentMsg = $('emailChangeSentMsg'), subtitle = $('emailChangeSubtitle');

    var userId = '', returnTo = null, cooldownTimer = null, changed = false;

    function showError(msg) { errorBox.querySelector('span').textContent = msg; errorBox.hidden = !msg; }

    function setStep(n) {
      step1.hidden = n !== 1; step2.hidden = n !== 2;
      sendBtn.hidden = n !== 1; verifyBtn.hidden = n !== 2;
      showError('');
      (n === 1 ? newInput : codeInput).focus();
    }

    function startCooldown(seconds) {
      clearInterval(cooldownTimer);
      var left = seconds || 60;
      resendBtn.disabled = true;
      resendBtn.textContent = 'Resend code (' + left + 's)';
      cooldownTimer = setInterval(function () {
        left--;
        if (left <= 0) { clearInterval(cooldownTimer); resendBtn.disabled = false; resendBtn.textContent = 'Resend code'; return; }
        resendBtn.textContent = 'Resend code (' + left + 's)';
      }, 1000);
    }

    function post(url, body, btn) {
      var label = btn.innerHTML;
      btn.disabled = true;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Please wait…';
      body.append('_token', csrf);
      if (userId) body.append('user_id', userId);
      return fetch(url, { method: 'POST', body: body, headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
        .then(function (r) { return r.json().catch(function () { return {}; }).then(function (d) { d._status = r.status; return d; }); })
        .catch(function () { return { ok: false, message: 'Network error. Please try again.' }; })
        .finally(function () { btn.disabled = false; btn.innerHTML = label; });
    }

    function firstError(d) {
      if (d.errors) { var k = Object.keys(d.errors)[0]; if (k) return d.errors[k][0]; }
      if (d._status === 419) return 'Your session expired. Please reload the page.';
      return d.message || 'Something went wrong. Please try again.';
    }

    function sendCode(btn) {
      var email = newInput.value.trim();
      if (!email) { showError('Please enter the new email.'); newInput.focus(); return; }
      var fd = new FormData(); fd.append('new_email', email);
      post(sendUrl, fd, btn).then(function (d) {
        if (!d.ok) { showError(firstError(d)); if (d.retryAfter) startCooldown(d.retryAfter); return; }
        sentMsg.textContent = d.message;
        codeInput.value = '';
        setStep(2);
        startCooldown(d.retryAfter);
      });
    }

    sendBtn.addEventListener('click', function () { sendCode(sendBtn); });
    resendBtn.addEventListener('click', function () { sendCode(resendBtn); });
    editBtn.addEventListener('click', function () { setStep(1); });
    newInput.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); sendBtn.click(); } });
    codeInput.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); verifyBtn.click(); } });

    verifyBtn.addEventListener('click', function () {
      var code = codeInput.value.trim();
      if (!/^\d{6}$/.test(code)) { showError('Enter the 6-digit code from the email.'); codeInput.focus(); return; }
      var fd = new FormData(); fd.append('code', code);
      post(verifyUrl, fd, verifyBtn).then(function (d) {
        if (!d.ok) { showError(firstError(d)); if (d.restart) setStep(1); return; }
        changed = true;
        window.location.reload();
      });
    });

    function open(trigger) {
      userId = trigger.dataset.userId || '';
      currentInput.value = trigger.dataset.currentEmail || '';
      subtitle.textContent = trigger.dataset.accountLabel
        ? 'For ' + trigger.dataset.accountLabel + ' — a verification code goes to the new address.'
        : "We'll send a verification code to the new address.";
      newInput.value = '';
      codeInput.value = '';
      clearInterval(cooldownTimer);
      resendBtn.disabled = false; resendBtn.textContent = 'Resend code';
      setStep(1);
      bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }

    document.addEventListener('click', function (e) {
      var trigger = e.target.closest('[data-email-change]');
      if (!trigger) return;
      e.preventDefault();
      // Only one Bootstrap modal at a time: close the one the button sits
      // in, open this once it's gone, and return to it on Cancel.
      var parent = trigger.closest('.modal.show');
      returnTo = parent;
      if (parent) {
        parent.addEventListener('hidden.bs.modal', function () { open(trigger); }, { once: true });
        bootstrap.Modal.getOrCreateInstance(parent).hide();
      } else {
        open(trigger);
      }
    });

    modalEl.addEventListener('shown.bs.modal', function () { newInput.focus(); });
    modalEl.addEventListener('hidden.bs.modal', function () {
      clearInterval(cooldownTimer);
      if (returnTo && !changed) bootstrap.Modal.getOrCreateInstance(returnTo).show();
      returnTo = null;
    });
  })();
</script>
