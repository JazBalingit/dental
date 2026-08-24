<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointments — Pus-Pus Britanico</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="/css/user_appointments.css">
</head>

<body>

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top mask-custom shadow-sm">
        <div class="container-fluid px-3 px-lg-5">
            <a class="navbar-brand d-flex align-items-center" href="#home">
                <img class="logo" src="/images/puspus_logo.png" alt="Pus-Pus Britanico logo">
                <span class="navt ms-1" style="color:#0f7a2d;">PUS-PUS</span>
                <span class="navt ms-2" style="color:#144d25;">BRITANICO</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarSupportedContent">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav mx-auto text-center">
                    <li class="nav-item"><a class="nav-link navh" href="{{ route('landingPage') }}#home">Home</a></li>
                    <li class="nav-item"><a class="nav-link navh"
                            href="{{ route('landingPage') }}#services">Services</a></li>
                    <li class="nav-item"><a class="nav-link navh" href="{{ route('landingPage') }}#how">How It Works</a>
                    </li>
                    <li class="nav-item"><a class="nav-link navh" href="{{ route('landingPage') }}#about">About</a></li>
                    <li class="nav-item"><a class="nav-link navh"
                            href="{{ route('landingPage') }}#appointment">Appointment</a></li>
                    <li class="nav-item"><a class="nav-link navh" href="{{ route('landingPage') }}#contact">Contact</a>
                    </li>
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
                                    <button class="nav-link navh border-0 bg-transparent dropdown-toggle"
                                        type="button" data-bs-toggle="dropdown" aria-expanded="false"
                                        aria-label="Account menu" style="padding-left:6px;padding-right:10px;margin-left:-4px;"></button>
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

    <!-- PAGE HERO -->
    <div class="page-hero">
        <div class="container px-4">
            <h1>USER PROFILE</h1>
            <p class="page-hero-sub">Appointments</p>
        </div>
    </div>

    <!-- SUB-NAV -->
    <div class="subnav">
        <div class="container px-4">
            <div class="subnav-inner">
                <a href="{{ route('userAppointment') }}" class="subnav-link active"><i
                        class="fas fa-calendar-check"></i>Appointments</a>
                <a href="{{ route('settings') }}" class="subnav-link"><i class="fas fa-gear"></i>
                    Settings</a>
            </div>
        </div>
    </div>

    <!-- CONTENT -->
    <div class="content-wrap">

        @include('partials.flash-toasts', ['topOffset' => '100px'])
        <!-- Stats -->
        <div class="stats-row">
            <div class="stat-box">
                <div class="stat-ico" style="background:rgba(59,217,101,0.1);color:#0f7a33"><i
                        class="fas fa-calendar-day"></i></div>
                <div>
                    <div class="stat-val">{{ $history->whereIn('Status', ['Pending', 'Approved'])->count() }}</div>
                    <div class="stat-lbl">Upcoming</div>
                </div>
            </div>
            <div class="stat-box">
                <div class="stat-ico" style="background:rgba(34,197,94,0.1);color:#22c55e"><i
                        class="fas fa-check-circle"></i></div>
                <div>
                    <div class="stat-val">{{ $history->where('Status', 'Completed')->count() }}</div>
                    <div class="stat-lbl">Completed</div>
                </div>
            </div>
            <div class="stat-box">
                <div class="stat-ico" style="background:rgba(239,68,68,0.1);color:#ef4444"><i
                        class="fas fa-times-circle"></i></div>
                <div>
                    <div class="stat-val">{{ $history->where('Status', 'Declined')->count() }}</div>
                    <div class="stat-lbl">Cancelled</div>
                </div>
            </div>
            <div class="stat-box">
                <div class="stat-ico" style="background:rgba(15,76,122,0.08);color:#0f4c7a"><i
                        class="fas fa-clipboard-list"></i></div>
                <div>
                    <div class="stat-val">{{ $history->total() }}</div>
                    <div class="stat-lbl">Total</div>
                </div>
            </div>
        </div>

        <!-- Current Appointment -->
        <div class="section-card">
            <div class="card-hd">
                <div class="card-hd-left">
                    <div class="card-hd-icon"><i class="fas fa-clock"></i></div>
                    <div>
                        <h4>Current Appointment</h4>
                        <p>Your next scheduled visit</p>
                    </div>
                </div>
            </div>
            @if ($current)
            <div class="appt-body">
                <div class="date-block">
                    <div class="date-day">{{ $current->AppointmentDate->format('d') }}</div>
                    <div class="date-mon">{{ $current->AppointmentDate->format('M Y') }}</div>
                </div>
                <div class="appt-detail">
                    <h3>{{ $current->service->ServiceName ?? $current->TypeOfAppointment }}</h3>
                    <div class="appt-meta">
                        <span><i class="fas fa-calendar"></i> {{ $current->AppointmentDate->format('F j, Y') }}</span>
                        <span><i class="fas fa-clock"></i> {{ \Carbon\Carbon::createFromFormat('H:i', $current->AppointmentTime)->format('g:i A') }}</span>
                        @if($current->Status === 'Approved')<span><i class="fas fa-hourglass-half"></i> {{ $current->DurationHours ?? 1 }} hour(s)</span>@endif
                    </div>
                    <div style="margin-top:10px">
                        <span class="badge-pill badge-scheduled">{{ $current->Status === 'Approved' ? 'Booked' : 'Pending' }}</span>
                    </div>
                </div>
                <div class="appt-actions">
                    <button class="btn-outline" data-bs-toggle="modal" data-bs-target="#rescheduleModal">
                        <i class="fas fa-calendar-alt"></i> Reschedule
                    </button>
                    <button class="btn-danger" data-bs-toggle="modal" data-bs-target="#cancelModal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>
            </div>
            @else <div class="appt-body"><p class="mb-0 text-muted">You have no current appointment.</p></div> @endif
        </div>

        <!-- Appointment History -->
        <div class="section-card">
            <div class="card-hd">
                <div class="card-hd-left">
                    <div class="card-hd-icon"><i class="fas fa-history"></i></div>
                    <div>
                        <h4>Appointment History</h4>
                        <p>A record of your past dental visits</p>
                    </div>
                </div>
                <form method="GET" class="history-filters"><div class="history-search"><i class="fas fa-search"></i><input class="form-control" name="search" value="{{ $search }}" placeholder="Search service"></div><select class="form-select" name="status"><option value="">All statuses</option>@foreach(['Pending','Approved','Completed','Declined'] as $option)<option value="{{ $option }}" @selected($status === $option)>{{ $option === 'Approved' ? 'Booked' : $option }}</option>@endforeach</select><button class="btn-prim"><i class="fas fa-filter"></i> Filter</button></form>
            </div>
            <div style="overflow-x:auto">
                <table class="appt-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Service</th>
                            <th>Doctor</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($history as $appointment)<tr><td style="font-weight:600;color:#0f4c7a">{{ $appointment->AppointmentDate->format('M j, Y') }}</td><td>{{ \Carbon\Carbon::createFromFormat('H:i', $appointment->AppointmentTime)->format('g:i A') }}</td><td><span class="service-tag">{{ $appointment->service->ServiceName ?? $appointment->TypeOfAppointment }}</span></td><td>Pus-Pus Britanico Dental Clinic</td><td><span class="badge-pill {{ $appointment->Status === 'Completed' ? 'badge-completed' : ($appointment->Status === 'Declined' ? 'badge-cancelled' : 'badge-scheduled') }}">{{ $appointment->Status === 'Approved' ? 'Booked' : $appointment->Status }}</span></td></tr>@empty<tr><td colspan="5" class="text-center text-muted">No appointments found.</td></tr>@endforelse
                    </tbody>
                </table>
            </div>
            <div class="history-footer">
                <small>Showing <strong>{{ $history->count() }}</strong> of <strong>{{ $history->total() }}</strong> appointments</small>
                @if ($history->lastPage() > 1)
                    <div class="pages">
                        <a href="{{ $history->previousPageUrl() ?? '#' }}"><i class="bi bi-chevron-left"></i></a>
                        @for ($i = 1; $i <= $history->lastPage(); $i++)
                            <a href="{{ $history->url($i) }}" class="{{ $history->currentPage() === $i ? 'active' : '' }}">{{ $i }}</a>
                        @endfor
                        <a href="{{ $history->nextPageUrl() ?? '#' }}"><i class="bi bi-chevron-right"></i></a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- RESCHEDULE MODAL -->
    <div class="modal fade" id="rescheduleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-calendar-alt me-2" style="color:#0f7a33"></i>Reschedule Appointment
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p>Rescheduling removes this appointment and releases its time. You can then choose a new available time from the booking calendar.</p>
                </div>
                <div class="modal-footer gap-2">
                    <button class="btn-sec" data-bs-dismiss="modal">Discard</button>
                    @if($current)<form method="POST" action="{{ route('userAppointment.remove', $current) }}">@csrf<input type="hidden" name="action" value="reschedule"><button class="btn-prim"><i class="fas fa-calendar-alt me-1"></i> Reschedule</button></form>@endif
                </div>
            </div>
        </div>
    </div>

    <!-- CANCEL MODAL -->
    <div class="modal fade" id="cancelModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2" style="color:#ef4444"></i>Cancel
                        Appointment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p style="font-size:14px;line-height:1.7">Are you sure you want to cancel your
                        @if($current)<strong style="color:#0f4c7a">{{ $current->AppointmentDate->format('M j') }} – {{ $current->service->ServiceName ?? $current->TypeOfAppointment }}</strong>@endif
                        appointment? This cannot be undone.</p>
                </div>
                <div class="modal-footer gap-2">
                    <button class="btn-sec" data-bs-dismiss="modal">Keep It</button>
                    @if($current)<form method="POST" action="{{ route('userAppointment.remove', $current) }}">@csrf<input type="hidden" name="action" value="cancel"><button class="btn-prim" style="background:linear-gradient(135deg,#b91c1c,#ef4444);box-shadow:0 4px 12px rgba(239,68,68,0.3)"><i class="fas fa-times me-1"></i> Yes, Cancel</button></form>@endif
                </div>
            </div>
        </div>
    </div>

    <!-- BOOK MODAL -->
    <div class="modal fade" id="bookModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-calendar-plus me-2" style="color:#0f7a33"></i>Book New
                        Appointment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3"><label class="ml">Type of Appointment</label>
                        <select class="mi">
                            <option disabled selected>Select a service</option>
                            <option>General Cleaning</option>
                            <option>Extraction</option>
                            <option>Braces Consultation</option>
                            <option>Whitening</option>
                            <option>Implant Consultation</option>
                        </select>
                    </div>
                    <div class="mb-3"><label class="ml">Preferred Date</label><input type="date" class="mi"></div>
                    <div class="mb-3"><label class="ml">Preferred Time</label>
                        <select class="mi">
                            <option disabled selected>Select a time</option>
                            <option>9:00 AM</option>
                            <option>10:00 AM</option>
                            <option>11:00 AM</option>
                            <option>1:00 PM</option>
                            <option>2:00 PM</option>
                            <option>3:00 PM</option>
                            <option>4:00 PM</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer gap-2">
                    <button class="btn-sec" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn-prim" data-bs-dismiss="modal"><i class="fas fa-check me-1"></i> Confirm
                        Booking</button>
                </div>
            </div>
        </div>
    </div>

    <!-- FOOTER -->
    <footer id="contact">
        <div class="container">
            <div class="row mb-5">
                <div class="col-lg-6 col-md-12 mb-4 mb-md-0">
                    <h5 class="text-uppercase fw-bold">PUS-PUS BRITANICO DENTAL CLINIC</h5>
                    <p>
                        Providing quality dental care with compassion and professionalism. Our clinic is dedicated to
                        ensuring every
                        patient receives personalized treatment in a comfortable and welcoming environment. Your smile
                        is our
                        priority.
                    </p>
                </div>
                <div class="col-md-6 col-lg-3 offset-lg-3">
                    <h6 class="text-uppercase fw-bold mb-4">Contact Information</h6>
                    <p><i class="fas fa-map-marker-alt me-3"></i> #50 Mainroad Ave. B21 L31 Phase 1 Pacita Complex 2 San
                        Pedro,
                        Laguna</p>
                    <p><i class="fas fa-phone me-3"></i>(02)84045642</p>
                    <p><i class="fa-solid fa-mobile me-3"></i>+63 968-476-5943</p>
                </div>
            </div>
            <hr style="border-color: rgba(255, 255, 255, 0.934); margin: 30px 0;">
            <div class="text-center">
                <p style="color: rgba(255, 255, 255, 0.8); margin: 0;">&copy; 2026 Pus-Pus Britanico Dental Clinic. All
                    rights
                    reserved.
                </p>
            </div>
        </div>
    </footer>
    @include('partials.user-notif-modal')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
