{{--
    "Change email" — one copy per page. Any button with data-email-change
    opens it:

      data-email-change                 (required)
      data-user-id="{{ $id }}"          account to change (omit = your own)
      data-current-email="{{ $email }}" shown as the current address
      data-account-label="Dr. Jane Cruz" optional, shown in the subtitle

    Your own account (3 steps):
      1. a code goes to the CURRENT email → enter it (proves it's you)
      2. type the new email → a code goes to it
      3. enter that code → only now does the email change; the page reloads
    Someone else's account (admin): steps 2–3 only.
    Closing or refreshing part-way changes nothing; the next try starts at
    step 1. Backed by EmailChangeController (JSON). If the trigger sits
    inside another open modal, that one is closed first and reopened on Cancel.
--}}
<div class="modal fade" id="emailChangeModal" tabindex="-1" aria-labelledby="emailChangeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <h5 class="modal-title fw-semibold" id="emailChangeModalLabel"><i class="bi bi-envelope-at"></i> Change Email</h5>
          <div class="small text-muted" id="emailChangeSubtitle"></div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="small fw-semibold text-success mb-3" id="emailChangeStepLabel"></div>

        <div class="mb-3">
          <label class="form-label">Current email</label>
          <div class="input-icon"><i class="bi bi-envelope"></i><input type="text" class="form-control" id="emailChangeCurrent" disabled></div>
        </div>

        {{-- Own account, step 1: confirm it's you --}}
        <div id="emailChangeIdentity" hidden>
          <p class="small text-muted-2 mb-0">
            To keep your account safe, we'll first send a 6-digit code to your <strong>current</strong> email.
            Enter it to continue to your new email.
          </p>
          <p class="small text-muted-2 mt-2 mb-0">Can't open your current email anymore? Ask the clinic to change it for you.</p>
        </div>

        {{-- Step: the new email --}}
        <div id="emailChangeNewWrap" hidden>
          <label class="form-label" for="emailChangeNew">New email</label>
          <div class="input-icon"><i class="bi bi-envelope-plus"></i><input type="email" class="form-control" id="emailChangeNew"
              maxlength="150" placeholder="name@example.com" autocomplete="email"></div>
        </div>

        {{-- Step: a code (for the current or the new email) --}}
        <div id="emailChangeCodeWrap" hidden>
          <p class="small text-muted-2 mb-2" id="emailChangeSentMsg"></p>
          <label class="form-label" for="emailChangeCode">Verification code</label>
          <input type="text" class="form-control text-center" id="emailChangeCode" maxlength="6" inputmode="numeric"
            pattern="[0-9]*" placeholder="••••••" autocomplete="one-time-code" style="letter-spacing:4px;">
          <div class="d-flex justify-content-between align-items-center mt-2 small">
            <button type="button" class="btn btn-link p-0 small" id="emailChangeEditBtn">Use a different email</button>
            <button type="button" class="btn btn-link p-0 small ms-auto" id="emailChangeResendBtn" disabled>Resend code</button>
          </div>
        </div>

        <div class="field-error mt-2" id="emailChangeError" hidden><i class="bi bi-exclamation-circle-fill"></i> <span></span></div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-brand px-3" id="emailChangeSendBtn"><i class="bi bi-send me-1"></i> <span>Send Code</span></button>
        <button type="button" class="btn btn-brand px-3" id="emailChangeVerifyBtn" hidden><i class="bi bi-check2-circle me-1"></i> <span>Verify</span></button>
      </div>
    </div>
  </div>
</div>

<script>
  (function () {
    var modalEl = document.getElementById('emailChangeModal');
    if (!modalEl || modalEl.dataset.ready) return;
    modalEl.dataset.ready = '1';

    var urls = {
      sendCurrent: @json(route('emailChange.sendCurrent')),
      verifyCurrent: @json(route('emailChange.verifyCurrent')),
      send: @json(route('emailChange.send')),
      verify: @json(route('emailChange.verify'))
    };
    var csrf = @json(csrf_token());
    var myId = @json((string) session('user_id'));

    var $ = function (id) { return document.getElementById(id); };
    var identityWrap = $('emailChangeIdentity'), newWrap = $('emailChangeNewWrap'), codeWrap = $('emailChangeCodeWrap');
    var currentInput = $('emailChangeCurrent'), newInput = $('emailChangeNew'), codeInput = $('emailChangeCode');
    var sendBtn = $('emailChangeSendBtn'), verifyBtn = $('emailChangeVerifyBtn');
    var resendBtn = $('emailChangeResendBtn'), editBtn = $('emailChangeEditBtn');
    var errorBox = $('emailChangeError'), sentMsg = $('emailChangeSentMsg'), subtitle = $('emailChangeSubtitle');
    var stepLabel = $('emailChangeStepLabel');

    // Phases: idSend → idCode (own account only) → new → newCode.
    var userId = '', isSelf = true, phase = 'new', returnTo = null, cooldownTimer = null, changed = false;

    function showError(msg) { errorBox.querySelector('span').textContent = msg; errorBox.hidden = !msg; }

    function setPhase(p) {
      phase = p;
      identityWrap.hidden = p !== 'idSend';
      newWrap.hidden = p !== 'new';
      codeWrap.hidden = p !== 'idCode' && p !== 'newCode';
      sendBtn.hidden = p !== 'idSend' && p !== 'new';
      verifyBtn.hidden = !sendBtn.hidden;
      editBtn.hidden = p !== 'newCode';

      sendBtn.querySelector('span').textContent = p === 'idSend' ? 'Send Code to Current Email' : 'Send Code';
      verifyBtn.querySelector('span').textContent = p === 'idCode' ? 'Verify' : 'Verify & Change';

      var n = { idSend: 1, idCode: 1, new: 2, newCode: 3 }[p];
      var labels = { 1: "Confirm it's you", 2: 'Enter your new email', 3: 'Verify your new email' };
      if (isSelf) {
        stepLabel.textContent = 'Step ' + n + ' of 3 · ' + labels[n];
      } else {
        stepLabel.textContent = 'Step ' + (n - 1) + ' of 2 · ' + (n === 2 ? 'Enter the new email' : 'Verify the new email');
      }

      showError('');
      if (p === 'new') newInput.focus();
      if (p === 'idCode' || p === 'newCode') { codeInput.value = ''; codeInput.focus(); }
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

    // An error that says "start over from step X" moves the modal there,
    // keeping the message visible.
    function handleFail(d) {
      var msg = firstError(d);
      if (d.restart === 'identity' && isSelf) setPhase('idSend');
      else if (d.restart === 'new' || d.restart === true) setPhase('new');
      showError(msg);
      if (d.retryAfter) startCooldown(d.retryAfter);
    }

    function sendCurrentCode(btn) {
      post(urls.sendCurrent, new FormData(), btn).then(function (d) {
        if (!d.ok) { handleFail(d); return; }
        setPhase('idCode');
        sentMsg.textContent = d.message;
        startCooldown(d.retryAfter);
      });
    }

    function sendNewCode(btn) {
      var email = newInput.value.trim();
      if (!email) { setPhase('new'); showError('Please enter the new email.'); return; }
      var fd = new FormData(); fd.append('new_email', email);
      post(urls.send, fd, btn).then(function (d) {
        if (!d.ok) { handleFail(d); return; }
        setPhase('newCode');
        sentMsg.textContent = d.message;
        startCooldown(d.retryAfter);
      });
    }

    sendBtn.addEventListener('click', function () {
      if (phase === 'idSend') sendCurrentCode(sendBtn); else sendNewCode(sendBtn);
    });
    resendBtn.addEventListener('click', function () {
      if (phase === 'idCode') sendCurrentCode(resendBtn); else sendNewCode(resendBtn);
    });
    editBtn.addEventListener('click', function () { setPhase('new'); });
    newInput.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); sendBtn.click(); } });
    codeInput.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); verifyBtn.click(); } });

    verifyBtn.addEventListener('click', function () {
      var code = codeInput.value.trim();
      if (!/^\d{6}$/.test(code)) { showError('Enter the 6-digit code from the email.'); codeInput.focus(); return; }
      var fd = new FormData(); fd.append('code', code);

      if (phase === 'idCode') {
        post(urls.verifyCurrent, fd, verifyBtn).then(function (d) {
          if (!d.ok) { handleFail(d); return; }
          clearInterval(cooldownTimer);
          resendBtn.disabled = false; resendBtn.textContent = 'Resend code';
          newInput.value = '';
          setPhase('new');
        });
        return;
      }

      post(urls.verify, fd, verifyBtn).then(function (d) {
        if (!d.ok) { handleFail(d); return; }
        changed = true;
        window.location.reload();
      });
    });

    function open(trigger) {
      userId = trigger.dataset.userId || '';
      isSelf = !userId || userId === myId;
      currentInput.value = trigger.dataset.currentEmail || '';
      subtitle.textContent = trigger.dataset.accountLabel && !isSelf
        ? 'For ' + trigger.dataset.accountLabel + ' — a verification code goes to the new address.'
        : 'Your current email and your new email are both verified before anything changes.';
      newInput.value = '';
      codeInput.value = '';
      clearInterval(cooldownTimer);
      resendBtn.disabled = false; resendBtn.textContent = 'Resend code';
      setPhase(isSelf ? 'idSend' : 'new');
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

    modalEl.addEventListener('shown.bs.modal', function () {
      if (phase === 'new') newInput.focus(); else sendBtn.focus();
    });
    modalEl.addEventListener('hidden.bs.modal', function () {
      clearInterval(cooldownTimer);
      if (returnTo && !changed) bootstrap.Modal.getOrCreateInstance(returnTo).show();
      returnTo = null;
    });
  })();
</script>
