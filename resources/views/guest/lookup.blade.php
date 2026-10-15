<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <link rel="icon" type="image/png" href="/images/puspus_logo.png">
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="robots" content="noindex">
  <title>Manage My Booking • Dental Clinic</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
  <link rel="stylesheet" href="{{ asset('css/guest-booking.css') }}">
  <link rel="stylesheet" href="{{ asset('css/modals.css') }}">
  <link rel="stylesheet" href="{{ asset('css/mobile.css') }}">
</head>

<body class="auth-page">
  @include('login_signup.partials.auth-navbar')

  <main class="auth-main">
    <div class="auth-card gm-card w-100">
      <div class="text-center mb-3">
        <div class="auth-emblem"><img src="/images/puspus_logo.png" alt="Pus-Pus Britanico Dental Clinic"></div>
        <h2 class="auth-title mb-1">Manage My Booking</h2>
        <p class="auth-subtitle mb-0">Booked without an account? Find your appointments with the email you used.</p>
      </div>

      @include('partials.flash-toasts', ['topOffset' => '20px', 'suppressErrors' => true])

      @if ($step === 'email')
        <form method="POST" action="{{ route('manage.lookup.send') }}">
          @csrf
          <div class="row g-3 align-items-end">
            @include('guest.partials.field', ['f' => ['name' => 'email', 'label' => 'Email you booked with', 'icon' => 'bi-envelope', 'type' => 'email', 'placeholder' => 'you@example.com', 'required' => true, 'col' => 'col-md-8']])
            <div class="col-md-4"><button type="submit" class="btn btn-brand w-100"><i class="bi bi-send me-1"></i> Send code</button></div>
          </div>
          <p class="small text-muted-2 mt-3 mb-0"><i class="bi bi-lightbulb me-1"></i> Tip: every email we sent you has a <strong>Manage my appointment</strong> button that opens your appointment directly.</p>
        </form>

      @elseif ($step === 'code')
        <p class="small text-muted-2 mb-2">We sent a 6-digit code to <strong>{{ $pendingEmail }}</strong>. It expires in 5 minutes.</p>
        <form method="POST" action="{{ route('manage.lookup.verify') }}">
          @csrf
          <div class="row g-3 align-items-end">
            <div class="col-md-8">
              <label class="form-label" for="mlCode">Verification code</label>
              <input type="text" name="code" id="mlCode" class="form-control text-center gb-code" inputmode="numeric" maxlength="6" pattern="[0-9]*" placeholder="••••••" autocomplete="one-time-code" required autofocus>
            </div>
            <div class="col-md-4"><button type="submit" class="btn btn-brand w-100"><i class="bi bi-shield-check me-1"></i> Verify</button></div>
          </div>
        </form>
        <div class="d-flex flex-wrap gap-3 mt-3 small">
          <form method="POST" action="{{ route('manage.lookup.send') }}" class="m-0">@csrf
            <input type="hidden" name="email" value="{{ $pendingEmail }}"><input type="hidden" name="resend" value="1">
            <button type="submit" class="btn btn-link btn-sm p-0" {{ $retryAfter ? 'disabled' : '' }}>Resend code{{ $retryAfter ? " (in {$retryAfter}s)" : '' }}</button>
          </form>
          <form method="POST" action="{{ route('manage.lookup.reset') }}" class="m-0">@csrf
            <button type="submit" class="btn btn-link btn-sm p-0">Use a different email</button>
          </form>
        </div>

      @else
        <div class="gb-verified mb-3">
          <i class="bi bi-patch-check-fill"></i> <span>{{ $verifiedEmail }}</span>
          <form method="POST" action="{{ route('manage.lookup.reset') }}" class="m-0 ms-auto">@csrf
            <button type="submit" class="btn btn-link btn-sm p-0">Sign out</button>
          </form>
        </div>

        @forelse ($appointments as $appt)
          @php
            $active = in_array($appt->Status, ['Pending', 'Approved'], true);
            $cls = match ($appt->Status) { 'Pending' => 'is-pending', 'Approved' => 'is-approved', 'Completed' => 'is-completed', default => 'is-closed' };
          @endphp
          <a class="gm-list-item" href="{{ $appt->manage_url }}">
            <div class="gm-list-date"><b>{{ $appt->AppointmentDate->format('d') }}</b><span>{{ $appt->AppointmentDate->format('M Y') }}</span></div>
            <div class="flex-grow-1" style="min-width:0;">
              <div class="fw-semibold text-truncate">{{ $appt->TypeOfAppointment ?: ($appt->service->ServiceName ?? 'Appointment') }}</div>
              <div class="small text-muted-2">{{ $appt->patientInfo?->full_name }} · {{ $appt->time_range_label }} · {{ $appt->dentist_name }}</div>
            </div>
            <span class="gm-status {{ $cls }}">{{ $appt->Status }}</span>
            <i class="bi bi-chevron-right text-muted-2"></i>
          </a>
        @empty
          <div class="text-center text-muted-2 py-4">
            <i class="bi bi-calendar-x fs-2 d-block mb-2"></i>
            No appointments were booked with this email.
          </div>
        @endforelse

        <div class="text-center mt-3">
          <a href="{{ route('landingPage') }}#appointment" class="btn btn-brand"><i class="bi bi-calendar-plus me-1"></i> Book an appointment</a>
        </div>
      @endif
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
