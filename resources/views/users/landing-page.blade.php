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
    <link rel="stylesheet" href="{{ asset('css/mobile.css') }}">
</head>

<body data-bs-spy="scroll" data-bs-target="#navbarSupportedContent" data-bs-root-margin="0px 0px -40%" data-bs-smooth-scroll="true" tabindex="0">
  {{-- Page-level flash + validation toasts (contact form, booking, etc.) — shown to guests and patients alike. --}}
  @include('partials.flash-toasts', ['topOffset' => '100px'])
  <!-- NAVBAR -->
  <nav class="navbar navbar-expand-lg navbar-light fixed-top mask-custom shadow-sm">
    <div class="container-fluid px-3 px-lg-5">
      <a class="navbar-brand d-flex align-items-center" href="#home">
        <img class="logo" src="/images/puspus_logo.png" alt="Pus-Pus Britanico logo">
        <span class="navt ms-1" style="color:#0f7a2d;">PUS-PUS</span>
        <span class="navt ms-2" style="color:#144d25;">BRITANICO</span>
      </a>
      {{-- Phone / tablet: notification bell sits next to the hamburger, outside the
      collapsing menu, so opening it never disturbs the nav links. --}}
      <div class="d-flex align-items-center gap-2 d-lg-none">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
          <span class="navbar-toggler-icon"></span>
        </button>
      </div>
      <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav mx-auto text-center">
          <li class="nav-item"><a class="nav-link navh" href="#home">Home</a></li>
          <li class="nav-item"><a class="nav-link navh" href="#services">Services</a></li>
          <li class="nav-item"><a class="nav-link navh" href="#how">How It Works</a></li>
          <li class="nav-item"><a class="nav-link navh" href="#appointment">Appointment</a></li>
          <li class="nav-item"><a class="nav-link navh" href="#about">About</a></li>
          <li class="nav-item"><a class="nav-link navh" href="#contact">Contact</a></li>
        </ul>
        @include('partials.user-nav-actions')
      </div>
    </div>
  </nav>
  <!-- HOME / HERO -->
  <section id="home" class="hero">
    <img class="hero-bg-img" src="{{ $aboutInfo['heroImage'] }}" alt="">
    <div class="hero-overlay"></div>
    <div class="container">
      <div class="hero-content">
        <span class="hero-eyebrow">Trusted Dental Care</span>
        <h1 class="hero-title">{{ $aboutInfo['heroTitle'] }}</h1>
        <p class="lead mb-2">{{ $aboutInfo['heroSubtitle'] }}</p>
        <p>{{ $aboutInfo['heroDescription'] }}</p>
        <div class="hero-cta">
          <a href="{{ route('login') }}" class="book"><i
              class="fa-regular fa-calendar-check me-2"></i>Sign in to Book</a>
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
  <!-- SCHEDULE PREVIEW (guests only — sign in to book from the Patient Portal) -->
  <section id="appointment" class="section" style="background: linear-gradient(180deg, #c2f2c677 0%, #d1ffca30 100%);">

    <div class="container">
      <div class="text-center">
        <span class="section-eyebrow">See Our Schedule</span>
        <h2 class="section-title">Dentist Availability</h2>
        <hr class="section-divider mx-auto">
        <p class="section-intro mx-auto">Browse open dates and times below. <a href="{{ route('login') }}">Sign in</a> to book a slot from your Patient Portal.</p>
      </div>

      @include('partials.booking-calendar', [
        'calendarMode' => 'post',
        'readOnly' => true,
        'bookWeeks' => $bookWeeks,
        'bookCurrent' => $bookCurrent,
        'bookSchedules' => $bookSchedules,
        'bookOccupiedSlots' => $bookOccupiedSlots,
        'bookSlots' => $bookSlots,
        'bookToday' => $bookToday,
        'services' => $services,
        'bookCurrentPatientId' => $bookCurrentPatientId,
        'bookDentists' => $bookDentists,
        'bookSelectedDentist' => $bookSelectedDentist,
        'bookSelectedDentistId' => $bookSelectedDentistId,
      ])
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
          <p class="mb-4">{{ $aboutInfo['description'] }}</p>
          <div class="ratio ratio-16x9 shadow rounded mb-4" style="border-radius: 12px; overflow: hidden;">
            <iframe
              src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3866.290422858543!2d121.00423227592287!3d14.294550984494727!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397d642f1da52ff%3A0xad05742ba9b8761d!2sPuspus%20Britanico%20Dental%20Clinic.!5e0!3m2!1sen!2sph!4v1787657874563!5m2!1sen!2sph"
              width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy"
              referrerpolicy="strict-origin-when-cross-origin"></iframe>
          </div>
          <div class="location-info">
            <p><i class="fa-solid fa-location-dot"></i><span><strong>Address</strong>{{ $aboutInfo['address'] }}</span>
            </p>
            <p><i class="fa-regular fa-calendar"></i><span><strong>Operating
                  Days</strong>{{ $aboutInfo['operatingDays'] }}</span></p>
            <p><i class="fa-regular fa-clock"></i><span><strong>Operating
                  Hours</strong>{{ $aboutInfo['operatingHours'] }}</span></p>
          </div>
        </div>
      </div>
    </div>
  </section>
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
              <span><strong>Address</strong>{{ $aboutInfo['address'] }}</span>
            </div>
            @if ($aboutInfo['phone'])
              <div class="info-row">
                <i class="fa-solid fa-phone"></i>
                <span><strong>Phone</strong>{{ $aboutInfo['phone'] }}</span>
              </div>
            @endif
            @if ($aboutInfo['mobile'])
              <div class="info-row">
                <i class="fa-solid fa-mobile-screen"></i>
                <span><strong>Mobile</strong>{{ $aboutInfo['mobile'] }}</span>
              </div>
            @endif
            <div class="info-row">
              <i class="fa-solid fa-envelope"></i>
              <span><strong>Email</strong>{{ $aboutInfo['email'] }}</span>
            </div>
          </div>
        </div>
        <div class="col-lg-7">
          <div class="appointment-card h-100">
            @php
              $contactName = trim(($currentPatient?->patientInfo?->FirstName ?? '') . ' ' . ($currentPatient?->patientInfo?->LastName ?? ''));
            @endphp
            <form method="POST" action="{{ route('contact.send') }}">
              @csrf
              @if ($currentPatient)
                {{-- Already know who this is — no need to ask again. --}}
                <p class="text-muted mb-3" style="font-size:.9rem;">
                  Sending as <strong>{{ $contactName ?: $currentPatient->Email }}</strong> ({{ $currentPatient->Email }}).
                </p>
              @else
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label class="form-label">Full Name</label>
                    <input type="text" class="form-control" name="name" value="{{ old('name') }}"
                      placeholder="Juan Dela Cruz" required>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label class="form-label">Email Address</label>
                    <input type="email" class="form-control" name="email" value="{{ old('email') }}"
                      placeholder="you@email.com" required>
                  </div>
                </div>
              @endif
              <div class="mb-3">
                <label class="form-label">Subject</label>
                <input type="text" class="form-control" name="subject" value="{{ old('subject') }}"
                  placeholder="How can we help?" required maxlength="150">
              </div>
              <div class="mb-4">
                <label class="form-label">Message</label>
                <textarea class="form-control" name="message" rows="5" placeholder="Write your message here..." required
                  maxlength="3000">{{ old('message') }}</textarea>
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
          <p>{{ $aboutInfo['footerDescription'] }}</p>
        </div>
        <div class="col-lg-3 col-md-6">
          <h6 class="text-uppercase fw-bold mb-3">Quick Links</h6>
          <a class="footer-link" href="#home">Home</a>
          <a class="footer-link" href="#services">Services</a>
          <a class="footer-link" href="#how">How It Works</a>
          <a class="footer-link" href="#appointment">Schedule</a>
          <a class="footer-link" href="#about">About</a>
          <a class="footer-link" href="#contact">Contact</a>
        </div>
        <div class="col-lg-4 col-md-6">
          <h6 class="text-uppercase fw-bold mb-3">Contact Information</h6>
          <p><i class="fas fa-map-marker-alt me-2"></i> {{ $aboutInfo['address'] }}</p>
          @if ($aboutInfo['phone'])
            <p><i class="fas fa-phone me-2"></i> {{ $aboutInfo['phone'] }}</p>
          @endif
          @if ($aboutInfo['mobile'])
            <p><i class="fa-solid fa-mobile-screen me-2"></i> {{ $aboutInfo['mobile'] }}</p>
          @endif
          <p><i class="fas fa-envelope me-2"></i> {{ $aboutInfo['email'] }}</p>
        </div>
      </div>
      <hr style="border-color: rgba(255, 255, 255, 0.2); margin: 40px 0 20px;">
      <div class="text-center">
        <p style="margin: 0;">{{ $aboutInfo['footerCopyright'] }}</p>
      </div>
    </div>
  </footer>

  @include('partials.user-notif-modal')

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