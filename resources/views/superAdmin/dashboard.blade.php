<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Dashboard • Dental Clinic</title>
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
    <!-- SIDEBAR -->
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
        <a href="{{ route('dashboard') }}" class="active"><i class="bi bi-grid-1x2-fill"></i> Dashboard</a>
        <a href="{{ route('staffAcc') }}"><i class="bi bi-people-fill"></i> Staff Accounts</a>
        <a href="{{ route('userAcc') }}"><i class="bi bi-people-fill"></i> User Accounts</a>
        <a href="{{ route('dentistSchedule') }}"><i class="bi bi-calendar3"></i> Dentist Schedule</a>
        <a href="{{ route('walkIn') }}"><i class="bi bi-calendar3"></i> Walk-in Appointments</a>
        <a href="{{ route('appointmentApproval') }}"><i class="bi bi-clipboard2-check"></i> Appointment
          Approval</a>
        <a href="{{ route('appointments') }}"><i class="bi bi-clipboard2-check"></i> Appointments</a>
        <a href="{{ route('patientRecords') }}"><i class="bi bi-folder2-open"></i> Patient Records</a>
        <div class="nav-section">System</div>
        <a href="{{ route('configuration') }}"><i class="bi bi-sliders2"></i> Configuration</a>
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
            <h2>Welcome back, Admin 👋</h2>
            <div class="crumbs">Here's what's happening at the clinic today.</div>
          </div>
          <div class="d-flex gap-2">
            <button class="btn btn-brand px-3" style="height:40px;" data-bs-toggle="modal"
              data-bs-target="#reportModal"><i class="bi bi-download me-1"></i> Generate Report</button>
          </div>
        </div>

        <div class="row g-3 mb-4">
          <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
              <div class="d-flex align-items-start justify-content-between">
                <div>
                  <div class="label">Total Patients</div>
                  <div class="value">1,284</div>
                </div>
                <div class="icon"><i class="bi bi-people-fill"></i></div>
              </div>
            </div>
          </div>
          <div class="col-sm-6 col-xl-3">
            <div class="stat-card alt-1">
              <div class="d-flex align-items-start justify-content-between">
                <div>
                  <div class="label">Appointments Today</div>
                  <div class="value">42</div>
                </div>
                <div class="icon"><i class="bi bi-calendar-check"></i></div>
              </div>
            </div>
          </div>
          <div class="col-sm-6 col-xl-3">
            <div class="stat-card alt-2">
              <div class="d-flex align-items-start justify-content-between">
                <div>
                  <div class="label">Cancelled Appointments</div>
                  <div class="value">17</div>
                </div>
                <div class="icon"><i class="bi bi-x-circle"></i></div>
              </div>
            </div>
          </div>
          <div class="col-sm-6 col-xl-3">
            <div class="stat-card alt-3">
              <div class="d-flex align-items-start justify-content-between">
                <div>
                  <div class="label">Doctor Available Schedule</div>
                  <div class="value">12</div>
                </div>
                <div class="icon"><i class="fa-regular fa-calendar"></i></div>
              </div>
            </div>
          </div>
        </div>

        <div class="row g-3 mb-4">
          <div class="col-xl-8">
            <div class="card-soft">
              <div class="card-header d-flex justify-content-between align-items-center">
                Appointments Overview
                <span class="pill pill-info">Last 7 days</span>
              </div>
              <div class="card-body">
                <div class="chart-ph">
                  <svg viewBox="0 0 600 240" preserveAspectRatio="none">
                    <defs>
                      <linearGradient id="g1" x1="0" x2="0" y1="0" y2="1">
                        <stop offset="0%" stop-color="#167d1d" stop-opacity=".55" />
                        <stop offset="100%" stop-color="#167d1d" stop-opacity="0" />
                      </linearGradient>
                    </defs>
                    <path
                      d="M0,180 C60,140 100,160 160,120 C220,80 260,150 320,110 C380,70 440,140 500,90 C540,60 580,90 600,80 L600,240 L0,240 Z"
                      fill="url(#g1)" />
                    <path
                      d="M0,180 C60,140 100,160 160,120 C220,80 260,150 320,110 C380,70 440,140 500,90 C540,60 580,90 600,80"
                      fill="none" stroke="#167d1d" stroke-width="3" />
                  </svg>
                </div>
              </div>
            </div>
          </div>
          <div class="col-xl-4">
            <div class="card-soft h-100">
              <div class="card-header">Patients by Treatment</div>
              <div class="card-body d-flex align-items-center justify-content-center">
                <div class="chart-ph w-100" style="height: 240px; background: transparent;">
                  <svg viewBox="0 0 200 200">
                    <circle cx="100" cy="100" r="70" fill="none" stroke="#167d1d" stroke-width="28" />
                    <circle cx="100" cy="100" r="70" fill="none" stroke="#008f07" stroke-width="28"
                      stroke-dasharray="180 440" transform="rotate(-90 100 100)" />
                    <circle cx="100" cy="100" r="70" fill="none" stroke="#55d85e" stroke-width="28"
                      stroke-dasharray="120 440" stroke-dashoffset="-180" transform="rotate(-90 100 100)" />
                    <circle cx="100" cy="100" r="70" fill="none" stroke="#10b981" stroke-width="28"
                      stroke-dasharray="80 440" stroke-dashoffset="-300" transform="rotate(-90 100 100)" />
                    <text x="100" y="95" text-anchor="middle" font-size="14" fill="#64748b"
                      font-family="Inter">Total</text>
                    <text x="100" y="118" text-anchor="middle" font-size="22" fill="#0f172a" font-weight="700"
                      font-family="Poppins">1,284</text>
                  </svg>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="row g-3">
          <div class="col-xl-6">
            <div class="card-soft">
              <div class="card-header">Revenue by Service</div>
              <div class="card-body">
                <div class="chart-ph">
                  <svg viewBox="0 0 600 240" preserveAspectRatio="none">
                    <g fill="#008f07">
                      <rect x="40" y="120" width="40" height="100" rx="6" />
                      <rect x="120" y="80" width="40" height="140" rx="6" />
                      <rect x="200" y="150" width="40" height="70" rx="6" />
                      <rect x="280" y="60" width="40" height="160" rx="6" />
                      <rect x="360" y="100" width="40" height="120" rx="6" />
                      <rect x="440" y="40" width="40" height="180" rx="6" />
                      <rect x="520" y="90" width="40" height="130" rx="6" />
                    </g>
                  </svg>
                </div>
              </div>
            </div>
          </div>
          <div class="col-xl-6">
            <div class="card-soft">
              <div class="card-header d-flex justify-content-between">
                Recent Activity <a href="#" class="small text-decoration-none" style="color: var(--brand-700);">View
                  all</a>
              </div>
              <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                  <li class="list-group-item d-flex align-items-center gap-3 py-3 px-3">
                    <div class="flex-fill">
                      <div class="fw-semibold">Jane Doe booked an appointment</div>
                      <div class="small text-muted-2">Cleaning • Apr 22, 10:30 AM</div>
                    </div>
                    <span class="pill pill-info">New</span>
                  </li>
                  <li class="list-group-item d-flex align-items-center gap-3 py-3 px-3">
                    <div class="flex-fill">
                      <div class="fw-semibold">Dr. Mark Andrews completed a procedure</div>
                      <div class="small text-muted-2">Tooth extraction • 9:15 AM</div>
                    </div>
                    <span class="pill pill-success">Done</span>
                  </li>
                  <li class="list-group-item d-flex align-items-center gap-3 py-3 px-3">
                    <div class="flex-fill">
                      <div class="fw-semibold">Rachel Smith requested rescheduling</div>
                      <div class="small text-muted-2">Apr 24 → Apr 26</div>
                    </div>
                    <span class="pill pill-warning">Pending</span>
                  </li>
                  <li class="list-group-item d-flex align-items-center gap-3 py-3 px-3">
                    <div class="flex-fill">
                      <div class="fw-semibold">Peter Lim cancelled appointment</div>
                      <div class="small text-muted-2">Whitening • Apr 23, 2:00 PM</div>
                    </div>
                    <span class="pill pill-danger">Cancelled</span>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>

  @include('partials.admin-notif-modal')

  <!-- ============ GENERATE REPORT MODAL ============ -->
  <div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:1000px;">
      <div class="modal-content">

        <!-- Header -->
        <div class="report-modal-header d-flex align-items-start gap-3">
          <div class="icon-badge"><i class="bi bi-file-earmark-bar-graph"></i></div>
          <div class="flex-grow-1">
            <div class="modal-title" id="reportModalLabel">Generate Report</div>
            <div class="subtitle">Export clinic data as a downloadable report</div>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <!-- Body -->
        <div class="modal-body">

          <!-- Report type -->
          <div class="section-block">
            <div class="section-label"><i class="bi bi-grid"></i> Report type</div>
            <div class="row g-2">

              <div class="col-6">
                <input type="radio" class="btn-check" name="reportType" id="rtAppointments" checked>
                <label class="report-card" for="rtAppointments">
                  <div class="rc-icon"><i class="bi bi-calendar-check"></i></div>
                  <div class="rc-title">Appointments</div>
                  <div class="rc-desc">Bookings, cancellations &amp; schedule</div>
                </label>
              </div>

              <div class="col-6">
                <input type="radio" class="btn-check" name="reportType" id="rtPatients">
                <label class="report-card" for="rtPatients">
                  <div class="rc-icon"><i class="bi bi-people"></i></div>
                  <div class="rc-title">Patients</div>
                  <div class="rc-desc">Records &amp; demographics</div>
                </label>
              </div>

              <div class="col-6">
                <input type="radio" class="btn-check" name="reportType" id="rtRevenue">
                <label class="report-card" for="rtRevenue">
                  <div class="rc-icon"><i class="fa-regular fa-calendar"></i></div>
                  <div class="rc-title">Doctor Schedule</div>
                  <div class="rc-desc">Availability of doctor's schedule</div>
                </label>
              </div>

              <div class="col-6">
                <input type="radio" class="btn-check" name="reportType" id="rtSummary">
                <label class="report-card" for="rtSummary">
                  <div class="rc-icon"><i class="bi bi-clipboard-data"></i></div>
                  <div class="rc-title">Full Summary</div>
                  <div class="rc-desc">Everything in one report</div>
                </label>
              </div>

            </div>
          </div>

          <!-- Date range -->
          <div class="section-block">
            <div class="section-label"><i class="bi bi-calendar3"></i> Date range</div>

            <div class="d-flex flex-wrap gap-2 mb-2">
              <input type="radio" class="btn-check" name="quickRange" id="qrToday">
              <label class="range-pill" for="qrToday">Today</label>

              <input type="radio" class="btn-check" name="quickRange" id="qrWeek" checked>
              <label class="range-pill" for="qrWeek">Last 7 days</label>

              <input type="radio" class="btn-check" name="quickRange" id="qrMonth">
              <label class="range-pill" for="qrMonth">This month</label>

              <input type="radio" class="btn-check" name="quickRange" id="qrAll">
              <label class="range-pill" for="qrAll">All time</label>
            </div>

            <a class="custom-range-link">
              <i class="bi bi-sliders me-1"></i>Use a custom range instead
            </a>

            <div class=" mt-3" id="customRange">
              <div class="row g-2">
                <div class="col-6">
                  <label class="form-label small fw-semibold mb-1">From</label>
                  <input type="date" class="form-control">
                </div>
                <div class="col-6">
                  <label class="form-label small fw-semibold mb-1">To</label>
                  <input type="date" class="form-control">
                </div>
              </div>
            </div>
          </div>

          <!-- Format
          <div class="section-block">
            <div class="section-label"><i class="bi bi-file-earmark"></i> File format</div>
            <div class="row g-2">
              <div class="col-4">
                <input type="radio" class="btn-check" name="fileFormat" id="fmtPdf" checked>
                <label class="format-pill" for="fmtPdf">
                  <i class="bi bi-file-earmark-pdf"></i>
                  <span>PDF</span>
                </label>
              </div>
              <div class="col-4">
                <input type="radio" class="btn-check" name="fileFormat" id="fmtCsv">
                <label class="format-pill" for="fmtCsv">
                  <i class="bi bi-filetype-csv"></i>
                  <span>CSV</span>
                </label>
              </div>
              <div class="col-4">
                <input type="radio" class="btn-check" name="fileFormat" id="fmtExcel">
                <label class="format-pill" for="fmtExcel">
                  <i class="bi bi-file-earmark-spreadsheet"></i>
                  <span>Excel</span>
                </label>
              </div>
            </div>
          </div> -->

          <!-- Include options -->
          <div class="section-block">
            <div class="section-label"><i class="bi bi-sliders2"></i> Include</div>

            <div class="include-row">
              <div>
                <div class="it-title">Charts &amp; graphs</div>
                <div class="it-desc">Visual breakdowns alongside the data</div>
              </div>
              <div class="form-check form-switch mb-0">
                <input class="form-check-input" type="checkbox" role="switch" checked>
              </div>
            </div>

            <div class="include-row">
              <div>
                <div class="it-title">Patient details</div>
                <div class="it-desc">Names &amp; contact info, not just totals</div>
              </div>
              <div class="form-check form-switch mb-0">
                <input class="form-check-input" type="checkbox" role="switch">
              </div>
            </div>
          </div>

        </div>

        <!-- Footer -->
        <div class="modal-footer d-flex justify-content-between">
          <button type="button" class="btn-ghost" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-brand px-4">
            <i class="bi bi-download me-1"></i> Generate Report
          </button>
        </div>

      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>