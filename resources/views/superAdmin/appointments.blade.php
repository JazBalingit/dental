<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Appointments • Dental Clinic</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
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
            <nav class="nav">
                <div class="nav-section">Main</div>
                <a href="{{ route('dashboard') }}"><i class="bi bi-grid-1x2-fill"></i> Dashboard</a>
                <a href="{{ route('staffAcc') }}"><i class="bi bi-people-fill"></i> Staff Accounts</a>
                <a href="{{ route('userAcc') }}"><i class="bi bi-people-fill"></i> User Accounts</a>
                <a href="{{ route('dentistSchedule') }}"><i class="bi bi-calendar3"></i> Dentist Schedule</a>
                <a href="{{ route('walkIn') }}"><i class="bi bi-calendar3"></i> Walk-in Appointments</a>
                <a href="{{ route('appointmentApproval') }}"><i class="bi bi-clipboard2-check"></i> Appointment
                    Approval</a>
                <a href="{{ route('appointments') }}" class="active"><i class="bi bi-clipboard2-check"></i> Appointments</a>
                <a href="{{ route('patientRecords') }}"><i class="bi bi-folder2-open"></i> Patient Records</a>
                <div class="nav-section">System</div>
                <a href="{{ route('configuration') }}"><i class="bi bi-sliders2"></i> Configuration</a>
                <div class="divider"></div>
                <a href="{{ route('login') }}"><i class="bi bi-box-arrow-right"></i> Log Out</a>
            </nav>
            <div class="footer">© PUS-PUS BRITANICO DENTAL CLINIC</div>
        </aside>

        <main>
            <div class="topbar">
                <div class="left">
                    <button class="toggle d-lg-none" type="button" data-bs-toggle="offcanvas"
                        data-bs-target="#sidebarOffcanvas" aria-controls="sidebarOffcanvas">
                        <i class="bi bi-list"></i>
                    </button>
                </div>
                <div class="right">
                    <div class="dropdown">
                        <button class="icon-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-bell"></i><span class="dot"></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end notif-dropdown shadow-sm p-2"
                            style="width: 500px; max-width: 90vw; max-height: 400px; overflow-y: auto;">
                            <li>
                                <h6 class="dropdown-header">Notifications</h6>
                            </li>

                            <li style="max-height: 260px;">
                                <a class="dropdown-item rounded d-flex gap-2 align-items-start" href="#">
                                    <i class="bi bi-check-circle-fill text-success mt-1"></i>
                                    <div>
                                        <p class="mb-0 small">
                                            <span class="text-muted">06-02-26:</span>
                                            <strong>Roberto Blanco</strong> has successfully scheduled an appointment on
                                            <strong>July 3, 2026</strong> at <strong>10:30 AM</strong>.
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
                                    <i class="bi bi-check-circle-fill text-success mt-1"></i>
                                    <div>
                                        <p class="mb-0 small">
                                            <span class="text-muted">06-02-26:</span>
                                            <strong>Roberto Blanco</strong> has successfully scheduled an appointment on
                                            <strong>July 3, 2026</strong> at <strong>10:30 AM</strong>.
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
                                            <strong>Roberto Blanco</strong> has successfully scheduled an appointment on
                                            <strong>July 3, 2026</strong> at <strong>10:30 AM</strong>.
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
                                            <strong>Roberto Blanco</strong> has successfully scheduled an appointment on
                                            <strong>July 3, 2026</strong> at <strong>10:30 AM</strong>.
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
                                            <strong>Roberto Blanco</strong> has successfully scheduled an appointment on
                                            <strong>July 3, 2026</strong> at <strong>10:30 AM</strong>.
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
                    <div class="user-chip">
                        <div><img class="avatar" src="/images/default.png" alt=""></div>
                        <div class="meta">
                            <div class="name"> Admin</div>
                            <div class="role">Administrator</div>
                        </div>
                        <i class="bi bi-chevron-down ms-1 text-muted-2"></i>
                    </div>
                </div>
            </div>

            <div class="content">
                <div class="page-head">
                    <div>
                        <h2>Appointments</h2>
                        <div class="crumbs">View and manage all the appointments of the patient.</div>
                    </div>
                </div>

                <!-- mini stats -->
                <div class="row g-3 mb-3">
                    <div class="col-6 col-md-3">
                        <div class="stat-card">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="label">Today</div>
                                    <div class="value">8</div>
                                </div>
                                <div class="icon"><i class="bi bi-calendar-day"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stat-card alt-1">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="label">Done</div>
                                    <div class="value">3</div>
                                </div>
                                <div class="icon"><i class="bi bi-check2-circle"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stat-card alt-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="label">Pending</div>
                                    <div class="value">1</div>
                                </div>
                                <div class="icon">
                                    <i class="bi bi-hourglass"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stat-card alt-2">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="label">Cancelled</div>
                                    <div class="value">4</div>
                                </div>
                                <div class="icon"><i class="bi bi-x-circle"></i></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-soft p-3 p-md-4">
                    <div class="data-toolbar">
                        <div class="left">
                            <span class="text-muted-2 small">Filter</span>
                            <select class="form-select" style="min-width:140px;">
                                <option>All Status</option>
                                <option>Pending</option>
                                <option>Done</option>
                                <option>Cancelled</option>
                            </select>
                        </div>
                        <div class="right">
                            <div class="input-icon search">
                                <i class="bi bi-search"></i>
                                <input class="form-control" placeholder="Search patient or treatment..."
                                    style="height:40px; padding-left:2.4rem;" />
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table-soft">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Patient</th>
                                    <th>Treatment</th>
                                    <th>Status</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><span class="fw-semibold">8:00 AM</span></td>
                                    <td><span><img class="avatar-initials" src="/images/default.png" alt=""></span>John
                                        Cruz</td>
                                    <td>Cleaning</td>
                                    <td><span class="pill pill-success">Done</span></td>
                                    <td class="text-end"><button class="btn btn-pill btn-pill-done"><i
                                                class="bi bi-check2"></i>
                                            Completed</button></td>
                                </tr>
                                <tr>
                                    <td><span class="fw-semibold">9:00 AM</span></td>
                                    <td><span><img class="avatar-initials" src="/images/default.png" alt=""></span>Marie
                                        Reyes</td>
                                    <td>Check-up</td>
                                    <td><span class="pill pill-success">Done</span></td>
                                    <td class="text-end"><button class="btn btn-pill btn-pill-done"><i
                                                class="bi bi-check2"></i>
                                            Completed</button></td>
                                </tr>
                                <tr>
                                    <td><span class="fw-semibold">10:00 AM</span></td>
                                    <td><span><img class="avatar-initials" src="/images/default.png" alt=""></span>Jane
                                        Doe</td>
                                    <td>Cleaning</td>
                                    <td><span class="pill pill-info">In Progress</span></td>
                                    <td class="text-end">
                                        <button class="btn btn-pill btn-pill-done me-1"><i class="bi bi-check2"></i>
                                            Done</button>
                                        <button class="btn btn-pill btn-pill-cancel" data-bs-toggle="modal"
                                            data-bs-target="#cancelledReasonModal"><i class="bi bi-x"></i>
                                            Cancel</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td><span class="fw-semibold">11:00 AM</span></td>
                                    <td><span><img class="avatar-initials" src="/images/default.png" alt=""></span>Peter
                                        Lim</td>
                                    <td>Whitening</td>
                                    <td><span class="pill pill-warning">Pending</span></td>
                                    <td class="text-end">
                                        <button class="btn btn-pill btn-pill-done me-1"><i class="bi bi-check2"></i>
                                            Done</button>
                                        <button class="btn btn-pill btn-pill-cancel" data-bs-toggle="modal"
                                            data-bs-target="#cancelledReasonModal"><i class="bi bi-x"></i>
                                            Cancel</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td><span class="fw-semibold">1:00 PM</span></td>
                                    <td><span><img class="avatar-initials" src="/images/default.png" alt=""></span>Kim
                                        Lee</td>
                                    <td>Braces Adjustment</td>
                                    <td><span class="pill pill-warning">Pending</span></td>
                                    <td class="text-end">
                                        <button class="btn btn-pill btn-pill-done me-1"><i class="bi bi-check2"></i>
                                            Done</button>
                                        <button class="btn btn-pill btn-pill-cancel" data-bs-toggle="modal"
                                            data-bs-target="#cancelledReasonModal">
                                            <i class="bi bi-x"></i> Cancel
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td><span class="fw-semibold">2:00 PM</span></td>
                                    <td><span><img class="avatar-initials" src="/images/default.png" alt=""></span>Brian
                                        Chan</td>
                                    <td>Whitening</td>
                                    <td><span class="pill pill-warning">Pending</span></td>
                                    <td class="text-end">
                                        <button class="btn btn-pill btn-pill-done me-1"><i class="bi bi-check2"></i>
                                            Done</button>
                                        <button class="btn btn-pill btn-pill-cancel" data-bs-toggle="modal"
                                            data-bs-target="#cancelledReasonModal">
                                            <i class="bi bi-x"></i> Cancel
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td><span class="fw-semibold">3:00 PM</span></td>
                                    <td><span><img class="avatar-initials" src="/images/default.png" alt=""></span>Grace
                                        Tan</td>
                                    <td>Check-up</td>
                                    <td><span class="pill pill-warning">Pending</span></td>
                                    <td class="text-end">
                                        <button class="btn btn-pill btn-pill-done me-1"><i class="bi bi-check2"></i>
                                            Done</button>
                                        <button class="btn btn-pill btn-pill-cancel" data-bs-toggle="modal"
                                            data-bs-target="#cancelledReasonModal">
                                            <i class="bi bi-x"></i> Cancel
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td><span class="fw-semibold">4:00 PM</span></td>
                                    <td><span><img class="avatar-initials" src="/images/default.png" alt=""></span>Felix
                                        Lee</td>
                                    <td>Cleaning</td>
                                    <td><span class="pill pill-danger">Cancelled</span></td>
                                    <td class="text-end"><button class="btn btn-pill btn-pill-cancel"><i
                                                class="bi bi-x"></i>
                                            Cancelled</button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="pagination-soft">
                        <div>Showing 8 of 8 appointments today</div>
                        <div class="pages">
                            <button><i class="bi bi-chevron-left"></i></button>
                            <button class="active">1</button>
                            <button><i class="bi bi-chevron-right"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <!-- Notification Modal -->
    <div class="modal fade" id="allNotificationsModal" tabindex="-1" aria-labelledby="allNotificationsLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content notif-modal">

                <div class="modal-header notif-modal-header">
                    <div class="d-flex align-items-center gap-2">
                        <span class="notif-modal-icon"><i class="bi bi-bell-fill"></i></span>
                        <h5 class="modal-title mb-0" id="allNotificationsLabel">All Notifications</h5>
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
                                <p class="notif-text"><strong>Roberto Blanco</strong> has successfully scheduled an
                                    appointment on
                                    <strong>July 3, 2026</strong> at <strong>10:30 AM</strong>.
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
                                <p class="notif-text"><strong>Maria Santos</strong> has successfully scheduled an
                                    appointment on
                                    <strong>July 4, 2026</strong> at <strong>2:00 PM</strong>.
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
                                <p class="notif-text"><strong>Juan Dela Cruz</strong> has cancelled their appointment on
                                    <strong>July 2,
                                        2026</strong> at <strong>9:00 AM</strong>.
                                </p>
                                <div class="notif-meta"><span>06-02-26</span><span class="notif-dot">•</span><span>1
                                        hour ago</span>
                                </div>
                            </div>
                            <span class="notif-badge notif-danger">Cancelled</span>
                        </li>
                        <li class="notif-card" data-date="jul2">
                            <span class="notif-icon notif-danger"><i class="bi bi-x-lg"></i></span>
                            <div class="notif-content">
                                <p class="notif-text"><strong>Juan Dela Cruz</strong> has cancelled their appointment on
                                    <strong>July 2,
                                        2026</strong> at <strong>9:00 AM</strong>.
                                </p>
                                <div class="notif-meta"><span>06-02-26</span><span class="notif-dot">•</span><span>1
                                        hour ago</span>
                                </div>
                            </div>
                            <span class="notif-badge notif-danger">Cancelled</span>
                        </li>
                        <li class="notif-card" data-date="jul2">
                            <span class="notif-icon notif-danger"><i class="bi bi-x-lg"></i></span>
                            <div class="notif-content">
                                <p class="notif-text"><strong>Juan Dela Cruz</strong> has cancelled their appointment on
                                    <strong>July 2,
                                        2026</strong> at <strong>9:00 AM</strong>.
                                </p>
                                <div class="notif-meta"><span>06-02-26</span><span class="notif-dot">•</span><span>1
                                        hour ago</span>
                                </div>
                            </div>
                            <span class="notif-badge notif-danger">Cancelled</span>
                        </li>
                        <li class="notif-card" data-date="jul2">
                            <span class="notif-icon notif-danger"><i class="bi bi-x-lg"></i></span>
                            <div class="notif-content">
                                <p class="notif-text"><strong>Juan Dela Cruz</strong> has cancelled their appointment on
                                    <strong>July 2,
                                        2026</strong> at <strong>9:00 AM</strong>.
                                </p>
                                <div class="notif-meta"><span>06-02-26</span><span class="notif-dot">•</span><span>1
                                        hour ago</span>
                                </div>
                            </div>
                            <span class="notif-badge notif-danger">Cancelled</span>
                        </li>

                        <li class="notif-card" data-date="jul5">
                            <span class="notif-icon notif-warning"><i class="bi bi-arrow-repeat"></i></span>
                            <div class="notif-content">
                                <p class="notif-text"><strong>Anna Reyes</strong> has rescheduled her appointment to
                                    <strong>July 5,
                                        2026</strong> at <strong>11:15 AM</strong>.
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
                                <p class="notif-text"><strong>Mark Villanueva</strong> has successfully scheduled an
                                    appointment on
                                    <strong>July 6, 2026</strong> at <strong>4:45 PM</strong>.
                                </p>
                                <div class="notif-meta"><span>06-01-26</span><span
                                        class="notif-dot">•</span><span>Yesterday</span>
                                </div>
                            </div>
                            <span class="notif-badge notif-success">Scheduled</span>
                        </li>

                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- ===================== DECLINE REASON MODAL (shared) ===================== -->
    <div class="modal fade" id="cancelledReasonModal" tabindex="-1" aria-labelledby="cancelledReasonModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h5 class="modal-title fw-semibold" id="cancelledReasonModalLabel">Cancelled Appointment</h5>
                        <div class="small text-muted">Let the patient know why this was cancelled</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-2">

                    <div class="section-label">Reason for Cancelation of Appointment</div>
                    <div class="mb-3">
                        <label class="form-label">Message to patient</label>
                        <textarea class="form-control" rows="4"
                            placeholder="e.g. Requested time slot is no longer available. Please choose another schedule."></textarea>
                    </div>

                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-pill btn-pill-cancel"><i class="bi bi-send"></i> Send &
                        Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>