<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>User Accounts • Dental Clinic</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
    integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
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
        <a href="{{ route('userAcc') }}" class="active"><i class="bi bi-people-fill"></i> User Accounts</a>
        <a href="{{ route('dentistSchedule') }}"><i class="bi bi-calendar3"></i> Dentist Schedule</a>
        <a href="{{ route('walkIn') }}"><i class="bi bi-calendar3"></i> Walk-in Appointments</a>
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
            <h2>User Accounts</h2>
            <div class="crumbs">Manage clinic staff, dentists, and patient logins.</div>
          </div>
          <div>
            <button class="btn btn-brand" data-bs-toggle="modal" data-bs-target="#archivesModal"><i
                class="bi bi-archive"></i> Archives</button>
          </div>
        </div>

        <div class="card-soft p-3 p-md-4">
          <div class="data-toolbar">
            <div class="left">
              <span class="text-muted-2 small">Show</span>
              <select class="form-select" style="width: 80px;">
                <option>10</option>
                <option>25</option>
                <option>50</option>
              </select>
              <span class="text-muted-2 small">entries</span>
            </div>
            <div class="right">
              <div class="input-icon search">
                <i class="bi bi-search"></i>
                <input class="form-control" placeholder="Search by name or email..."
                  style="height:40px; padding-left:2.4rem;" />
              </div>
            </div>
          </div>

          <div class="table-responsive">
            <table class="table-soft">
              <thead>
                <tr>
                  <th>User</th>
                  <th>Email</th>
                  <th>Role</th>
                  <th>Status</th>
                  <th>Last Login</th>
                  <th class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td><span><img class="avatar-initials" src="/images/default.png" alt=""></span><span
                      class="fw-semibold">Jane
                      Doe</span></td>
                  <td>jane.doe@clinic.com</td>
                  <td>Patient</td>
                  <td><span class="pill pill-success">Active</span></td>
                  <td>Apr 22, 2025 09:14</td>
                  <td class="text-end">
                    <button class="btn btn-pill btn-pill-edit me-1" data-bs-toggle="modal"
                      data-bs-target="#editUserModal"><i class="bi bi-pencil-square"></i> Edit</button>
                    <button class="btn btn-pill btn-pill-archive"><i class="bi bi-archive"></i> Archive</button>
                  </td>
                </tr>
                <tr>
                  <td><span><img class="avatar-initials" src="/images/default.png" alt=""></span><span
                      class="fw-semibold">Mark
                      Andrews</span></td>
                  <td>mark.a@clinic.com</td>
                  <td>Patient</td>
                  <td><span class="pill pill-success">Active</span></td>
                  <td>Apr 22, 2025 08:02</td>
                  <td class="text-end">
                    <button class="btn btn-pill btn-pill-edit me-1" data-bs-toggle="modal"
                      data-bs-target="#editUserModal"><i class="bi bi-pencil-square"></i> Edit</button>
                    <button class="btn btn-pill btn-pill-archive"><i class="bi bi-archive"></i> Archive</button>
                  </td>
                </tr>
                <tr>
                  <td><span><img class="avatar-initials" src="/images/default.png" alt=""></span><span
                      class="fw-semibold">Rachel Smith</span></td>
                  <td>rachel.s@clinic.com</td>
                  <td>Patient</td>
                  <td><span class="pill pill-warning">Pending</span></td>
                  <td>Apr 21, 2025 17:45</td>
                  <td class="text-end">
                    <button class="btn btn-pill btn-pill-edit me-1" data-bs-toggle="modal"
                      data-bs-target="#editUserModal"><i class="bi bi-pencil-square"></i> Edit</button>
                    <button class="btn btn-pill btn-pill-archive"><i class="bi bi-archive"></i> Archive</button>
                  </td>
                </tr>
                <tr>
                  <td><span><img class="avatar-initials" src="/images/default.png" alt=""></span><span
                      class="fw-semibold">Peter
                      Lim</span></td>
                  <td>peter.lim@mail.com</td>
                  <td>Patient</td>
                  <td><span class="pill pill-muted">Inactive</span></td>
                  <td>Mar 18, 2025 11:22</td>
                  <td class="text-end">
                    <button class="btn btn-pill btn-pill-edit me-1" data-bs-toggle="modal"
                      data-bs-target="#editUserModal"><i class="bi bi-pencil-square"></i> Edit</button>
                    <button class="btn btn-pill btn-pill-archive"><i class="bi bi-archive"></i> Archive</button>
                  </td>
                </tr>
                <tr>
                  <td><span><img class="avatar-initials" src="/images/default.png" alt=""></span><span
                      class="fw-semibold">Alex
                      Cruz</span></td>
                  <td>alex.cruz@mail.com</td>
                  <td>Patient</td>
                  <td><span class="pill pill-success">Active</span></td>
                  <td>Apr 22, 2025 10:11</td>
                  <td class="text-end">
                    <button class="btn btn-pill btn-pill-edit me-1" data-bs-toggle="modal"
                      data-bs-target="#editUserModal"><i class="bi bi-pencil-square"></i> Edit</button>
                    <button class="btn btn-pill btn-pill-archive"><i class="bi bi-archive"></i> Archive</button>
                  </td>
                </tr>
                <tr>
                  <td><span><img class="avatar-initials" src="/images/default.png" alt=""></span><span
                      class="fw-semibold">Nina
                      Morales</span></td>
                  <td>nina.m@clinic.com</td>
                  <td>Patient</td>
                  <td><span class="pill pill-success">Active</span></td>
                  <td>Apr 22, 2025 07:55</td>
                  <td class="text-end">
                    <button class="btn btn-pill btn-pill-edit me-1" data-bs-toggle="modal"
                      data-bs-target="#editUserModal"><i class="bi bi-pencil-square"></i> Edit</button>
                    <button class="btn btn-pill btn-pill-archive"><i class="bi bi-archive"></i> Archive</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="pagination-soft">
            <div>Showing 1 to 6 of 124 entries</div>
            <div class="pages">
              <button><i class="bi bi-chevron-left"></i></button>
              <button class="active">1</button>
              <button>2</button>
              <button>3</button>
              <button>…</button>
              <button>21</button>
              <button><i class="bi bi-chevron-right"></i></button>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>

  <!-- ===================== ARCHIVES MODAL ===================== -->
  <div class="modal fade" id="archivesModal" tabindex="-1" aria-labelledby="modalWeek3Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
      <div class="modal-content">
        <div class="modal-header border-0 pb-0">
          <div>
            <h5 class="modal-title fw-semibold" id="modalWeek3Label">User Accounts -Archives</h5>
            <div class="small text-muted">View and manage archived information</div>
          </div>

        </div>
        <div class="modal-body pt-2">
          <div class="card-soft p-3 p-md-4">
            <div class="data-toolbar">
              <div class="left">
                <span class="text-muted-2 small">Show</span>
                <select class="form-select" style="width: 80px;">
                  <option>10</option>
                  <option>25</option>
                  <option>50</option>
                </select>
                <span class="text-muted-2 small">entries</span>
              </div>
              <div class="right">
                <div class="input-icon search">
                  <i class="bi bi-search"></i>
                  <input class="form-control" placeholder="Search by name or email..."
                    style="height:40px; padding-left:2.4rem;" />
                </div>
              </div>
            </div>
          </div>
          <div class="table-responsive">
            <table class="table-soft">
              <thead>
                <tr>
                  <th>User</th>
                  <th>Email</th>
                  <th>Role</th>
                  <th>Status</th>
                  <th>Last Login</th>
                  <th class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td><span><img class="avatar-initials" src="/images/default.png" alt=""></span><span
                      class="fw-semibold">Jane
                      Doe</span></td>
                  <td>jane.doe@clinic.com</td>
                  <td>Patient</td>
                  <td><span class="pill pill-success">Active</span></td>
                  <td>Apr 22, 2025 09:14</td>
                  <td class="text-end">
                    <button class="btn btn-pill btn-pill-edit me-1" data-bs-toggle="modal"
                      data-bs-target="#editUserModal"><i class="bi bi-pencil-square"></i>
                      Edit</button>
                    <button class="btn btn-pill btn-pill-archive"><i class="bi bi-archive"></i> Unarchive</button>
                  </td>
                </tr>
                <tr>
                  <td><span><img class="avatar-initials" src="/images/default.png" alt=""></span><span
                      class="fw-semibold">Mark
                      Andrews</span></td>
                  <td>mark.a@clinic.com</td>
                  <td>Patient</td>
                  <td><span class="pill pill-success">Active</span></td>
                  <td>Apr 22, 2025 08:02</td>
                  <td class="text-end">
                    <button class="btn btn-pill btn-pill-edit me-1" data-bs-toggle="modal"
                      data-bs-target="#editUserModal"><i class="bi bi-pencil-square"></i>
                      Edit</button>
                    <button class="btn btn-pill btn-pill-archive"><i class="bi bi-archive"></i> Unarchive</button>
                  </td>
                </tr>
                <tr>
                  <td><span><img class="avatar-initials" src="/images/default.png" alt=""></span><span
                      class="fw-semibold">Rachel Smith</span></td>
                  <td>rachel.s@clinic.com</td>
                  <td>Patient</td>
                  <td><span class="pill pill-warning">Pending</span></td>
                  <td>Apr 21, 2025 17:45</td>
                  <td class="text-end">
                    <button class="btn btn-pill btn-pill-edit me-1" data-bs-toggle="modal"
                      data-bs-target="#editUserModal"><i class="bi bi-pencil-square"></i>
                      Edit</button>
                    <button class="btn btn-pill btn-pill-archive"><i class="bi bi-archive"></i> Unarchive</button>
                  </td>
                </tr>
                <tr>
                  <td><span><img class="avatar-initials" src="/images/default.png" alt=""></span><span
                      class="fw-semibold">Peter
                      Lim</span></td>
                  <td>peter.lim@mail.com</td>
                  <td>Patient</td>
                  <td><span class="pill pill-muted">Inactive</span></td>
                  <td>Mar 18, 2025 11:22</td>
                  <td class="text-end">
                    <button class="btn btn-pill btn-pill-edit me-1" data-bs-toggle="modal"
                      data-bs-target="#editUserModal"><i class="bi bi-pencil-square"></i>
                      Edit</button>
                    <button class="btn btn-pill btn-pill-archive"><i class="bi bi-archive"></i> Unarchive</button>
                  </td>
                </tr>
                <tr>
                  <td><span><img class="avatar-initials" src="/images/default.png" alt=""></span><span
                      class="fw-semibold">Alex
                      Cruz</span></td>
                  <td>alex.cruz@mail.com</td>
                  <td>Patient</td>
                  <td><span class="pill pill-success">Active</span></td>
                  <td>Apr 22, 2025 10:11</td>
                  <td class="text-end">
                    <button class="btn btn-pill btn-pill-edit me-1" data-bs-toggle="modal"
                      data-bs-target="#editUserModal"><i class="bi bi-pencil-square"></i>
                      Edit</button>
                    <button class="btn btn-pill btn-pill-archive"><i class="bi bi-archive"></i> Unarchive</button>
                  </td>
                </tr>
                <tr>
                  <td><span><img class="avatar-initials" src="/images/default.png" alt=""></span><span
                      class="fw-semibold">Nina
                      Morales</span></td>
                  <td>nina.m@clinic.com</td>
                  <td>Patient</td>
                  <td><span class="pill pill-success">Active</span></td>
                  <td>Apr 22, 2025 07:55</td>
                  <td class="text-end">
                    <button class="btn btn-pill btn-pill-edit me-1" data-bs-toggle="modal"
                      data-bs-target="#editUserModal"><i class="bi bi-pencil-square"></i>
                      Edit</button>
                    <button class="btn btn-pill btn-pill-archive"><i class="bi bi-archive"></i> Unarchive</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="pagination-soft">
            <div>Showing 1 to 6 of 124 entries</div>
            <div class="pages">
              <button><i class="bi bi-chevron-left"></i></button>
              <button class="active">1</button>
              <button>2</button>
              <button>3</button>
              <button>…</button>
              <button>21</button>
              <button><i class="bi bi-chevron-right"></i></button>
            </div>
          </div>
        </div>
        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Close</button>
          <button type="button" class="btn btn-brand">Save Changes</button>
        </div>
      </div>
    </div>
  </div>

  <!-- ===================== EDIT USER MODAL (shared by both tables) ===================== -->
  <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header border-0 pb-0">
          <div>
            <h5 class="modal-title fw-semibold" id="editUserModalLabel">Edit User</h5>
            <div class="small text-muted">Update account details</div>
          </div>
        </div>
        <div class="modal-body pt-2">

          <div class="d-flex align-items-center gap-3 mb-4">
            <img class="avatar-initials" src="/images/default.png" alt="" style="width:64px;height:64px;">
            <div>
              <button class="btn btn-pill btn-pill-edit" type="button"><i class="bi bi-camera"></i> Change
                Photo</button>
              <div class="small text-muted-2 mt-1">JPG or PNG, max 2MB.</div>
            </div>
          </div>

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">First Name</label>
              <div class="input-icon">
                <i class="bi bi-person"></i>
                <input type="text" class="form-control" placeholder="First name" style="padding-left:2.4rem;">
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Last Name</label>
              <div class="input-icon">
                <i class="bi bi-person"></i>
                <input type="text" class="form-control" placeholder="Last name" style="padding-left:2.4rem;">
              </div>
            </div>
            <div class="col-md-12">
              <label class="form-label">Status</label>
              <select class="form-select">
                <option>Active</option>
                <option>Pending</option>
                <option>Inactive</option>
              </select>
            </div>

            <div class="col-md-12">
              <label class="form-label">Last Login</label>
              <input type="text" class="form-control" value="Apr 22, 2025 09:14" disabled>
            </div>
          </div>

        </div>
        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-brand">Save Changes</button>
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
                <div class="notif-meta"><span>06-02-26</span><span class="notif-dot">•</span><span>2 minutes ago</span>
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
                <div class="notif-meta"><span>06-02-26</span><span class="notif-dot">•</span><span>15 minutes ago</span>
                </div>
              </div>
              <span class="notif-badge notif-success">Scheduled</span>
            </li>

            <li class="notif-card" data-date="jul2">
              <span class="notif-icon notif-danger"><i class="bi bi-x-lg"></i></span>
              <div class="notif-content">
                <p class="notif-text"><strong>Juan Dela Cruz</strong> has cancelled their appointment on <strong>July 2,
                    2026</strong> at <strong>9:00 AM</strong>.</p>
                <div class="notif-meta"><span>06-02-26</span><span class="notif-dot">•</span><span>1 hour ago</span>
                </div>
              </div>
              <span class="notif-badge notif-danger">Cancelled</span>
            </li>
            <li class="notif-card" data-date="jul2">
              <span class="notif-icon notif-danger"><i class="bi bi-x-lg"></i></span>
              <div class="notif-content">
                <p class="notif-text"><strong>Juan Dela Cruz</strong> has cancelled their appointment on <strong>July 2,
                    2026</strong> at <strong>9:00 AM</strong>.</p>
                <div class="notif-meta"><span>06-02-26</span><span class="notif-dot">•</span><span>1 hour ago</span>
                </div>
              </div>
              <span class="notif-badge notif-danger">Cancelled</span>
            </li>
            <li class="notif-card" data-date="jul2">
              <span class="notif-icon notif-danger"><i class="bi bi-x-lg"></i></span>
              <div class="notif-content">
                <p class="notif-text"><strong>Juan Dela Cruz</strong> has cancelled their appointment on <strong>July 2,
                    2026</strong> at <strong>9:00 AM</strong>.</p>
                <div class="notif-meta"><span>06-02-26</span><span class="notif-dot">•</span><span>1 hour ago</span>
                </div>
              </div>
              <span class="notif-badge notif-danger">Cancelled</span>
            </li>
            <li class="notif-card" data-date="jul2">
              <span class="notif-icon notif-danger"><i class="bi bi-x-lg"></i></span>
              <div class="notif-content">
                <p class="notif-text"><strong>Juan Dela Cruz</strong> has cancelled their appointment on <strong>July 2,
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
                <div class="notif-meta"><span>06-02-26</span><span class="notif-dot">•</span><span>2 minutes ago</span>
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
                <div class="notif-meta"><span>06-02-26</span><span class="notif-dot">•</span><span>15 minutes ago</span>
                </div>
              </div>
              <span class="notif-badge notif-success">Scheduled</span>
            </li>

            <li class="notif-card" data-date="jul2">
              <span class="notif-icon notif-danger"><i class="bi bi-x-lg"></i></span>
              <div class="notif-content">
                <p class="notif-text"><strong>Juan Dela Cruz</strong> has cancelled their appointment on <strong>July 2,
                    2026</strong> at <strong>9:00 AM</strong>.</p>
                <div class="notif-meta"><span>06-02-26</span><span class="notif-dot">•</span><span>1 hour ago</span>
                </div>
              </div>
              <span class="notif-badge notif-danger">Cancelled</span>
            </li>
            <li class="notif-card" data-date="jul2">
              <span class="notif-icon notif-danger"><i class="bi bi-x-lg"></i></span>
              <div class="notif-content">
                <p class="notif-text"><strong>Juan Dela Cruz</strong> has cancelled their appointment on <strong>July 2,
                    2026</strong> at <strong>9:00 AM</strong>.</p>
                <div class="notif-meta"><span>06-02-26</span><span class="notif-dot">•</span><span>1 hour ago</span>
                </div>
              </div>
              <span class="notif-badge notif-danger">Cancelled</span>
            </li>
            <li class="notif-card" data-date="jul2">
              <span class="notif-icon notif-danger"><i class="bi bi-x-lg"></i></span>
              <div class="notif-content">
                <p class="notif-text"><strong>Juan Dela Cruz</strong> has cancelled their appointment on <strong>July 2,
                    2026</strong> at <strong>9:00 AM</strong>.</p>
                <div class="notif-meta"><span>06-02-26</span><span class="notif-dot">•</span><span>1 hour ago</span>
                </div>
              </div>
              <span class="notif-badge notif-danger">Cancelled</span>
            </li>
            <li class="notif-card" data-date="jul2">
              <span class="notif-icon notif-danger"><i class="bi bi-x-lg"></i></span>
              <div class="notif-content">
                <p class="notif-text"><strong>Juan Dela Cruz</strong> has cancelled their appointment on <strong>July 2,
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