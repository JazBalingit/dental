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
    <div class="container-fluid px-3 px-lg-5">
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
              @include('partials.user-notif-dropdown')
              @if (session('user_email'))
                <div class="dropdown d-flex align-items-center">
                  <a href="{{ route('settings', ['tab' => 'profile']) }}"
                    class="nav-link navh d-flex align-items-center gap-2" style="padding-right:8px;">
                    <i class="bi bi-person-circle"></i>
                    <span>{{ session('user_email') }}</span>
                  </a>
                  <button class="nav-link navh border-0 bg-transparent dropdown-toggle" type="button"
                    data-bs-toggle="dropdown" aria-expanded="false" aria-label="Account menu"
                    style="padding-left:6px;padding-right:10px;margin-left:-4px;"></button>
                  <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                    <li>
                      <a class="dropdown-item small" href="{{ route('userAppointment') }}">
                        <i class="bi bi-calendar-check me-2"></i>User Appointments
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
          <a href="{{ session('user_id') ? '#appointment' : route('login') }}" class="book"><i
              class="fa-regular fa-calendar-check me-2"></i>{{ session('user_id') ? 'Book Appointment' : 'Sign in to Book' }}</a>
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
        @foreach ($serviceCategories as $category)
          <div class="col">
            <div class="soft-card service-card">
              <div class="service-icon"><i class="{{ $category->Icon ?: 'fa-solid fa-tooth' }}"></i></div>
              <h5>{{ $category->Name }}</h5>
              <ul>
                @foreach ($category->services as $service)
                  <li>{{ $service->ServiceName }}</li>
                @endforeach
              </ul>
            </div>
          </div>
        @endforeach

        @if ($uncategorizedServices->isNotEmpty())
          <div class="col">
            <div class="soft-card service-card">
              <div class="service-icon"><i class="fa-solid fa-notes-medical"></i></div>
              <h5>Other Services</h5>
              <ul>
                @foreach ($uncategorizedServices as $service)
                  <li>{{ $service->ServiceName }}</li>
                @endforeach
              </ul>
            </div>
          </div>
        @endif

        @if ($serviceCategories->isEmpty() && $uncategorizedServices->isEmpty())
          <div class="col-12 text-center text-muted-2">Our services will be listed here soon.</div>
        @endif
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
        <p class="section-intro mx-auto">Follow these simple steps to schedule your visit with us — it only takes a
          few minutes.</p>
      </div>
      @php
        $stepIcons = \App\Models\SystemSetting::appointmentStepIcons();
        $lastStep = count($appointmentSteps);
      @endphp
      <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 g-lg-5 mt-2">
        @foreach ($appointmentSteps as $n => $step)
          <div class="col">
            <div class="soft-card step-card">
              <span class="step-badge">{{ $n }}</span>
              <div class="step-icon"><i class="{{ $stepIcons[($n - 1) % count($stepIcons)] }}"></i></div>
              <h5>{{ $step['title'] }}</h5>
              <p>{{ $step['desc'] }}</p>
              @if ($n === 1)
                @unless (session('user_id'))
                  <a href="{{ route('signup') }}" class="step-cta"><i class="fa-solid fa-user-plus me-2"></i>Sign Up</a>
                @endunless
              @endif
              @if ($n === $lastStep && session('user_id'))
                <a href="{{ route('userAppointment') }}" class="step-cta"><i
                    class="fa-regular fa-calendar-check me-2"></i>My Appointments</a>
              @endif
            </div>
          </div>
        @endforeach
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
          <img src="{{ $aboutInfo['image'] }}" class="about-img" alt="Pus-Pus Britanico Dental Clinic">
        </div>
        <div class="col-md-6">
          <h3 class="fw-bold mb-3">Location & Hours</h3>
          <p class="mb-4">We're conveniently located in Pacita Complex, San Pedro. Walk-ins welcome, but booking ahead
            means no wait.</p>
          <div class="ratio ratio-16x9 shadow rounded mb-4" style="border-radius: 12px; overflow: hidden;">
            <iframe
              src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3866.290422858543!2d121.00423227592287!3d14.294550984494727!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397d642f1da52ff%3A0xad05742ba9b8761d!2sPuspus%20Britanico%20Dental%20Clinic.!5e0!3m2!1sen!2sph!4v1787657874563!5m2!1sen!2sph"
              width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy"
              referrerpolicy="strict-origin-when-cross-origin"></iframe>
          </div>
          <div class="location-info">
            <p><i class="fa-solid fa-location-dot"></i><span><strong>Address</strong>{{ $aboutInfo['address'] }}</span></p>
            <p><i class="fa-regular fa-calendar"></i><span><strong>Operating Days</strong>{{ $aboutInfo['operatingDays'] }}</span></p>
            <p><i class="fa-regular fa-clock"></i><span><strong>Operating Hours</strong>{{ $aboutInfo['operatingHours'] }}</span></p>
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

        @include('partials.flash-toasts', ['topOffset' => '100px'])

        @include('partials.booking-calendar', [
          'calendarMode' => 'post',
          'bookWeeks' => $bookWeeks,
          'bookCurrent' => $bookCurrent,
          'bookSchedules' => $bookSchedules,
          'bookOccupiedSlots' => $bookOccupiedSlots,
          'bookSlots' => $bookSlots,
          'bookToday' => $bookToday,
          'services' => $services,
          'bookCurrentPatientId' => $bookCurrentPatientId,
        ])
      </div>
    </section>
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
            @php
              $contactName = trim(($currentPatient?->patientInfo?->FirstName ?? '') . ' ' . ($currentPatient?->patientInfo?->LastName ?? ''));
            @endphp
            <form>
              @if ($currentPatient)
                {{-- Already know who this is — no need to ask again. --}}
                <input type="hidden" name="name" value="{{ $contactName }}">
                <input type="hidden" name="email" value="{{ $currentPatient->Email }}">
              @else
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label class="form-label">Full Name</label>
                    <input type="text" class="form-control" name="name" placeholder="Juan Dela Cruz" required>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label class="form-label">Email Address</label>
                    <input type="email" class="form-control" name="email" placeholder="you@email.com" required>
                  </div>
                </div>
              @endif
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

  @include('partials.user-notif-modal')

  @if (session('user_id'))
    <!-- RESCHEDULE CONFIRMATION MODAL -->
    <div class="modal fade confirm-modal" id="landingRescheduleModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title"><i class="fas fa-calendar-alt me-2" style="color:#0f7a33"></i>Reschedule Appointment
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body p-4">
            <p class="mb-0" style="font-size:14px;line-height:1.7">Rescheduling removes this appointment and releases its
              time. You can then choose a new available time from the booking calendar.</p>
          </div>
          <div class="modal-footer gap-2">
            <button type="button" class="confirm-btn-sec" data-bs-dismiss="modal">Discard</button>
            <form method="POST" id="landingRescheduleForm">
              @csrf
              <input type="hidden" name="action" value="reschedule">
              <button class="confirm-btn-prim"><i class="fas fa-calendar-alt me-1"></i> Reschedule</button>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- CANCEL CONFIRMATION MODAL -->
    <div class="modal fade confirm-modal" id="landingCancelModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2" style="color:#ef4444"></i>Cancel
              Appointment</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body p-4">
            <p style="font-size:14px;line-height:1.7">Are you sure you want to cancel your <strong id="landingCancelLabel"
                style="color:#0f4c7a"></strong> appointment? This cannot be undone.</p>
          </div>
          <div class="modal-footer gap-2">
            <button type="button" class="confirm-btn-sec" data-bs-dismiss="modal">Keep It</button>
            <form method="POST" id="landingCancelForm">
              @csrf
              <input type="hidden" name="action" value="cancel">
              <button class="confirm-btn-prim"
                style="background:linear-gradient(135deg,#b91c1c,#ef4444);box-shadow:0 4px 12px rgba(239,68,68,0.3)"><i
                  class="fas fa-times me-1"></i> Yes, Cancel</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  @endif

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
  <script>
    document.getElementById('bookMonthForm')?.addEventListener('submit', function () {
      var month = document.getElementById('bookMonthNum').value.padStart(2, '0');
      var year = document.getElementById('bookYear').value;
      document.getElementById('bookMonth').value = year + '-' + month;
    });

    // ---------- Clinic slot grid, mirrors DentistSchedule on the server — used to
    // preview the actual end time (skipping the lunch-hour gap) before submitting ----------
    var SLOT_TIMES = @json(\App\Models\DentistSchedule::slotTimes());
    var SLOT_MINUTES = {{ \App\Models\DentistSchedule::SLOT_MINUTES }};

    function formatTime12h(hours, minutes) {
      var period = hours >= 12 ? 'PM' : 'AM';
      var hour12 = hours % 12 || 12;
      return hour12 + ':' + String(minutes).padStart(2, '0') + ' ' + period;
    }

    // "1 hour 30 minutes" / "30 minutes" — mirrors DentistSchedule::formatSlotDuration().
    function formatDurationLabel(totalMinutes) {
      var hours = Math.floor(totalMinutes / 60);
      var minutes = totalMinutes % 60;
      var parts = [];
      if (hours > 0) parts.push(hours + ' hour' + (hours > 1 ? 's' : ''));
      if (minutes > 0) parts.push(minutes + ' minute' + (minutes > 1 ? 's' : ''));
      return parts.length ? parts.join(' ') : '0 minutes';
    }

    // The last reserved slot's end time for a booking of totalMinutes
    // starting at startTime — null if it would run past closing (the
    // actual check still happens server-side; this is just a preview).
    function computeEndTimeLabel(startTime, totalMinutes) {
      var slotsNeeded = Math.max(1, Math.ceil(totalMinutes / SLOT_MINUTES));
      var startIndex = SLOT_TIMES.indexOf(startTime);
      if (startIndex === -1) return null;
      var lastIndex = startIndex + slotsNeeded - 1;
      if (lastIndex >= SLOT_TIMES.length) return null;
      var lastSlot = SLOT_TIMES[lastIndex].split(':').map(Number);
      var endMinutesTotal = lastSlot[0] * 60 + lastSlot[1] + SLOT_MINUTES;
      return formatTime12h(Math.floor(endMinutesTotal / 60), endMinutesTotal % 60);
    }

    // ---------- Multi-service dropdown: keep each toggle button's label in sync ----------
    function updateServiceToggleLabel(wrapper) {
      var text = wrapper.querySelector('.book-slot-service-toggle-text');
      var checked = wrapper.querySelectorAll('.book-slot-service-option:checked');
      text.textContent = checked.length
        ? Array.from(checked).map(function (c) { return c.dataset.name; }).join(', ')
        : 'Select services';
    }

    document.addEventListener('change', function (e) {
      if (e.target.matches('.book-slot-service-option')) {
        var wrapper = e.target.closest('.book-slot-service');
        updateServiceToggleLabel(wrapper);
        wrapper.querySelector('.book-slot-service-toggle').classList.remove('is-invalid');
      }
    });

    // ---------- Review & Confirm inside the day modal (select service(s) -> review -> confirm) ----------
    document.addEventListener('click', function (e) {
      var selectBtn = e.target.closest('.book-select-btn');
      if (selectBtn) {
        var row = selectBtn.closest('.d-flex');
        var wrapper = row ? row.querySelector('.book-slot-service') : null;
        var checked = wrapper ? Array.from(wrapper.querySelectorAll('.book-slot-service-option:checked')) : [];

        if (!checked.length) {
          if (wrapper) wrapper.querySelector('.book-slot-service-toggle').classList.add('is-invalid');
          return;
        }
        wrapper.querySelector('.book-slot-service-toggle').classList.remove('is-invalid');

        var modalEl = selectBtn.closest('.modal');
        if (!modalEl) return;

        var slotsView = modalEl.querySelector('.book-slots-view');
        var confirmView = modalEl.querySelector('.book-confirm-view');
        if (!slotsView || !confirmView) return;

        var dateLabel = modalEl.querySelector('.modal-title')?.textContent || selectBtn.dataset.date;
        var totalMinutes = checked.reduce(function (sum, c) { return sum + (parseInt(c.dataset.duration, 10) || 60); }, 0);
        var startLabel = selectBtn.dataset.timeLabel || selectBtn.dataset.time;
        var endLabel = computeEndTimeLabel(selectBtn.dataset.time, totalMinutes);

        confirmView.querySelector('.book-confirm-service').textContent = checked.map(function (c) { return c.dataset.name; }).join(', ');
        confirmView.querySelector('.book-confirm-date').textContent = dateLabel;
        confirmView.querySelector('.book-confirm-time').textContent = endLabel ? (startLabel + ' - ' + endLabel) : startLabel;
        confirmView.querySelector('.book-confirm-duration').textContent = formatDurationLabel(totalMinutes);
        confirmView.querySelector('.book-confirm-date-input').value = selectBtn.dataset.date;
        confirmView.querySelector('.book-confirm-time-input').value = selectBtn.dataset.time;

        var inputsContainer = confirmView.querySelector('.book-confirm-service-inputs');
        inputsContainer.innerHTML = '';
        checked.forEach(function (c) {
          var input = document.createElement('input');
          input.type = 'hidden';
          input.name = 'service_ids[]';
          input.value = c.value;
          inputsContainer.appendChild(input);
        });

        slotsView.hidden = true;
        confirmView.hidden = false;
        return;
      }

      var backBtn = e.target.closest('.book-confirm-back-btn');
      if (backBtn) {
        var modal = backBtn.closest('.modal');
        if (!modal) return;
        var slots = modal.querySelector('.book-slots-view');
        var confirm = modal.querySelector('.book-confirm-view');
        if (slots) slots.hidden = false;
        if (confirm) confirm.hidden = true;
      }
    });

    // Reset every day-modal back to the slot list whenever it's (re)opened.
    document.addEventListener('show.bs.modal', function (e) {
      var slotsView = e.target.querySelector('.book-slots-view');
      var confirmView = e.target.querySelector('.book-confirm-view');
      if (slotsView) slotsView.hidden = false;
      if (confirmView) confirmView.hidden = true;
    });

    // ---------- Wire the Reschedule / Cancel confirmation modals to whichever slot triggered them ----------
    document.addEventListener('show.bs.modal', function (e) {
      var trigger = e.relatedTarget;
      if (!trigger) return;

      if (e.target.id === 'landingRescheduleModal') {
        var rescheduleForm = document.getElementById('landingRescheduleForm');
        if (rescheduleForm) rescheduleForm.action = trigger.dataset.removeUrl || '';
      }

      if (e.target.id === 'landingCancelModal') {
        var cancelForm = document.getElementById('landingCancelForm');
        if (cancelForm) cancelForm.action = trigger.dataset.removeUrl || '';
        var label = document.getElementById('landingCancelLabel');
        if (label) label.textContent = [trigger.dataset.apptDate, trigger.dataset.apptService].filter(Boolean).join(' – ');
      }
    });
  </script>
</body>

</html>