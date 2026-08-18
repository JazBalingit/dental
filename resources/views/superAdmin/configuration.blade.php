<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Configuration • Dental Clinic</title>
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
        <a href="{{ route('patientRecords') }}"><i class="bi bi-folder2-open"></i> Patient Records</a>
        <div class="nav-section">System</div>
        <a href="{{ route('configuration') }}" class="active"><i class="bi bi-sliders2"></i> Configuration</a>
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
          @include('partials.admin-notif-dropdown')
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

      <div class="content">
        <div class="page-head">
          <div>
            <h2>Configuration</h2>
            <div class="crumbs">Branding, activity & audit logs.</div>
          </div>
          <!-- BUTTON ARCHIVE -->
          <div class="d-flex justify-content-end">
            <button class="btn btn-brand" data-bs-toggle="modal" data-bs-target="#archivesModal"><i
                class="bi bi-archive"></i> Archives</button>
          </div>
        </div>

        <!-- LOGO -->
        <div class="card-soft mb-4">
          <div class="card-header d-flex align-items-center justify-content-between">
            <span><i class="bi bi-image me-2" style="color: var(--brand-700);"></i> Change Clinic Logo</span>
            <span class="pill pill-info">PNG / SVG • Max 2MB</span>
          </div>
          <div class="card-body">
            <div class="row g-4 align-items-center">
              <div class="col-md-3 text-center">
                <div class="logo-preview mx-auto"><i class="bi bi-heart-pulse-fill"></i></div>
                <div class="small text-muted-2 mt-2">Current logo</div>
              </div>
              <div class="col-md-9">
                <div class="upload-zone">
                  <i class="bi bi-cloud-arrow-up-fill mb-2 d-block"></i>
                  <div class="fw-semibold">Drag & drop your new logo here</div>
                  <div class="small text-muted-2 mb-3">or click to browse from your computer</div>
                  <button class="btn btn-brand px-4"><i class="bi bi-folder2-open me-1"></i> Browse Files</button>
                </div>
                <div class="d-flex gap-2 mt-3 justify-content-end">
                  <button class="btn btn-ghost px-3">Reset</button>
                  <button class="btn btn-brand px-3">Save Changes</button>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- ACTIVITY LOGS -->
        <div class="card-soft mb-4">
          <div class="card-header d-flex align-items-center justify-content-between">
            <span><i class="bi bi-activity me-2" style="color: var(--brand-700);"></i> Activity Logs</span>
            <div class="d-flex gap-2">
              <div class="input-icon">
                <i class="bi bi-search"></i>
                <input class="form-control" placeholder="Search activity..."
                  style="height:38px; padding-left:2.3rem; min-width:240px;" />
              </div>
              <button class="btn btn-ghost px-3" style="height:38px;"><i class="bi bi-download"></i></button>
            </div>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table-soft" style="border-radius:0; box-shadow:none;">
                <thead>
                  <tr>
                    <th>User</th>
                    <th>Action</th>
                    <th>Module</th>
                    <th>Date & Time</th>
                    <th class="text-end">Action</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td><span><img class="avatar-initials" src="/images/default.png" alt=""></span> Admin</td>
                    <td>Logged in</td>
                    <td>Auth</td>
                    <td>Apr 22, 2025 09:01</td>
                    <td class="text-end">
                      <button class="btn btn-pill btn-pill-archive"><i class="bi bi-archive"></i> Archive</button>
                    </td>
                  </tr>
                  <tr>
                    <td><span><img class="avatar-initials" src="/images/default.png" alt=""></span>Jane Doe</td>
                    <td>Logged in</td>
                    <td>Patients</td>
                    <td>Apr 22, 2025 09:14</td>
                    <td class="text-end">
                      <button class="btn btn-pill btn-pill-archive"><i class="bi bi-archive"></i> Archive</button>
                    </td>
                  </tr>
                  <tr>
                    <td><span><img class="avatar-initials" src="/images/default.png" alt=""></span>Rachel Smith</td>
                    <td>Logged out</td>
                    <td>Appointments</td>
                    <td>Apr 22, 2025 09:32</td>
                    <td class="text-end">
                      <button class="btn btn-pill btn-pill-archive"><i class="bi bi-archive"></i> Archive</button>
                    </td>
                  </tr>
                  <tr>
                    <td><span><img class="avatar-initials" src="/images/default.png" alt=""></span>Mark Andrews</td>
                    <td>Logged out</td>
                    <td>Appointments</td>
                    <td>Apr 22, 2025 09:45</td>
                    <td class="text-end">
                      <button class="btn btn-pill btn-pill-archive"><i class="bi bi-archive"></i> Archive</button>
                    </td>
                  </tr>
                  <tr>
                    <td><span><img class="avatar-initials" src="/images/default.png" alt=""></span>Admin</td>
                    <td>Logged out</td>
                    <td>Configuration</td>
                    <td>Apr 22, 2025 10:02</td>
                    <td class="text-end">
                      <button class="btn btn-pill btn-pill-archive"><i class="bi bi-archive"></i> Archive</button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <!-- AUDIT LOGS -->
        <div class="card-soft">
          <div class="card-header d-flex align-items-center justify-content-between">
            <span><i class="bi bi-shield-check me-2" style="color: var(--brand-700);"></i> Audit Logs</span>
            <div class="d-flex gap-2">
              <select class="form-select" style="height:38px; min-width:140px;">
                <option>All Severity</option>
                <option>Info</option>
                <option>Warning</option>
                <option>Critical</option>
              </select>
              <button class="btn btn-ghost px-3" style="height:38px;"><i class="bi bi-funnel"></i></button>
            </div>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table-soft" style="border-radius:0; box-shadow:none;">
                <thead>
                  <tr>
                    <th>Severity</th>
                    <th>Event</th>
                    <th>Performed By</th>
                    <th>Target</th>
                    <th>Timestamp</th>
                    <th class="text-end">Action</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td><span class="pill pill-info">Info</span></td>
                    <td>Admin changed system logo.</td>
                    <td>Admin</td>
                    <td>auth.session</td>
                    <td>Apr 22, 2025 09:01</td>
                    <td class="text-end">
                      <button class="btn btn-pill btn-pill-archive"><i class="bi bi-archive"></i> Archive</button>
                    </td>
                  </tr>
                  <tr>
                    <td><span class="pill pill-warning">Warning</span></td>
                    <td>Admin edited patient record.</td>
                    <td>unknown</td>
                    <td>jane.d@clinic.com</td>
                    <td>Apr 22, 2025 08:44</td>
                    <td class="text-end">
                      <button class="btn btn-pill btn-pill-archive"><i class="bi bi-archive"></i> Archive</button>
                    </td>
                  </tr>
                  <tr>
                    <td><span class="pill pill-info">Info</span></td>
                    <td>Admin generated a report.</td>
                    <td> Admin</td>
                    <td>user #00125</td>
                    <td>Apr 21, 2025 16:20</td>
                    <td class="text-end">
                      <button class="btn btn-pill btn-pill-archive"><i class="bi bi-archive"></i> Archive</button>
                    </td>
                  </tr>
                  <tr>
                    <td><span class="pill pill-danger">Critical</span></td>
                    <td>Admin edited user information</td>
                    <td> Admin</td>
                    <td>PT-00098</td>
                    <td>Apr 21, 2025 11:05</td>
                    <td class="text-end">
                      <button class="btn btn-pill btn-pill-archive"><i class="bi bi-archive"></i> Archive</button>
                    </td>
                  </tr>
                  <tr>
                    <td><span class="pill pill-warning">Warning</span></td>
                    <td>Admin archived a patient record.</td>
                    <td> Admin</td>
                    <td>config.branding</td>
                    <td>Apr 22, 2025 10:02</td>
                    <td class="text-end">
                      <button class="btn btn-pill btn-pill-archive"><i class="bi bi-archive"></i> Archive</button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>

  <!-- ===================== MODAL ===================== -->
  <div class="modal fade" id="archivesModal" tabindex="-1" aria-labelledby="modalWeek3Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
      <div class="modal-content">
        <div class="modal-header border-0 pb-0">
          <div>
            <h5 class="modal-title fw-semibold" id="modalWeek3Label">Configuration -Archives</h5>
            <div class="small text-muted">View and manage archived information</div>
          </div>
        </div>
        <div class="modal-body pt-2">
          <!-- ACTIVITY LOGS -->
          <div class="card-soft mb-4">
            <div class="card-header d-flex align-items-center justify-content-between">
              <span><i class="bi bi-activity me-2" style="color: var(--brand-700);"></i> Activity Logs</span>
              <div class="d-flex gap-2">
                <div class="input-icon">
                  <i class="bi bi-search"></i>
                  <input class="form-control" placeholder="Search activity..."
                    style="height:38px; padding-left:2.3rem; min-width:240px;" />
                </div>
                <button class="btn btn-ghost px-3" style="height:38px;"><i class="bi bi-download"></i></button>
              </div>
            </div>
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table-soft" style="border-radius:0; box-shadow:none;">
                  <thead>
                    <tr>
                      <th>User</th>
                      <th>Action</th>
                      <th>Module</th>
                      <th>Date & Time</th>
                      <th class="text-end">Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td><span><img class="avatar-initials" src="/images/default.png" alt=""></span> Admin</td>
                      <td>Logged in</td>
                      <td>Auth</td>
                      <td>Apr 22, 2025 09:01</td>
                      <td class="text-end">
                        <button class="btn btn-pill btn-pill-archive"><i class="bi bi-archive"></i> Archive</button>
                      </td>
                    </tr>
                    <tr>
                      <td><span><img class="avatar-initials" src="/images/default.png" alt=""></span>Jane Doe</td>
                      <td>Logged in</td>
                      <td>Patients</td>
                      <td>Apr 22, 2025 09:14</td>
                      <td class="text-end">
                        <button class="btn btn-pill btn-pill-archive"><i class="bi bi-archive"></i> Archive</button>
                      </td>
                    </tr>
                    <tr>
                      <td><span><img class="avatar-initials" src="/images/default.png" alt=""></span>Rachel Smith</td>
                      <td>Logged out</td>
                      <td>Appointments</td>
                      <td>Apr 22, 2025 09:32</td>
                      <td class="text-end">
                        <button class="btn btn-pill btn-pill-archive"><i class="bi bi-archive"></i> Archive</button>
                      </td>
                    </tr>
                    <tr>
                      <td><span><img class="avatar-initials" src="/images/default.png" alt=""></span>Mark Andrews</td>
                      <td>Logged out</td>
                      <td>Appointments</td>
                      <td>Apr 22, 2025 09:45</td>
                      <td class="text-end">
                        <button class="btn btn-pill btn-pill-archive"><i class="bi bi-archive"></i> Archive</button>
                      </td>
                    </tr>
                    <tr>
                      <td><span><img class="avatar-initials" src="/images/default.png" alt=""></span>Admin</td>
                      <td>Logged out</td>
                      <td>Configuration</td>
                      <td>Apr 22, 2025 10:02</td>
                      <td class="text-end">
                        <button class="btn btn-pill btn-pill-archive"><i class="bi bi-archive"></i> Archive</button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
          <!-- AUDIT LOGS -->
          <div class="card-soft">
            <div class="card-header d-flex align-items-center justify-content-between">
              <span><i class="bi bi-shield-check me-2" style="color: var(--brand-700);"></i> Audit Logs</span>
              <div class="d-flex gap-2">
                <select class="form-select" style="height:38px; min-width:140px;">
                  <option>All Severity</option>
                  <option>Info</option>
                  <option>Warning</option>
                  <option>Critical</option>
                </select>
                <button class="btn btn-ghost px-3" style="height:38px;"><i class="bi bi-funnel"></i></button>
              </div>
            </div>
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table-soft" style="border-radius:0; box-shadow:none;">
                  <thead>
                    <tr>
                      <th>Severity</th>
                      <th>Event</th>
                      <th>Performed By</th>
                      <th>Target</th>
                      <th>Timestamp</th>
                      <th class="text-end">Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td><span class="pill pill-info">Info</span></td>
                      <td>Admin changed system logo.</td>
                      <td>Admin</td>
                      <td>auth.session</td>
                      <td>Apr 22, 2025 09:01</td>
                      <td class="text-end">
                        <button class="btn btn-pill btn-pill-archive"><i class="bi bi-archive"></i> Archive</button>
                      </td>
                    </tr>
                    <tr>
                      <td><span class="pill pill-warning">Warning</span></td>
                      <td>Admin edited patient record.</td>
                      <td>unknown</td>
                      <td>jane.d@clinic.com</td>
                      <td>Apr 22, 2025 08:44</td>
                      <td class="text-end">
                        <button class="btn btn-pill btn-pill-archive"><i class="bi bi-archive"></i> Archive</button>
                      </td>
                    </tr>
                    <tr>
                      <td><span class="pill pill-info">Info</span></td>
                      <td>Admin generated a report.</td>
                      <td> Admin</td>
                      <td>user #00125</td>
                      <td>Apr 21, 2025 16:20</td>
                      <td class="text-end">
                        <button class="btn btn-pill btn-pill-archive"><i class="bi bi-archive"></i> Archive</button>
                      </td>
                    </tr>
                    <tr>
                      <td><span class="pill pill-danger">Critical</span></td>
                      <td>Admin edited user information</td>
                      <td> Admin</td>
                      <td>PT-00098</td>
                      <td>Apr 21, 2025 11:05</td>
                      <td class="text-end">
                        <button class="btn btn-pill btn-pill-archive"><i class="bi bi-archive"></i> Archive</button>
                      </td>
                    </tr>
                    <tr>
                      <td><span class="pill pill-warning">Warning</span></td>
                      <td>Admin archived a patient record.</td>
                      <td> Admin</td>
                      <td>config.branding</td>
                      <td>Apr 22, 2025 10:02</td>
                      <td class="text-end">
                        <button class="btn btn-pill btn-pill-archive"><i class="bi bi-archive"></i> Archive</button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
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
  @include('partials.admin-notif-modal')
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
