<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Patient Records • Dental Clinic</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
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
        <a href="{{ route('appointments') }}"><i class="bi bi-clipboard2-check"></i> Appointments</a>
        <a href="{{ route('patientRecords') }}" class="active"><i class="bi bi-folder2-open"></i> Patient Records</a>
        <div class="nav-section">System</div>
        <a href="{{ route('configuration') }}"><i class="bi bi-sliders2"></i> Configuration</a>
      </nav>
      <div class="footer">© PUS-PUS BRITANICO DENTAL CLINIC</div>
    </aside>

    <!-- ── MAIN ── -->
    <main>

      <!-- Topbar -->
      <div class="topbar">
        <div class="left">
          <button class="toggle d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas"
            aria-controls="sidebarOffcanvas">
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
          <div class="dropdown">
            <button class="user-chip" type="button" data-bs-toggle="dropdown" aria-expanded="false"
              style="all:unset; cursor:pointer; display:flex; align-items:center; gap:.6rem; padding:.35rem .8rem .35rem .35rem; border-radius:999px; background:var(--brand-50); border:1px solid var(--brand-100); font-family:inherit;">
              <div><img class="avatar" src="/images/default.png" alt=""></div>
              <div class="meta">
                <div class="name">{{ session('user_email', 'Admin') }}</div>
                <div class="role">{{ session('account_type') === 'staff' ? 'Staff' : 'Administrator' }}</div>
              </div>
              <i class="bi bi-chevron-down ms-1 text-muted-2"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
              <li><a class="dropdown-item small" href="{{ route('staffProfile') }}"><i class="bi bi-person me-2"></i>My Profile</a></li>
              <li><hr class="dropdown-divider"></li>
              <li>
                <form method="POST" action="{{ route('logout') }}" class="m-0">
                  @csrf
                  <button type="submit" class="dropdown-item text-danger small"><i class="bi bi-box-arrow-right me-1"></i> Log Out</button>
                </form>
              </li>
            </ul>
          </div>
        </div>
      </div>

      <!-- Content -->
      <div class="content">

        <!-- Page head -->
        <div class="page-head">
          <div>
            <h2>Patient Records</h2>
            <div class="crumbs">Browse and manage all patient files and treatment history.</div>
          </div>
          <button class="btn-brand" data-bs-toggle="modal" data-bs-target="#archivesModal">
            <i class="bi bi-archive"></i> Archives
          </button>
        </div>

        <!-- Table card -->
        <div class="card-soft p-3 p-md-4">
          <div class="data-toolbar">
            <div class="left">
              <span class="text-muted-2 small">Show</span>
              <select class="form-select" style="width:80px;">
                <option>10</option>
                <option>25</option>
                <option>50</option>
              </select>
              <span class="text-muted-2 small">entries</span>
            </div>
            <div class="right">
              <select class="form-select" style="min-width:160px;">
                <option>All Treatments</option>
                <option>Cleaning</option>
                <option>Filling</option>
                <option>Extraction</option>
                <option>Whitening</option>
              </select>
              <select class="form-select" style="min-width:140px;">
                <option>All Status</option>
                <option>Completed</option>
                <option>Scheduled</option>
                <option>Cancelled</option>
              </select>
              <div class="input-icon search">
                <i class="bi bi-search"></i>
                <input class="form-control" placeholder="Search patient..." style="height:36px; padding-left:2.4rem;" />
              </div>
            </div>
          </div>

          <div class="table-responsive">
            <table class="table-soft">
              <thead>
                <tr>
                  <th>Patient ID</th>
                  <th>Patient</th>
                  <th>Email</th>
                  <th>Last Visit</th>
                  <th>Treatment</th>
                  <th>Status</th>
                  <th class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody>
                <!-- Row 1 — John Cruz -->
                <tr>
                  <td><span style="font-size:12px; color:#9ca3af; font-weight:500;">PT-00124</span></td>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <span><img class="avatar-initials" src="/images/default.png" alt=""></span><span
                        class="fw-semibold">John
                        Doe</span>
                    </div>
                  </td>
                  <td>john.c@mail.com
                  </td>
                  <td>Apr 18, 2025</td>
                  <td>Cleaning</td>
                  <td><span class="pill pill-success">Completed</span></td>
                  <td class="text-end">
                    <button class="btn-pill btn-pill-edit me-1" data-bs-toggle="modal" data-bs-target="#viewModal"><i
                        class="bi bi-eye"></i> View</button>
                    <button class="btn-pill btn-pill-archive"><i class="bi bi-archive"></i> Archive</button>
                  </td>
                </tr>
                <!-- Row 2 — Rachel Tan -->
                <tr>
                  <td><span style="font-size:12px; color:#9ca3af; font-weight:500;">PT-00125</span></td>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <span><img class="avatar-initials" src="/images/default.png" alt=""></span><span
                        class="fw-semibold">Rachel
                        Tan</span>
                    </div>
                  </td>
                  <td>rachel.t@mail.com
                  </td>
                  <td>Apr 22, 2025</td>
                  <td>Whitening</td>
                  <td><span class="pill pill-info">Scheduled</span></td>
                  <td class="text-end">
                    <button class="btn-pill btn-pill-edit me-1" data-bs-toggle="modal" data-bs-target="#viewModal"><i
                        class="bi bi-eye"></i> View</button>
                    <button class="btn-pill btn-pill-archive"><i class="bi bi-archive"></i> Archive</button>
                  </td>
                </tr>
                <!-- Row 3 — Peter Lim -->
                <tr>
                  <td><span style="font-size:12px; color:#9ca3af; font-weight:500;">PT-00126</span></td>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <span><img class="avatar-initials" src="/images/default.png" alt=""></span><span
                        class="fw-semibold">Peter Lim</span>
                    </div>
                  </td>
                  <td>peter.lim@mail.com
                  </td>
                  <td>Apr 10, 2025</td>
                  <td>Filling</td>
                  <td><span class="pill pill-danger">Cancelled</span></td>
                  <td class="text-end">
                    <button class="btn-pill btn-pill-edit me-1" data-bs-toggle="modal" data-bs-target="#viewModal"><i
                        class="bi bi-eye"></i> View</button>
                    <button class="btn-pill btn-pill-archive"><i class="bi bi-archive"></i> Archive</button>
                  </td>
                </tr>
                <!-- Row 4 — Nina Morales -->
                <tr>
                  <td><span style="font-size:12px; color:#9ca3af; font-weight:500;">PT-00127</span></td>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <span><img class="avatar-initials" src="/images/default.png" alt=""></span><span
                        class="fw-semibold">Nina Morales</span>
                    </div>
                  </td>
                  <td>nina.m@mail.com
                  </td>
                  <td>Apr 20, 2025</td>
                  <td>Extraction</td>
                  <td><span class="pill pill-success">Completed</span></td>
                  <td class="text-end">
                    <button class="btn-pill btn-pill-edit me-1" data-bs-toggle="modal" data-bs-target="#viewModal"><i
                        class="bi bi-eye"></i> View</button>
                    <button class="btn-pill btn-pill-archive"><i class="bi bi-archive"></i> Archive</button>
                  </td>
                </tr>
                <!-- Row 5 — Alex Cruz -->
                <tr>
                  <td><span style="font-size:12px; color:#9ca3af; font-weight:500;">PT-00128</span></td>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <span><img class="avatar-initials" src="/images/default.png" alt=""></span><span
                        class="fw-semibold">Alex Cruz</span>
                    </div>
                  </td>
                  <td>alex.c@mail.com
                  </td>
                  <td>Apr 25, 2025</td>
                  <td>Braces Adjustment</td>
                  <td><span class="pill pill-info">Scheduled</span></td>
                  <td class="text-end">
                    <button class="btn-pill btn-pill-edit me-1" data-bs-toggle="modal" data-bs-target="#viewModal"><i
                        class="bi bi-eye"></i> View</button>
                    <button class="btn-pill btn-pill-archive"><i class="bi bi-archive"></i> Archive</button>
                  </td>
                </tr>
                <!-- Row 6 — Sarah Yu -->
                <tr>
                  <td><span style="font-size:12px; color:#9ca3af; font-weight:500;">PT-00129</span></td>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <span><img class="avatar-initials" src="/images/default.png" alt=""></span><span
                        class="fw-semibold">Sarah Yu</span>
                    </div>
                  </td>
                  <td>sarah.y@mail.com
                  </td>
                  <td>Apr 19, 2025</td>
                  <td>Check-up</td>
                  <td><span class="pill pill-success">Completed</span></td>
                  <td class="text-end">
                    <button class="btn-pill btn-pill-edit me-1" data-bs-toggle="modal" data-bs-target="#viewModal"><i
                        class="bi bi-eye"></i> View</button>
                    <button class="btn-pill btn-pill-archive"><i class="bi bi-archive"></i> Archive</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="pagination-soft">
            <div>Showing 1 to 6 of 1,284 entries</div>
            <div class="pages">
              <button><i class="bi bi-chevron-left"></i></button>
              <button class="active">1</button>
              <button>2</button>
              <button>3</button>
              <button>…</button>
              <button>214</button>
              <button><i class="bi bi-chevron-right"></i></button>
            </div>
          </div>
        </div><!-- /card-soft -->

      </div><!-- /content -->
    </main>
  </div><!-- /app -->


  <!-- ══════════════════════════════════════════════
     VIEW MODAL — single modal for all records
══════════════════════════════════════════════ -->
  <div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
      <div class="modal-content">

        <!-- Header -->
        <div class="modal-header">
          <div>
            <h5 class="modal-title" id="viewModalLabel">Patient Record</h5>
            <div class="modal-subtitle">View and edit patient information and treatment history</div>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <!-- Body -->
        <div class="modal-body px-4 py-3">

          <!-- Hero -->
          <div class="patient-hero">
            <div><img class="hero-avatar-wrap" src="/images/default.png" alt=""></div>
            <div>
              <div class="hero-name">John Cruz</div>
              <div class="hero-id">PT-00124</div>
              <div class="hero-badges">
                <span class="pill pill-success"><i class="bi bi-check-circle"></i> Completed</span>
                <span class="pill pill-muted"><i class="bi bi-tooth"></i> Cleaning</span>
              </div>
            </div>
          </div>

          <div class="section-label">
            <i class="bi bi-person" style="font-size:13px; color:var(--brand);"></i> Patient Information
          </div>

          <div class="info-grid-edit">
            <div class="field-group">
              <label class="field-label"><i class="bi bi-telephone"></i> Phone</label>
              <input type="tel" class="field-input" value="+63 917 123 4567" />
            </div>
            <div class="field-group">
              <label class="field-label"><i class="bi bi-envelope"></i> Email</label>
              <input type="email" class="field-input" value="john.c@mail.com" />
            </div>
            <div class="field-group">
              <label class="field-label"><i class="bi bi-calendar-date"></i> Date of Birth</label>
              <input type="text" class="field-input" value="Mar 12, 1990" />
            </div>
            <div class="field-group">
              <label class="field-label"><i class="bi bi-calendar3"></i> Age</label>
              <input type="number" min="0" class="field-input" value="11" />
            </div>
            <div class="field-group">
              <label class="field-label"><i class="bi bi-person-fill"></i> Gender</label>
              <select class="field-input">
                <option>Male</option>
                <option>Female</option>
                <option>Other</option>
              </select>
            </div>
            <div class="field-group">
              <label class="field-label"><i class="bi bi-people"></i> Civil Status</label>
              <select class="field-input">
                <option>Single</option>
                <option>Married</option>
                <option>Widowed</option>
                <option>Separated</option>
              </select>
            </div>
            <div class="field-group">
              <label class="field-label"><i class="bi bi-geo-alt"></i> Address</label>
              <input type="text" class="field-input" value="Quezon City, Metro Manila" />
            </div>
            <div class="field-group">
              <label class="field-label"><i class="bi bi-calendar-check"></i> Last Visit</label>
              <input type="text" class="field-input" value="Apr 18, 2025" />
            </div>
          </div>

          <!-- Doctor's Notes -->
          <div class="section-label">
            <i class="bi bi-journal-text" style="font-size:13px; color:var(--brand);"></i> Doctor's Notes
          </div>
          <div class="note-item">
            <div class="note-meta">
              <i class="bi bi-person-circle me-1"></i>Dr. Andrews &nbsp;·&nbsp;
              <i class="bi bi-clock me-1"></i>Apr 18, 2025, 10:30 AM
            </div>
            <div class="note-add-row">
              <textarea
                rows="2">Patient completed routine cleaning. Gums appear healthy. Recommended flossing daily and return in 6 months for follow-up.</textarea>
            </div>
          </div>
          <div class="note-add-row">
            <textarea rows="2" placeholder="Add a note for this patient..."></textarea>
            <button class="btn-brand" style="flex-shrink:0; height:40px; align-self:flex-end;">
              <i class="bi bi-plus-lg"></i> Add Note
            </button>
          </div>
        </div><!-- /modal-body -->

        <!-- Footer -->
        <div class="modal-footer" style="border-top:1px solid var(--border); padding:14px 24px; gap:8px;">
          <button type="button" class="btn-ghost" data-bs-dismiss="modal">
            <i class="bi bi-x"></i> Close
          </button>
          <button type="button" class="btn-brand">
            <i class="bi bi-floppy"></i> Save Changes
          </button>
        </div>

      </div>
    </div>
  </div>


  <!-- ══════════════════════════════════════════════
     ARCHIVES MODAL
══════════════════════════════════════════════ -->
  <div class="modal fade" id="archivesModal" tabindex="-1" aria-labelledby="archivesLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
      <div class="modal-content">

        <div class="modal-header" style="border-bottom:1px solid var(--border);">
          <div>
            <h5 class="modal-title fw-semibold" id="archivesLabel">Patient Records - Archives</h5>
            <div class="modal-subtitle">View and restore archived patient records</div>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <!-- Table card -->
          <div class="card-soft ">
            <div class="data-toolbar">
              <div class="left">
                <span class="text-muted-2 small">Show</span>
                <select class="form-select" style="width:80px;">
                  <option>10</option>
                  <option>25</option>
                  <option>50</option>
                </select>
                <span class="text-muted-2 small">entries</span>
              </div>
              <div class="right">
                <select class="form-select" style="min-width:160px;">
                  <option>All Treatments</option>
                  <option>Cleaning</option>
                  <option>Filling</option>
                  <option>Extraction</option>
                  <option>Whitening</option>
                </select>
                <select class="form-select" style="min-width:140px;">
                  <option>All Status</option>
                  <option>Completed</option>
                  <option>Scheduled</option>
                  <option>Cancelled</option>
                </select>
                <div class="input-icon search">
                  <i class="bi bi-search"></i>
                  <input class="form-control" placeholder="Search patient..."
                    style="height:36px; padding-left:2.4rem;" />
                </div>
              </div>
            </div>
          </div><!-- /card-soft -->

          <div class="table-responsive">
            <table class="table-soft">
              <thead>
                <tr>
                  <th>Patient ID</th>
                  <th>Patient</th>
                  <th>Email</th>
                  <th>Last Visit</th>
                  <th>Treatment</th>
                  <th>Status</th>
                  <th class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody>
                <!-- Row 1 — John Cruz -->
                <tr>
                  <td><span style="font-size:12px; color:#9ca3af; font-weight:500;">PT-00124</span></td>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <span><img class="avatar-initials" src="/images/default.png" alt=""></span><span
                        class="fw-semibold">John
                        Doe</span>
                    </div>
                  </td>
                  <td>john.c@mail.com
                  </td>
                  <td>Apr 18, 2025</td>
                  <td>Cleaning</td>
                  <td><span class="pill pill-success">Completed</span></td>
                  <td class="text-end">
                    <button class="btn-pill btn-pill-archive"><i class="bi bi-archive"></i> Unarchive</button>
                  </td>
                </tr>
                <!-- Row 2 — Rachel Tan -->
                <tr>
                  <td><span style="font-size:12px; color:#9ca3af; font-weight:500;">PT-00125</span></td>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <span><img class="avatar-initials" src="/images/default.png" alt=""></span><span
                        class="fw-semibold">Rachel
                        Tan</span>
                    </div>
                  </td>
                  <td>rachel.t@mail.com
                  </td>
                  <td>Apr 22, 2025</td>
                  <td>Whitening</td>
                  <td><span class="pill pill-info">Scheduled</span></td>
                  <td class="text-end">
                    <button class="btn-pill btn-pill-archive"><i class="bi bi-archive"></i> Unarchive</button>
                  </td>
                </tr>
                <!-- Row 3 — Peter Lim -->
                <tr>
                  <td><span style="font-size:12px; color:#9ca3af; font-weight:500;">PT-00126</span></td>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <span><img class="avatar-initials" src="/images/default.png" alt=""></span><span
                        class="fw-semibold">Peter Lim</span>
                    </div>
                  </td>
                  <td>peter.lim@mail.com
                  </td>
                  <td>Apr 10, 2025</td>
                  <td>Filling</td>
                  <td><span class="pill pill-danger">Cancelled</span></td>
                  <td class="text-end">
                    <button class="btn-pill btn-pill-archive"><i class="bi bi-archive"></i> Unarchive</button>
                  </td>
                </tr>
                <!-- Row 4 — Nina Morales -->
                <tr>
                  <td><span style="font-size:12px; color:#9ca3af; font-weight:500;">PT-00127</span></td>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <span><img class="avatar-initials" src="/images/default.png" alt=""></span><span
                        class="fw-semibold">Nina Morales</span>
                    </div>
                  </td>
                  <td>nina.m@mail.com
                  </td>
                  <td>Apr 20, 2025</td>
                  <td>Extraction</td>
                  <td><span class="pill pill-success">Completed</span></td>
                  <td class="text-end">
                    <button class="btn-pill btn-pill-archive"><i class="bi bi-archive"></i> Unarchive</button>
                  </td>
                </tr>
                <!-- Row 5 — Alex Cruz -->
                <tr>
                  <td><span style="font-size:12px; color:#9ca3af; font-weight:500;">PT-00128</span></td>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <span><img class="avatar-initials" src="/images/default.png" alt=""></span><span
                        class="fw-semibold">Alex Cruz</span>
                    </div>
                  </td>
                  <td>alex.c@mail.com
                  </td>
                  <td>Apr 25, 2025</td>
                  <td>Braces Adjustment</td>
                  <td><span class="pill pill-info">Scheduled</span></td>
                  <td class="text-end">
                    <button class="btn-pill btn-pill-archive"><i class="bi bi-archive"></i> Unarchive</button>
                  </td>
                </tr>
                <!-- Row 6 — Sarah Yu -->
                <tr>
                  <td><span style="font-size:12px; color:#9ca3af; font-weight:500;">PT-00129</span></td>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <span><img class="avatar-initials" src="/images/default.png" alt=""></span><span
                        class="fw-semibold">Sarah Yu</span>
                    </div>
                  </td>
                  <td>sarah.y@mail.com
                  </td>
                  <td>Apr 19, 2025</td>
                  <td>Check-up</td>
                  <td><span class="pill pill-success">Completed</span></td>
                  <td class="text-end">
                    <button class="btn-pill btn-pill-archive"><i class="bi bi-archive"></i> Unarchive</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="pagination-soft mb-3">
            <div>Showing 1 to 6 of 1,284 entries</div>
            <div class="pages">
              <button><i class="bi bi-chevron-left"></i></button>
              <button class="active">1</button>
              <button>2</button>
              <button>3</button>
              <button>…</button>
              <button>214</button>
              <button><i class="bi bi-chevron-right"></i></button>
            </div>
          </div>


          <div class="modal-footer border-0 pt-0">
            <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-brand">Save Changes</button>
          </div>

        </div>
      </div>
    </div>
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
                <p class="notif-text"><strong>Roberto Blanco</strong> has successfully scheduled an appointment on
                  <strong>July 3, 2026</strong> at <strong>10:30 AM</strong>.
                </p>
                <div class="notif-meta"><span>06-02-26</span><span class="notif-dot">•</span><span>2 minutes
                    ago</span>
                </div>
              </div>
              <span class="notif-badge notif-success">Scheduled</span>
            </li>

            <li class="notif-card" data-date="jul4">
              <span class="notif-icon notif-success"><i class="bi bi-check-lg"></i></span>
              <div class="notif-content">
                <p class="notif-text"><strong>Maria Santos</strong> has successfully scheduled an appointment on
                  <strong>July 4, 2026</strong> at <strong>2:00 PM</strong>.
                </p>
                <div class="notif-meta"><span>06-02-26</span><span class="notif-dot">•</span><span>15 minutes
                    ago</span>
                </div>
              </div>
              <span class="notif-badge notif-success">Scheduled</span>
            </li>

            <li class="notif-card" data-date="jul2">
              <span class="notif-icon notif-danger"><i class="bi bi-x-lg"></i></span>
              <div class="notif-content">
                <p class="notif-text"><strong>Juan Dela Cruz</strong> has cancelled their appointment on <strong>July
                    2,
                    2026</strong> at <strong>9:00 AM</strong>.</p>
                <div class="notif-meta"><span>06-02-26</span><span class="notif-dot">•</span><span>1 hour ago</span>
                </div>
              </div>
              <span class="notif-badge notif-danger">Cancelled</span>
            </li>
            <li class="notif-card" data-date="jul2">
              <span class="notif-icon notif-danger"><i class="bi bi-x-lg"></i></span>
              <div class="notif-content">
                <p class="notif-text"><strong>Juan Dela Cruz</strong> has cancelled their appointment on <strong>July
                    2,
                    2026</strong> at <strong>9:00 AM</strong>.</p>
                <div class="notif-meta"><span>06-02-26</span><span class="notif-dot">•</span><span>1 hour ago</span>
                </div>
              </div>
              <span class="notif-badge notif-danger">Cancelled</span>
            </li>
            <li class="notif-card" data-date="jul2">
              <span class="notif-icon notif-danger"><i class="bi bi-x-lg"></i></span>
              <div class="notif-content">
                <p class="notif-text"><strong>Juan Dela Cruz</strong> has cancelled their appointment on <strong>July
                    2,
                    2026</strong> at <strong>9:00 AM</strong>.</p>
                <div class="notif-meta"><span>06-02-26</span><span class="notif-dot">•</span><span>1 hour ago</span>
                </div>
              </div>
              <span class="notif-badge notif-danger">Cancelled</span>
            </li>
            <li class="notif-card" data-date="jul2">
              <span class="notif-icon notif-danger"><i class="bi bi-x-lg"></i></span>
              <div class="notif-content">
                <p class="notif-text"><strong>Juan Dela Cruz</strong> has cancelled their appointment on <strong>July
                    2,
                    2026</strong> at <strong>9:00 AM</strong>.</p>
                <div class="notif-meta"><span>06-02-26</span><span class="notif-dot">•</span><span>1 hour ago</span>
                </div>
              </div>
              <span class="notif-badge notif-danger">Cancelled</span>
            </li>

            <li class="notif-card" data-date="jul5">
              <span class="notif-icon notif-warning"><i class="bi bi-arrow-repeat"></i></span>
              <div class="notif-content">
                <p class="notif-text"><strong>Anna Reyes</strong> has rescheduled her appointment to <strong>July 5,
                    2026</strong> at <strong>11:15 AM</strong>.</p>
                <div class="notif-meta"><span>06-02-26</span><span class="notif-dot">•</span><span>3 hours ago</span>
                </div>
              </div>
              <span class="notif-badge notif-warning">Rescheduled</span>
            </li>

            <li class="notif-card" data-date="jul6">
              <span class="notif-icon notif-success"><i class="bi bi-check-lg"></i></span>
              <div class="notif-content">
                <p class="notif-text"><strong>Mark Villanueva</strong> has successfully scheduled an appointment on
                  <strong>July 6, 2026</strong> at <strong>4:45 PM</strong>.
                </p>
                <div class="notif-meta"><span>06-01-26</span><span class="notif-dot">•</span><span>Yesterday</span>
                </div>
              </div>
              <span class="notif-badge notif-success">Scheduled</span>
            </li>

          </ul>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>