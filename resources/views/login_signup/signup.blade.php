<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <link rel="icon" type="image/png" href="/images/puspus_logo.png">
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Sign Up • Dental Clinic</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link
    href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
    integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modals.css') }}">
    <link rel="stylesheet" href="{{ asset('css/mobile.css') }}">
</head>

<body class="auth-page">
  @php
    $showOtp = session('show_otp', false);
  @endphp

  @include('login_signup.partials.auth-navbar')

  <main class="auth-main">
    <div class="auth-card signup-card">
      <div class="text-center mb-3">
        <div class="auth-emblem"><img src="/images/puspus_logo.png" alt="Pus-Pus Britanico Dental Clinic"></div>
        <h2 class="auth-title mb-1">Create your account</h2>
        <p class="auth-subtitle mb-0">Join the Dental Clinic patient portal</p>
      </div>

      @include('partials.flash-toasts', ['topOffset' => '20px', 'suppressErrors' => true])

          @php
            // Which wizard step a field belongs to, so a validation failure
            // reopens the form on the step that actually has the error
            // instead of always dumping the patient back to step one.
            $fieldStepMap = [
              'last_name' => 'personal', 'first_name' => 'personal', 'middle_name' => 'personal',
              'birthdate' => 'personal', 'gender' => 'personal', 'religion' => 'personal',
              'nationality' => 'personal', 'occupation' => 'personal',
              'address' => 'address', 'addr_street' => 'address', 'addr_barangay' => 'address',
              'addr_city' => 'address', 'addr_province' => 'address', 'email' => 'address', 'phone' => 'address',
              'guardian_name' => 'minor', 'guardian_occupation' => 'minor',
              'password' => 'password', 'password_confirmation' => 'password', 'agree_terms' => 'password',
            ];
            $initialStep = 'personal';
            foreach ($errors->keys() as $erroredField) {
              if (isset($fieldStepMap[$erroredField])) {
                $initialStep = $fieldStepMap[$erroredField];
                break;
              }
            }
          @endphp

          <form method="POST" action="{{ route('register.store') }}" id="signupWizard" data-initial-step="{{ $initialStep }}">
            @csrf

            <div class="wizard-progress"><div class="wizard-progress-bar" id="wizardProgressBar"></div></div>
            <div class="wizard-step-label" id="wizardStepLabel"></div>

            {{-- ===================== STEP 1: PERSONAL INFORMATION ===================== --}}
            <div class="wizard-step" data-step="personal">
              <div class="section-label">Personal Information</div>
              <div class="row g-3 mb-3">
                <div class="col-md-6 col-lg-3">
                  <label class="form-label">Last name</label>
                  <div class="input-icon @error('last_name') has-error @enderror"><i class="bi bi-person"></i><input name="last_name" class="form-control"
                      value="{{ old('last_name') }}" placeholder="Last name" required />
                  </div>
                  @error('last_name') <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div> @enderror
                </div>
                <div class="col-md-6 col-lg-3">
                  <label class="form-label">First name</label>
                  <div class="input-icon @error('first_name') has-error @enderror"><i class="bi bi-person"></i><input name="first_name" class="form-control"
                      value="{{ old('first_name') }}" placeholder="First name" required />
                  </div>
                  @error('first_name') <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div> @enderror
                </div>
                <div class="col-md-6 col-lg-3">
                  <label class="form-label">Middle name</label>
                  <div class="input-icon @error('middle_name') has-error @enderror"><i class="bi bi-person"></i><input name="middle_name" class="form-control"
                      value="{{ old('middle_name') }}" placeholder="Middle name" />
                  </div>
                  @error('middle_name') <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div> @enderror
                </div>
                <div class="col-md-6 col-lg-3">
                  <label class="form-label">Birthdate</label>
                  <div class="input-icon @error('birthdate') has-error @enderror"><i class="bi bi-calendar-event"></i><input type="date" name="birthdate"
                      id="signupBirthdate" class="form-control" value="{{ old('birthdate') }}" max="2023-12-31" required
                      data-age-target="#signupAge" />
                  </div>
                  @error('birthdate') <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div> @enderror
                  <div class="small text-muted-2 mt-1">Must be born on or before Dec 31, 2023.</div>
                </div>
                <div class="col-md-6 col-lg-3">
                  <label class="form-label">Age</label>
                  <div class="input-icon"><i class="bi bi-person-vcard"></i><input type="text" id="signupAge" class="form-control" placeholder="—" disabled /></div>
                </div>

                <div class="col-md-6 col-lg-3">
                  <label class="form-label">Gender</label>
                  <div class="input-icon @error('gender') has-error @enderror">
                    <select name="gender" class="form-select" required>
                      <option value="" disabled {{ old('gender') ? '' : 'selected' }}>Select gender</option>
                      <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>Male</option>
                      <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Female</option>
                      <option value="other" {{ old('gender') === 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                  </div>
                  @error('gender') <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div> @enderror
                </div>
                <div class="col-md-6 col-lg-3">
                  <label class="form-label">Religion</label>
                  <div class="input-icon @error('religion') has-error @enderror"><i class="bi bi-book"></i><input name="religion" class="form-control"
                      value="{{ old('religion') }}" placeholder="Catholic" />
                  </div>
                  @error('religion') <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div> @enderror
                </div>
                <div class="col-md-6 col-lg-3">
                  <label class="form-label">Nationality</label>
                  <div class="input-icon @error('nationality') has-error @enderror"><i class="bi bi-flag"></i><input name="nationality" class="form-control"
                      value="{{ old('nationality') }}" placeholder="Filipino" required />
                  </div>
                  @error('nationality') <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div> @enderror
                </div>
                <div class="col-md-6 col-lg-3">
                  <label class="form-label">Occupation <span class="text-muted">(optional)</span></label>
                  <div class="input-icon @error('occupation') has-error @enderror"><i class="bi bi-briefcase"></i><input name="occupation" class="form-control"
                      value="{{ old('occupation') }}" placeholder="Occupation" />
                  </div>
                  @error('occupation') <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div> @enderror
                </div>
              </div>
            </div>

            {{-- ===================== STEP 2: HOME ADDRESS & CONTACT DETAILS ===================== --}}
            <div class="wizard-step" data-step="address" hidden>
              <div class="section-label">Home Address</div>
              <div class="row g-3 mb-3">
                <div class="col-md-6">
                  <label class="form-label">Street / House No.</label>
                  <div class="input-icon"><i class="bi bi-signpost-2"></i><input name="addr_street" class="form-control addr-part"
                      value="{{ old('addr_street') }}" placeholder="123 Sample St." />
                  </div>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Barangay</label>
                  <div class="input-icon"><i class="bi bi-geo"></i><input name="addr_barangay" class="form-control addr-part"
                      value="{{ old('addr_barangay') }}" placeholder="Barangay" />
                  </div>
                </div>
                <div class="col-md-6">
                  <label class="form-label">City / Municipality</label>
                  <div class="input-icon"><i class="bi bi-buildings"></i><input name="addr_city" class="form-control addr-part"
                      value="{{ old('addr_city') }}" placeholder="City / Municipality" required />
                  </div>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Province</label>
                  <div class="input-icon"><i class="bi bi-map"></i><input name="addr_province" class="form-control addr-part"
                      value="{{ old('addr_province') }}" placeholder="Province" required />
                  </div>
                </div>
                @error('address') <div class="col-12"><div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div></div> @enderror
              </div>
              <input type="hidden" name="address" id="signupAddress" value="{{ old('address') }}">

              <div class="section-label">Contact Details</div>
              <div class="row g-3 mb-3">
                <div class="col-sm-6">
                  <label class="form-label">Email address</label>
                  <div class="input-icon @error('email') has-error @enderror"><i class="bi bi-envelope"></i><input type="email" name="email"
                      class="form-control" value="{{ old('email') }}" placeholder="you@clinic.com" required /></div>
                  @error('email') <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div> @enderror
                </div>
                <div class="col-sm-6">
                  <label class="form-label">Cell/Mobile number</label>
                  <div class="input-icon @error('phone') has-error @enderror"><i class="bi bi-telephone"></i><input type="tel" name="phone" id="signupPhone" class="form-control"
                      value="{{ old('phone') }}" placeholder="09XXXXXXXXX" inputmode="numeric" pattern="[0-9]{11}" title="Enter an 11-digit mobile number" required /></div>
                  @error('phone') <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div> @enderror
                </div>
              </div>
            </div>

            {{-- ===================== STEP 3: FOR MINORS (only if under 18) ===================== --}}
            <div class="wizard-step" data-step="minor" hidden>
              <div class="section-label">For Minors</div>
              <p class="text-muted small mb-3">The patient is under 18 based on the birthdate provided — a parent or guardian needs to be on file.</p>
              <div class="row g-3 mb-3">
                <div class="col-sm-6">
                  <label class="form-label">Parent/Guardian's name</label>
                  <div class="input-icon @error('guardian_name') has-error @enderror"><i class="bi bi-person-heart"></i><input name="guardian_name"
                      class="form-control" value="{{ old('guardian_name') }}" placeholder="Guardian's name" />
                  </div>
                  @error('guardian_name') <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div> @enderror
                </div>
                <div class="col-sm-6">
                  <label class="form-label">Guardian's occupation <span class="text-muted">(optional)</span></label>
                  <div class="input-icon @error('guardian_occupation') has-error @enderror"><i class="bi bi-briefcase"></i><input name="guardian_occupation"
                      class="form-control" value="{{ old('guardian_occupation') }}" placeholder="Occupation" /></div>
                  @error('guardian_occupation') <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div> @enderror
                </div>
              </div>
            </div>

            {{-- ===================== STEP 4: PASSWORD ===================== --}}
            <div class="wizard-step" data-step="password" hidden>
              <div class="section-label">Credentials</div>
              <div class="row g-3 mb-2">
                <div class="col-md-6">
                  <label class="form-label">Password</label>
                  <div class="pw-field">
                    <input type="checkbox" class="pw-toggle-checkbox" id="pwCheckSignup">
                    <div class="input-icon @error('password') has-error @enderror" id="signupPwWrap"><i class="bi bi-lock"></i><input type="text" name="password"
                        id="signupPw" class="form-control pw-mask" placeholder="••••••••" required minlength="8"
                        value="{{ old('password') }}" autocomplete="new-password" /></div>
                    <label for="pwCheckSignup" class="pw-eye-btn">
                      <i class="bi bi-eye"></i><i class="bi bi-eye-slash"></i>
                    </label>
                  </div>
                  @error('password') <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                  <label class="form-label">Confirm password</label>
                  <div class="pw-field">
                    <input type="checkbox" class="pw-toggle-checkbox" id="pwCheckSignupConfirm">
                    <div class="input-icon @error('password') has-error @enderror" id="signupConfirmPwWrap"><i class="bi bi-shield-lock"></i><input type="text"
                        name="password_confirmation" id="signupConfirmPw" class="form-control pw-mask" placeholder="••••••••" required
                        value="{{ old('password_confirmation') }}" autocomplete="new-password" /></div>
                    <label for="pwCheckSignupConfirm" class="pw-eye-btn">
                      <i class="bi bi-eye"></i><i class="bi bi-eye-slash"></i>
                    </label>
                  </div>
                  <div class="field-error" id="signupConfirmPwError" hidden><i class="bi bi-exclamation-circle-fill"></i> Passwords do not match.</div>
                </div>
              </div>

              <div class="auth-tips mb-3">
                <span class="auth-tips-title"><i class="fa-solid fa-lightbulb"></i> Security Tips</span>
                <ul>
                  <li><i class="fa-solid fa-circle-check"></i> Mix uppercase, lowercase, numbers &amp; symbols</li>
                  <li><i class="fa-solid fa-circle-check"></i> Never reuse a password from another site</li>
                  <li><i class="fa-solid fa-circle-check"></i> At least 8 characters long</li>
                  <li><i class="fa-solid fa-circle-check"></i> Avoid your name or birthday</li>
                </ul>
              </div>

              <div class="form-check mb-2">
                <input type="checkbox" class="form-check-input" name="agree_terms" id="agreeTerms" value="1"
                  {{ old('agree_terms') ? 'checked' : '' }} required>
                <label class="form-check-label small text-muted-2" for="agreeTerms">
                  I agree to the
                  <a href="#" data-bs-toggle="modal" data-bs-target="#privacyPolicyModal">Privacy Policy</a>
                  and
                  <a href="#" data-bs-toggle="modal" data-bs-target="#legalTermsModal">Terms</a>.
                </label>
                @error('agree_terms') <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div> @enderror
              </div>
            </div>

            <div class="wizard-nav">
              <div>
                <a href="{{ route('login') }}" class="btn btn-ghost" id="wizardCancelLink">Back to Login</a>
                <button type="button" class="btn btn-ghost" id="wizardBackBtn" hidden>Back</button>
              </div>
              <div>
                <button type="button" class="btn btn-brand" id="wizardNextBtn">Next</button>
                <button type="submit" class="btn btn-brand" id="wizardSubmitBtn" hidden>Create Account</button>
              </div>
            </div>
          </form>
    </div>
  </main>

  <script>
    (function () {
      var form = document.getElementById('signupWizard');
      if (!form) return;

      var STEP_ORDER = ['personal', 'address', 'minor', 'password'];
      var steps = Array.prototype.slice.call(form.querySelectorAll('.wizard-step'));
      var backBtn = document.getElementById('wizardBackBtn');
      var nextBtn = document.getElementById('wizardNextBtn');
      var submitBtn = document.getElementById('wizardSubmitBtn');
      var cancelLink = document.getElementById('wizardCancelLink');
      var stepLabel = document.getElementById('wizardStepLabel');
      var progressBar = document.getElementById('wizardProgressBar');
      var birthdateInput = document.getElementById('signupBirthdate');
      var addressHidden = document.getElementById('signupAddress');

      var currentStepId = form.dataset.initialStep || 'personal';
      if (STEP_ORDER.indexOf(currentStepId) === -1) currentStepId = 'personal';

      function isMinor() {
        if (!birthdateInput.value) return false;
        var dob = new Date(birthdateInput.value);
        if (isNaN(dob.getTime())) return false;
        var today = new Date();
        var age = today.getFullYear() - dob.getFullYear();
        var m = today.getMonth() - dob.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) age--;
        return age < 18;
      }

      function activeStepIds() {
        return STEP_ORDER.filter(function (id) {
          return id !== 'minor' || isMinor();
        });
      }

      function syncAddress() {
        var parts = ['addr_street', 'addr_barangay', 'addr_city', 'addr_province'].map(function (name) {
          var el = form.querySelector('[name="' + name + '"]');
          return el ? el.value.trim() : '';
        }).filter(Boolean);
        addressHidden.value = parts.join(', ');
      }

      function stepById(id) {
        return steps.filter(function (s) { return s.dataset.step === id; })[0];
      }

      function validateStep(stepEl) {
        var fields = stepEl.querySelectorAll('input, select, textarea');
        for (var i = 0; i < fields.length; i++) {
          if (!fields[i].checkValidity()) {
            fields[i].reportValidity();
            return false;
          }
        }
        return true;
      }

      function render() {
        var active = activeStepIds();
        if (active.indexOf(currentStepId) === -1) currentStepId = active[0];

        steps.forEach(function (s) { s.hidden = s.dataset.step !== currentStepId; });

        var idx = active.indexOf(currentStepId);
        stepLabel.textContent = 'Step ' + (idx + 1) + ' of ' + active.length;
        progressBar.style.width = (((idx + 1) / active.length) * 100) + '%';

        var isFirst = idx === 0;
        var isLast = idx === active.length - 1;
        backBtn.hidden = isFirst;
        cancelLink.hidden = !isFirst;
        nextBtn.hidden = isLast;
        submitBtn.hidden = !isLast;
      }

      nextBtn.addEventListener('click', function () {
        var active = activeStepIds();
        var idx = active.indexOf(currentStepId);
        if (!validateStep(stepById(currentStepId))) return;
        if (idx < active.length - 1) {
          currentStepId = active[idx + 1];
          render();
          form.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
      });

      backBtn.addEventListener('click', function () {
        var active = activeStepIds();
        var idx = active.indexOf(currentStepId);
        if (idx > 0) {
          currentStepId = active[idx - 1];
          render();
          form.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
      });

      form.querySelectorAll('.addr-part').forEach(function (el) {
        el.addEventListener('input', syncAddress);
      });

      var phoneInput = document.getElementById('signupPhone');
      if (phoneInput) {
        phoneInput.addEventListener('input', function () {
          phoneInput.value = phoneInput.value.replace(/\D/g, '').slice(0, 11);
        });
      }
      form.addEventListener('submit', function (e) {
        syncAddress();
        if (pwInput && confirmInput && !checkPasswordsMatch()) {
          e.preventDefault();
          confirmInput.focus();
        }
      });

      var pwInput = document.getElementById('signupPw');
      var pwWrap = document.getElementById('signupPwWrap');
      var confirmInput = document.getElementById('signupConfirmPw');
      var confirmWrap = document.getElementById('signupConfirmPwWrap');
      var confirmError = document.getElementById('signupConfirmPwError');

      function passwordsMismatched() {
        return confirmInput.value.length > 0 && pwInput.value !== confirmInput.value;
      }

      function checkPasswordsMatch() {
        var mismatched = passwordsMismatched();
        pwWrap.classList.toggle('has-error', mismatched);
        confirmWrap.classList.toggle('has-error', mismatched);
        confirmError.hidden = !mismatched;
        return !mismatched;
      }

      syncAddress();
      render();
    })();
  </script>

  {{-- ===================== PRIVACY POLICY / LEGAL TERMS MODALS ===================== --}}
  <div class="modal fade" id="privacyPolicyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title fw-semibold">Privacy Policy</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" style="max-height:60vh; overflow-y:auto; white-space:pre-wrap;">{{ $privacyPolicy ?: 'This clinic has not published a privacy policy yet.' }}</div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="legalTermsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title fw-semibold">Legal Terms</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" style="max-height:60vh; overflow-y:auto; white-space:pre-wrap;">{{ $legalTerms ?: 'This clinic has not published its legal terms yet.' }}</div>
      </div>
    </div>
  </div>

  {{-- ===================== OTP VERIFICATION MODAL ===================== --}}
  {{-- Server-rendered "open" state — no custom JS needed. When `show_otp` is
  flashed from the controller, this modal appears already-open on load. --}}
  @if ($showOtp)
    <div class="modal-backdrop fade show"></div>
  @endif

  <div class="modal fade {{ $showOtp ? 'show' : '' }}" id="otpModal" tabindex="-1"
    aria-hidden="{{ $showOtp ? 'false' : 'true' }}" style="{{ $showOtp ? 'display:block;' : '' }}">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title fw-semibold">Verify your email</h5>
          <form method="POST" action="{{ route('register.cancel') }}" class="m-0" style="margin-left: auto;">
            @csrf
            <button type="submit" class="btn-close"
              style="background-color: transparent; border: 0; padding: 0.25em; margin: 0; box-shadow: none;"
              aria-label="Close"></button>
          </form>
        </div>
        <div class="modal-body">
          <p class="small text-muted-2 mb-3">
            We sent a 6-digit code to
            <strong>{{ session('otp_email') }}</strong>. Enter it below to finish creating your account.
          </p>

          @if (session('otp_sent'))
            <div class="alert alert-success py-2 small">Code sent! Check your inbox (and spam folder).</div>
          @endif
          @if (session('otp_resent'))
            <div class="alert alert-success py-2 small">A new code has been sent.</div>
          @endif
          @if (session('otp_expired'))
            <div class="alert alert-warning py-2 small">Your session expired. Please fill out the form again.</div>
          @endif
          <form method="POST" action="{{ route('register.verify') }}" class="mb-2">
            @csrf
            <label class="form-label">Verification code</label>
            <input type="text" name="code" class="form-control text-center {{ session('otp_error') ? 'has-error' : '' }}" maxlength="6" inputmode="numeric"
              pattern="[0-9]*" placeholder="••••••" required value="{{ old('code') }}" style="letter-spacing: 6px; font-size: 1.25rem;">
            @if (session('otp_error'))
              <div class="field-error mt-1"><i class="bi bi-exclamation-circle-fill"></i> {{ session('otp_error') }}</div>
            @endif
            <button type="submit" class="btn btn-brand w-100 mt-3">Verify &amp; Create Account</button>
          </form>

          <form method="POST" action="{{ route('register.resend') }}" class="text-center">
            @csrf
            <span class="small text-muted-2">Didn't receive the code?</span>
            <button type="submit" class="btn btn-link btn-sm p-0 ms-1" style="color:#0f7a23;"
              data-resend-cooldown="{{ $otpRetrySeconds }}">Resend</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="{{ asset('js/reopen-modal.js') }}"></script>
  <script src="{{ asset('js/field-restrictions.js') }}"></script>
  <script src="{{ asset('js/birthdate-age.js') }}"></script>
  <script src="{{ asset('js/otp-resend-cooldown.js') }}"></script>
</body>

</html>
