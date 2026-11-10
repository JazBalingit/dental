<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <link rel="icon" type="image/png" href="/images/puspus_logo.png">
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Verify Your Email • Dental Clinic</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link
    href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
  <link rel="stylesheet" href="{{ asset('css/modals.css') }}">
  <link rel="stylesheet" href="{{ asset('css/mobile.css') }}">
</head>

{{-- Where a patient account created by the clinic (User Accounts → Add
     User) waits until its email is proven. EnsurePatientIsVerified sends
     every other page here; only these forms and Log Out get through. --}}
<body class="auth-page">
  <main class="auth-main">
    <div class="auth-card" style="max-width: 440px;">
      <div class="text-center mb-4">
        <div class="auth-emblem"><img src="/images/puspus_logo.png" alt="Pus-Pus Britanico Dental Clinic"></div>
        <h2 class="auth-title mb-1">Verify your email</h2>
        <p class="auth-subtitle mb-0">
          Your account was created by the clinic. Before you can use it, confirm that
          <strong>{{ $email }}</strong> is your email.
        </p>
      </div>

      @include('partials.flash-toasts', ['topOffset' => '24px', 'suppressErrors' => true])

      @if ($codeSent)
        <form method="POST" action="{{ route('patientVerify.verify') }}" class="mb-3">
          @csrf
          <label class="form-label">Verification code</label>
          <input type="text" name="code" class="form-control text-center mb-1 {{ session('verify_error') || $errors->has('code') ? 'has-error' : '' }}"
            maxlength="6" inputmode="numeric" pattern="[0-9]*" placeholder="••••••" required autofocus
            value="{{ old('code') }}" style="letter-spacing: 6px; font-size: 1.25rem;">
          @if (session('verify_error'))
            <div class="field-error mb-2"><i class="bi bi-exclamation-circle-fill"></i> {{ session('verify_error') }}</div>
          @elseif ($errors->has('code'))
            <div class="field-error mb-2"><i class="bi bi-exclamation-circle-fill"></i> {{ $errors->first('code') }}</div>
          @else
            <div class="small text-muted-2 mb-2">Enter the 6-digit code we sent to your email. It expires in 5 minutes.</div>
          @endif
          <button type="submit" class="btn btn-brand w-100 mt-2">Verify Email</button>
        </form>

        <form method="POST" action="{{ route('patientVerify.send') }}" class="text-center mb-3">
          @csrf
          <span class="small text-muted-2">Didn't receive the code?</span>
          <button type="submit" class="btn btn-link btn-sm p-0 ms-1" style="color:#0f7a23;"
            data-resend-cooldown="{{ $otpRetrySeconds }}">Resend</button>
        </form>
      @else
        <form method="POST" action="{{ route('patientVerify.send') }}" class="mb-3">
          @csrf
          <button type="submit" class="btn btn-brand w-100">Send Verification Code</button>
        </form>
      @endif

      <form method="POST" action="{{ route('logout') }}" class="text-center" data-logout-form>
        @csrf
        <button type="submit" class="btn btn-link btn-sm text-muted-2 p-0">
          <i class="bi bi-box-arrow-right"></i> Log out
        </button>
      </form>
    </div>
  </main>

  @include('partials.logout-confirm-modal')
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="{{ asset('js/otp-resend-cooldown.js') }}"></script>
</body>

</html>
