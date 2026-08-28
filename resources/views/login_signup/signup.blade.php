<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
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
</head>

<body>
  @php
    $showOtp = session('show_otp', false);
  @endphp

  <div class="row">
    <div class="col-lg-6 column-1">
      <div class="banner-wrap position-fixed">
        <img class="auth-banner-img" src="/images/dental_chair.jpg" alt="">
        <div class="banner-overlay"></div>

        <div class="banner-brand">
          <div class="banner-home sub mb-2"><a class="sub text-decoration-none" href="{{ route('landingPage') }}"><i
                class="fa-solid fa-arrow-left" style="margin-right: 8px;"></i>Back to Home</a></div>
          <div class="brand">
            <div><img class="logo" src="/images/puspus_logo.png" alt=""></div>
            <div>
              <div class="name">PUSPUS-BRITANICO</div>
              <div class="sub">Dental Clinic</div>
            </div>
          </div>
        </div>

        <div class="banner-text">
          <h1 class="clinic-name">Join us for a healthier, brighter smile.</h1>
          <p class="clinic-tagline">
            Create an account today to book appointments, access your dental records, receive treatment updates, and
            enjoy a more convenient dental care experience.
          </p>
        </div>

        <div class="banner-footer">
          <p style="color: rgba(255, 255, 255, 0.8); margin: 0;">&copy; 2026 Pus-Pus Britanico Dental Clinic. All rights
            reserved.
          </p>
        </div>
      </div>
    </div>
    <div class="col-lg-6">
      <div class="auth-bg">
        <div class="auth-card" style="max-width: 760px;">
          <div class="text-center mb-4">
            <div><img class="brand-mark" src="/images/puspus_logo.png" alt=""></div>
            <h2 class="auth-title mb-1">Create your account</h2>
            <p class="auth-subtitle mb-0">Join the Dental Clinic patient portal</p>
          </div>

          @include('partials.flash-toasts', ['topOffset' => '20px'])

          <form method="POST" action="{{ route('register.store') }}">
            @csrf
            <div class="section-label">Patient Information</div>
            <div class="row g-3 mb-3">
              <div class="col-md-4">
                <label class="form-label">Last name</label>
                <div class="input-icon"><i class="bi bi-person"></i><input name="last_name" class="form-control"
                    value="{{ old('last_name') }}" placeholder="Last name" required />
                </div>
              </div>
              <div class="col-md-4">
                <label class="form-label">First name</label>
                <div class="input-icon"><i class="bi bi-person"></i><input name="first_name" class="form-control"
                    value="{{ old('first_name') }}" placeholder="First name" required />
                </div>
              </div>
              <div class="col-md-4">
                <label class="form-label">Middle name</label>
                <div class="input-icon"><i class="bi bi-person"></i><input name="middle_name" class="form-control"
                    value="{{ old('middle_name') }}" placeholder="Middle name" />
                </div>
              </div>

              <div class="col-md-6">
                <label class="form-label">Birthdate</label>
                <div class="input-icon"><i class="bi bi-calendar-event"></i><input type="date" name="birthdate"
                    class="form-control" value="{{ old('birthdate') }}" required />
                </div>
              </div>
              <div class="col-md-6">
                <label class="form-label">Gender</label>
                <div class="input-icon">
                  <select name="gender" class="form-select" required>
                    <option value="" disabled {{ old('gender') ? '' : 'selected' }}>Select gender</option>
                    <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>Male</option>
                    <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Female</option>
                    <option value="other" {{ old('gender') === 'other' ? 'selected' : '' }}>Other</option>
                  </select>
                </div>
              </div>

              <div class="col-md-6">
                <label class="form-label">Religion</label>
                <div class="input-icon"><i class="bi bi-book"></i><input name="religion" class="form-control"
                    value="{{ old('religion') }}" placeholder="Catholic" />
                </div>
              </div>
              <div class="col-md-6">
                <label class="form-label">Nationality</label>
                <div class="input-icon"><i class="bi bi-flag"></i><input name="nationality" class="form-control"
                    value="{{ old('nationality') }}" placeholder="Filipino" required />
                </div>
              </div>

              <div class="col-12">
                <label class="form-label">Occupation <span class="text-muted">(optional)</span></label>
                <div class="input-icon"><i class="bi bi-briefcase"></i><input name="occupation" class="form-control"
                    value="{{ old('occupation') }}" placeholder="Occupation" />
                </div>
              </div>

              <div class="col-12">
                <label class="form-label">Home address</label>
                <div class="input-icon"><i class="bi bi-geo-alt"></i><input name="address" class="form-control"
                    value="{{ old('address') }}" placeholder="Street, City, Province" required /></div>
              </div>
            </div>

            <div class="section-label mt-2">Contact Details</div>
            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <label class="form-label">Email address</label>
                <div class="input-icon"><i class="bi bi-envelope"></i><input type="email" name="email"
                    class="form-control" value="{{ old('email') }}" placeholder="you@clinic.com" required /></div>
              </div>
              <div class="col-md-6">
                <label class="form-label">Cell/Mobile number</label>
                <div class="input-icon"><i class="bi bi-telephone"></i><input name="phone" class="form-control"
                    value="{{ old('phone') }}" placeholder="+63 9XX XXX XXXX" required /></div>
              </div>
            </div>

            <div class="section-label mt-2">For Minors</div>
            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <label class="form-label">Parent/Guardian's name</label>
                <div class="input-icon"><i class="bi bi-person-heart"></i><input name="guardian_name"
                    class="form-control" value="{{ old('guardian_name') }}" placeholder="Parent/Guardian's name" />
                </div>
              </div>
              <div class="col-md-6">
                <label class="form-label">Parent/Guardian's occupation <span
                    class="text-muted">(optional)</span></label>
                <div class="input-icon"><i class="bi bi-briefcase"></i><input name="guardian_occupation"
                    class="form-control" value="{{ old('guardian_occupation') }}" placeholder="Occupation" /></div>
              </div>
            </div>

            <div class="section-label mt-2">Credentials</div>
            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <label class="form-label">Password</label>
                <div class="pw-field">
                  <input type="checkbox" class="pw-toggle-checkbox" id="pwCheckSignup">
                  <div class="input-icon"><i class="bi bi-lock"></i><input type="text" name="password"
                      class="form-control pw-mask" placeholder="••••••••" required minlength="8"
                      autocomplete="new-password" /></div>
                  <label for="pwCheckSignup" class="pw-eye-btn">
                    <i class="bi bi-eye"></i><i class="bi bi-eye-slash"></i>
                  </label>
                </div>
              </div>
              <div class="col-md-6">
                <label class="form-label">Confirm password</label>
                <div class="pw-field">
                  <input type="checkbox" class="pw-toggle-checkbox" id="pwCheckSignupConfirm">
                  <div class="input-icon"><i class="bi bi-shield-lock"></i><input type="text"
                      name="password_confirmation" class="form-control pw-mask" placeholder="••••••••" required
                      autocomplete="new-password" /></div>
                  <label for="pwCheckSignupConfirm" class="pw-eye-btn">
                    <i class="bi bi-eye"></i><i class="bi bi-eye-slash"></i>
                  </label>
                </div>
              </div>
            </div>

            <div class="form-check mt-3">
              <input type="checkbox" class="form-check-input" name="agree_terms" id="agreeTerms" value="1"
                {{ old('agree_terms') ? 'checked' : '' }} required>
              <label class="form-check-label small text-muted-2" for="agreeTerms">
                I agree to the
                <a href="#" data-bs-toggle="modal" data-bs-target="#privacyPolicyModal">Privacy Policy</a>
                and
                <a href="#" data-bs-toggle="modal" data-bs-target="#legalTermsModal">Terms</a>.
              </label>
            </div>

            <div class="d-flex gap-2 mt-3">
              <a href="{{ route('login') }}" class="btn btn-ghost flex-fill">Back to Login</a>
              <button type="submit" class="btn btn-brand flex-fill">Create Account</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  {{-- ===================== PRIVACY POLICY / LEGAL TERMS MODALS ===================== --}}
  <div class="modal fade" id="privacyPolicyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header border-0 pb-0">
          <h5 class="modal-title fw-semibold">Privacy Policy</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body pt-2" style="max-height:60vh; overflow-y:auto; white-space:pre-wrap;">{{ $privacyPolicy ?: 'This clinic has not published a privacy policy yet.' }}</div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="legalTermsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header border-0 pb-0">
          <h5 class="modal-title fw-semibold">Legal Terms</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body pt-2" style="max-height:60vh; overflow-y:auto; white-space:pre-wrap;">{{ $legalTerms ?: 'This clinic has not published its legal terms yet.' }}</div>
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
        <div class="modal-header border-0 pb-0">
          <h5 class="modal-title fw-semibold">Verify your email</h5>
          <form method="POST" action="{{ route('register.cancel') }}" class="m-0" style="margin-left: auto;">
            @csrf
            <button type="submit" class="btn-close"
              style="background-color: transparent; border: 0; padding: 0.25em; margin: 0; box-shadow: none;"
              aria-label="Close"></button>
          </form>
        </div>
        <div class="modal-body pt-2">
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
          @if (session('otp_error'))
            <div class="alert alert-danger py-2 small">{{ session('otp_error') }}</div>
          @endif

          <form method="POST" action="{{ route('register.verify') }}" class="mb-2">
            @csrf
            <label class="form-label">Verification code</label>
            <input type="text" name="code" class="form-control text-center" maxlength="6" inputmode="numeric"
              pattern="[0-9]*" placeholder="••••••" required style="letter-spacing: 6px; font-size: 1.25rem;">
            <button type="submit" class="btn btn-brand w-100 mt-3">Verify &amp; Create Account</button>
          </form>

          <form method="POST" action="{{ route('register.resend') }}" class="text-center">
            @csrf
            <span class="small text-muted-2">Didn't receive the code?</span>
            <button type="submit" class="btn btn-link btn-sm p-0 ms-1">Resend</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
