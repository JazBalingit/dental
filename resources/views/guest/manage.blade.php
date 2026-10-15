<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <link rel="icon" type="image/png" href="/images/puspus_logo.png">
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="robots" content="noindex">
  <title>Manage Appointment • Dental Clinic</title>
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

  @php
    $a = $appointment;
    $p = $a->patientInfo;
    $statusClass = match ($a->Status) {
      'Pending' => 'is-pending',
      'Approved' => 'is-approved',
      'Completed' => 'is-completed',
      default => 'is-closed',
    };
    $statusLabel = $a->Status === 'Approved' ? 'Approved' : $a->Status;
    $rs = $a->pendingReschedule;
    $hasAccount = $a->BookedByEmail && \App\Models\UserAccount::where('Email', $a->BookedByEmail)->exists();
  @endphp

  <main class="auth-main">
    <div class="auth-card gm-card w-100">
      @include('partials.flash-toasts', ['topOffset' => '80px'])

      @if (session('just_booked'))
        <div class="text-center mb-3">
          <div class="auth-emblem" style="background:var(--brand-50);"><i class="bi bi-envelope-check" style="font-size:1.8rem;color:var(--brand-700);"></i></div>
          <h2 class="auth-title mb-1">Request sent!</h2>
          <p class="auth-subtitle mb-0">The clinic will review it and email you once it's approved. You can come back to this page any time from the link in your email.</p>
        </div>
      @else
        <div class="text-center mb-3">
          <div class="auth-emblem"><img src="/images/puspus_logo.png" alt="Pus-Pus Britanico Dental Clinic"></div>
          <h2 class="auth-title mb-1">Your Appointment</h2>
          <p class="auth-subtitle mb-0">View, cancel or reschedule — no login needed.</p>
        </div>
      @endif

      <div class="d-flex align-items-center flex-wrap gap-2">
        <span class="gm-status {{ $statusClass }}"><i class="bi bi-circle-fill" style="font-size:.5rem;"></i> {{ $statusLabel }}</span>
        @if ($a->BookedForOther && $p)
          <span class="for-badge"><i class="bi bi-person-heart"></i> For: {{ $p->full_name }}{{ $p->Relationship ? " ({$p->Relationship})" : '' }}</span>
        @endif
      </div>

      <div class="gm-schedule">
        <div class="small text-uppercase" style="opacity:.8;letter-spacing:.06em;">Appointment schedule</div>
        <div class="gm-date">{{ $a->AppointmentDate->format('l, F j, Y') }}</div>
        <div class="gm-time">{{ $a->time_range_label }} · {{ $a->duration_label }}</div>
      </div>

      <div class="gm-rows">
        <div class="gm-row"><div class="gm-k">Patient</div><div class="gm-v">{{ $p?->full_name ?? '—' }}</div></div>
        @if ($a->BookedForOther)
          <div class="gm-row"><div class="gm-k">Booked by</div><div class="gm-v">{{ $a->booked_by_label }}</div></div>
        @endif
        <div class="gm-row"><div class="gm-k">Service(s)</div><div class="gm-v">{{ $a->TypeOfAppointment ?: ($a->service->ServiceName ?? '—') }}</div></div>
        <div class="gm-row"><div class="gm-k">Dentist</div><div class="gm-v">{{ $a->dentist_name }}</div></div>
        @if ($a->PatientNotes)
          <div class="gm-row"><div class="gm-k">Your note</div><div class="gm-v" style="font-weight:500;white-space:pre-wrap;">{{ $a->PatientNotes }}</div></div>
        @endif
        @if ($a->attachments->isNotEmpty())
          <div class="gm-row"><div class="gm-k">Photos</div><div class="gm-v">{{ $a->attachments->count() }} attached</div></div>
        @endif
        @if (!$isActive && $a->DeclineReason)
          <div class="gm-row"><div class="gm-k">Reason</div><div class="gm-v">{{ $a->DeclineReason }}</div></div>
        @endif
      </div>

      {{-- The clinic proposed a new time — the patient decides. --}}
      @if ($isActive && $rs)
        <div class="gm-proposal">
          <div class="fw-bold" style="color:#6b4b00;"><i class="bi bi-calendar-event me-1"></i> The clinic proposed a new time</div>
          <div class="mt-1"><strong>{{ $rs->when_label }}</strong> with {{ $rs->dentist?->display_name ?? $a->dentist_name }}</div>
          @if ($rs->Reason)<div class="small text-muted-2">Reason: {{ $rs->Reason }}</div>@endif
          <div class="small" style="color:#8a6100;">Please answer by {{ $rs->ExpiresAt->format('M j, g:i A') }} — otherwise your original time stays.</div>
          <div class="d-flex flex-wrap gap-2 mt-2">
            <form method="POST" action="{{ route('manage.proposal.accept', $a->ManageToken) }}" class="m-0">@csrf
              <button type="submit" class="btn btn-brand btn-sm"><i class="bi bi-check2 me-1"></i> Accept new time</button>
            </form>
            <form method="POST" action="{{ route('manage.proposal.decline', $a->ManageToken) }}" class="m-0">@csrf
              <button type="submit" class="btn btn-ghost btn-sm"><i class="bi bi-arrow-counterclockwise me-1"></i> Keep original time</button>
            </form>
          </div>
        </div>
      @endif

      @if ($isActive)
        <div class="gm-actions">
          @if ($a->can_reschedule)
            <a href="{{ route('manage.reschedule', $a->ManageToken) }}" class="btn btn-brand"><i class="bi bi-calendar2-week me-1"></i> {{ $rs ? 'Choose another time' : 'Reschedule' }}</a>
          @endif
          <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#gmCancelModal"><i class="bi bi-x-circle me-1"></i> Cancel appointment</button>
        </div>
        @if (!$a->can_reschedule)
          {{-- Pending/Approved but the start time has already passed today. --}}
          <p class="small text-muted-2 mt-3 mb-0"><i class="bi bi-info-circle me-1"></i> This appointment's time has already started, so it can't be rescheduled online. Please call the clinic if you need a new time.</p>
        @elseif ($a->Status === 'Pending')
          <p class="small text-muted-2 mt-3 mb-0"><i class="bi bi-hourglass-split me-1"></i> Waiting for the clinic to approve. We'll email you as soon as it's reviewed.</p>
        @else
          <p class="small text-muted-2 mt-3 mb-0"><i class="bi bi-info-circle me-1"></i> Need a different time? Rescheduling sends your new time back to the clinic for approval.</p>
        @endif
      @else
        <div class="gm-actions">
          <a href="{{ route('landingPage') }}#appointment" class="btn btn-brand"><i class="bi bi-calendar-plus me-1"></i> Book another appointment</a>
        </div>
      @endif

      @if (!$hasAccount && $a->BookedByEmail)
        <div class="mt-4 p-3 rounded-3" style="background:#f1fcf0;">
          <div class="fw-semibold small" style="color:var(--brand-800);"><i class="bi bi-person-plus me-1"></i> See all your visits in one place</div>
          <div class="small text-muted-2">Create a free account with <strong class="gb-email-wrap">{{ $a->BookedByEmail }}</strong> — this booking and your dental records will already be there.
            <a href="{{ route('signup', ['email' => $a->BookedByEmail]) }}" class="fw-semibold">Create an account</a></div>
        </div>
      @endif

      <p class="text-center small text-muted-2 mt-4 mb-0">
        Keep this page's link private — anyone with it can manage this appointment.
        <br><a href="{{ route('manage.lookup') }}">Find my other bookings</a>
      </p>
    </div>
  </main>

  @if ($isActive)
    <div class="modal fade" id="gmCancelModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <form method="POST" action="{{ route('manage.cancel', $a->ManageToken) }}">
            @csrf
            <div class="modal-header">
              <h5 class="modal-title"><i class="bi bi-exclamation-triangle me-2" style="color:#ef4444"></i>Cancel appointment?</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <p class="mb-2">Your <strong>{{ $a->AppointmentDate->format('M j') }} · {{ $a->TypeOfAppointment }}</strong> appointment will be cancelled and the time released. This can't be undone.</p>
              <label class="form-label small fw-semibold" for="gmReason">Reason <span class="text-muted-2 fw-normal">(optional)</span></label>
              <textarea class="form-control" id="gmReason" name="reason" rows="2" maxlength="500" placeholder="e.g. I can't make it that day"></textarea>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Keep it</button>
              <button type="submit" class="btn btn-danger"><i class="bi bi-x-circle me-1"></i> Yes, cancel</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  @endif

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
