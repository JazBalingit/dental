<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <link rel="icon" type="image/png" href="/images/puspus_logo.png">
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
    <link rel="stylesheet" href="{{ asset('css/mobile.css') }}">
</head>

<body class="auth-page">
  @php
    $showReset = session('show_reset_form', false);

    // A login attempt that actually submitted the form (vs. being redirected
    // here by an auth guard with nothing typed in yet) gets its error shown
    // inline under the password field instead of a top-of-page toast.
    $loginFormError = old('email') !== null ? session('login_error') : null;
  @endphp

  @include('login_signup.partials.auth-navbar')

  <main class="auth-main">
    <div class="auth-card" style="max-width: 440px;">
      <div class="text-center mb-4">
        <div class="auth-emblem"><img src="/images/puspus_logo.png" alt="Pus-Pus Britanico Dental Clinic"></div>
        <h2 class="auth-title mb-1">Welcome back</h2>
        <p class="auth-subtitle mb-0">Sign in to your Dental Clinic account</p>
      </div>

      @include('partials.flash-toasts', ['topOffset' => '20px', 'suppress' => $loginFormError ? ['login_error'] : []])

      <form method="POST" action="{{ route('login.store') }}">
        @csrf
        <div class="mb-3">
          <label class="form-label">Email address</label>
          <div class="input-icon {{ $loginFormError ? 'has-error' : '' }}">
            <i class="bi bi-envelope"></i>
            <input type="email" name="email" class="form-control" value="{{ old('email') }}"
              placeholder="you@clinic.com" required autofocus />
          </div>
        </div>

        <div class="mb-2">
          <label class="form-label">Password</label>
          <div class="pw-field">
            <input type="checkbox" class="pw-toggle-checkbox" id="pwCheckLogin">
            <div class="input-icon {{ $loginFormError ? 'has-error' : '' }}">
              <i class="bi bi-lock"></i>
              <input type="text" name="password" class="form-control pw-mask" placeholder="••••••••" required
                value="{{ old('password') }}" autocomplete="current-password" />
            </div>
            <label for="pwCheckLogin" class="pw-eye-btn">
              <i class="bi bi-eye"></i><i class="bi bi-eye-slash"></i>
            </label>
          </div>
          @if ($loginFormError)
            <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $loginFormError }}</div>
          @endif
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
  </main>

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

          @unless ($showReset)
            {{-- STEP 1: email --}}
            <form method="POST" action="{{ route('password.email') }}">
              @csrf
              <label class="form-label">Email address</label>
              <div class="input-icon @error('email') has-error @enderror">
                <i class="bi bi-envelope"></i>
                <input type="email" name="email" class="form-control" placeholder="you@clinic.com" required
                  value="{{ old('email', session('reset_email')) }}" />
              </div>
              @error('email') <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div> @enderror
              <button type="submit" class="btn btn-brand w-100 mt-3">Send Reset Code</button>
            </form>
          @else
            {{-- STEP 2: code + new password --}}
            <form method="POST" action="{{ route('password.update') }}" class="mb-2">
              @csrf
              <label class="form-label">Verification code</label>
              <input type="text" name="code" class="form-control text-center mb-1 {{ session('reset_error') || $errors->has('code') ? 'has-error' : '' }}" maxlength="6" inputmode="numeric"
                pattern="[0-9]*" placeholder="••••••" required value="{{ old('code') }}" style="letter-spacing: 6px; font-size: 1.25rem;">
              @if (session('reset_error'))
                <div class="field-error mb-2"><i class="bi bi-exclamation-circle-fill"></i> {{ session('reset_error') }}</div>
              @elseif ($errors->has('code'))
                <div class="field-error mb-2"><i class="bi bi-exclamation-circle-fill"></i> {{ $errors->first('code') }}</div>
              @endif

              <label class="form-label">New password</label>
              <div class="pw-field mb-1">
                <input type="checkbox" class="pw-toggle-checkbox" id="pwCheckNew">
                <div class="input-icon @error('password') has-error @enderror">
                  <i class="bi bi-lock"></i>
                  <input type="text" name="password" class="form-control pw-mask" placeholder="••••••••" required
                    minlength="8" value="{{ old('password') }}" autocomplete="new-password" />
                </div>
                <label for="pwCheckNew" class="pw-eye-btn">
                  <i class="bi bi-eye"></i><i class="bi bi-eye-slash"></i>
                </label>
              </div>
              @error('password') <div class="field-error mb-2"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div> @enderror

              <label class="form-label">Confirm new password</label>
              <div class="pw-field mb-3">
                <input type="checkbox" class="pw-toggle-checkbox" id="pwCheckNewConfirm">
                <div class="input-icon @error('password') has-error @enderror">
                  <i class="bi bi-shield-lock"></i>
                  <input type="text" name="password_confirmation" class="form-control pw-mask" placeholder="••••••••"
                    required value="{{ old('password_confirmation') }}" autocomplete="new-password" />
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
