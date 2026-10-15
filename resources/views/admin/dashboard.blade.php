<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <link rel="icon" type="image/png" href="/images/puspus_logo.png">
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
    <link rel="stylesheet" href="{{ asset('css/modals.css') }}">
    <link rel="stylesheet" href="{{ asset('css/mobile.css') }}">
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
      @include('partials.admin-sidebar-nav', ['active' => 'dashboard'])
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
            <div class="crumbs">Here's what's happening at the clinic — <span class="fw-semibold" style="color: var(--brand-700);"><i class="bi bi-calendar3 me-1"></i>{{ $range['label'] }}</span></div>
          </div>
          <div class="d-flex gap-2 flex-wrap">
            {{-- Period filter — every date-based card and chart follows it --}}
            <form method="GET" action="{{ route('dashboard') }}" class="m-0 d-flex gap-2 flex-wrap align-items-center" id="dashPeriodForm">
              <select name="period" class="form-select" style="height:40px; min-width:150px; width:auto;" aria-label="Dashboard period" id="dashPeriodSelect">
                @foreach ($periods as $value => $periodLabel)
                  <option value="{{ $value }}" {{ $period === $value ? 'selected' : '' }}>{{ $periodLabel }}</option>
                @endforeach
              </select>
              {{-- Custom Range — only shown while "Custom Range" is picked --}}
              <div class="d-flex gap-2 align-items-center flex-wrap" id="dashCustomRange" @if ($period !== 'custom') hidden @endif>
                <input type="date" name="from" class="form-control" style="height:40px; width:auto;" aria-label="From date"
                  value="{{ $range['from'] }}" @if ($period !== 'custom') disabled @endif>
                <span class="text-muted-2 small">to</span>
                <input type="date" name="to" class="form-control" style="height:40px; width:auto;" aria-label="To date"
                  value="{{ $range['to'] }}" @if ($period !== 'custom') disabled @endif>
                <button type="submit" class="btn btn-outline-secondary px-3" style="height:40px;">Apply</button>
              </div>
            </form>
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
                  <div class="label">Appointments <span class="text-muted-2" style="font-weight:400;">({{ $range['noun'] }})</span></div>
                  <div class="value">{{ number_format($stats['appointmentsInPeriod']) }}</div>
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
                  <div class="label">Dentist Available Schedule <span class="text-muted-2" style="font-weight:400;">({{ $range['noun'] }})</span></div>
                  <div class="value">{{ number_format($stats['dentistAvailableSchedule']) }}</div>
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
                <span class="pill pill-info">{{ $overviewChart['pill'] }}</span>
              </div>
              <div class="card-body">
                <div class="chart-ph">
                  <canvas id="overviewChart"></canvas>
                </div>
                <div class="chart-legend">
                  <div class="legend-item">
                    <span class="legend-dot" style="background:#167d1d;"></span>
                    Appointments per day
                    <span class="legend-count">({{ number_format($overviewChart['total']) }} {{ $overviewChart['totalLabel'] }})</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-xl-4">
            <div class="card-soft h-100">
              <div class="card-header d-flex justify-content-between align-items-center">Appointments by Status <span class="pill pill-info">{{ $range['noun'] }}</span></div>
              <div class="card-body">
                <div class="chart-ph" style="position: relative;">
                  <canvas id="statusDonutChart"></canvas>
                  <div style="position:absolute; inset:0; display:flex; flex-direction:column; align-items:center; justify-content:center; pointer-events:none;">
                    <div style="font-size:14px; color:#64748b; font-family:'Inter',sans-serif;">Total</div>
                    <div style="font-size:22px; color:#0f172a; font-weight:700; font-family:'Poppins',sans-serif;">{{ number_format($statusDonut['total']) }}</div>
                  </div>
                </div>
                <div class="chart-legend">
                  @foreach ($statusDonut['segments'] as $segment)
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
              <div class="card-header d-flex justify-content-between align-items-center">Appointments by Service <span class="pill pill-info">{{ $range['noun'] }}</span></div>
              <div class="card-body">
                <div class="chart-ph" style="height: 320px;">
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
                Activity Logs
                @if (session('is_super_admin'))
                  <a href="{{ route('configuration', ['settingsTab' => 'activity']) }}"
                    class="small text-decoration-none" style="color: var(--brand-700);">View all</a>
                @endif
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
                    <li class="list-group-item d-flex align-items-start gap-3 py-3 px-3">
                      <div class="flex-fill" style="min-width:0;">
                        <div class="fw-semibold text-truncate">{{ $actor }}</div>
                        <div class="small text-muted-2" style="overflow-wrap:anywhere;">
                          {{ $log->Description ?: $log->ActivityType }} • {{ optional($when)->diffForHumans() ?? '—' }}
                        </div>
                      </div>
                      <span class="pill {{ $pill }} mt-1">{{ $log->ActivityType }}</span>
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

  @include('partials.report-preview-modal')

  <!-- ============ GENERATE REPORT MODAL ============ -->
  <div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:1000px;">
      <div class="modal-content">

        <!-- Header -->
        <div class="modal-header report-modal-header">
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

            {{-- Same periods as the dashboard filter. --}}
            <select class="form-select" id="reportPeriod" aria-label="Report date range" style="max-width: 420px;">
              @foreach ($reportPeriods as $value => $label)
                <option value="{{ $value }}" {{ $value === ($period === 'custom' ? 'daily' : $period) ? 'selected' : '' }}>{{ $label }}</option>
              @endforeach
            </select>

            <div class="mt-3" id="customRange" hidden>
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
        <div class="modal-footer">
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
        var donutData = @json($statusDonut['segments']);
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

        new Chart(document.getElementById('statusDonutChart'), {
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
                                return item.label + ': ' + count + ' appointment' + (count === 1 ? '' : 's');
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
                    borderRadius: 5,
                    borderSkipped: false,
                    barThickness: 'flex',
                    maxBarThickness: 38,
                    categoryPercentage: 0.72,
                    barPercentage: 0.86,
                }],
            },
            options: {
                // Horizontal bars read cleanly whether the clinic offers 2
                // services or 20 — the vertical version left one lonely
                // skyscraper bar in a wide empty box.
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                layout: { padding: { right: 12 } },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function (item) { return item.parsed.x + ' appointment' + (item.parsed.x === 1 ? '' : 's'); },
                        },
                    },
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grace: '6%',
                        ticks: { precision: 0, color: '#64748b', font: { size: 11 } },
                        grid: { color: '#eef1ee', drawBorder: false },
                        border: { display: false },
                    },
                    y: {
                        ticks: { color: '#334155', font: { size: 12, family: "'Inter', sans-serif" } },
                        grid: { display: false, drawBorder: false },
                        border: { display: false },
                    },
                },
            },
        });
    })();
  </script>
  <script>
    (function () {
        var reportTypeMap = { rtAppointments: 'appointments', rtPatients: 'patients', rtSummary: 'summary' };

        var reportUrl = @json(route('reports.generate'));

        // Date range dropdown: "Custom Range" reveals the From/To dates.
        var periodSelect = document.getElementById('reportPeriod');
        var customBox = document.getElementById('customRange');
        if (periodSelect && customBox) {
            periodSelect.addEventListener('change', function () {
                customBox.hidden = periodSelect.value !== 'custom';
                if (!customBox.hidden) document.getElementById('reportDateFrom')?.focus();
            });
        }

        var generateBtn = document.getElementById('generateReportBtn');
        if (generateBtn) {
            generateBtn.addEventListener('click', function () {
                var typeInput = document.querySelector('input[name="reportType"]:checked');
                var reportType = typeInput ? (reportTypeMap[typeInput.id] || 'appointments') : 'appointments';

                var params = new URLSearchParams();
                params.set('type', reportType);

                var chosen = periodSelect ? periodSelect.value : 'daily';
                if (chosen === 'custom') {
                    var from = document.getElementById('reportDateFrom');
                    var to = document.getElementById('reportDateTo');
                    if (!from.value || !to.value) {
                        (from.value ? to : from).focus();
                        from.classList.toggle('is-invalid', !from.value);
                        to.classList.toggle('is-invalid', !to.value);
                        return;
                    }
                    from.classList.remove('is-invalid'); to.classList.remove('is-invalid');
                    params.set('range', 'custom');
                    params.set('from', from.value);
                    params.set('to', to.value);
                } else {
                    params.set('range', chosen);
                }

                var charts = document.getElementById('includeCharts');
                var patients = document.getElementById('includePatientDetails');
                params.set('charts', charts && charts.checked ? '1' : '0');
                params.set('patients', patients && patients.checked ? '1' : '0');

                // Show the report in the "Report Preview" modal first — it's
                // only printed when the admin clicks Print there.
                params.set('preview', '1');
                openPreview(reportUrl + '?' + params.toString());
            });
        }

        var optionsModalEl = document.getElementById('reportModal');
        var previewModalEl = document.getElementById('reportPreviewModal');
        var previewFrame = document.getElementById('reportPreviewFrame');
        var previewLoading = document.getElementById('reportPreviewLoading');
        var printBtn = document.getElementById('reportPreviewPrintBtn');
        var backBtn = document.getElementById('reportPreviewBackBtn');

        function openPreview(url) {
            printBtn.disabled = true;
            previewLoading.hidden = false;
            previewFrame.style.visibility = 'hidden';
            previewFrame.src = url;
            swapModal(optionsModalEl, previewModalEl);
        }

        // Open `to` only once `from` has finished closing — two Bootstrap
        // modals transitioning at once can leave a stray backdrop behind.
        function swapModal(from, to) {
            if (from.classList.contains('show')) {
                from.addEventListener('hidden.bs.modal', function () {
                    bootstrap.Modal.getOrCreateInstance(to).show();
                }, { once: true });
                bootstrap.Modal.getOrCreateInstance(from).hide();
            } else {
                bootstrap.Modal.getOrCreateInstance(to).show();
            }
        }

        previewFrame.addEventListener('load', function () {
            if (!previewFrame.src || previewFrame.src === 'about:blank') return;
            previewLoading.hidden = true;
            previewFrame.style.visibility = 'visible';
            printBtn.disabled = false;
        });

        printBtn.addEventListener('click', function () {
            previewFrame.contentWindow.focus();
            previewFrame.contentWindow.print();
        });

        backBtn.addEventListener('click', function () {
            swapModal(previewModalEl, optionsModalEl);
        });

        // Unload the report when the preview closes so a stale one never flashes.
        previewModalEl.addEventListener('hidden.bs.modal', function () {
            previewFrame.src = 'about:blank';
        });
    })();
  </script>

  <script>
    // Dashboard period filter: a preset reloads right away; "Custom Range"
    // reveals the From/To dates and waits for Apply.
    (function () {
      var select = document.getElementById('dashPeriodSelect');
      var custom = document.getElementById('dashCustomRange');
      if (!select || !custom) return;
      select.addEventListener('change', function () {
        var isCustom = select.value === 'custom';
        custom.hidden = !isCustom;
        custom.querySelectorAll('input').forEach(function (i) { i.disabled = !isCustom; });
        if (!isCustom) select.form.submit();
      });
    })();
  </script>
</body>

</html>
