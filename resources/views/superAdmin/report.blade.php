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
        'summary' => 'Summary Report',
    ];
    $reportTitle = $typeTitles[$type] ?? 'Report';
    // Phrase for the covered dates, e.g. "from Sep 14, 2026 to Sep 20, 2026".
    $rangePhrase = match (true) {
        !$start || !$end => 'across all dates',
        $start->isSameDay($end) => 'on ' . $start->format('M j, Y'),
        default => 'from ' . $start->format('M j, Y') . ' to ' . $end->format('M j, Y'),
    };
    $withBreakdowns = $includeCharts ?? false;
    $withDetails = $includePatientDetails ?? false;

    $intro = match ($type) {
        'patients' => "This report lists the patients — registered, guest or walk-in — who had a completed visit {$rangePhrase}, with totals by patient type and gender.",
        'summary' => "This summary combines the appointments and patients reports {$rangePhrase} in one document: completed visits first, then the patients who were seen.",
        default => "This report lists every completed visit {$rangePhrase} — online appointments, walk-ins and follow-ups — with totals by booking type.",
    };

    // e.g. "appointments-report_2026-11-01_to_2026-11-30.pdf"
    $pdfFilename = \Illuminate\Support\Str::slug($reportTitle) . '_' . match (true) {
        !$start || !$end => 'all-dates',
        $start->isSameDay($end) => $start->format('Y-m-d'),
        default => $start->format('Y-m-d') . '_to_' . $end->format('Y-m-d'),
    } . '.pdf';

    $patientCode = fn ($id) => $id ? 'PT-' . str_pad($id, 4, '0', STR_PAD_LEFT) : '—';
    $share = fn ($count, $total) => $total ? number_format($count / $total * 100, 1) . '%' : '—';
  @endphp
  <title>{{ $reportTitle }} • Dental Clinic</title>
  <meta name="pdf-filename" content="{{ $pdfFilename }}">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/print-doc.css') }}">
</head>

<body>
  <div class="doc-wrap">
    <div class="doc-sheet">

      @include('partials.print-letterhead', ['clinic' => $clinic ?? null])

      <div class="doc-title">
        <h1>{{ $reportTitle }}</h1>
      </div>

      <table class="doc-table doc-kv doc-meta">
        <tr>
          <th>Report Period</th>
          <td>{{ $rangeLabel }}</td>
          <th>Date Generated</th>
          <td>{{ $generatedAt->format('M j, Y g:i A') }}</td>
        </tr>
        <tr>
          <th>Prepared By</th>
          <td>{{ $adminAccountName ?? 'Administrator' }}</td>
          <th>Coverage</th>
          <td>Completed visits only</td>
        </tr>
      </table>

      <p class="doc-intro">{{ $intro }}</p>

      {{-- ============ APPOINTMENTS ============ --}}
      @if (in_array($type, ['appointments', 'summary']))
        @php $totalVisits = $appointmentStats['completed']; @endphp

        <section class="doc-section doc-keep">
          <h2 class="doc-section-title">Appointment Summary</h2>
          <p class="doc-section-desc">Totals for the period. Booking type shows how each completed visit was made.</p>
          <table class="doc-table doc-figures">
            <thead>
              <tr>
                <th>Completed Visits</th>
                <th>Patients Served</th>
                <th>Services Performed</th>
                @foreach ($appointmentsBySource as $label => $count)
                  <th>{{ $label }}</th>
                @endforeach
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>{{ number_format($totalVisits) }}</td>
                <td>{{ number_format($appointmentStats['patients']) }}</td>
                <td>{{ number_format($appointmentStats['services']) }}</td>
                @foreach ($appointmentsBySource as $count)
                  <td>{{ number_format($count) }}</td>
                @endforeach
              </tr>
            </tbody>
          </table>
        </section>

        @if ($withBreakdowns && !empty($visitsByDate))
          <section class="doc-section doc-keep">
            <h2 class="doc-section-title">Completed Visits by {{ $dateBucket }}</h2>
            <p class="doc-section-desc">Number of completed visits per {{ strtolower($dateBucket) === 'month' ? 'month' : 'day' }}; {{ strtolower($dateBucket) === 'month' ? 'months' : 'days' }} without visits are not listed.</p>
            <table class="doc-table">
              <thead>
                <tr><th>{{ $dateBucket }}</th><th class="num" style="width: 18%;">Visits</th><th class="num" style="width: 18%;">Share</th></tr>
              </thead>
              <tbody>
                @foreach ($visitsByDate as $label => $count)
                  <tr><td>{{ $label }}</td><td class="num">{{ number_format($count) }}</td><td class="num">{{ $share($count, $totalVisits) }}</td></tr>
                @endforeach
              </tbody>
              <tfoot>
                <tr><td>Total</td><td class="num">{{ number_format($totalVisits) }}</td><td class="num">100.0%</td></tr>
              </tfoot>
            </table>
          </section>
        @endif

        @if ($withBreakdowns && !empty($visitsByService))
          <section class="doc-section doc-keep">
            <h2 class="doc-section-title">Completed Visits by Service</h2>
            <p class="doc-section-desc">How often each service was performed. A visit covering several services is counted under each one.</p>
            <table class="doc-table">
              <thead>
                <tr><th>Service</th><th class="num" style="width: 18%;">Visits</th></tr>
              </thead>
              <tbody>
                @foreach ($visitsByService as $label => $count)
                  <tr><td>{{ $label }}</td><td class="num">{{ number_format($count) }}</td></tr>
                @endforeach
              </tbody>
            </table>
          </section>
        @endif

        <section class="doc-section">
          <h2 class="doc-section-title">List of Completed Visits</h2>
          <p class="doc-section-desc">Every completed visit in the period, oldest first.</p>
          @if ($visits->isEmpty())
            <div class="doc-empty">No completed visits found for this period.</div>
          @else
            <div class="doc-table-scroll">
              <table class="doc-table">
                <thead>
                  <tr>
                    <th class="center">No.</th>
                    <th>Schedule</th>
                    <th>Patient ID</th>
                    <th>Patient Name</th>
                    <th>Service(s)</th>
                    <th>Booking Type</th>
                    <th>Dentist</th>
                    @if ($withDetails)<th>Contact</th>@endif
                  </tr>
                </thead>
                <tbody>
                  @foreach ($visits as $a)
                    <tr>
                      <td class="row-no">{{ $loop->iteration }}</td>
                      <td class="nowrap">
                        {{ optional($a->AppointmentDate)->format('M j, Y') }}
                        @if ($a->AppointmentTime)<div class="muted">{{ $a->time_range_label }}</div>@endif
                      </td>
                      <td class="nowrap">{{ $patientCode($a->patientInfo?->PatientID) }}</td>
                      <td>{{ trim(($a->patientInfo->FirstName ?? '') . ' ' . ($a->patientInfo->LastName ?? '')) ?: '—' }}</td>
                      <td>{{ $a->serviceLabel }}</td>
                      <td class="nowrap">{{ $a->Source }}</td>
                      <td>{{ $a->dentist?->display_name ?? '—' }}</td>
                      @if ($withDetails)
                        <td>{{ $a->patientInfo->PhoneNumber ?? $a->patientInfo?->userAccount?->Email ?? '—' }}</td>
                      @endif
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @endif
        </section>
      @endif

      {{-- ============ PATIENTS ============ --}}
      @if (in_array($type, ['patients', 'summary']))
        <section class="doc-section doc-keep">
          <h2 class="doc-section-title">Patient Summary</h2>
          <p class="doc-section-desc">Patients with at least one completed visit in the period, each counted once.</p>
          <table class="doc-table doc-figures">
            <thead>
              <tr>
                <th>Total Patients</th>
                <th>Registered</th>
                <th>Guest</th>
                <th>Walk-in</th>
                <th>Male</th>
                <th>Female</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>{{ number_format($patientStats['total']) }}</td>
                <td>{{ number_format($patientStats['registered']) }}</td>
                <td>{{ number_format($patientStats['guest']) }}</td>
                <td>{{ number_format($patientStats['walkin']) }}</td>
                <td>{{ number_format($patientStats['male']) }}</td>
                <td>{{ number_format($patientStats['female']) }}</td>
              </tr>
            </tbody>
          </table>
        </section>

        @if ($withBreakdowns && !empty($patientsByTreatment))
          <section class="doc-section doc-keep">
            <h2 class="doc-section-title">Patients per Treatment</h2>
            <p class="doc-section-desc">Number of different patients who received each treatment. A patient with several treatments is counted under each one.</p>
            <table class="doc-table">
              <thead>
                <tr><th>Treatment</th><th class="num" style="width: 18%;">Patients</th></tr>
              </thead>
              <tbody>
                @foreach ($patientsByTreatment as $label => $count)
                  <tr><td>{{ $label }}</td><td class="num">{{ number_format($count) }}</td></tr>
                @endforeach
              </tbody>
            </table>
          </section>
        @endif

        @if (isset($patientsList))
          <section class="doc-section">
            <h2 class="doc-section-title">List of Patients</h2>
            <p class="doc-section-desc">Sorted by last name. Visits counts completed visits within the period only.</p>
            @if ($patientsList->isEmpty())
              <div class="doc-empty">No patients with a completed visit in this period.</div>
            @else
              <div class="doc-table-scroll">
                <table class="doc-table">
                  <thead>
                    <tr>
                      <th class="center">No.</th>
                      <th>Patient ID</th>
                      <th>Name</th>
                      <th>Type</th>
                      <th class="center">Age</th>
                      <th>Gender</th>
                      <th>Contact No.</th>
                      <th>Address</th>
                      <th class="num">Visits</th>
                      <th>Last Visit</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach ($patientsList as $p)
                      <tr>
                        <td class="row-no">{{ $loop->iteration }}</td>
                        <td class="nowrap">{{ $patientCode($p->PatientID) }}</td>
                        <td>{{ collect([$p->LastName, $p->FirstName])->filter()->implode(', ') ?: '—' }}</td>
                        <td>{{ $p->patient_type }}</td>
                        <td class="center">{{ $p->Age ?? '—' }}</td>
                        <td>{{ $p->Gender ? ucfirst(strtolower($p->Gender)) : '—' }}</td>
                        <td class="nowrap">{{ $p->PhoneNumber ?? '—' }}</td>
                        <td>{{ $p->Address ?? '—' }}</td>
                        <td class="num">{{ $p->completedVisits }}</td>
                        <td class="nowrap">{{ $p->lastCompletedVisit?->format('M j, Y') ?? '—' }}</td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            @endif
          </section>
        @endif
      @endif

      <div class="doc-signatures end">
        <div class="doc-signature">
          <div class="doc-signature-label">Prepared by:</div>
          <div class="doc-signature-space"></div>
          <div class="doc-signature-line">
            <div class="doc-signature-name">{{ $adminAccountName ?? 'Administrator' }}</div>
            <div class="doc-signature-role">Administrator</div>
          </div>
        </div>
      </div>

      <div class="doc-footnote">
        Puspus-Britanico Dental Clinic · {{ $reportTitle }} · System-generated on {{ $generatedAt->format('M j, Y g:i A') }}
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
  {{-- "Download PDF" in the Report Preview modal: window.downloadDocPdf(). --}}
  <script src="{{ asset('js/print-doc.js') }}"></script>
</body>

</html>
