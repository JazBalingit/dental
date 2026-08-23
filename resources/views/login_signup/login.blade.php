<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Login • Dental Clinic</title>
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
    $showReset = session('show_reset_form', false);
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
          <h1 class="clinic-name">Welcome back to your dental care home.</h1>
          <p class="clinic-tagline">
            It's wonderful to see you again. We're here to help you maintain a healthy, confident smile in a warm and
            caring environment.
          </p>
        </div>

        <div class="banner-footer">
          <p style="color: rgba(255, 255, 255, 0.8); margin: 0;">&copy; 2026 Puspus-Britanico Dental Clinic. All rights
            reserved.
          </p>
        </div>
      </div>
    </div>
    <div class="col-lg-6">
      <div class="auth-bg">
        <div class="auth-card" style="max-width: 440px;">
          <div><img class="brand-mark" src="/images/puspus_logo.png" alt=""></div>
          <div class="text-center mb-4">
            <h2 class="auth-title mb-1">Welcome back</h2>
            <p class="auth-subtitle mb-0">Sign in to your Dental Clinic account</p>
          </div>

          @include('partials.flash-toasts', ['topOffset' => '20px'])

          <form method="POST" action="{{ route('login.store') }}">
            @csrf
            <div class="mb-3">
              <label class="form-label">Email address</label>
              <div class="input-icon">
                <i class="bi bi-envelope"></i>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}"
                  placeholder="you@clinic.com" required autofocus />
              </div>
            </div>

            <div class="mb-2">
              <label class="form-label">Password</label>
              <div class="pw-field">
                <input type="checkbox" class="pw-toggle-checkbox" id="pwCheckLogin">
                <div class="input-icon">
                  <i class="bi bi-lock"></i>
                  <input type="text" name="password" class="form-control pw-mask" placeholder="••••••••" required
                    autocomplete="current-password" />
                </div>
                <label for="pwCheckLogin" class="pw-eye-btn">
                  <i class="bi bi-eye"></i><i class="bi bi-eye-slash"></i>
                </label>
              </div>
            </div>

            <div class="d-flex justify-content-end align-items-center mb-4">
              <a href="#" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal"
                class="text-decoration-none small fw-semibold" style="color: var(--brand-700);">Forgot
                password?</a>
            </div>
            <button type="submit" class="btn btn-brand w-100 mb-3">Sign In</button>

            <p class="text-center mb-0 text-muted-2 small">
              Don't have an account?
              <a href="{{ route('signup') }}" class="text-decoration-none fw-semibold"
                style="color: var(--brand-700);">Create
                one</a>
            </p>
          </form>
        </div>
      </div>
    </div>
  </div>

  {{-- ===================== FORGOT PASSWORD MODAL ===================== --}}
  {{-- Opens two ways: (1) clicking "Forgot password?" via Bootstrap's own
  data-bs-toggle (shows step 1 by default), or (2) server-rendered
  already-open via the `show_reset_form` session flag after the code
  is sent — same no-custom-JS trick used on the signup page. --}}
  @if ($showReset)
    <div class="modal-backdrop fade show"></div>
  @endif

  <div class="modal fade {{ $showReset ? 'show' : '' }}" id="forgotPasswordModal" tabindex="-1"
    aria-hidden="{{ $showReset ? 'false' : 'true' }}" style="{{ $showReset ? 'display:block;' : '' }}">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header border-0 pb-0">
          <h5 class="modal-title fw-semibold">
            {{ $showReset ? 'Enter your code' : 'Forgot your password?' }}
          </h5>
          <form method="POST" action="{{ route('password.cancel') }}" class="m-0" style="margin-left: auto;">
            @csrf
            <button type="submit" class="btn-close"
              style="background-color: transparent; border: 0; padding: 0.25em; margin: 0; box-shadow: none;"
              aria-label="Close"></button>
          </form>
        </div>
        <div class="modal-body pt-2">


          @if ($showReset)
            <p class="small text-muted-2 mb-3">
              We sent a 6-digit code to <strong>{{ session('reset_email') }}</strong>.
            </p>
          @else
            <p class="small text-muted-2 mb-3">
              Enter the email on your account and we'll send you a code to reset your password.
            </p>
          @endif
          @if ($errors->any())
            <div class="alert alert-danger py-2 small">
              <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif
          @if (session('reset_sent'))
            <div class="alert alert-success py-2 small">Code sent! Check your inbox (and spam folder).</div>
          @endif
          @if (session('reset_resent'))
            <div class="alert alert-success py-2 small">A new code has been sent.</div>
          @endif
          @if (session('reset_expired'))
            <div class="alert alert-warning py-2 small">Your session expired. Please enter your email again.</div>
          @endif
          @if (session('reset_error'))
            <div class="alert alert-danger py-2 small">{{ session('reset_error') }}</div>
          @endif

          @unless ($showReset)
            {{-- STEP 1: email --}}
            <form method="POST" action="{{ route('password.email') }}">
              @csrf
              <label class="form-label">Email address</label>
              <div class="input-icon">
                <i class="bi bi-envelope"></i>
                <input type="email" name="email" class="form-control" placeholder="you@clinic.com" required />
              </div>
              <button type="submit" class="btn btn-brand w-100 mt-3">Send Reset Code</button>
            </form>
          @else
            {{-- STEP 2: code + new password --}}
            <form method="POST" action="{{ route('password.update') }}" class="mb-2">
              @csrf
              <label class="form-label">Verification code</label>
              <input type="text" name="code" class="form-control text-center mb-3" maxlength="6" inputmode="numeric"
                pattern="[0-9]*" placeholder="••••••" required style="letter-spacing: 6px; font-size: 1.25rem;">

              <label class="form-label">New password</label>
              <div class="pw-field mb-3">
                <input type="checkbox" class="pw-toggle-checkbox" id="pwCheckNew">
                <div class="input-icon">
                  <i class="bi bi-lock"></i>
                  <input type="text" name="password" class="form-control pw-mask" placeholder="••••••••" required
                    minlength="8" autocomplete="new-password" />
                </div>
                <label for="pwCheckNew" class="pw-eye-btn">
                  <i class="bi bi-eye"></i><i class="bi bi-eye-slash"></i>
                </label>
              </div>

              <label class="form-label">Confirm new password</label>
              <div class="pw-field mb-3">
                <input type="checkbox" class="pw-toggle-checkbox" id="pwCheckNewConfirm">
                <div class="input-icon">
                  <i class="bi bi-shield-lock"></i>
                  <input type="text" name="password_confirmation" class="form-control pw-mask" placeholder="••••••••"
                    required autocomplete="new-password" />
                </div>
                <label for="pwCheckNewConfirm" class="pw-eye-btn">
                  <i class="bi bi-eye"></i><i class="bi bi-eye-slash"></i>
                </label>
              </div>

              <button type="submit" class="btn btn-brand w-100">Reset Password</button>
            </form>

            <form method="POST" action="{{ route('password.resend') }}" class="text-center">
              @csrf
              <span class="small text-muted-2">Didn't receive the code?</span>
              <button type="submit" class="btn btn-link btn-sm p-0 ms-1">Resend</button>
            </form>
          @endunless
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>