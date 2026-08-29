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
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
  <style>
    .chart-ph canvas { width: 100% !important; height: 100% !important; }
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
      @include('partials.admin-profile-badge')
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
        </div>
      </div>

      <div class="content">
        <div class="page-head">
          <div>
            <h2>Welcome back, {{ $adminName }} 👋</h2>
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
                  <canvas id="overviewChart"></canvas>
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
                <div class="chart-ph w-100" style="height: 240px; background: transparent; position: relative;">
                  <canvas id="treatmentDonutChart"></canvas>
                  <div style="position:absolute; inset:0; display:flex; flex-direction:column; align-items:center; justify-content:center; pointer-events:none;">
                    <div style="font-size:14px; color:#64748b; font-family:'Inter',sans-serif;">Total</div>
                    <div style="font-size:22px; color:#0f172a; font-weight:700; font-family:'Poppins',sans-serif;">{{ number_format($treatmentDonut['total']) }}</div>
                  </div>
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
                  <canvas id="serviceBarsChart"></canvas>
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
                Activity Logs <a href="{{ route('configuration', ['settingsTab' => 'activity']) }}"
                  class="small text-decoration-none" style="color: var(--brand-700);">View all</a>
              </div>
              <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                  @forelse ($recentActivity as $log)
                    @php
                      $ua = $log->userAccount;
                      $isStaff = $ua?->AccountType === 'Staff';
                      $info = $isStaff ? $ua?->staffInfo : $ua?->patientInfo;
                      $actor = $log->ActorName
                          ?: ($info ? trim($info->FirstName . ' ' . $info->LastName) : ($ua->Email ?? 'Unknown'));
                      $when = $log->LoggedInTime ?? $log->created_at;
                      $isFail = str_starts_with($log->ActivityType, 'Failed');
                      $pill = $isFail ? 'pill-danger' : ($isStaff ? 'pill-info' : 'pill-success');
                    @endphp
                    <li class="list-group-item d-flex align-items-center gap-3 py-3 px-3">
                      <div class="flex-fill">
                        <div class="fw-semibold">{{ $actor }}</div>
                        <div class="small text-muted-2">
                          {{ $log->Description ?: $log->ActivityType }} • {{ optional($when)->diffForHumans() ?? '—' }}
                        </div>
                      </div>
                      <span class="pill {{ $pill }}">{{ $log->ActivityType }}</span>
                    </li>
                  @empty
                    <li class="list-group-item py-3 px-3 text-muted-2 small">No activity recorded yet.</li>
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
        var overviewData = @json($overviewChart);
        var donutData = @json($treatmentDonut['segments']);
        var barsData = @json($serviceBars['bars']);

        var brandGreen = '#167d1d';

        new Chart(document.getElementById('overviewChart'), {
            type: 'line',
            data: {
                labels: overviewData.labels,
                datasets: [{
                    data: overviewData.data,
                    borderColor: brandGreen,
                    backgroundColor: 'rgba(22, 125, 29, 0.12)',
                    pointBackgroundColor: '#fff',
                    pointBorderColor: brandGreen,
                    pointBorderWidth: 2.5,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            title: function (items) { return overviewData.fullLabels[items[0].dataIndex]; },
                            label: function (item) { return item.parsed.y + ' appointment' + (item.parsed.y === 1 ? '' : 's'); },
                        },
                    },
                },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#eef1ee' } },
                    x: { grid: { display: false } },
                },
            },
        });

        new Chart(document.getElementById('treatmentDonutChart'), {
            type: 'doughnut',
            data: {
                labels: donutData.map(function (s) { return s.label; }),
                datasets: [{
                    data: donutData.map(function (s) { return s.count; }),
                    backgroundColor: donutData.map(function (s) { return s.color; }),
                    borderWidth: 0,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function (item) {
                                var count = item.parsed;
                                return item.label + ': ' + count + ' patient' + (count === 1 ? '' : 's');
                            },
                        },
                    },
                },
            },
        });

        new Chart(document.getElementById('serviceBarsChart'), {
            type: 'bar',
            data: {
                labels: barsData.map(function (b) { return b.name; }),
                datasets: [{
                    data: barsData.map(function (b) { return b.count; }),
                    backgroundColor: barsData.map(function (b) { return b.color; }),
                    borderRadius: 6,
                    maxBarThickness: 40,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function (item) { return item.parsed.y + ' appointment' + (item.parsed.y === 1 ? '' : 's'); },
                        },
                    },
                },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#eef1ee' } },
                    x: { grid: { display: false } },
                },
            },
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