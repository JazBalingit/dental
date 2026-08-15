<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Walk-in Appointment • Dental Clinic</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">

    <!-- Page-specific additions only (everything else comes from styles.css already used across the app) -->
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
                <a href="{{ route('walkIn') }}"  class="active"><i class="bi bi-calendar3"></i> Walk-in Appointments</a>
                <a href="{{ route('appointmentApproval') }}"><i class="bi bi-clipboard2-check"></i> Appointment
                    Approval</a>
                <a href="{{ route('appointments') }}"><i class="bi bi-clipboard2-check"></i> Appointments</a>
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
                            <li>
                                <a class="dropdown-item rounded d-flex gap-2 align-items-start" href="#">
                                    <i class="bi bi-check-circle-fill text-success mt-1"></i>
                                    <div>
                                        <p class="mb-0 small">
                                            <span class="text-muted">07-15-26:</span>
                                            <strong>Roberto Blanco</strong> has successfully scheduled an appointment on
                                            <strong>July 20, 2026</strong> at <strong>10:00 AM</strong>.
                                        </p>
                                        <span class="text-muted" style="font-size: 0.75rem;">2 minutes ago</span>
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
                        <h2>Walk-in Appointment</h2>
                        <div class="crumbs">Register a walk-in patient and book them into an open slot.</div>
                    </div>
                </div>

                <!-- ===================== DOCTOR'S CALENDAR ===================== -->
                <!-- ===================== MONTH VIEW ===================== -->
                <div class="schedule-wrap mb-5">
                    <div class="schedule-toolbar">
                        <div>
                            <h4>Doctor Schedule</h4>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button class="icon-btn border-0"><i class="bi bi-chevron-left"></i></button>
                            <div>
                                <div class="fw-semibold">April 2025</div>
                                <div class="small text-muted-2">Monthly overview</div>
                            </div>
                            <button class="icon-btn border-0"><i class="bi bi-chevron-right"></i></button>
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

                        <!-- Week 1 -->
                        <button class="day-cell border-0 text-start p-0 w-100 d-block" data-bs-toggle="modal"
                            data-bs-target="#modalWeek">
                            <div class="n" style="margin-left: 8px;">31</div><span class="count">1</span><span
                                class="ev">11:00
                                AM</span>
                        </button>
                        <button class="day-cell border-0 text-start p-0 w-100 d-block" data-bs-toggle="modal"
                            data-bs-target="#modalWeek">
                            <div class="n" style="margin-left: 8px;">1</div>
                        </button>
                        <button class="day-cell border-0 text-start p-0 w-100 d-block" data-bs-toggle="modal"
                            data-bs-target="#modalWeek">
                            <div class="n" style="margin-left: 8px;">2</div><span class="count">2</span><span
                                class="ev">7:00
                                AM</span><span class="ev">11:00 AM</span>
                        </button>
                        <button class="day-cell border-0 text-start p-0 w-100 d-block" data-bs-toggle="modal"
                            data-bs-target="#modalWeek">
                            <div class="n" style="margin-left: 8px;">3</div><span class="count">1</span><span
                                class="ev">3:00
                                PM</span>
                        </button>
                        <button class="day-cell border-0 text-start p-0 w-100 d-block" data-bs-toggle="modal"
                            data-bs-target="#modalWeek">
                            <div class="n" style="margin-left: 8px;">4</div>
                        </button>
                        <button class="day-cell border-0 text-start p-0 w-100 d-block" data-bs-toggle="modal"
                            data-bs-target="#modalWeek">
                            <div class="n" style="margin-left: 8px;">5</div>
                        </button>
                        <button class="day-cell border-0 text-start p-0 w-100 d-block disabled" disabled
                            data-bs-toggle="modal" data-bs-target="#modalWeek">
                            <div class="n" style="margin-left: 8px;">6</div>
                        </button>

                        <!-- Week 2 -->
                        <button class="day-cell border-0 text-start p-0 w-100 d-block" data-bs-toggle="modal"
                            data-bs-target="#modalWeek">
                            <div class="n" style="margin-left: 8px;">7</div><span class="count">3</span><span
                                class="ev">9:00
                                AM</span><span class="ev">10:00 AM</span><span class="ev">11:00 AM</span>
                        </button>
                        <button class="day-cell border-0 text-start p-0 w-100 d-block" data-bs-toggle="modal"
                            data-bs-target="#modalWeek">
                            <div class="n" style="margin-left: 8px;">8</div>
                        </button>
                        <button class="day-cell border-0 text-start p-0 w-100 d-block" data-bs-toggle="modal"
                            data-bs-target="#modalWeek">
                            <div class="n" style="margin-left: 8px;">9</div>
                        </button>
                        <button class="day-cell border-0 text-start p-0 w-100 d-block" data-bs-toggle="modal"
                            data-bs-target="#modalWeek">
                            <div class="n" style="margin-left: 8px;">10</div><span class="count">1</span><span
                                class="ev">10:00
                                AM</span>
                        </button>
                        <button class="day-cell border-0 text-start p-0 w-100 d-block" data-bs-toggle="modal"
                            data-bs-target="#modalWeek">
                            <div class="n" style="margin-left: 8px;">11</div><span class="count">2</span><span
                                class="ev">11:00
                                AM</span><span class="ev">2:00 PM</span>
                        </button>
                        <button class="day-cell border-0 text-start p-0 w-100 d-block" data-bs-toggle="modal"
                            data-bs-target="#modalWeek">
                            <div class="n" style="margin-left: 8px;">12</div>
                        </button>
                        <button class="day-cell border-0 text-start p-0 w-100 d-block disabled" disabled
                            data-bs-toggle="modal" data-bs-target="#modalWeek">
                            <div class="n" style="margin-left: 8px;">13</div>
                        </button>

                        <!-- Week 3 -->
                        <button class="day-cell border-0 text-start p-0 w-100 d-block" data-bs-toggle="modal"
                            data-bs-target="#modalWeek">
                            <div class="n" style="margin-left: 8px;">14</div>
                        </button>
                        <button class="day-cell border-0 text-start p-0 w-100 d-block" data-bs-toggle="modal"
                            data-bs-target="#modalWeek">
                            <div class="n" style="margin-left: 8px;">15</div>
                        </button>
                        <button class="day-cell border-0 text-start p-0 w-100 d-block" data-bs-toggle="modal"
                            data-bs-target="#modalWeek">
                            <div class="n" style="margin-left: 8px;">16</div>
                        </button>
                        <button class="day-cell border-0 text-start p-0 w-100 d-block" data-bs-toggle="modal"
                            data-bs-target="#modalWeek">
                            <div class="n" style="margin-left: 8px;">17</div><span class="count">1</span><span
                                class="ev">9:00
                                AM</span>
                        </button>
                        <button class="day-cell border-0 text-start p-0 w-100 d-block" data-bs-toggle="modal"
                            data-bs-target="#modalWeek">
                            <div class="n" style="margin-left: 8px;">18</div>
                        </button>
                        <button class="day-cell border-0 text-start p-0 w-100 d-block" data-bs-toggle="modal"
                            data-bs-target="#modalWeek">
                            <div class="n" style="margin-left: 8px;">19</div><span class="count">1</span><span
                                class="ev">11:00
                                AM</span><span class="ev">3:00 PM</span>
                        </button>
                        <button class="day-cell border-0 text-start p-0 w-100 d-block disabled" disabled
                            data-bs-toggle="modal" data-bs-target="#modalWeek">
                            <div class="n" style="margin-left: 8px;">20</div>
                        </button>

                        <!-- Week 4 -->
                        <button class="day-cell border-0 text-start p-0 w-100 d-block" data-bs-toggle="modal"
                            data-bs-target="#modalWeek">
                            <div class="n" style="margin-left: 8px;">21</div>
                        </button>
                        <button class="day-cell today border-0 text-start p-0 w-100 d-block" data-bs-toggle="modal"
                            data-bs-target="#modalWeek">
                            <div class="n" style="margin-left: 8px;">22</div>
                        </button>
                        <button class="day-cell border-0 text-start p-0 w-100 d-block" data-bs-toggle="modal"
                            data-bs-target="#modalWeek">
                            <div class="n" style="margin-left: 8px;">23</div><span class="count">3</span><span
                                class="ev">1:00
                                PM</span><span class="ev">2:00 PM</span><span class="ev">3:00 PM</span>
                        </button>
                        <button class="day-cell border-0 text-start p-0 w-100 d-block" data-bs-toggle="modal"
                            data-bs-target="#modalWeek">
                            <div class="n" style="margin-left: 8px;">24</div>
                        </button>
                        <button class="day-cell border-0 text-start p-0 w-100 d-block" data-bs-toggle="modal"
                            data-bs-target="#modalWeek">
                            <div class="n" style="margin-left: 8px;">25</div><span class="count">1</span><span
                                class="ev">11:00
                                AM</span>
                        </button>
                        <button class="day-cell border-0 text-start p-0 w-100 d-block" data-bs-toggle="modal"
                            data-bs-target="#modalWeek">
                            <div class="n" style="margin-left: 8px;">26</div><span class="count">2</span><span
                                class="ev">11:00
                                AM</span><span class="ev">3:00 PM</span>
                        </button>
                        <button class="day-cell border-0 text-start p-0 w-100 d-block disabled" disabled
                            data-bs-toggle="modal" data-bs-target="#modalWeek">
                            <div class="n" style="margin-left: 8px;">27</div>
                        </button>

                        <!-- Week 5 -->
                        <button class="day-cell border-0 text-start p-0 w-100 d-block" data-bs-toggle="modal"
                            data-bs-target="#modalWeek">
                            <div class="n" style="margin-left: 8px;">28</div><span class="count">1</span><span
                                class="ev">11:00
                                AM</span>
                        </button>
                        <button class="day-cell border-0 text-start p-0 w-100 d-block" data-bs-toggle="modal"
                            data-bs-target="#modalWeek">
                            <div class="n" style="margin-left: 8px;">29</div><span class="count">1</span><span
                                class="ev">4:00
                                PM</span>
                        </button>
                        <button class="day-cell border-0 text-start p-0 w-100 d-block" data-bs-toggle="modal"
                            data-bs-target="#modalWeek">
                            <div class="n" style="margin-left: 8px;">30</div>
                        </button>
                        <button class="day-cell border-0 text-start p-0 w-100 d-block" data-bs-toggle="modal"
                            data-bs-target="#modalWeek">
                            <div class="n" style="margin-left: 8px;">1</div>
                        </button>
                        <button class="day-cell border-0 text-start p-0 w-100 d-block" data-bs-toggle="modal"
                            data-bs-target="#modalWeek">
                            <div class="n" style="margin-left: 8px;">2</div>
                        </button>
                        <button class="day-cell border-0 text-start p-0 w-100 d-block" data-bs-toggle="modal"
                            data-bs-target="#modalWeek">
                            <div class="n" style="margin-left: 8px;">3</div>
                        </button>
                        <button class="day-cell border-0 text-start p-0 w-100 d-block disabled" disabled
                            data-bs-toggle="modal" data-bs-target="#modalWeek">
                            <div class="n" style="margin-left: 8px;">4</div>
                        </button>
                    </div>

                    <div class="p-3 d-flex justify-content-between flex-wrap gap-2">
                        <div class="legend">
                            <span><span class="dot" style="background: var(--brand-700);"></span>Today</span>
                            <span><span class="dot" style="background: var(--brand-100);"></span>Has Schedule</span>
                            <span>
                        </div>
                        <div class="small text-muted-2">Available schedule this month: 124 schedules</div>
                    </div>
                </div>

                <!--  =================== WALK IN ======================= -->
                <div class="card-soft p-3 p-md-4">
                    <form id="walkinForm">
                        <!-- ===================== PATIENT INFORMATION ===================== -->
                        <ul class="nav nav-tabs patient-tabs" id="patientTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="existing-tab" data-bs-toggle="tab"
                                    data-bs-target="#existing-pane" type="button" role="tab">Existing Patient</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="new-tab" data-bs-toggle="tab" data-bs-target="#new-pane"
                                    type="button" role="tab">New Patient</button>
                            </li>
                        </ul>

                        <div class="tab-content patient-tab-pane-wrap mb-4">

                            <!-- Existing patient lookup -->
                            <div class="tab-pane fade show active" id="existing-pane" role="tabpanel">
                                <div class="row g-3 mb-3">
                                    <div class="col-md-9">
                                        <label class="form-label">Search patient (name or Patient ID)</label>
                                        <div class="input-icon"><i class="bi bi-search"></i>
                                            <input type="text" class="form-control"
                                                placeholder="e.g. John Cruz or PT-0001">
                                        </div>
                                    </div>
                                    <div class="col-md-3 d-flex align-items-end">
                                        <button type="button" class="btn btn-brand w-100">Load Patient</button>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center gap-3 mb-4">
                                    <img class="avatar-initials" src="/images/default.png" alt=""
                                        style="width:64px;height:64px;">
                                    <div>
                                        <div class="fw-semibold">John Cruz</div>
                                        <div class="small text-muted-2">Patient ID: PT-0001</div>
                                    </div>
                                </div>

                                <div class="section-label">Personal Information</div>
                                <div class="row g-3 mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Last name</label>
                                        <div class="input-icon"><i class="bi bi-person"></i><input type="text"
                                                class="form-control" value="Cruz" disabled /></div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">First name</label>
                                        <div class="input-icon"><i class="bi bi-person"></i><input type="text"
                                                class="form-control" value="John" disabled /></div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Middle name</label>
                                        <div class="input-icon"><i class="bi bi-person"></i><input type="text"
                                                class="form-control" value="Santos" disabled /></div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Birthdate</label>
                                        <div class="input-icon"><i class="bi bi-calendar-event"></i><input type="date"
                                                class="form-control" value="1998-05-12" disabled /></div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Age</label>
                                        <div class="input-icon"><i class="bi bi-calendar3"></i><input type="number"
                                                class="form-control" value="28" disabled /></div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Gender</label>
                                        <div class="input-icon">
                                            <select class="form-select" disabled>
                                                <option selected>Male</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Status</label>
                                        <div class="input-icon">
                                            <select class="form-select" disabled>
                                                <option selected>Active</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">Home address</label>
                                        <div class="input-icon"><i class="bi bi-geo-alt"></i><input class="form-control"
                                                value="Purok 3, San Pedro, Laguna" disabled /></div>
                                    </div>
                                </div>

                                <div class="section-label mt-2">Contact Details</div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Email address</label>
                                        <div class="input-icon"><i class="bi bi-envelope"></i><input type="email"
                                                class="form-control" value="john.cruz@email.com" disabled /></div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Cell/Mobile number</label>
                                        <div class="input-icon"><i class="bi bi-telephone"></i><input
                                                class="form-control" value="+63 912 345 6789" disabled /></div>
                                    </div>
                                </div>
                            </div>

                            <!-- New patient registration -->
                            <div class="tab-pane fade" id="new-pane" role="tabpanel">
                                <div class="section-label">Personal Information</div>
                                <div class="row g-3 mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Last name</label>
                                        <div class="input-icon"><i class="bi bi-person"></i><input type="text"
                                                class="form-control" placeholder="Dela Cruz" required /></div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">First name</label>
                                        <div class="input-icon"><i class="bi bi-person"></i><input type="text"
                                                class="form-control" placeholder="Maria" required /></div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Middle name</label>
                                        <div class="input-icon"><i class="bi bi-person"></i><input type="text"
                                                class="form-control" placeholder="Reyes" /></div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Birthdate</label>
                                        <div class="input-icon"><i class="bi bi-calendar-event"></i><input type="date"
                                                class="form-control" required /></div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Age</label>
                                        <div class="input-icon"><i class="bi bi-calendar3"></i><input type="number"
                                                class="form-control" placeholder="0" /></div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Gender</label>
                                        <div class="input-icon">
                                            <select class="form-select" required>
                                                <option selected disabled value="">Select gender</option>
                                                <option>Male</option>
                                                <option>Female</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Home address</label>
                                        <div class="input-icon"><i class="bi bi-geo-alt"></i><input class="form-control"
                                                placeholder="Street, Barangay, San Pedro, Laguna" required /></div>
                                    </div>
                                </div>

                                <div class="section-label mt-2">Contact Details</div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Email address</label>
                                        <div class="input-icon"><i class="bi bi-envelope"></i><input type="email"
                                                class="form-control" placeholder="name@email.com" /></div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Cell/Mobile number</label>
                                        <div class="input-icon"><i class="bi bi-telephone"></i><input
                                                class="form-control" placeholder="+63 9XX XXX XXXX" required /></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ===================== APPOINTMENT DETAILS ===================== -->
                        <div class="appointment-card mb-4">
                            <div class="section-label mb-3">Appointment Details</div>
                            <div class="mb-3">
                                <label class="form-label">Type of Appointment</label>
                                <select class="form-select" name="appointment-type" required>
                                    <option selected disabled>Select Service</option>
                                    <option>Cleaning</option>
                                    <option>Extraction</option>
                                    <option>Braces Consultation</option>
                                    <option>Whitening</option>
                                    <option>Implant Consultation</option>
                                </select>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Select Day</label>
                                    <select class="form-select" name="choose-day" required>
                                        <option selected disabled>Select Day</option>
                                        <option>Wednesday - 07/15/26</option>
                                        <option>Thursday - 07/16/26</option>
                                        <option>Friday - 07/17/26</option>
                                        <option>Saturday - 07/18/26</option>
                                        <option>Monday - 07/20/26</option>
                                        <option>Monday - 07/27/26</option>
                                    </select>
                                    <div class="form-text">Tip: pick an open slot straight from the calendar below.
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Choose Time of Visit</label>
                                    <select class="form-select" name="choose-time" required>
                                        <option selected disabled>Select time</option>
                                        <option>8:00 AM</option>
                                        <option>9:00 AM</option>
                                        <option>1:00 PM</option>
                                        <option>4:00 PM</option>
                                    </select>
                                </div>
                            </div>
                            <div class="text-end">
                                <button type="submit" class="btn btn-brand">
                                    <i class="fa-regular fa-paper-plane me-2"></i>Confirm Walk-in Appointment
                                </button>
                            </div>
                        </div>

                    </form>
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
                                    <div class="slot"><span class="slot-btn booked">Roberto
                                            Blanco<small>Extraction</small></span></div>
                                    <div class="time">9:00</div>
                                    <div class="slot"><span class="slot-btn">Available</span></div>
                                    <div class="time">10:00</div>
                                    <div class="slot"><span class="slot-btn booked">John
                                            Cruz<small>Whitening</small></span></div>
                                    <div class="time">11:00</div>
                                    <div class="slot"><span class="slot-btn">Available</span></div>
                                    <div class="time">1:00</div>
                                    <div class="slot"><span class="slot-btn booked">Ana
                                            Reyes<small>Cleaning</small></span></div>
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

        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>