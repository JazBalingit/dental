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
  <style>
    .chart-tooltip {
      position: fixed;
      z-index: 2000;
      background: var(--ink-900, #0f172a);
      color: #fff;
      padding: .45rem .7rem;
      border-radius: .5rem;
      font-size: .78rem;
      font-family: 'Inter', sans-serif;
      pointer-events: none;
      opacity: 0;
      transform: translateY(4px);
      transition: opacity .12s ease, transform .12s ease;
      box-shadow: var(--shadow-md, 0 6px 16px rgba(15,23,42,.08));
      display: flex;
      align-items: center;
      gap: .45rem;
      white-space: nowrap;
    }
    .chart-tooltip.show { opacity: 1; transform: translateY(0); }
    .chart-tooltip .chart-tooltip-dot { width: 8px; height: 8px; border-radius: 50%; flex: none; }
    .chart-legend {
      display: flex;
      flex-wrap: wrap;
      gap: .5rem 1.1rem;
      margin-top: .85rem;
      padding-top: .75rem;
      border-top: 1px solid var(--ink-100, #f1f5f9);
    }
    .chart-legend .legend-item {
      display: flex;
      align-items: center;
      gap: .45rem;
      font-size: .8rem;
      font-family: 'Inter', sans-serif;
      color: var(--ink-700, #334155);
    }
    .chart-legend .legend-dot {
      width: 10px;
      height: 10px;
      border-radius: 50%;
      flex: none;
    }
    .chart-legend .legend-count {
      color: var(--ink-500, #64748b);
    }
    .chart-tip-target { transition: filter .12s ease; }
    .chart-tip-target.chart-active { filter: brightness(1.18) saturate(1.1); }
    .chart-tip-target.chart-active[fill-opacity="0.001"] { fill-opacity: 0.12 !important; }
    .chart-dot { transition: r .12s ease; }
  </style>
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
              @if (session('account_type') === 'staff')
                <li><a class="dropdown-item small" href="{{ route('staffProfile') }}"><i class="bi bi-person me-2"></i>My Profile</a></li>
                <li><hr class="dropdown-divider"></li>
              @endif
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
                  <div class="value">{{ number_format($stats['totalPatients']) }}</div>
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
                  <div class="value">{{ number_format($stats['appointmentsToday']) }}</div>
                </div>
                <div class="icon"><i class="bi bi-calendar-check"></i></div>
              </div>
            </div>
          </div>
          <div class="col-sm-6 col-xl-3">
            <div class="stat-card alt-2">
              <div class="d-flex align-items-start justify-content-between">
                <div>
                  <div class="label">Services Available</div>
                  <div class="value">{{ number_format($stats['availableServices']) }}</div>
                </div>
                <div class="icon"><i class="bi bi-clipboard2-pulse"></i></div>
              </div>
            </div>
          </div>
          <div class="col-sm-6 col-xl-3">
            <div class="stat-card alt-3">
              <div class="d-flex align-items-start justify-content-between">
                <div>
                  <div class="label">Doctor Available Schedule</div>
                  <div class="value">{{ number_format($stats['doctorAvailableSchedule']) }}</div>
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
                    <path d="{{ $overviewChart['areaPath'] }}" fill="url(#g1)" />
                    <path d="{{ $overviewChart['linePath'] }}" fill="none" stroke="#167d1d" stroke-width="3" />
                    @foreach ($overviewChart['points'] as $point)
                      <circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="10" fill="#167d1d" fill-opacity="0.001"
                        pointer-events="all" class="chart-tip-target" style="cursor:pointer;"
                        data-tip-color="#167d1d" data-tip-label="{{ $point['label'] }}"
                        data-tip-value="{{ $point['count'] }} appointment{{ $point['count'] === 1 ? '' : 's' }}"></circle>
                      <circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="4" fill="#167d1d" pointer-events="none" class="chart-dot" />
                      <text x="{{ $point['x'] }}" y="{{ max($point['y'] - 14, 14) }}"
                        text-anchor="{{ $loop->first ? 'start' : ($loop->last ? 'end' : 'middle') }}"
                        font-size="13" font-weight="600" fill="#0f172a" font-family="Inter"
                        pointer-events="none">{{ $point['count'] }}</text>
                      <text x="{{ $point['x'] }}" y="232"
                        text-anchor="{{ $loop->first ? 'start' : ($loop->last ? 'end' : 'middle') }}"
                        font-size="12" fill="#64748b" font-family="Inter" pointer-events="none">{{ $point['day'] }}</text>
                    @endforeach
                  </svg>
                </div>
                <div class="chart-legend">
                  <div class="legend-item">
                    <span class="legend-dot" style="background:#167d1d;"></span>
                    Appointments per day
                    <span class="legend-count">({{ number_format($overviewChart['total']) }} total this week)</span>
                  </div>
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
                    <circle cx="100" cy="100" r="70" fill="none" stroke="#e3fde5" stroke-width="28" />
                    @foreach ($treatmentDonut['segments'] as $segment)
                      <circle cx="100" cy="100" r="70" fill="none" stroke="{{ $segment['color'] }}" stroke-width="28"
                        stroke-dasharray="{{ $segment['dasharray'] }}" stroke-dashoffset="{{ $segment['dashoffset'] }}"
                        transform="rotate(-90 100 100)" pointer-events="all" class="chart-tip-target" style="cursor:pointer;"
                        data-tip-color="{{ $segment['color'] }}" data-tip-label="{{ $segment['label'] }}"
                        data-tip-value="{{ $segment['count'] }} patient{{ $segment['count'] === 1 ? '' : 's' }}"></circle>
                    @endforeach
                    <text x="100" y="95" text-anchor="middle" font-size="14" fill="#64748b"
                      font-family="Inter">Total</text>
                    <text x="100" y="118" text-anchor="middle" font-size="22" fill="#0f172a" font-weight="700"
                      font-family="Poppins">{{ number_format($treatmentDonut['total']) }}</text>
                  </svg>
                </div>
              </div>
              <div class="card-body pt-0">
                <div class="chart-legend">
                  @foreach ($treatmentDonut['segments'] as $segment)
                    <div class="legend-item">
                      <span class="legend-dot" style="background:{{ $segment['color'] }};"></span>
                      {{ $segment['label'] }}
                      <span class="legend-count">({{ $segment['count'] }})</span>
                    </div>
                  @endforeach
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="row g-3">
          <div class="col-xl-6">
            <div class="card-soft h-100">
              <div class="card-header">Appointments by Service</div>
              <div class="card-body">
                <div class="chart-ph">
                  <svg viewBox="0 0 600 240" preserveAspectRatio="none">
                    @foreach ($serviceBars['bars'] as $bar)
                      <rect x="{{ $bar['x'] }}" y="{{ $bar['y'] }}" width="40" height="{{ $bar['height'] }}" rx="6"
                        fill="{{ $bar['color'] }}"
                        pointer-events="all" class="chart-tip-target" style="cursor:pointer;"
                        data-tip-color="{{ $bar['color'] }}" data-tip-label="{{ $bar['name'] }}"
                        data-tip-value="{{ $bar['count'] }} appointment{{ $bar['count'] === 1 ? '' : 's' }}"></rect>
                    @endforeach
                  </svg>
                </div>
                <div class="chart-legend">
                  @foreach ($serviceBars['bars'] as $bar)
                    <div class="legend-item">
                      <span class="legend-dot" style="background:{{ $bar['color'] }};"></span>
                      {{ $bar['name'] }}
                      <span class="legend-count">({{ $bar['count'] }})</span>
                    </div>
                  @endforeach
                </div>
              </div>
            </div>
          </div>
          <div class="col-xl-6">
            <div class="card-soft h-100">
              <div class="card-header d-flex justify-content-between">
                Notifications <a href="#" class="small text-decoration-none" style="color: var(--brand-700);"
                  data-bs-toggle="modal" data-bs-target="#allNotificationsModal">View all</a>
              </div>
              <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                  @php
                    $dashPill = fn ($type) => match ($type) {
                        'success' => 'pill-success',
                        'danger' => 'pill-danger',
                        'warning' => 'pill-warning',
                        default => 'pill-info',
                    };
                  @endphp
                  @forelse ($adminNotifications->take(4) as $n)
                    <li class="list-group-item d-flex align-items-center gap-3 py-3 px-3">
                      <div class="flex-fill">
                        <div class="fw-semibold">{{ $n->Title }}</div>
                        <div class="small text-muted-2">{{ $n->Message }} • {{ $n->created_at->diffForHumans() }}</div>
                      </div>
                      <span class="pill {{ $dashPill($n->Type) }}">{{ $n->Status ?? ucfirst($n->Type) }}</span>
                    </li>
                  @empty
                    <li class="list-group-item py-3 px-3 text-muted-2 small">No notifications yet.</li>
                  @endforelse
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

            <a class="custom-range-link" id="customRangeLink" href="#" role="button">
              <i class="bi bi-sliders me-1"></i>Use a custom range instead
            </a>

            <div class=" mt-3" id="customRange">
              <div class="row g-2">
                <div class="col-6">
                  <label class="form-label small fw-semibold mb-1">From</label>
                  <input type="date" class="form-control" id="reportDateFrom">
                </div>
                <div class="col-6">
                  <label class="form-label small fw-semibold mb-1">To</label>
                  <input type="date" class="form-control" id="reportDateTo">
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
                <input class="form-check-input" type="checkbox" role="switch" checked id="includeCharts">
              </div>
            </div>

            <div class="include-row">
              <div>
                <div class="it-title">Patient details</div>
                <div class="it-desc">Names &amp; contact info, not just totals</div>
              </div>
              <div class="form-check form-switch mb-0">
                <input class="form-check-input" type="checkbox" role="switch" id="includePatientDetails">
              </div>
            </div>
          </div>

        </div>

        <!-- Footer -->
        <div class="modal-footer d-flex justify-content-between">
          <button type="button" class="btn-ghost" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-brand px-4" id="generateReportBtn">
            <i class="bi bi-download me-1"></i> Generate Report
          </button>
        </div>

      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    (function () {
        var tooltip = document.createElement('div');
        tooltip.className = 'chart-tooltip';
        tooltip.innerHTML = '<span class="chart-tooltip-dot"></span><span class="chart-tooltip-text"></span>';
        document.body.appendChild(tooltip);
        var dot = tooltip.querySelector('.chart-tooltip-dot');
        var text = tooltip.querySelector('.chart-tooltip-text');

        function position(x, y) {
            var pad = 14;
            var left = x + pad;
            var top = y + pad;
            var rect = tooltip.getBoundingClientRect();
            if (left + rect.width > window.innerWidth - 8) left = x - rect.width - pad;
            if (top + rect.height > window.innerHeight - 8) top = y - rect.height - pad;
            tooltip.style.left = left + 'px';
            tooltip.style.top = top + 'px';
        }

        function show(el, x, y) {
            dot.style.background = el.getAttribute('data-tip-color') || '#167d1d';
            text.textContent = el.getAttribute('data-tip-label') + ': ' + el.getAttribute('data-tip-value');
            tooltip.classList.add('show');
            position(x, y);
        }

        function hide() {
            tooltip.classList.remove('show');
        }

        document.querySelectorAll('.chart-tip-target').forEach(function (el) {
            var dotSibling = el.nextElementSibling && el.nextElementSibling.classList.contains('chart-dot')
                ? el.nextElementSibling : null;
            el.addEventListener('mouseenter', function (e) {
                show(el, e.clientX, e.clientY);
                el.classList.add('chart-active');
                if (dotSibling) dotSibling.setAttribute('r', 7);
            });
            el.addEventListener('mousemove', function (e) { position(e.clientX, e.clientY); });
            el.addEventListener('mouseleave', function () {
                hide();
                el.classList.remove('chart-active');
                if (dotSibling) dotSibling.setAttribute('r', 4);
            });
        });
    })();
  </script>
  <script>
    (function () {
        var reportTypeMap = { rtAppointments: 'appointments', rtPatients: 'patients', rtRevenue: 'schedule', rtSummary: 'summary' };
        var quickRangeMap = { qrToday: 'today', qrWeek: 'week', qrMonth: 'month', qrAll: 'all' };
        var reportUrl = @json(route('reports.generate'));

        var customRangeActive = false;
        var customLink = document.getElementById('customRangeLink');
        var quickRadios = document.querySelectorAll('input[name="quickRange"]');

        if (customLink) {
            customLink.addEventListener('click', function (e) {
                e.preventDefault();
                customRangeActive = true;
                quickRadios.forEach(function (r) { r.checked = false; });
                var fromInput = document.getElementById('reportDateFrom');
                if (fromInput) fromInput.focus();
            });
        }

        quickRadios.forEach(function (r) {
            r.addEventListener('change', function () { customRangeActive = false; });
        });

        var generateBtn = document.getElementById('generateReportBtn');
        if (generateBtn) {
            generateBtn.addEventListener('click', function () {
                var typeInput = document.querySelector('input[name="reportType"]:checked');
                var reportType = typeInput ? (reportTypeMap[typeInput.id] || 'appointments') : 'appointments';

                var params = new URLSearchParams();
                params.set('type', reportType);

                if (customRangeActive) {
                    var from = document.getElementById('reportDateFrom');
                    var to = document.getElementById('reportDateTo');
                    params.set('range', 'custom');
                    if (from && from.value) params.set('from', from.value);
                    if (to && to.value) params.set('to', to.value);
                } else {
                    var rangeInput = document.querySelector('input[name="quickRange"]:checked');
                    params.set('range', rangeInput ? (quickRangeMap[rangeInput.id] || 'week') : 'week');
                }

                var charts = document.getElementById('includeCharts');
                var patients = document.getElementById('includePatientDetails');
                params.set('charts', charts && charts.checked ? '1' : '0');
                params.set('patients', patients && patients.checked ? '1' : '0');

                window.open(reportUrl + '?' + params.toString(), '_blank');
            });
        }
    })();
  </script>

</body>

</html>