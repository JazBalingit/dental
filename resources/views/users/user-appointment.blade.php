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
        <div class="container">
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
                                                    Your appointment has been set on <strong>July 3, 2026</strong> at
                                                    <strong>10:30 AM</strong>.
                                                </p>
                                                <span class="text-muted" style="font-size: 0.75rem;">2 minutes
                                                    ago</span>
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
                                                    Reminder: you have an appointment tomorrow, <strong>July 3,
                                                        2026</strong> at <strong>10:30
                                                        AM</strong>.
                                                </p>
                                                <span class="text-muted" style="font-size: 0.75rem;">15 minutes
                                                    ago</span>
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
                                                    Your appointment on <strong>July 3, 2026</strong> at <strong>10:30
                                                        AM</strong> has been
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
                                                    Your appointment has been rescheduled to <strong>July 5,
                                                        2026</strong> at <strong>2:00
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
                                                    Your appointment on <strong>June 20, 2026</strong> has been marked
                                                    as completed.
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
                                    <button
                                        class="nav-link navh d-flex align-items-center gap-2 border-0 bg-transparent dropdown-toggle"
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

    <!-- PAGE HERO -->
    <div class="page-hero">
        <div class="container px-4">
            <nav aria-label="breadcrumb" style="margin-bottom:12px">
            </nav>
            <h1><i class="fas fa-calendar-check me-2" style="opacity:0.8"></i>Appointments</h1>
            <p>View, manage and book your dental visit schedule</p>
        </div>
    </div>

    <!-- SUB-NAV -->
    <div class="subnav">
        <div class="container px-4">
            <div class="subnav-inner">
                <a href="{{ route('userProfile') }}" class="subnav-link"><i class="fas fa-user"></i> Personal Info</a>
                <a href="{{ route('userAppointment') }}" class="subnav-link active"><i
                        class="fas fa-calendar-check"></i>Appointments</a>
                <a href="{{ route('settings') }}" class="subnav-link"><i class="fas fa-lock"></i> Change
                    Password</a>
            </div>
        </div>
    </div>

    <!-- CONTENT -->
    <div class="content-wrap">

        @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        <!-- Stats -->
        <div class="stats-row">
            <div class="stat-box">
                <div class="stat-ico" style="background:rgba(59,159,217,0.1);color:#3b9fd9"><i
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
            <div class="history-footer"><small>Showing <strong>{{ $history->count() }}</strong> of <strong>{{ $history->total() }}</strong> appointments</small><div>{{ $history->links() }}</div></div>
        </div>
    </div>

    <!-- RESCHEDULE MODAL -->
    <div class="modal fade" id="rescheduleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-calendar-alt me-2" style="color:#3b9fd9"></i>Reschedule Appointment
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
                    <p style="font-size:14px;line-height:1.7">Are you sure you want to cancel your <strong
                            style="color:#0f4c7a">June 28 – General Cleaning</strong> appointment? This cannot be
                        undone.</p>
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
                    <h5 class="modal-title"><i class="fas fa-calendar-plus me-2" style="color:#3b9fd9"></i>Book New
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
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>

                <div class="modal-body p-3 notif-modal-body">

                    <div class="notif-filter-bar mb-3">
                        <input type="radio" class="btn-check" name="dateFilter" id="filterAll" autocomplete="off"
                            checked>
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
                                <p class="notif-text">Your appointment has been set on <strong>July 3, 2026</strong> at
                                    <strong>10:30
                                        AM</strong>.
                                </p>
                                <div class="notif-meta"><span>06-02-26</span><span class="notif-dot">•</span><span>2
                                        minutes ago</span>
                                </div>
                            </div>
                            <span class="notif-badge notif-success">Scheduled</span>
                        </li>

                        <li class="notif-card" data-date="jul4">
                            <span class="notif-icon notif-success"><i class="bi bi-check-lg"></i></span>
                            <div class="notif-content">
                                <p class="notif-text">Your appointment has been set on <strong>July 4, 2026</strong> at
                                    <strong>2:00
                                        PM</strong>.
                                </p>
                                <div class="notif-meta"><span>06-02-26</span><span class="notif-dot">•</span><span>15
                                        minutes ago</span>
                                </div>
                            </div>
                            <span class="notif-badge notif-success">Scheduled</span>
                        </li>

                        <li class="notif-card" data-date="jul2">
                            <span class="notif-icon notif-danger"><i class="bi bi-x-lg"></i></span>
                            <div class="notif-content">
                                <p class="notif-text">Your appointment on <strong>July 2, 2026</strong> at <strong>9:00
                                        AM</strong> has
                                    been cancelled.</p>
                                <div class="notif-meta"><span>06-02-26</span><span class="notif-dot">•</span><span>1
                                        hour ago</span>
                                </div>
                            </div>
                            <span class="notif-badge notif-danger">Cancelled</span>
                        </li>

                        <li class="notif-card" data-date="jul3">
                            <span class="notif-icon notif-primary"><i class="bi bi-alarm"></i></span>
                            <div class="notif-content">
                                <p class="notif-text">Reminder: you have an appointment tomorrow, <strong>July 3,
                                        2026</strong> at
                                    <strong>10:30 AM</strong>.
                                </p>
                                <div class="notif-meta"><span>06-02-26</span><span class="notif-dot">•</span><span>2
                                        hours ago</span>
                                </div>
                            </div>
                            <span class="notif-badge notif-primary">Reminder</span>
                        </li>

                        <li class="notif-card" data-date="jul5">
                            <span class="notif-icon notif-warning"><i class="bi bi-arrow-repeat"></i></span>
                            <div class="notif-content">
                                <p class="notif-text">Your appointment has been rescheduled to <strong>July 5,
                                        2026</strong> at
                                    <strong>11:15 AM</strong>.
                                </p>
                                <div class="notif-meta"><span>06-02-26</span><span class="notif-dot">•</span><span>3
                                        hours ago</span>
                                </div>
                            </div>
                            <span class="notif-badge notif-warning">Rescheduled</span>
                        </li>

                        <li class="notif-card" data-date="jul6">
                            <span class="notif-icon notif-success"><i class="bi bi-check-lg"></i></span>
                            <div class="notif-content">
                                <p class="notif-text">Your appointment has been set on <strong>July 6, 2026</strong> at
                                    <strong>4:45
                                        PM</strong>.
                                </p>
                                <div class="notif-meta"><span>06-01-26</span><span
                                        class="notif-dot">•</span><span>Yesterday</span>
                                </div>
                            </div>
                            <span class="notif-badge notif-success">Scheduled</span>
                        </li>

                        <li class="notif-card" data-date="jul3">
                            <span class="notif-icon notif-primary"><i class="bi bi-alarm"></i></span>
                            <div class="notif-content">
                                <p class="notif-text">Reminder: you have an appointment tomorrow, <strong>July 3,
                                        2026</strong> at
                                    <strong>10:30 AM</strong>.
                                </p>
                                <div class="notif-meta"><span>06-02-26</span><span class="notif-dot">•</span><span>2
                                        hours ago</span>
                                </div>
                            </div>
                            <span class="notif-badge notif-primary">Reminder</span>
                        </li>

                        <li class="notif-card" data-date="jul2">
                            <span class="notif-icon notif-danger"><i class="bi bi-x-lg"></i></span>
                            <div class="notif-content">
                                <p class="notif-text">Your appointment on <strong>July 2, 2026</strong> at <strong>9:00
                                        AM</strong> has
                                    been cancelled.</p>
                                <div class="notif-meta"><span>06-02-26</span><span class="notif-dot">•</span><span>1
                                        hour ago</span>
                                </div>
                            </div>
                            <span class="notif-badge notif-danger">Cancelled</span>
                        </li>

                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
