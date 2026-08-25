<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  @php
    $typeTitles = [
        'appointments' => 'Appointments Report',
        'patients' => 'Patients Report',
        'schedule' => 'Doctor Schedule Report',
        'summary' => 'Full Summary Report',
    ];
    $reportTitle = $typeTitles[$type] ?? 'Report';
    $statusPill = fn ($status) => match ($status) {
        'Completed' => 'good',
        'Approved' => 'info',
        'Pending' => 'warn',
        'Cancelled', 'Declined' => 'bad',
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
    .report-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: .75rem; margin-bottom: 1.25rem; }
    .report-stat { background: var(--brand-50); border: 1px solid var(--brand-100); border-radius: var(--radius-md); padding: .85rem 1rem; }
    .report-stat .label { font-size: .75rem; color: var(--ink-500); font-weight: 600; text-transform: uppercase; letter-spacing: .02em; }
    .report-stat .value { font-family: 'Poppins', sans-serif; font-size: 1.4rem; color: var(--ink-900); font-weight: 700; }
    .report-chart { display: flex; flex-direction: column; gap: .5rem; }
    .report-bar-row { display: grid; grid-template-columns: 90px 1fr 40px; align-items: center; gap: .65rem; font-size: .82rem; }
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
    @media print {
      body { background: #fff; }
      .no-print { display: none !important; }
      .report-wrap { padding: 0; max-width: none; }
      .report-sheet { box-shadow: none; padding: 0; border-radius: 0; }
      .report-section { page-break-inside: avoid; }
      table.report-table { page-break-inside: auto; }
      tr { page-break-inside: avoid; }
    }
  </style>
</head>

<body>

  <div class="report-toolbar no-print">
    <a href="{{ url('dashboard') }}" class="back-link"><i class="bi bi-arrow-left me-1"></i>Back to Dashboard</a>
    <button type="button" class="btn btn-brand px-4" onclick="window.print()">
      <i class="bi bi-printer me-1"></i> Print / Save as PDF
    </button>
  </div>

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
          <div><strong>By:</strong> Administrator</div>
        </div>
      </div>

      @if (in_array($type, ['appointments', 'summary']))
        <div class="report-section">
          <h2><i class="bi bi-calendar-check"></i> Appointments</h2>
          <div class="report-stats">
            <div class="report-stat"><div class="label">Total</div><div class="value">{{ $appointmentStats['total'] }}</div></div>
            <div class="report-stat"><div class="label">Pending</div><div class="value">{{ $appointmentStats['pending'] }}</div></div>
            <div class="report-stat"><div class="label">Approved</div><div class="value">{{ $appointmentStats['approved'] }}</div></div>
            <div class="report-stat"><div class="label">Completed</div><div class="value">{{ $appointmentStats['completed'] }}</div></div>
            <div class="report-stat"><div class="label">Cancelled</div><div class="value">{{ $appointmentStats['cancelled'] }}</div></div>
            <div class="report-stat"><div class="label">Declined</div><div class="value">{{ $appointmentStats['declined'] }}</div></div>
          </div>

          @if (!empty($dailyChart))
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

          @if (isset($appointmentsList))
            @if ($appointmentsList->isEmpty())
              <div class="report-empty">No appointments found for this range.</div>
            @else
              <table class="report-table">
                <thead>
                  <tr><th>Date</th><th>Time</th><th>Patient</th><th>Contact</th><th>Service</th><th>Status</th></tr>
                </thead>
                <tbody>
                  @foreach ($appointmentsList as $a)
                    <tr>
                      <td>{{ optional($a->AppointmentDate)->format('M j, Y') }}</td>
                      <td>{{ $a->AppointmentTime }}</td>
                      <td>{{ trim(($a->patientInfo->FirstName ?? '') . ' ' . ($a->patientInfo->LastName ?? '')) ?: '—' }}</td>
                      <td>{{ $a->patientInfo->PhoneNumber ?? $a->patientInfo->userAccount?->Email ?? $a->patientInfo->Email ?? '—' }}</td>
                      <td>{{ $a->service->ServiceName ?? '—' }}</td>
                      <td><span class="pill-mini {{ $statusPill($a->Status) }}">{{ $a->Status }}</span></td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            @endif
          @endif
        </div>
      @endif

      @if (in_array($type, ['patients', 'summary']))
        <div class="report-section">
          <h2><i class="bi bi-people"></i> Patients</h2>
          <div class="report-stats">
            <div class="report-stat"><div class="label">Total</div><div class="value">{{ $patientStats['total'] }}</div></div>
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
              <div class="report-empty">No patients found for this range.</div>
            @else
              <table class="report-table">
                <thead>
                  <tr><th>Name</th><th>Age</th><th>Gender</th><th>Phone</th><th>Address</th></tr>
                </thead>
                <tbody>
                  @foreach ($patientsList as $p)
                    <tr>
                      <td>{{ trim(($p->FirstName ?? '') . ' ' . ($p->LastName ?? '')) ?: '—' }}</td>
                      <td>{{ $p->Age ?? '—' }}</td>
                      <td>{{ $p->Gender ?? '—' }}</td>
                      <td>{{ $p->PhoneNumber ?? '—' }}</td>
                      <td>{{ $p->Address ?? '—' }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            @endif
          @endif
        </div>
      @endif

      @if (in_array($type, ['schedule', 'summary']))
        <div class="report-section">
          <h2><i class="bi bi-calendar3"></i> Doctor Schedule</h2>
          <div class="report-stats">
            <div class="report-stat"><div class="label">Total Slots</div><div class="value">{{ $scheduleStats['total'] }}</div></div>
            <div class="report-stat"><div class="label">Available</div><div class="value">{{ $scheduleStats['available'] }}</div></div>
            <div class="report-stat"><div class="label">Booked</div><div class="value">{{ $scheduleStats['booked'] }}</div></div>
          </div>

          @if (!empty($scheduleBreakdown))
            <div class="report-chart mb-3">
              @foreach ($scheduleBreakdown as $row)
                <div class="report-bar-row">
                  <div class="report-bar-label">{{ $row['label'] }}</div>
                  <div class="report-bar-track"><div class="report-bar-fill" style="width: {{ $row['pct'] }}%"></div></div>
                  <div class="report-bar-value">{{ $row['count'] }}</div>
                </div>
              @endforeach
            </div>
          @endif

          @if (isset($scheduleList))
            @if ($scheduleList->isEmpty())
              <div class="report-empty">No schedule slots found for this range.</div>
            @else
              <table class="report-table">
                <thead>
                  <tr><th>Date</th><th>Time</th><th>Status</th></tr>
                </thead>
                <tbody>
                  @foreach ($scheduleList as $s)
                    <tr>
                      <td>{{ optional($s->Date)->format('M j, Y') }}</td>
                      <td>{{ $s->Time }}</td>
                      <td><span class="pill-mini {{ $statusPill($s->Status) }}">{{ $s->Status }}</span></td>
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

    </div>
  </div>

</body>

</html>
