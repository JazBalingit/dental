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
        ?? ($record->VisitTime ? \Carbon\Carbon::createFromFormat('H:i', substr($record->VisitTime, 0, 5))->format('g:i A') : '—');
    $services = $appointment && $appointment->services->isNotEmpty()
        ? $appointment->services->pluck('ServiceName')->implode(', ')
        : ($record->Service ?: ($record->service->ServiceName ?? '—'));
    $dentistName = $appointment?->dentist?->display_name;
    $email = $patient?->userAccount?->Email ?? $patient?->Email;
    $birthdate = $patient?->DateOfBirth ? \Carbon\Carbon::parse($patient->DateOfBirth)->format('F j, Y') : null;
    // Medical history declared for this visit (or the current one for older visits).
    $medical = $record->appointment?->medicalHistory ?? $patient?->latestMedicalHistory;

    $teeth = $record->odontogramTeeth->keyBy(fn ($t) => (string) $t->ToothNumber);
    $conditions = \App\Models\OdontogramTooth::CONDITIONS;
    $codes = \App\Models\OdontogramTooth::CONDITION_CODES;
    // Teeth worth a line in the findings table: anything not plain healthy.
    $findings = $teeth->filter(fn ($t) => $t->Condition !== 'healthy' || $t->Description || $t->surface_list)
        ->sortBy(fn ($t) => (int) $t->ToothNumber);

    // Chart layout as seen facing the patient (their right on the left).
    $upper = [['18', '17', '16', '15', '14', '13', '12', '11'], ['21', '22', '23', '24', '25', '26', '27', '28']];
    $lower = [['48', '47', '46', '45', '44', '43', '42', '41'], ['31', '32', '33', '34', '35', '36', '37', '38']];

    // e.g. "dental-record_juan-dela-cruz_2026-11-09.pdf"
    $pdfFilename = 'dental-record_' . (\Illuminate\Support\Str::slug($fullName) ?: 'patient') . '_' . $record->VisitDate->format('Y-m-d') . '.pdf';
  @endphp
  <title>Dental Record — {{ $fullName }} — {{ $visitDate }}</title>
  <meta name="pdf-filename" content="{{ $pdfFilename }}">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/print-doc.css') }}">
  <style>
    /* Tooth chart: one column per tooth, the midline drawn heavier. */
    table.doc-teeth { table-layout: fixed; }
    table.doc-teeth th, table.doc-teeth td { text-align: center; padding: .3rem .1rem; }
    table.doc-teeth .tooth-no { background: var(--doc-label-bg); font-size: .68rem; font-weight: 700; color: var(--doc-muted); letter-spacing: 0; text-transform: none; }
    table.doc-teeth .tooth-code { height: 1.6rem; vertical-align: middle; font-size: .8rem; font-weight: 700; color: var(--doc-ink); }
    table.doc-teeth .quadrant { background: var(--doc-head-bg); color: var(--doc-ink); font-size: .62rem; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; }
    table.doc-teeth .midline { border-left: 2px solid var(--doc-rule-strong); }
    table.doc-teeth tr.arch-gap td { border-left: 0; border-right: 0; height: .35rem; padding: 0; }
    .doc-legend { font-size: .7rem; color: var(--doc-muted); margin-top: .35rem; line-height: 1.6; }
    .doc-legend b { color: var(--doc-ink); }
    .doc-legend .item + .item::before { content: '·'; margin: 0 .45rem; color: var(--doc-rule); }
  </style>
</head>

<body>
  <div class="doc-wrap">
    <div class="doc-sheet">

      @include('partials.print-letterhead')

      <div class="doc-title">
        <h1>Dental Record</h1>
      </div>

      <table class="doc-table doc-kv doc-meta">
        <tr>
          <th>Record No.</th>
          <td>#{{ $record->RecordID }}</td>
          <th>Visit Date</th>
          <td>{{ $visitDate }}</td>
        </tr>
        <tr>
          <th>Patient ID</th>
          <td>{{ $patientCode }}</td>
          <th>Date Printed</th>
          <td>{{ $generatedAt->format('M j, Y g:i A') }}</td>
        </tr>
      </table>

      <p class="doc-intro">
        This record documents {{ $fullName }}'s visit to the clinic on {{ $visitDate }}: the patient's details,
        the treatment given and the dental chart recorded by the attending dentist. Please keep it for your
        records and bring it to future visits or referrals.
      </p>

      {{-- Patient --}}
      <section class="doc-section doc-keep">
        <h2 class="doc-section-title">Patient Information</h2>
        <table class="doc-table doc-kv">
          <tr>
            <th>Full Name</th>
            <td colspan="3">{{ $fullName }}</td>
          </tr>
          <tr>
            <th>Age</th>
            <td>{{ $patient?->age_years !== null ? $patient->age_years . ' years old' : '—' }}</td>
            <th>Gender</th>
            <td>{{ $patient?->Gender ? ucfirst($patient->Gender) : '—' }}</td>
          </tr>
          <tr>
            <th>Birthdate</th>
            <td>{{ $birthdate ?? '—' }}</td>
            <th>Contact No.</th>
            <td>{{ $patient?->PhoneNumber ?: '—' }}</td>
          </tr>
          <tr>
            <th>Email</th>
            <td colspan="3">{{ $email ?: '—' }}</td>
          </tr>
          <tr>
            <th>Address</th>
            <td colspan="3">{{ $patient?->Address ?: '—' }}</td>
          </tr>
        </table>
      </section>

      {{-- Medical --}}
      <section class="doc-section doc-keep">
        <h2 class="doc-section-title">Medical Information</h2>
        <p class="doc-section-desc">As declared by the patient{{ $record->appointment?->medicalHistory ? ' for this visit' : '' }}.</p>
        <table class="doc-table doc-kv">
          <tr>
            <th>Blood Type</th>
            <td>{{ $medical?->answer('blood_type') ?: 'Unknown' }}</td>
            <th>Allergies</th>
            <td>{{ $medical ? ($medical->allergy_list ? implode(', ', $medical->allergy_list) : 'None declared') : 'Not on file' }}</td>
          </tr>
          <tr>
            <th>Conditions</th>
            <td colspan="3">{{ $medical ? ($medical->condition_list ? implode(', ', $medical->condition_list) : 'None declared') : 'Not on file' }}</td>
          </tr>
        </table>
      </section>

      {{-- Visit --}}
      <section class="doc-section doc-keep">
        <h2 class="doc-section-title">Treatment Details</h2>
        <p class="doc-section-desc">The completed visit this record belongs to, with the vital signs taken before treatment.</p>
        <table class="doc-table">
          <thead>
            <tr>
              <th>Date</th>
              <th>Time</th>
              <th>Service(s)</th>
              <th>Attending Dentist</th>
              <th>Blood Pressure</th>
              <th>Bleeding Time</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="nowrap">{{ $record->VisitDate->format('M j, Y') }}</td>
              <td class="nowrap">{{ $timeLabel }}</td>
              <td>{{ $services }}</td>
              <td>{{ $dentistName ?? '—' }}</td>
              <td>{{ $record->BloodPressure ?: '—' }}</td>
              <td>{{ $record->BleedingTime ?: '—' }}</td>
            </tr>
          </tbody>
        </table>
      </section>

      {{-- Odontogram --}}
      <section class="doc-section doc-keep">
        <h2 class="doc-section-title">Dental Chart (Odontogram)</h2>
        @if ($teeth->isEmpty())
          <div class="doc-empty">No teeth were charted on this visit.</div>
        @else
          <p class="doc-section-desc">
            Permanent teeth in FDI numbering, drawn facing the patient. Each charted tooth shows its condition code; blank teeth were not charted.
          </p>
          <table class="doc-table doc-teeth">
            <thead>
              <tr>
                <th colspan="8" class="quadrant">Upper Right</th>
                <th colspan="8" class="quadrant midline">Upper Left</th>
              </tr>
            </thead>
            <tbody>
              @foreach ([$upper, $lower] as $arch)
                @php $numbersFirst = $loop->first; @endphp
                @if (!$loop->first)
                  <tr class="arch-gap"><td colspan="16"></td></tr>
                @endif
                @foreach ($numbersFirst ? ['no', 'code'] : ['code', 'no'] as $rowType)
                  <tr>
                    @foreach ($arch as $side => $numbers)
                      @foreach ($numbers as $n)
                        @php $cls = $side === 1 && $loop->first ? 'midline' : ''; @endphp
                        @if ($rowType === 'no')
                          <th class="tooth-no {{ $cls }}">{{ $n }}</th>
                        @else
                          <td class="tooth-code {{ $cls }}">{{ isset($teeth[$n]) ? ($codes[$teeth[$n]->Condition] ?? '?') : '' }}</td>
                        @endif
                      @endforeach
                    @endforeach
                  </tr>
                @endforeach
              @endforeach
              <tr>
                <th colspan="8" class="quadrant">Lower Right</th>
                <th colspan="8" class="quadrant midline">Lower Left</th>
              </tr>
            </tbody>
          </table>
          <div class="doc-legend">
            @foreach ($codes as $key => $code)
              <span class="item"><b>{{ $code }}</b> {{ $conditions[$key] }}</span>
            @endforeach
          </div>
        @endif
      </section>

      @if ($teeth->isNotEmpty())
        <section class="doc-section doc-keep">
          <h2 class="doc-section-title">Findings per Tooth</h2>
          @if ($findings->isEmpty())
            <div class="doc-empty">Every charted tooth is healthy.</div>
          @else
            <p class="doc-section-desc">Teeth that need attention or have a note. Healthy teeth appear on the chart only.</p>
            <table class="doc-table">
              <thead>
                <tr>
                  <th class="center" style="width: 11%;">Tooth</th>
                  <th style="width: 25%;">Condition</th>
                  <th style="width: 22%;">Surface(s)</th>
                  <th>Notes</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($findings as $tooth)
                  <tr>
                    <td class="center"><b>{{ $tooth->ToothNumber }}</b></td>
                    <td>{{ $conditions[$tooth->Condition] ?? ucfirst((string) $tooth->Condition) }}</td>
                    <td>{{ $tooth->surface_list ? implode(', ', array_map('ucfirst', $tooth->surface_list)) : '—' }}</td>
                    <td>{{ $tooth->Description ?: '—' }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          @endif
        </section>
      @endif

      @if ($record->Notes)
        <section class="doc-section doc-keep">
          <h2 class="doc-section-title">Dentist's Notes</h2>
          <div class="doc-note">{{ $record->Notes }}</div>
        </section>
      @endif

      <div class="doc-signatures">
        <div class="doc-signature">
          <div class="doc-signature-label">Patient:</div>
          <div class="doc-signature-space"></div>
          <div class="doc-signature-line">
            <div class="doc-signature-name">{{ $fullName }}</div>
            <div class="doc-signature-role">Patient</div>
          </div>
        </div>
        <div class="doc-signature">
          <div class="doc-signature-label">Attending dentist:</div>
          <div class="doc-signature-space"></div>
          <div class="doc-signature-line">
            <div class="doc-signature-name">{{ $dentistName ?? '' }}</div>
            <div class="doc-signature-role">Dentist</div>
          </div>
        </div>
      </div>

      <div class="doc-footnote">Puspus-Britanico Dental Clinic · This document is confidential and intended only for the patient and the clinic.</div>
    </div>
  </div>

  {{-- "Download PDF" in the Print Preview modal: window.downloadDocPdf(). --}}
  <script src="{{ asset('js/print-doc.js') }}"></script>
</body>

</html>
