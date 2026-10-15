<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <link rel="icon" type="image/png" href="/images/puspus_logo.png">
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  @php
    $typeTitles = [
        'appointments' => 'Appointments Report',
        'patients' => 'Patients Report',
        'summary' => 'Full Summary Report',
    ];
    $reportTitle = $typeTitles[$type] ?? 'Report';
    // Phrase for the covered dates, e.g. "from Sep 14, 2026 to Sep 20, 2026".
    $rangePhrase = match (true) {
        !$start || !$end => 'across all dates',
        $start->isSameDay($end) => 'on ' . $start->format('M j, Y'),
        default => 'from ' . $start->format('M j, Y') . ' to ' . $end->format('M j, Y'),
    };
    $withCharts = $includeCharts ?? false;
    $withDetails = $includePatientDetails ?? false;

    $descriptions = [
        'appointments' => "This report covers completed visits {$rangePhrase}, including walk-ins and follow-ups. It shows how many visits were completed, how many patients were served, how many services were performed and how many visits came from online appointments, walk-ins and follow-ups"
            . ($withCharts ? ', along with completed visits over time, per booking type and per service' : '')
            . ', followed by every completed visit grouped by service, with each patient\'s ID, name, booking type and appointment details'
            . ($withDetails ? ' including their contact.' : '.'),
        'patients' => 'This report covers every patient, registered or walk-in, who had a completed visit ' . $rangePhrase . '. It shows the total number of patients, how many are registered or walk-in, and their gender breakdown'
            . ($withCharts ? ', along with the number of patients per treatment' : '')
            . ($withDetails ? ', followed by the full list of patients with each one\'s type, age, gender, phone, address and visits.' : '.'),
        'services' => "This section shows how many appointments were booked for each service {$rangePhrase}.",
    ];
    $summaryIntro = "This summary combines the appointments and patients reports {$rangePhrase} into a single document.";
    $statusPill = fn ($status) => match ($status) {
        'Completed' => 'good',
        'Approved' => 'info',
        'Pending' => 'warn',
        'Cancelled', 'Declined', 'No Show' => 'bad',
        'Available' => 'good',
        default => 'info',
    };
  @endphp
  <title>{{ $reportTitle }} • Dental Clinic</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link
    href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
  <style>
    body { background: var(--ink-100); font-family: 'Inter', sans-serif; }
    /* The chart bars, stat cards and status pills are all CSS backgrounds,
       which browsers strip when printing unless told to keep them (the
       "Background graphics" checkbox) — force them on so the printout matches. */
    html, body, .report-sheet, .report-sheet * {
      -webkit-print-color-adjust: exact !important;
      print-color-adjust: exact !important;
    }
    .report-toolbar {
      position: sticky; top: 0; z-index: 10;
      background: #fff; border-bottom: 1px solid var(--ink-300);
      padding: .85rem 1.5rem; display: flex; align-items: center; justify-content: space-between; gap: 1rem;
    }
    .report-toolbar .back-link { color: var(--ink-700); text-decoration: none; font-weight: 600; font-size: .9rem; }
    .report-toolbar .back-link:hover { color: var(--brand-700); }
    .report-wrap { max-width: 900px; margin: 0 auto; padding: 2rem 1.5rem 4rem; }
    .report-sheet { background: #fff; border-radius: var(--radius-lg); padding: 2.25rem; box-shadow: 0 1px 3px rgba(15,23,42,.08); }
    .report-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; border-bottom: 2px solid var(--brand-700); padding-bottom: 1.25rem; margin-bottom: 1.5rem; }
    .report-head img.logo { height: 46px; }
    .report-head h1 { font-family: 'Poppins', sans-serif; font-size: 1.4rem; color: var(--ink-900); margin: .5rem 0 0; }
    .report-head .meta { text-align: right; font-size: .85rem; color: var(--ink-500); }
    .report-head .meta strong { color: var(--ink-700); }
    .report-section { margin-bottom: 2rem; }
    .report-section h2 { font-family: 'Poppins', sans-serif; font-size: 1.05rem; color: var(--brand-900); margin-bottom: 1rem; display: flex; align-items: center; gap: .5rem; }
    .report-desc { font-size: .85rem; color: var(--ink-500); line-height: 1.55; margin: -.5rem 0 1rem; }
    .report-intro { font-size: .9rem; color: var(--ink-700); line-height: 1.6; margin-bottom: 1.75rem; }
    .report-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: .75rem; margin-bottom: 1.25rem; }
    .report-stat { background: var(--brand-50); border: 1px solid var(--brand-100); border-radius: var(--radius-md); padding: .85rem 1rem; }
    .report-stat .label { font-size: .75rem; color: var(--ink-500); font-weight: 600; text-transform: uppercase; letter-spacing: .02em; }
    .report-stat .value { font-family: 'Poppins', sans-serif; font-size: 1.4rem; color: var(--ink-900); font-weight: 700; }
    .report-chart { display: flex; flex-direction: column; gap: .5rem; }
    .report-subtitle { font-size: .8rem; font-weight: 700; color: var(--ink-700); text-transform: uppercase; letter-spacing: .03em; margin: 1.1rem 0 .55rem; }
    .report-bar-row { display: grid; grid-template-columns: 90px 1fr 40px; align-items: center; gap: .65rem; font-size: .82rem; }
    .report-chart.wide .report-bar-row { grid-template-columns: 150px 1fr 96px; }
    .report-bar-note { color: var(--ink-500); font-weight: 500; font-size: .74rem; white-space: nowrap; }
    .report-bar-track { background: var(--ink-100); border-radius: 999px; height: 10px; overflow: hidden; }
    .report-bar-fill { background: linear-gradient(90deg, var(--brand-700), var(--brand-500)); height: 100%; border-radius: 999px; }
    .report-bar-label { color: var(--ink-700); font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .report-bar-value { text-align: right; color: var(--ink-900); font-weight: 700; }
    table.report-table { width: 100%; border-collapse: collapse; font-size: .82rem; }
    table.report-table th { text-align: left; background: var(--brand-50); color: var(--brand-900); font-weight: 700; padding: .55rem .7rem; border-bottom: 2px solid var(--brand-100); }
    table.report-table td { padding: .55rem .7rem; border-bottom: 1px solid var(--ink-100); color: var(--ink-700); }
    table.report-table tr:last-child td { border-bottom: none; }
    .pill-mini { display: inline-block; padding: .15rem .55rem; border-radius: 999px; font-size: .72rem; font-weight: 700; }
    .pill-mini.good { background: #e3fde5; color: #167d1d; }
    .pill-mini.info { background: #e3f2fd; color: #0d6efd; }
    .pill-mini.warn { background: #fff8e1; color: #b45309; }
    .pill-mini.bad { background: #fdecea; color: #c0392b; }
    .report-empty { color: var(--ink-500); font-size: .85rem; font-style: italic; }
    .report-service-group { margin-top: 1.25rem; }
    .report-service-head { display: flex; justify-content: space-between; align-items: baseline; gap: 1rem; font-weight: 700; color: var(--brand-900); font-size: .92rem; padding: 0 0 .4rem; border-bottom: 1px solid var(--brand-100); margin-bottom: .25rem; }
    .report-service-count { font-weight: 600; font-size: .78rem; color: var(--ink-500); white-space: nowrap; }
    /* Standard document margins: 1 inch on every side of every printed page. */
    @page { size: A4; margin: 1in; }
    .report-signature { display: flex; justify-content: flex-end; margin-top: 3rem; }
    .report-signature-block { width: 260px; text-align: center; }
    .report-signature-label { font-size: .8rem; color: var(--ink-500); text-align: left; }
    .report-signature-space { height: 50px; }
    .report-signature-line { border-top: 1px solid var(--ink-700); padding-top: .4rem; }
    .report-signature-name { font-weight: 700; color: var(--ink-900); font-size: .9rem; }
    .report-signature-role { font-size: .75rem; color: var(--ink-500); text-transform: uppercase; letter-spacing: .04em; margin-top: .1rem; }
    @media print {
      /* styles.css sets `html, body { background: var(--ink-100) }` for
         the normal on-screen admin theme — this page only ever whitened
         `body`. Because print-color-adjust:exact above forces backgrounds
         to actually print instead of being stripped, `html`'s grey never
         got overridden and showed through as a shaded block wherever the
         document's content ended before the physical page did (e.g. below
         the signature on the last page). */
      html,
      body { background: #fff; }
      .no-print { display: none !important; }
      .report-wrap { padding: 0; max-width: none; }
      .report-sheet { box-shadow: none; padding: 0; border-radius: 0; }
      /* A whole .report-section (heading + stats + chart + the FULL table)
         easily runs longer than one page once there's real data in the
         table — telling the browser to never break *inside* it just pushed
         the entire section to the next page instead, leaving whatever came
         before it (the report header) alone on an almost-blank page 1.
         Only the small pieces below need to stay intact; the section and
         its table are left free to flow and break across pages normally. */
      .report-head,
      .report-stats,
      .report-bar-row,
      .report-signature { page-break-inside: avoid; }
      .report-section h2,
      .report-subtitle,
      .report-service-head { page-break-after: avoid; }
      thead { display: table-header-group; } /* repeat column headers on each page */
      table.report-table { page-break-inside: auto; }
      tr { page-break-inside: avoid; }
    }
  </style>
    <link rel="stylesheet" href="{{ asset('css/modals.css') }}">
    <link rel="stylesheet" href="{{ asset('css/mobile.css') }}">
</head>

<body>


  <div class="report-wrap">
    <div class="report-sheet">

      <div class="report-head">
        <div>
          <img class="logo" src="/images/puspus_logo.png" alt="">
          <h1>{{ $reportTitle }}</h1>
        </div>
        <div class="meta">
          <div><strong>Range:</strong> {{ $rangeLabel }}</div>
          <div><strong>Generated:</strong> {{ $generatedAt->format('M j, Y g:i A') }}</div>
        </div>
      </div>

      @if ($type === 'summary')
        <p class="report-intro">{{ $summaryIntro }}</p>
      @endif

      @if (in_array($type, ['appointments', 'summary']))
        <div class="report-section">
          <h2><i class="bi bi-calendar-check"></i> Completed Appointments</h2>
          <p class="report-desc">{{ $descriptions['appointments'] }}</p>
          <div class="report-stats">
            <div class="report-stat"><div class="label">Completed Visits</div><div class="value">{{ $appointmentStats['completed'] }}</div></div>
            <div class="report-stat"><div class="label">Patients Served</div><div class="value">{{ $appointmentStats['patients'] }}</div></div>
            <div class="report-stat"><div class="label">Services Performed</div><div class="value">{{ $appointmentStats['services'] }}</div></div>
          </div>
          <div class="report-stats">
            @foreach ($appointmentsBySource as $label => $count)
              <div class="report-stat"><div class="label">{{ $label }}</div><div class="value">{{ $count }}</div></div>
            @endforeach
          </div>

          @if (!empty($dailyChart))
            <div class="report-subtitle">Completed Visits Over Time</div>
            <div class="report-chart mb-3">
              @foreach ($dailyChart as $row)
                <div class="report-bar-row">
                  <div class="report-bar-label">{{ $row['label'] }}</div>
                  <div class="report-bar-track"><div class="report-bar-fill" style="width: {{ $row['pct'] }}%"></div></div>
                  <div class="report-bar-value">{{ $row['count'] }}</div>
                </div>
              @endforeach
            </div>
          @endif

          @if (!empty($sourceChart))
            <div class="report-subtitle">Completed Visits by Booking Type</div>
            <div class="report-chart wide mb-3">
              @foreach ($sourceChart as $row)
                <div class="report-bar-row">
                  <div class="report-bar-label">{{ $row['label'] }}</div>
                  <div class="report-bar-track"><div class="report-bar-fill" style="width: {{ $row['pct'] }}%"></div></div>
                  <div class="report-bar-value">{{ $row['count'] }}</div>
                </div>
              @endforeach
            </div>
          @endif

          @if (!empty($completedServiceChart))
            <div class="report-subtitle">Completed Visits per Service</div>
            <div class="report-chart wide mb-3">
              @foreach ($completedServiceChart as $row)
                <div class="report-bar-row">
                  <div class="report-bar-label">{{ $row['label'] }}</div>
                  <div class="report-bar-track"><div class="report-bar-fill" style="width: {{ $row['pct'] }}%"></div></div>
                  <div class="report-bar-value">{{ $row['count'] }}</div>
                </div>
              @endforeach
            </div>
          @endif

          {{-- Every completed visit, grouped by the service performed. A visit
               covering several services appears under each one. --}}
          @if (empty($appointmentsByService))
            <div class="report-empty">No completed appointments found for this range.</div>
          @else
            @foreach ($appointmentsByService as $serviceName => $rows)
              <div class="report-service-group">
                <div class="report-service-head">
                  <span>{{ $serviceName }}</span>
                  <span class="report-service-count">{{ count($rows) }} completed {{ count($rows) === 1 ? 'visit' : 'visits' }}</span>
                </div>
                <table class="report-table">
                  <thead>
                    <tr>
                      <th>Patient ID</th><th>Patient Name</th><th>Booking Type</th><th>Date</th><th>Time</th><th>Duration</th><th>Dentist</th>
                      @if ($withDetails)<th>Contact</th>@endif
                    </tr>
                  </thead>
                  <tbody>
                    @foreach ($rows as $a)
                      <tr>
                        <td>{{ $a->patientInfo ? 'PT-' . str_pad($a->patientInfo->PatientID, 4, '0', STR_PAD_LEFT) : '—' }}</td>
                        <td>{{ trim(($a->patientInfo->FirstName ?? '') . ' ' . ($a->patientInfo->LastName ?? '')) ?: '—' }}</td>
                        <td>{{ \App\Http\Controllers\ReportController::SOURCES[$a->Source] ?? $a->Source }}</td>
                        <td>{{ optional($a->AppointmentDate)->format('M j, Y') }}</td>
                        <td>{{ $a->AppointmentTime ? $a->time_range_label : '—' }}</td>
                        <td>{{ ($a->fromRecord ?? false) ? '—' : $a->duration_label }}</td>
                        <td>{{ $a->dentist?->display_name ?? '—' }}</td>
                        @if ($withDetails)
                          <td>{{ $a->patientInfo->PhoneNumber ?? $a->patientInfo?->userAccount?->Email ?? '—' }}</td>
                        @endif
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            @endforeach
          @endif
        </div>
      @endif

      @if (in_array($type, ['patients', 'summary']))
        <div class="report-section">
          <h2><i class="bi bi-people"></i> Patients</h2>
          <p class="report-desc">{{ $descriptions['patients'] }}</p>
          <div class="report-stats">
            <div class="report-stat"><div class="label">Total</div><div class="value">{{ $patientStats['total'] }}</div></div>
            <div class="report-stat"><div class="label">Registered</div><div class="value">{{ $patientStats['registered'] }}</div></div>
            <div class="report-stat"><div class="label">Walk-in</div><div class="value">{{ $patientStats['walkin'] }}</div></div>
            <div class="report-stat"><div class="label">Guest (no account)</div><div class="value">{{ $patientStats['guest'] }}</div></div>
            <div class="report-stat"><div class="label">Male</div><div class="value">{{ $patientStats['male'] }}</div></div>
            <div class="report-stat"><div class="label">Female</div><div class="value">{{ $patientStats['female'] }}</div></div>
          </div>

          @if (!empty($treatmentBreakdown))
            <div class="report-chart mb-3">
              @foreach ($treatmentBreakdown as $row)
                <div class="report-bar-row">
                  <div class="report-bar-label">{{ $row['label'] }}</div>
                  <div class="report-bar-track"><div class="report-bar-fill" style="width: {{ $row['pct'] }}%"></div></div>
                  <div class="report-bar-value">{{ $row['count'] }}</div>
                </div>
              @endforeach
            </div>
          @endif

          @if (isset($patientsList))
            @if ($patientsList->isEmpty())
              <div class="report-empty">No patients with a completed visit in this range.</div>
            @else
              <table class="report-table">
                <thead>
                  <tr><th>Patient ID</th><th>Name</th><th>Type</th><th>Age</th><th>Gender</th><th>Phone</th><th>Address</th><th>Visits</th><th>Last Visit</th></tr>
                </thead>
                <tbody>
                  @foreach ($patientsList as $p)
                    <tr>
                      <td>PT-{{ str_pad($p->PatientID, 4, '0', STR_PAD_LEFT) }}</td>
                      <td>{{ trim(($p->FirstName ?? '') . ' ' . ($p->LastName ?? '')) ?: '—' }}</td>
                      <td>{{ $p->patient_type }}</td>
                      <td>{{ $p->Age ?? '—' }}</td>
                      <td>{{ $p->Gender ?? '—' }}</td>
                      <td>{{ $p->PhoneNumber ?? '—' }}</td>
                      <td>{{ $p->Address ?? '—' }}</td>
                      <td>{{ $p->completedVisits }}</td>
                      <td>{{ $p->lastCompletedVisit?->format('M j, Y') ?? '—' }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            @endif
          @endif
        </div>
      @endif

      @if ($type === 'summary' && !empty($serviceBreakdown))
        <div class="report-section">
          <h2><i class="bi bi-bar-chart"></i> Appointments by Service</h2>
          <p class="report-desc">{{ $descriptions['services'] }}</p>
          <div class="report-chart">
            @foreach ($serviceBreakdown as $row)
              <div class="report-bar-row">
                <div class="report-bar-label">{{ $row['label'] }}</div>
                <div class="report-bar-track"><div class="report-bar-fill" style="width: {{ $row['pct'] }}%"></div></div>
                <div class="report-bar-value">{{ $row['count'] }}</div>
              </div>
            @endforeach
          </div>
        </div>
      @endif

      <div class="report-signature">
        <div class="report-signature-block">
          <div class="report-signature-label">Prepared by:</div>
          <div class="report-signature-space"></div>
          <div class="report-signature-line">
            <div class="report-signature-name">{{ $adminAccountName ?? 'Administrator' }}</div>
            <div class="report-signature-role">Administrator</div>
          </div>
        </div>
      </div>

    </div>
  </div>

  {{-- With ?preview=1 the dashboard shows this page inside its "Report
       Preview" modal and prints it from there (Print button). Without it,
       the page opens straight into the browser's print dialog. --}}
  <script>
    @if (!request()->boolean('preview'))
    window.addEventListener('load', function () {
      setTimeout(function () { window.print(); }, 300);
    });
    @endif
    window.addEventListener('afterprint', function () {
      if (window.parent !== window) window.parent.postMessage('report-printed', window.location.origin);
    });
  </script>
</body>

</html>
