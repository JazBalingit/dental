<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <link rel="icon" type="image/png" href="/images/puspus_logo.png">
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  @php
    $fullName = trim(implode(' ', array_filter([$patient?->FirstName, $patient?->MiddleName, $patient?->LastName]))) ?: '—';
    $patientCode = $patient ? 'PT-' . str_pad($patient->PatientID, 4, '0', STR_PAD_LEFT) : '—';
    $visitDate = $record->VisitDate->format('F j, Y');
    $timeLabel = $appointment?->time_range_label
        ?? ($record->VisitTime ? \Carbon\Carbon::createFromFormat('H:i', $record->VisitTime)->format('g:i A') : '—');
    $services = $appointment && $appointment->services->isNotEmpty()
        ? $appointment->services->pluck('ServiceName')->implode(', ')
        : ($record->Service ?: ($record->service->ServiceName ?? '—'));
    $dentistName = $appointment?->dentist?->display_name;
    $email = $patient?->userAccount?->Email ?? $patient?->Email;
    $birthdate = $patient?->DateOfBirth ? \Carbon\Carbon::parse($patient->DateOfBirth)->format('F j, Y') : null;
    $teeth = $record->odontogramTeeth;
    $conditions = \App\Models\OdontogramTooth::CONDITIONS;
  @endphp
  <title>Dental Record — {{ $fullName }} — {{ $visitDate }}</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
  <link rel="stylesheet" href="{{ asset('css/odontogram.css') }}">
  <style>
    html, body { background: var(--ink-100); font-family: 'Inter', sans-serif; }
    /* Keep chart colours, pills and section tints when printing. */
    html, body, .record-sheet, .record-sheet * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    .record-wrap { max-width: 900px; margin: 0 auto; padding: 2rem 1.5rem 4rem; }
    .record-sheet { background: #fff; border-radius: var(--radius-lg); padding: 2.25rem; box-shadow: 0 1px 3px rgba(15,23,42,.08); }
    .record-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; border-bottom: 2px solid var(--brand-700); padding-bottom: 1.25rem; margin-bottom: 1.25rem; }
    .record-head img.logo { height: 46px; }
    .record-head h1 { font-family: 'Poppins', sans-serif; font-size: 1.4rem; color: var(--ink-900); margin: .5rem 0 0; }
    .record-head .meta { text-align: right; font-size: .85rem; color: var(--ink-500); }
    .record-head .meta strong { color: var(--ink-700); }
    .record-intro { font-size: .88rem; color: var(--ink-700); line-height: 1.6; margin-bottom: 1.5rem; }
    .record-section { margin-bottom: 1.6rem; }
    .record-section h2 { font-family: 'Poppins', sans-serif; font-size: 1.02rem; color: var(--brand-900); margin-bottom: .75rem; display: flex; align-items: center; gap: .5rem; }
    .record-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: .6rem .9rem; }
    .record-grid.two { grid-template-columns: repeat(2, 1fr); }
    .record-field { background: var(--brand-50); border: 1px solid var(--brand-100); border-radius: var(--radius-md); padding: .55rem .8rem; }
    .record-field.wide { grid-column: 1 / -1; }
    .record-field .lbl { font-size: .68rem; font-weight: 700; color: var(--ink-500); text-transform: uppercase; letter-spacing: .03em; }
    .record-field .val { font-size: .9rem; color: var(--ink-900); font-weight: 600; overflow-wrap: anywhere; }
    .pill-mini { display: inline-block; padding: .12rem .55rem; border-radius: 999px; font-size: .72rem; font-weight: 700; background: #e3fde5; color: #167d1d; }
    .record-notes { border: 1px solid var(--ink-300, #e2e8f0); border-left: 4px solid var(--brand-700); border-radius: var(--radius-md); padding: .8rem 1rem; font-size: .9rem; color: var(--ink-700); white-space: pre-line; }
    .record-muted { color: var(--ink-500); font-style: italic; font-size: .88rem; }
    table.record-table { width: 100%; border-collapse: collapse; font-size: .82rem; margin-top: .75rem; }
    table.record-table th { text-align: left; background: var(--brand-50); color: var(--brand-900); font-weight: 700; padding: .5rem .65rem; border-bottom: 2px solid var(--brand-100); }
    table.record-table td { padding: .5rem .65rem; border-bottom: 1px solid var(--ink-100); color: var(--ink-700); vertical-align: top; }
    .cond-dot { display: inline-block; width: 10px; height: 10px; border-radius: 50%; margin-right: .35rem; vertical-align: middle; }
    /* The chart is display-only here: no hover/click affordances, and the
       app's on-screen "Charted teeth" list is replaced by the table below. */
    .record-sheet .odontogram-heading,
    .record-sheet .odontogram-toolbar,
    .record-sheet .odontogram-readonly-detail { display: none !important; }
    .record-sheet .odontogram { border: 1px solid var(--ink-100); border-radius: var(--radius-md); padding: .75rem; }
    .record-sheet .odontogram-tooth { cursor: default; pointer-events: none; }
    .record-signature { display: flex; justify-content: space-between; gap: 2rem; margin-top: 2.75rem; }
    .record-signature-block { width: 240px; text-align: center; }
    .record-signature-label { font-size: .8rem; color: var(--ink-500); text-align: left; }
    .record-signature-space { height: 46px; }
    .record-signature-line { border-top: 1px solid var(--ink-700); padding-top: .4rem; }
    .record-signature-name { font-weight: 700; color: var(--ink-900); font-size: .9rem; }
    .record-signature-role { font-size: .72rem; color: var(--ink-500); text-transform: uppercase; letter-spacing: .04em; margin-top: .1rem; }
    .record-footnote { margin-top: 1.75rem; font-size: .72rem; color: var(--ink-500); text-align: center; }
    @page { size: A4; margin: 1in; }
    @media print {
      html, body { background: #fff; }
      .record-wrap { padding: 0; max-width: none; }
      .record-sheet { box-shadow: none; padding: 0; border-radius: 0; }
      .record-head, .record-grid, .record-sheet .odontogram, .record-notes, .record-signature, tr { page-break-inside: avoid; }
      .record-section h2 { page-break-after: avoid; }
      thead { display: table-header-group; }
    }
    @media (max-width: 640px) {
      .record-sheet { padding: 1.25rem; }
      .record-grid { grid-template-columns: repeat(2, 1fr); }
    }
  </style>
</head>

<body>
  <div class="record-wrap">
    <div class="record-sheet">

      <div class="record-head">
        <div>
          <img class="logo" src="/images/puspus_logo.png" alt="">
          <h1>Dental Record</h1>
        </div>
        <div class="meta">
          <div><strong>Patient ID:</strong> {{ $patientCode }}</div>
          <div><strong>Visit:</strong> {{ $visitDate }}</div>
          <div><strong>Printed:</strong> {{ $generatedAt->format('M j, Y g:i A') }}</div>
        </div>
      </div>

      <p class="record-intro">
        This dental record documents {{ $fullName }}'s visit to Pus-Pus Britanico Dental Clinic on {{ $visitDate }}.
        It includes the patient's personal information, the details of the appointment, the dental chart
        (odontogram) recorded by the attending dentist for this visit{{ $record->Notes ? ', and the dentist\'s notes' : '' }}.
        Teeth are numbered using the FDI (two-digit) system — the first digit is the quadrant and the second is the tooth.
        Please keep this copy for your records and bring it to future visits or referrals.
      </p>

      {{-- Patient information --}}
      <div class="record-section">
        <h2><i class="bi bi-person-vcard"></i> Patient Information</h2>
        <div class="record-grid">
          <div class="record-field"><div class="lbl">Patient ID</div><div class="val">{{ $patientCode }}</div></div>
          <div class="record-field"><div class="lbl">Full Name</div><div class="val">{{ $fullName }}</div></div>
          <div class="record-field"><div class="lbl">Age</div><div class="val">{{ $patient?->age_years !== null ? $patient->age_years . ' years old' : '—' }}</div></div>
          <div class="record-field"><div class="lbl">Gender</div><div class="val">{{ $patient?->Gender ? ucfirst($patient->Gender) : '—' }}</div></div>
          <div class="record-field"><div class="lbl">Birthdate</div><div class="val">{{ $birthdate ?? '—' }}</div></div>
          <div class="record-field"><div class="lbl">Contact Number</div><div class="val">{{ $patient?->PhoneNumber ?: '—' }}</div></div>
          <div class="record-field"><div class="lbl">Email</div><div class="val">{{ $email ?: '—' }}</div></div>
          <div class="record-field" style="grid-column: span 2;"><div class="lbl">Address</div><div class="val">{{ $patient?->Address ?: '—' }}</div></div>
          {{-- Medical history declared for this visit (or the current one for older visits). --}}
          @php $printMedical = $record->appointment?->medicalHistory ?? $patient?->latestMedicalHistory; @endphp
          <div class="record-field"><div class="lbl">Blood Type</div><div class="val">{{ $printMedical?->answer('blood_type') ?: 'Unknown' }}</div></div>
          <div class="record-field" style="grid-column: 1 / -1;"><div class="lbl">Allergies</div><div class="val">{{ $printMedical ? ($printMedical->allergy_list ? implode(', ', $printMedical->allergy_list) : 'None declared') : 'Not on file' }}</div></div>
          <div class="record-field" style="grid-column: 1 / -1;"><div class="lbl">Medical conditions</div><div class="val">{{ $printMedical ? ($printMedical->condition_list ? implode(', ', $printMedical->condition_list) : 'None declared') : 'Not on file' }}</div></div>
        </div>
      </div>

      {{-- Appointment details --}}
      <div class="record-section">
        <h2><i class="bi bi-calendar-check"></i> Appointment Details</h2>
        <div class="record-grid">
          <div class="record-field"><div class="lbl">Visit Date</div><div class="val">{{ $visitDate }}</div></div>
          <div class="record-field"><div class="lbl">Time</div><div class="val">{{ $timeLabel }}</div></div>
          <div class="record-field"><div class="lbl">Duration</div><div class="val">{{ $appointment?->duration_label ?? '—' }}</div></div>
          <div class="record-field" style="grid-column: span 2;"><div class="lbl">Service(s)</div><div class="val">{{ $services }}</div></div>
          <div class="record-field"><div class="lbl">Status</div><div class="val"><span class="pill-mini">{{ $record->Status ?: 'Completed' }}</span></div></div>
          <div class="record-field"><div class="lbl">Attending Dentist</div><div class="val">{{ $dentistName ?? '—' }}</div></div>
          <div class="record-field"><div class="lbl">Completed On</div><div class="val">{{ $record->created_at?->format('M j, Y g:i A') ?? '—' }}</div></div>
          <div class="record-field"><div class="lbl">Record No.</div><div class="val">#{{ $record->RecordID }}</div></div>
          <div class="record-field"><div class="lbl">Blood Pressure</div><div class="val">{{ $record->BloodPressure ?: '—' }}</div></div>
          <div class="record-field"><div class="lbl">Bleeding Time</div><div class="val">{{ $record->BleedingTime ?: '—' }}</div></div>
        </div>
      </div>

      {{-- Odontogram --}}
      <div class="record-section">
        <h2><i class="bi bi-clipboard2-pulse"></i> Odontogram / Dental Chart</h2>
        @include('partials.odontogram', ['record' => $record, 'readonly' => true])

        @if ($teeth->isEmpty())
          <p class="record-muted mt-2 mb-0">No teeth were charted on this visit.</p>
        @else
          <table class="record-table">
            <thead><tr><th style="width:70px;">Tooth</th><th style="width:170px;">Condition</th><th style="width:150px;">Surface(s)</th><th>Description</th></tr></thead>
            <tbody>
              @foreach ($teeth->sortBy(fn ($t) => (int) $t->ToothNumber) as $tooth)
                <tr>
                  <td class="fw-semibold">{{ $tooth->ToothNumber }}</td>
                  <td><span class="cond-dot" style="background: {{ \App\Models\OdontogramTooth::CONDITION_COLORS[$tooth->Condition] ?? '#94a3b8' }};"></span>{{ $conditions[$tooth->Condition] ?? ucfirst((string) $tooth->Condition) }}</td>
                  <td>{{ $tooth->surface_list ? implode(', ', array_map('ucfirst', $tooth->surface_list)) : '—' }}</td>
                  <td>{{ $tooth->Description ?: '—' }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        @endif
      </div>

      {{-- Notes (only when there are any) --}}
      @if ($record->Notes)
        <div class="record-section">
          <h2><i class="bi bi-journal-text"></i> Dentist's Notes</h2>
          <div class="record-notes">{{ $record->Notes }}</div>
        </div>
      @endif

      <div class="record-signature">
        <div class="record-signature-block">
          <div class="record-signature-label">Patient:</div>
          <div class="record-signature-space"></div>
          <div class="record-signature-line">
            <div class="record-signature-name">{{ $fullName }}</div>
            <div class="record-signature-role">Patient</div>
          </div>
        </div>
        <div class="record-signature-block">
          <div class="record-signature-label">Attending dentist:</div>
          <div class="record-signature-space"></div>
          <div class="record-signature-line">
            <div class="record-signature-name">{{ $dentistName ?? ' ' }}</div>
            <div class="record-signature-role">Dentist</div>
          </div>
        </div>
      </div>

      <div class="record-footnote">Pus-Pus Britanico Dental Clinic · This document is confidential and intended only for the patient and the clinic.</div>
    </div>
  </div>

  <script src="{{ asset('js/odontogram.js') }}"></script>
</body>

</html>
