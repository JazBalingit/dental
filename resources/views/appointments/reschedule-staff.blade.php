<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <link rel="icon" type="image/png" href="/images/puspus_logo.png">
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Reschedule Appointment • Dental Clinic</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link
    href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
  <link rel="stylesheet" href="{{ asset('css/modals.css') }}">
  <link rel="stylesheet" href="{{ asset('css/mobile.css') }}">
</head>

<body>
  <div class="app">
    <aside class="sidebar offcanvas position-sticky" tabindex="-1" id="sidebarOffcanvas">
      <div class="brand">
        <div><img class="logo" src="/images/puspus_logo.png" alt=""></div>
        <div>
          <div class="name">PUS-PUS BRITANICO</div>
          <div class="sub">DENTAL CLINIC</div>
        </div>
      </div>
      {{-- Not a menu entry of its own — keep the section it came from highlighted. --}}
      @include('partials.admin-sidebar-nav', ['active' => request('from') === 'requests' ? 'appointmentApproval' : 'appointments'])
      @include('partials.admin-profile-badge')
    </aside>

    <main>
      <div class="topbar">
        <div class="left">
          <button class="toggle d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas"
            aria-controls="sidebarOffcanvas">
            <i class="bi bi-list"></i>
          </button>
        </div>
        <div class="right">
          @include('partials.admin-notif-dropdown')
        </div>
      </div>

      <div class="content">
        <div class="page-head">
          <div>
            <h2>Reschedule Appointment</h2>
            <div class="crumbs">Propose a new date and time — the patient confirms it.</div>
          </div>
          <a href="{{ $returnTo }}" class="btn-ghost text-decoration-none"><i class="bi bi-arrow-left"></i> Back</a>
        </div>

        @include('partials.flash-toasts')

        @include('partials.reschedule-current-card', ['appointment' => $appointment, 'audience' => 'staff'])

        <div class="card-soft p-0">
          @include('partials.booking-calendar', [
              'calendarMode' => 'post',
              'bookMode' => 'reschedule',
              'bookBaseUrl' => route('appointments.reschedule', $appointment->AppointmentID),
              'bookHash' => '',
              'bookConfirmAction' => route('appointments.reschedule.propose', $appointment->AppointmentID),
              'bookPreselect' => $appointment->services->pluck('ServiceID')->all() ?: array_filter([$appointment->ServiceID]),
              'bookAskReason' => true,
              'bookSubmitLabel' => 'Send Proposal to Patient',
              'bookExtraHidden' => ['from' => request('from') === 'requests' ? 'requests' : 'appointments'],
          ])
        </div>
      </div>
    </main>
  </div>

  @include('partials.admin-notif-modal')

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
