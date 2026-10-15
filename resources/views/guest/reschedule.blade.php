<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <link rel="icon" type="image/png" href="/images/puspus_logo.png">
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="robots" content="noindex">
  <title>Reschedule Appointment • Dental Clinic</title>
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

  <main class="auth-main" style="align-items:flex-start;">
    <div class="container" style="max-width:1100px;">
      @include('partials.flash-toasts', ['topOffset' => '20px'])
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <div>
          <h2 class="auth-title mb-0" style="text-align:left;">Reschedule Appointment</h2>
          <div class="small text-muted-2">Choose a new date and time that works for you.</div>
        </div>
        <a href="{{ route('manage.show', $token) }}" class="btn btn-ghost"><i class="bi bi-arrow-left me-1"></i> Back</a>
      </div>

      @include('partials.reschedule-current-card', ['appointment' => $appointment, 'audience' => 'patient'])

      <div class="auth-card w-100" style="max-width:none;">
        @include('partials.booking-calendar', [
          'calendarMode' => 'post',
          'bookMode' => 'reschedule',
          'bookInteractive' => true,
          'bookBaseUrl' => route('manage.reschedule', $token),
          'bookHash' => '',
          'bookConfirmAction' => route('manage.reschedule.store', $token),
          'bookPreselect' => $appointment->services->pluck('ServiceID')->all() ?: array_filter([$appointment->ServiceID]),
          'bookSubmitLabel' => 'Move My Appointment',
        ])
      </div>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
