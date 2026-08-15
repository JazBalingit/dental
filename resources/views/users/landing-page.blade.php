<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Pus-Pus Britanico Dental Clinic — Quality & Affordable Dental Care</title>
  <meta name="description"
    content="Pus-Pus Britanico Dental Clinic provides quality and affordable dental care. Book your appointment online — cleaning, braces, surgery, and more.">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
    integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link
    href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="/css/landing.css">
</head>

<body data-bs-spy="scroll" data-bs-target="#navbarSupportedContent" data-bs-offset="90" tabindex="0">
  <!-- NAVBAR -->
  <nav class="navbar navbar-expand-lg navbar-light fixed-top mask-custom shadow-sm">
    <div class="container">
      <a class="navbar-brand d-flex align-items-center" href="#home">
        <img class="logo" src="/images/puspus_logo.png" alt="Pus-Pus Britanico logo">
        <span class="navt ms-1" style="color:#0f7a2d;">PUS-PUS</span>
        <span class="navt ms-2" style="color:#144d25;">BRITANICO</span>
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav mx-auto text-center">
          <li class="nav-item"><a class="nav-link navh" href="#home">Home</a></li>
          <li class="nav-item"><a class="nav-link navh" href="#services">Services</a></li>
          <li class="nav-item"><a class="nav-link navh" href="#how">How It Works</a></li>
          <li class="nav-item"><a class="nav-link navh" href="#about">About</a></li>
          @if (session('user_id'))
            <li class="nav-item"><a class="nav-link navh" href="#appointment">Appointment</a></li>
          @endif
          <li class="nav-item"><a class="nav-link navh" href="#contact">Contact</a></li>
        </ul>
        <ul class="navbar-nav ms-lg-3">
          <li class="nav-item">
            <div class="d-flex justify-content-between">
              <div class="dropdown">
                <button class="icon-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="bi bi-bell"></i><span class="dot"></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end notif-dropdown shadow-sm p-2"
                  style="width: 500px; max-width: 90vw; max-height: 400px; overflow-y: auto;">
                  <li>
                    <h6 class="dropdown-header">Notifications</h6>
                  </li>

                  <li>
                    <a class="dropdown-item rounded d-flex gap-2 align-items-start" href="#">
                      <i class="bi bi-check-circle-fill text-success mt-1"></i>
                      <div>
                        <p class="mb-0 small">
                          <span class="text-muted">06-02-26:</span>
                          Your appointment has been set on <strong>July 3, 2026</strong> at <strong>10:30 AM</strong>.
                        </p>
                        <span class="text-muted" style="font-size: 0.75rem;">2 minutes ago</span>
                      </div>
                    </a>
                  </li>
                  <li>
                    <hr class="dropdown-divider my-1">
                  </li>

                  <li>
                    <a class="dropdown-item rounded d-flex gap-2 align-items-start" href="#">
                      <i class="bi bi-alarm-fill text-primary mt-1"></i>
                      <div>
                        <p class="mb-0 small">
                          <span class="text-muted">06-02-26:</span>
                          Reminder: you have an appointment tomorrow, <strong>July 3, 2026</strong> at <strong>10:30
                            AM</strong>.
                        </p>
                        <span class="text-muted" style="font-size: 0.75rem;">15 minutes ago</span>
                      </div>
                    </a>
                  </li>
                  <li>
                    <hr class="dropdown-divider my-1">
                  </li>

                  <li>
                    <a class="dropdown-item rounded d-flex gap-2 align-items-start" href="#">
                      <i class="bi bi-x-circle-fill text-danger mt-1"></i>
                      <div>
                        <p class="mb-0 small">
                          <span class="text-muted">06-02-26:</span>
                          Your appointment on <strong>July 3, 2026</strong> at <strong>10:30 AM</strong> has been
                          cancelled.
                        </p>
                        <span class="text-muted" style="font-size: 0.75rem;">1 hour ago</span>
                      </div>
                    </a>
                  </li>
                  <li>
                    <hr class="dropdown-divider my-1">
                  </li>

                  <li>
                    <a class="dropdown-item rounded d-flex gap-2 align-items-start" href="#">
                      <i class="bi bi-arrow-repeat text-warning mt-1"></i>
                      <div>
                        <p class="mb-0 small">
                          <span class="text-muted">06-02-26:</span>
                          Your appointment has been rescheduled to <strong>July 5, 2026</strong> at <strong>2:00
                            PM</strong>.
                        </p>
                        <span class="text-muted" style="font-size: 0.75rem;">3 hours ago</span>
                      </div>
                    </a>
                  </li>
                  <li>
                    <hr class="dropdown-divider my-1">
                  </li>

                  <li>
                    <a class="dropdown-item rounded d-flex gap-2 align-items-start" href="#">
                      <i class="bi bi-check-circle-fill text-success mt-1"></i>
                      <div>
                        <p class="mb-0 small">
                          <span class="text-muted">06-02-26:</span>
                          Your appointment on <strong>June 20, 2026</strong> has been marked as completed.
                        </p>
                        <span class="text-muted" style="font-size: 0.75rem;">Yesterday</span>
                      </div>
                    </a>
                  </li>

                  <li>
                    <hr class="dropdown-divider">
                  </li>
                  <li><a class="dropdown-item text-center small" href="#" data-bs-toggle="modal"
                      data-bs-target="#allNotificationsModal">View all notifications</a></li>
                </ul>
              </div>
              @if (session('user_email'))
                <div class="dropdown">
                  <button class="nav-link navh d-flex align-items-center gap-2 border-0 bg-transparent dropdown-toggle"
                    type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-person-circle"></i>
                    <span>{{ session('user_email') }}</span>
                  </button>
                  <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                    <li>
                      <a class="dropdown-item small" href="{{ route('userProfile') }}">
                        <i class="bi bi-person me-2"></i>User Profile
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item small" href="{{ route('settings') }}">
                        <i class="bi bi-gear me-2"></i>Settings
                      </a>
                    </li>
                    <li>
                      <hr class="dropdown-divider">
                    </li>
                    <li>
                      <form method="POST" action="{{ route('logout') }}" class="m-0">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger">
                          <i class="bi bi-box-arrow-right me-1"></i> Log Out
                        </button>
                      </form>
                    </li>
                  </ul>
                </div>
              @else
                <a href="{{ route('login') }}" class="nav-link navh signin-btn">Sign In</a>
                <a href="{{ route('signup') }}" class="nav-link navh signup-btn">Sign Up</a>
              @endif
            </div>
          </li>
        </ul>
      </div>
    </div>
  </nav>
  <!-- HOME / HERO -->
  <section id="home" class="hero">
    <img class="hero-bg-img" src="/images/dental_chair.jpg" alt="">
    <div class="hero-overlay"></div>
    <div class="container">
      <div class="hero-content">
        <span class="hero-eyebrow">Trusted Dental Care</span>
        <h1 class="hero-title">PUS-PUS BRITANICO</h1>
        <p class="lead mb-2">Providing quality and affordable dental care.</p>
        <p>Your smile is our priority. Experience compassionate, professional dental care in a welcoming environment.
        </p>
        <div class="hero-cta">
          <a href="{{ session('user_id') ? '#appointment' : route('login') }}" class="book"><i class="fa-regular fa-calendar-check me-2"></i>{{ session('user_id') ? 'Book Appointment' : 'Sign in to Book' }}</a>
          <a href="#how" class="book-ghost">How It Works</a>
        </div>
      </div>
    </div>
  </section>
  <!-- SERVICES -->
  <section id="services" class="section" style="background: linear-gradient(180deg, #eef9f0 0%, #ffffff 100%);">
    <div class="container">
      <div class="text-center">
        <span class="section-eyebrow">What We Offer</span>
        <h2 class="section-title">Our Services</h2>
        <hr class="section-divider mx-auto">
        <p class="section-intro mx-auto">From routine check-ups to specialized treatments, we deliver complete dental
          care under one roof.</p>
      </div>
      <div class="row row-cols-1 row-cols-md-2 g-4">
        <div class="col">
          <div class="soft-card service-card">
            <div class="service-icon"><i class="fa-solid fa-tooth"></i></div>
            <h5>General Dentistry</h5>
            <ul>
              <li>Oral Prophylaxis (Cleaning)</li>
              <li>Tooth Filling</li>
              <li>Extraction</li>
              <li>Periapical X-ray</li>
              <li>Consultation</li>
            </ul>
          </div>
        </div>
        <div class="col">
          <div class="soft-card service-card">
            <div class="service-icon"><i class="fa-solid fa-teeth"></i></div>
            <h5>Prosthodontic Treatment</h5>
            <ul>
              <li>Dentures</li>
              <li>Crowns</li>
              <li>Retainers</li>
            </ul>
          </div>
        </div>
        <div class="col">
          <div class="soft-card service-card">
            <div class="service-icon"><i class="fa-solid fa-teeth-open"></i></div>
            <h5>Orthodontic Treatment</h5>
            <ul>
              <li>Braces</li>
              <li>Bite Correction</li>
              <li>Alignment Consultation</li>
            </ul>
          </div>
        </div>
        <div class="col">
          <div class="soft-card service-card">
            <div class="service-icon"><i class="fa-solid fa-stethoscope"></i></div>
            <h5>Oral Surgery & Specialized</h5>
            <ul>
              <li>Root Canal Treatment</li>
              <li>Odontectomy (Surgery)</li>
              <li>Fluoride Treatment (Pedo Patients)</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </section>
  <!-- HOW IT WORKS -->
  <section id="how" class="section" style="background: linear-gradient(180deg, #c5f2c277 0%, #d3ffca30 100%);">
    <div class="container">
      <div class="text-center">
        <span class="section-eyebrow">Easy Steps</span>
        <h2 class="section-title">How to Book Your Appointment</h2>
        <hr class="section-divider mx-auto">
        <p class="section-intro mx-auto">Follow these six simple steps to schedule your visit with us — it only takes a
          few minutes.</p>
      </div>
      <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 g-lg-5 mt-2">
        <div class="col">
          <div class="soft-card step-card">
            <span class="step-badge">1</span>
            <div class="step-icon"><i class="fa-solid fa-user-plus"></i></div>
            <h5>Create an Account</h5>
            <p>Sign up with your basic details to get started with online booking.</p>
            <a href="#" class="step-cta"><i class="fa-solid fa-user-plus me-2"></i>Sign Up</a>
          </div>
        </div>
        <div class="col">
          <div class="soft-card step-card">
            <span class="step-badge">2</span>
            <div class="step-icon"><i class="fa-regular fa-calendar"></i></div>
            <h5>Go to Appointments</h5>
            <p>After logging in, open the Appointments page from your dashboard.</p>
          </div>
        </div>
        <div class="col">
          <div class="soft-card step-card">
            <span class="step-badge">3</span>
            <div class="step-icon"><i class="fa-solid fa-tooth"></i></div>
            <h5>Select a Service</h5>
            <p>Choose the dental treatment you need from our list of services.</p>
          </div>
        </div>
        <div class="col">
          <div class="soft-card step-card">
            <span class="step-badge">4</span>
            <div class="step-icon"><i class="fa-regular fa-calendar-days"></i></div>
            <h5>Pick a Date</h5>
            <p>Select a preferred date for your appointment from the available calendar.</p>
          </div>
        </div>
        <div class="col">
          <div class="soft-card step-card">
            <span class="step-badge">5</span>
            <div class="step-icon"><i class="fa-regular fa-clock"></i></div>
            <h5>Pick a Time</h5>
            <p>Choose a time slot that fits your schedule during clinic hours.</p>
          </div>
        </div>
        <div class="col">
          <div class="soft-card step-card">
            <span class="step-badge">6</span>
            <div class="step-icon"><i class="fa-regular fa-circle-check"></i></div>
            <h5>Submit Appointment</h5>
            <p>Review your details and confirm — we'll see you at the clinic!</p>
          </div>
        </div>
      </div>
    </div>
  </section>
  <!-- ABOUT -->
  <section id="about" class="section" style="background: linear-gradient(180deg, #eef9f0 0%, #ffffff 100%);">
    <div class="container">
      <div class="text-center">
        <span class="section-eyebrow">Visit Us</span>
        <h2 class="section-title">About the Clinic</h2>
        <hr class="section-divider mx-auto">
      </div>
      <div class="row align-items-center g-5">
        <div class="col-md-6">
          <img src="/images/clinic2.jpg" class="about-img" alt="Pus-Pus Britanico Dental Clinic">
        </div>
        <div class="col-md-6">
          <h3 class="fw-bold mb-3">Location & Hours</h3>
          <p class="mb-4">We're conveniently located in Pacita Complex, San Pedro. Walk-ins welcome, but booking ahead
            means no wait.</p>
          <div class="ratio ratio-16x9 shadow rounded mb-4" style="border-radius: 12px; overflow: hidden;">
            <iframe
              src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3865.35287554996!2d121.04920417380845!3d14.348978786108079!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397d7e836c1d71f%3A0x9b42ae743363dc2b!2sDr.%20M.E.B%20Austria%20Dental%20Clinic!5e0!3m2!1sen!2sph!4v1781873394761!5m2!1sen!2sph"
              style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
          </div>
          <div class="location-info">
            <p><i class="fa-solid fa-location-dot"></i><span><strong>Address</strong>#50 Mainroad Ave. B21 L31 Phase 1
                Pacita Complex 2 San Pedro, Laguna</span></p>
            <p><i class="fa-regular fa-calendar"></i><span><strong>Operating Days</strong>Monday – Sunday</span></p>
            <p><i class="fa-regular fa-clock"></i><span><strong>Operating Hours</strong>Mon – Sat: 7:00 PM – 9:00
                PM<br>Sunday: 9:00 AM – 6:00 PM</span></p>
          </div>
        </div>
      </div>
    </div>
  </section>
  @if (session('user_id'))
  <!-- APPOINTMENT -->
  <section id="appointment" class="section" style="background: linear-gradient(180deg, #c2f2c677 0%, #d1ffca30 100%);">

    <div class="container">
      <div class="text-center">
        <span class="section-eyebrow">Book Now</span>
        <h2 class="section-title">Schedule Your Appointment</h2>
        <hr class="section-divider mx-auto">
        <p class="section-intro mx-auto">Click a date on the calendar, pick a service, and choose an open time slot.</p>
      </div>

      @if (session('booking_success'))
        <div class="alert alert-success mx-auto" style="max-width: 880px;">{{ session('booking_success') }}</div>
      @endif
      @if (session('booking_error'))
        <div class="alert alert-danger mx-auto" style="max-width: 880px;">{{ session('booking_error') }}</div>
      @endif

      <!-- ===================== MONTH VIEW (read-only, click a date to book) ===================== -->
      <div class="content">
        <div class="schedule-wrap booking-calendar">
          <div class="schedule-toolbar">
            <div>
              <h4>Doctor Schedule</h4>
            </div>
            <div class="d-flex align-items-center gap-2">
              <a class="icon-btn border-0"
                href="{{ route('landingPage') }}?bookMonth={{ $bookCurrent->copy()->subMonth()->format('Y-m') }}#appointment">
                <i class="bi bi-chevron-left"></i>
              </a>
              <div>
                <div class="fw-semibold">{{ $bookCurrent->format('F Y') }}</div>
                <div class="small text-muted-2">Monthly overview</div>
              </div>
              <a class="icon-btn border-0"
                href="{{ route('landingPage') }}?bookMonth={{ $bookCurrent->copy()->addMonth()->format('Y-m') }}#appointment">
                <i class="bi bi-chevron-right"></i>
              </a>
            </div>
          </div>

          <div class="month-grid p-3">
            <div class="mh">Mon</div>
            <div class="mh">Tue</div>
            <div class="mh">Wed</div>
            <div class="mh">Thu</div>
            <div class="mh">Fri</div>
            <div class="mh">Sat</div>
            <div class="mh">Sun</div>

            @foreach ($bookWeeks as $week)
              @foreach ($week as $d)
                @php
                  $dateStr = $d->format('Y-m-d');
                  $inMonth = $d->month === $bookCurrent->month;
                  $isSunday = $d->isSunday();
                  $daySlots = $bookSchedules[$dateStr] ?? collect();
                  $takenSlots = collect($bookSlots)->filter(function ($label, $time) use ($daySlots, $dateStr, $bookOccupiedSlots) {
                    return isset($bookOccupiedSlots[$dateStr . '_' . $time]) || (($daySlots[$time]->Status ?? 'Available') === 'Not Available');
                  });
                @endphp

                @if ($inMonth && !$isSunday)
                  <button type="button"
                    class="day-cell border-0 text-start p-0 w-100 d-block {{ $dateStr === $bookToday ? 'today' : '' }}"
                    data-bs-toggle="modal" data-bs-target="#bookDay{{ $d->format('Ymd') }}">
                    <div class="n" style="margin-left: 8px;">{{ $d->day }}</div>
                    @foreach ($takenSlots->take(3) as $time => $label)
                      @php
                        $apptKey = $dateStr . '_' . $time;
                        $apptForSlot = $bookOccupiedSlots[$apptKey] ?? null;
                        $isMine = $apptForSlot && $apptForSlot->PatientID === $bookCurrentPatientId;
                        $label = $isMine ? ($apptForSlot->Status === 'Approved' ? 'Booked by you' : 'Appointment Pending') : ($apptForSlot && $apptForSlot->Status === 'Approved' ? 'Booked' : 'Appointment Pending for other patient');
                      @endphp
                      <span
                        class="ev {{ $label === 'Booked' ? 'ev-unavailable' : 'ev-pending' }}">{{ \Carbon\Carbon::createFromFormat('H:i', $time)->format('g:i A') }}
                        · {{ $label }}</span>
                    @endforeach
                  </button>
                @elseif ($inMonth && $isSunday)
                  <button type="button" class="day-cell day-off border-0 text-start p-0 w-100 d-block" disabled>
                    <div class="n" style="margin-left: 8px;">{{ $d->day }}</div>
                    <span class="ev ev-unavailable">Closed</span>
                  </button>
                @else
                  <button class="day-cell border-0 text-start p-0 w-100 d-block disabled" disabled>
                    <div class="n" style="margin-left: 8px;">{{ $d->day }}</div>
                  </button>
                @endif
              @endforeach
            @endforeach
          </div>

          <div class="p-3 d-flex justify-content-between flex-wrap gap-2">
            <div class="legend">
              <span><span class="dot" style="background: var(--brand-700);"></span>Today</span>
              <span><span class="dot" style="background: #dc3545;"></span>Booked</span>
              <span><span class="dot" style="background: #e0a800;"></span>Pending</span>
            </div>
            <div class="small text-muted-2">Click any date to view open times and book.</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- ===================== ONE BOOKING MODAL PER DAY ===================== --}}
  @foreach ($bookWeeks as $week)
    @foreach ($week as $d)
      @continue($d->month !== $bookCurrent->month)
      @continue($d->isSunday())
      @php
        $dateStr = $d->format('Y-m-d');
        $daySlots = $bookSchedules[$dateStr] ?? collect();
        $isPast = $d->lt(\Carbon\Carbon::parse($bookToday));
      @endphp

      <div class="modal fade" id="bookDay{{ $d->format('Ymd') }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
          <div class="modal-content">
            <div class="modal-header border-0 pb-0">
              <h5 class="modal-title fw-semibold">{{ $d->format('l, F j, Y') }}</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-2">
              @if ($isPast)
                <p class="text-muted-2 small">This date has already passed.</p>
              @else
                <div class="schedule-wrap mb-0">
                  <div class="week-grid">
                    <div class="wh">Time</div>
                    <div class="wh day">{{ $d->format('D') }} <span class="num">{{ $d->day }}</span></div>
                @foreach ($bookSlots as $time => $label)
                  @php
                    $row = $daySlots[$time] ?? null;
                    $apptKey = $dateStr . '_' . $time;
                    $apptForSlot = $bookOccupiedSlots[$apptKey] ?? null;
                    $isAvailable = !$apptForSlot && (!$row || $row->Status === 'Available');
                    $isMine = $apptForSlot && $apptForSlot->PatientID === $bookCurrentPatientId;
                    $isStartSlot = $apptForSlot && $apptForSlot->AppointmentTime === $time;
                    $statusLabel = $isMine ? ($apptForSlot->Status === 'Approved' ? 'Booked by you' : 'Appointment Pending') : ($apptForSlot && $apptForSlot->Status === 'Approved' ? 'Booked by another patient' : 'Appointment Pending for other patient');
                  @endphp

                  <div class="time">{{ $label }}</div>
                  <div class="slot">

                    @if ($isAvailable)
                      @if (session('user_email'))
                        <form method="POST" action="{{ route('booking.store') }}" class="d-flex gap-2 w-100 align-items-center flex-wrap px-3 py-2" style="background:#eaf8ec;border:1px solid #198754;border-radius:8px;">
                          @csrf
                          <input type="hidden" name="date" value="{{ $dateStr }}">
                          <input type="hidden" name="time" value="{{ $time }}">
                          <select name="service_id" class="form-select form-select-sm" style="max-width:220px;" required>
                            <option value="" disabled selected>Select service</option>
                            @foreach ($services as $service)
                              <option value="{{ $service->ServiceID }}">{{ $service->ServiceName }}</option>
                            @endforeach
                          </select>
                          <button type="submit" class="btn btn-sm btn-brand ms-auto">Book</button>
                        </form>
                      @else
                        <a href="{{ route('login') }}" class="slot-btn is-available text-center">Log in to book this slot</a>
                      @endif
                    @else
                      <div class="slot-btn booking-status {{ $apptForSlot && $apptForSlot->Status === 'Approved' ? 'booking-status-booked' : 'booking-status-pending' }} text-center">
                        <div>{{ $statusLabel }}@if($isMine && $apptForSlot->Status === 'Approved') · {{ $apptForSlot->service?->ServiceName ?? $apptForSlot->TypeOfAppointment }} · {{ $apptForSlot->DurationHours ?? 1 }} hour(s)@endif</div>
                        @if($isMine && $isStartSlot)
                          <div class="d-flex justify-content-center gap-2 mt-2">
                            <form method="POST" action="{{ route('userAppointment.remove', $apptForSlot) }}">@csrf<input type="hidden" name="action" value="reschedule"><button class="btn btn-sm btn-light">Reschedule</button></form>
                            <form method="POST" action="{{ route('userAppointment.remove', $apptForSlot) }}">@csrf<input type="hidden" name="action" value="cancel"><button class="btn btn-sm btn-outline-danger">Cancel</button></form>
                          </div>
                        @endif
                      </div>
                    @endif
                  </div>
                @endforeach
                  </div>
                </div>
              @endif
            </div>
            <div class="modal-footer border-0 pt-0">
              <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Close</button>
            </div>
          </div>
        </div>
      </div>
    @endforeach
  @endforeach
  @endif
  <!-- CONTACT US -->
  <section id="contact" class="section" style="background: linear-gradient(180deg, #eff9ee 0%, #ffffff 100%);">
    <div class="container">
      <div class="text-center">
        <span class="section-eyebrow">Get In Touch</span>
        <h2 class="section-title">Contact Us</h2>
        <hr class="section-divider mx-auto">
        <p class="section-intro mx-auto">Have a question or need help? Reach out and we'll get back to you as soon as
          possible.</p>
      </div>
      <div class="row g-4 align-items-stretch">
        <div class="col-lg-5">
          <div class="contact-info-card h-100">
            <h3 class="mb-2">Contact Information</h3>
            <p class="mb-4">We'd love to hear from you. Visit, call, or message us.</p>
            <div class="info-row">
              <i class="fa-solid fa-location-dot"></i>
              <span><strong>Address</strong>#50 Mainroad Ave. B21 L31, Phase 1 Pacita Complex 2, San Pedro,
                Laguna</span>
            </div>
            <div class="info-row">
              <i class="fa-solid fa-phone"></i>
              <span><strong>Phone</strong>(02) 8404-5642</span>
            </div>
            <div class="info-row">
              <i class="fa-solid fa-mobile-screen"></i>
              <span><strong>Mobile</strong>+63 968-476-5943</span>
            </div>
            <div class="info-row">
              <i class="fa-solid fa-envelope"></i>
              <span><strong>Email</strong>drmebdentalclinic@gmail.com</span>
            </div>
          </div>
        </div>
        <div class="col-lg-7">
          <div class="appointment-card h-100">
            <form>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">Full Name</label>
                  <input type="text" class="form-control" placeholder="Juan Dela Cruz" required>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Email Address</label>
                  <input type="email" class="form-control" placeholder="you@email.com" required>
                </div>
              </div>
              <div class="mb-3">
                <label class="form-label">Subject</label>
                <input type="text" class="form-control" placeholder="How can we help?" required>
              </div>
              <div class="mb-4">
                <label class="form-label">Message</label>
                <textarea class="form-control" rows="5" placeholder="Write your message here..." required></textarea>
              </div>
              <div class="text-end">
                <button type="submit" class="btn btn-submit">
                  <i class="fa-regular fa-paper-plane me-2"></i>Send Message
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>
  <!-- FOOTER -->
  <footer>
    <div class="container">
      <div class="row g-4">
        <div class="col-lg-5">
          <h5 class="text-uppercase fw-bold mb-3">Pus-Pus Britanico Dental Clinic</h5>
          <p>
            Providing quality dental care with compassion and professionalism. Our clinic is dedicated to ensuring every
            patient receives personalized treatment in a comfortable and welcoming environment. Your smile is our
            priority.
          </p>
        </div>
        <div class="col-lg-3 col-md-6">
          <h6 class="text-uppercase fw-bold mb-3">Quick Links</h6>
          <a class="footer-link" href="#home">Home</a>
          <a class="footer-link" href="#services">Services</a>
          <a class="footer-link" href="#how">How It Works</a>
          <a class="footer-link" href="#about">About</a>
          @if (session('user_id'))
            <a class="footer-link" href="#appointment">Appointment</a>
          @endif
          <a class="footer-link" href="#contact">Contact</a>
        </div>
        <div class="col-lg-4 col-md-6">
          <h6 class="text-uppercase fw-bold mb-3">Contact Information</h6>
          <p><i class="fas fa-map-marker-alt me-2"></i> #50 Mainroad Ave. B21 L31 Phase 1 Pacita Complex 2 San Pedro,
            Laguna</p>
          <p><i class="fas fa-phone me-2"></i> (02) 8404-5642</p>
          <p><i class="fa-solid fa-mobile-screen me-2"></i> +63 968-476-5943</p>
        </div>
      </div>
      <hr style="border-color: rgba(255, 255, 255, 0.2); margin: 40px 0 20px;">
      <div class="text-center">
        <p style="margin: 0;">&copy; 2026 Pus-Pus Britanico Dental Clinic. All rights reserved.</p>
      </div>
    </div>
  </footer>

  <!-- Notification Modal -->
  <div class="modal fade" id="allNotificationsModal" tabindex="-1" aria-labelledby="allNotificationsLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
      <div class="modal-content notif-modal">

        <div class="modal-header notif-modal-header">
          <div class="d-flex align-items-center gap-2">
            <span class="notif-modal-icon"><i class="bi bi-bell-fill"></i></span>
            <h5 class="modal-title mb-0 text-light" id="allNotificationsLabel">All Notifications</h5>
          </div>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body p-3 notif-modal-body">

          <div class="notif-filter-bar mb-3">
            <input type="radio" class="btn-check" name="dateFilter" id="filterAll" autocomplete="off" checked>
            <label class="notif-pill" for="filterAll">All</label>

            <input type="radio" class="btn-check" name="dateFilter" id="filterJul2" autocomplete="off">
            <label class="notif-pill" for="filterJul2">Jul 2</label>

            <input type="radio" class="btn-check" name="dateFilter" id="filterJul3" autocomplete="off">
            <label class="notif-pill" for="filterJul3">Jul 3</label>

            <input type="radio" class="btn-check" name="dateFilter" id="filterJul4" autocomplete="off">
            <label class="notif-pill" for="filterJul4">Jul 4</label>

            <input type="radio" class="btn-check" name="dateFilter" id="filterJul5" autocomplete="off">
            <label class="notif-pill" for="filterJul5">Jul 5</label>

            <input type="radio" class="btn-check" name="dateFilter" id="filterJul6" autocomplete="off">
            <label class="notif-pill" for="filterJul6">Jul 6</label>
          </div>

          <ul class="notif-list">

            <li class="notif-card" data-date="jul3">
              <span class="notif-icon notif-success"><i class="bi bi-check-lg"></i></span>
              <div class="notif-content">
                <p class="notif-text">Your appointment has been set on <strong>July 3, 2026</strong> at <strong>10:30
                    AM</strong>.</p>
                <div class="notif-meta"><span>06-02-26</span><span class="notif-dot">•</span><span>2 minutes ago</span>
                </div>
              </div>
              <span class="notif-badge notif-success">Scheduled</span>
            </li>

            <li class="notif-card" data-date="jul4">
              <span class="notif-icon notif-success"><i class="bi bi-check-lg"></i></span>
              <div class="notif-content">
                <p class="notif-text">Your appointment has been set on <strong>July 4, 2026</strong> at <strong>2:00
                    PM</strong>.</p>
                <div class="notif-meta"><span>06-02-26</span><span class="notif-dot">•</span><span>15 minutes ago</span>
                </div>
              </div>
              <span class="notif-badge notif-success">Scheduled</span>
            </li>

            <li class="notif-card" data-date="jul2">
              <span class="notif-icon notif-danger"><i class="bi bi-x-lg"></i></span>
              <div class="notif-content">
                <p class="notif-text">Your appointment on <strong>July 2, 2026</strong> at <strong>9:00 AM</strong> has
                  been cancelled.</p>
                <div class="notif-meta"><span>06-02-26</span><span class="notif-dot">•</span><span>1 hour ago</span>
                </div>
              </div>
              <span class="notif-badge notif-danger">Cancelled</span>
            </li>

            <li class="notif-card" data-date="jul3">
              <span class="notif-icon notif-primary"><i class="bi bi-alarm"></i></span>
              <div class="notif-content">
                <p class="notif-text">Reminder: you have an appointment tomorrow, <strong>July 3, 2026</strong> at
                  <strong>10:30 AM</strong>.
                </p>
                <div class="notif-meta"><span>06-02-26</span><span class="notif-dot">•</span><span>2 hours ago</span>
                </div>
              </div>
              <span class="notif-badge notif-primary">Reminder</span>
            </li>

            <li class="notif-card" data-date="jul5">
              <span class="notif-icon notif-warning"><i class="bi bi-arrow-repeat"></i></span>
              <div class="notif-content">
                <p class="notif-text">Your appointment has been rescheduled to <strong>July 5, 2026</strong> at
                  <strong>11:15 AM</strong>.
                </p>
                <div class="notif-meta"><span>06-02-26</span><span class="notif-dot">•</span><span>3 hours ago</span>
                </div>
              </div>
              <span class="notif-badge notif-warning">Rescheduled</span>
            </li>

            <li class="notif-card" data-date="jul6">
              <span class="notif-icon notif-success"><i class="bi bi-check-lg"></i></span>
              <div class="notif-content">
                <p class="notif-text">Your appointment has been set on <strong>July 6, 2026</strong> at <strong>4:45
                    PM</strong>.</p>
                <div class="notif-meta"><span>06-01-26</span><span class="notif-dot">•</span><span>Yesterday</span>
                </div>
              </div>
              <span class="notif-badge notif-success">Scheduled</span>
            </li>

            <li class="notif-card" data-date="jul3">
              <span class="notif-icon notif-primary"><i class="bi bi-alarm"></i></span>
              <div class="notif-content">
                <p class="notif-text">Reminder: you have an appointment tomorrow, <strong>July 3, 2026</strong> at
                  <strong>10:30 AM</strong>.
                </p>
                <div class="notif-meta"><span>06-02-26</span><span class="notif-dot">•</span><span>2 hours ago</span>
                </div>
              </div>
              <span class="notif-badge notif-primary">Reminder</span>
            </li>

            <li class="notif-card" data-date="jul2">
              <span class="notif-icon notif-danger"><i class="bi bi-x-lg"></i></span>
              <div class="notif-content">
                <p class="notif-text">Your appointment on <strong>July 2, 2026</strong> at <strong>9:00 AM</strong> has
                  been cancelled.</p>
                <div class="notif-meta"><span>06-02-26</span><span class="notif-dot">•</span><span>1 hour ago</span>
                </div>
              </div>
              <span class="notif-badge notif-danger">Cancelled</span>
            </li>

          </ul>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalOpenDay" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
      <div class="modal-content">
        <div class="modal-header border-0 pb-0">
          <div>
            <h5 class="modal-title fw-semibold">Day Schedule</h5>
            <div class="small text-muted">Booked slots show who already has the appointment.</div>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body pt-2">
          <div class="schedule-wrap mb-0">
            <div class="week-grid">
              <div class="wh">Time</div>
              <div class="wh day">Slot</div>

              <div class="time">8:00</div>
              <div class="slot"><span class="slot-btn booked">Roberto Blanco<small>Extraction</small></span></div>
              <div class="time">9:00</div>
              <div class="slot"><span class="slot-btn">Available</span></div>
              <div class="time">10:00</div>
              <div class="slot"><span class="slot-btn booked">John Cruz<small>Whitening</small></span></div>
              <div class="time">11:00</div>
              <div class="slot"><span class="slot-btn">Available</span></div>
              <div class="time">1:00</div>
              <div class="slot"><span class="slot-btn booked">Ana Reyes<small>Cleaning</small></span></div>
              <div class="time">2:00</div>
              <div class="slot"><span class="slot-btn">Available</span></div>
              <div class="time">3:00</div>
              <div class="slot"><span class="slot-btn">Available</span></div>
              <div class="time">4:00</div>
              <div class="slot"><span class="slot-btn">Available</span></div>
            </div>
          </div>
        </div>
        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Close</button>
          <button type="button" class="btn btn-brand">Use an Open Slot</button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
