<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <link rel="icon" type="image/png" href="/images/puspus_logo.png">
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>{{ $patient->FirstName }} {{ $patient->LastName }}'s Patient Record History • Dental Clinic</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link
    href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
  <link rel="stylesheet" href="{{ asset('css/odontogram.css') }}">
  <link rel="stylesheet" href="{{ asset('css/medical-history.css') }}">
  <link rel="stylesheet" href="{{ asset('css/modals.css') }}">
  <link rel="stylesheet" href="{{ asset('css/mobile.css') }}">
</head>

@php $fullName = trim($patient->FirstName . ' ' . $patient->LastName); @endphp

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
      {{-- No item is highlighted: this sub-page isn't a menu entry of its own. --}}
      @include('partials.admin-sidebar-nav', ['active' => ''])
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
            <h2>{{ $fullName }}'s Patient Record History</h2>
            <div class="crumbs">All visits and treatments on file for this patient.</div>
          </div>
          <a href="{{ route('patientRecords') }}" class="btn-ghost text-decoration-none"><i class="bi bi-arrow-left"></i> Back</a>
        </div>

        @include('partials.flash-toasts')

        {{-- Static (read-only) profile; editing is on the Patient Records list. --}}
        @include('patient-records.profile-card', ['patient' => $patient])

        {{-- Medical history: current version + every earlier one --}}
        @php $currentMedical = $medicalVersions->first(); @endphp
        <div class="card-soft p-3 p-md-4 mb-3">
          <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
            <div class="fw-semibold"><i class="bi bi-heart-pulse me-1" style="color:#b91c1c;"></i> Medical History</div>
            <button type="button" class="btn btn-pill btn-pill-edit" data-bs-toggle="modal" data-bs-target="#medicalEditModal">
              <i class="bi bi-pencil-square"></i> {{ $currentMedical ? 'Update' : 'Fill in' }}
            </button>
          </div>
          @include('partials.medical-history-view', ['mhv' => $currentMedical, 'mhvCompact' => true])

          @if ($medicalVersions->count() > 1)
            <details class="mt-3">
              <summary class="small fw-semibold" style="cursor:pointer;">Earlier versions ({{ $medicalVersions->count() - 1 }})</summary>
              @foreach ($medicalVersions->slice(1) as $version)
                <div class="mt-3 pt-3" style="border-top:1px dashed #e3ece6;">
                  @include('partials.medical-history-view', ['mhv' => $version, 'mhvCompact' => true])
                </div>
              @endforeach
            </details>
          @endif
        </div>

        <div class="modal fade" id="medicalEditModal" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content">
              <form method="POST" action="{{ route('patientRecords.medical.update', $patient->PatientID) }}">
                @csrf
                <div class="modal-header">
                  <h5 class="modal-title"><i class="bi bi-heart-pulse me-2" style="color:#b91c1c;"></i>Medical History — {{ $patient->full_name }}</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" data-mh-step>
                  <p class="small text-muted-2 mb-2">Saving keeps the old answers as an earlier version — nothing is overwritten.</p>
                  <div data-mh-form>
                    @include('partials.medical-history-form', ['mhf' => ['name' => 'mh', 'id' => 'staff', 'values' => $currentMedical?->form_values ?? [], 'staff' => true]])
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Cancel</button>
                  <button type="submit" class="btn btn-brand"><i class="bi bi-check2-circle me-1"></i> Save Medical History</button>
                </div>
              </form>
            </div>
          </div>
        </div>
        @if ($errors->has('mh') || collect($errors->keys())->contains(fn ($k) => str_starts_with($k, 'mh.')))
          <script>document.addEventListener('DOMContentLoaded', () => bootstrap.Modal.getOrCreateInstance(document.getElementById('medicalEditModal')).show());</script>
        @endif

        <div class="card-soft p-3 p-md-4">
          <form method="GET" action="{{ route('patientRecords.history', $patient->PatientID) }}" class="data-toolbar">
            <div class="left">
              <div class="fw-semibold">Visit History</div>
            </div>
            <div class="right">
              <div class="input-icon search">
                <i class="bi bi-search"></i>
                <input class="form-control" name="search" value="{{ $search }}" placeholder="Search treatment..." style="height:40px; padding-left:2.4rem;" />
              </div>
            </div>
          </form>

          {{-- Visits can't be archived one by one — only the whole patient can,
               from the Patient Records list — so this is a single read-only list. --}}
          <div class="mt-3">
            <div class="table-responsive">
              <table class="table-soft">
                <thead>
                  <tr>
                    <th>Visit Date &amp; Time</th>
                    <th>Treatment</th>
                    <th>Completed On</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse ($records as $record)
                    <tr>
                      <td>{{ $record->VisitDate->format('M j, Y') }} &bull; {{ \Carbon\Carbon::createFromFormat('H:i', $record->VisitTime)->format('g:i A') }}</td>
                      <td>{{ $record->Service }}</td>
                      <td>{{ $record->created_at->format('M j, Y g:i A') }}</td>
                      <td><span class="pill pill-success">{{ $record->Status }}</span></td>
                      <td class="text-end">
                        <button type="button" class="btn-pill btn-pill-edit me-1" data-bs-toggle="modal" data-bs-target="#viewModal{{ $record->RecordID }}"><i
                            class="bi bi-eye"></i> View</button>
                        <button type="button" class="btn-pill btn-pill-archive" data-print-preview="{{ route('patientRecords.print', $record->RecordID) }}"
                          data-print-title="Dental record · {{ $record->VisitDate->format('M j, Y') }}"><i class="bi bi-printer"></i> Print</button>
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="5" class="text-center text-muted-2 py-4">No patient records yet.</td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>

            <div class="pagination-soft">
              <div>Showing {{ $records->count() }} of {{ $records->total() }} entries</div>
              <div class="pages">
                @include('partials.pagination-pages', ['paginator' => $records])
              </div>
            </div>
          </div>
        </div><!-- /card-soft -->

      </div><!-- /content -->
    </main>
  </div><!-- /app -->

  {{-- ══════════════════════════════════════════════
     VIEW MODAL — one per record.
     Appointment details + odontogram (+ the dentist's notes);
     the patient's own information is deliberately left out.
══════════════════════════════════════════════ --}}
  @foreach ($records as $record)
    @php
      $timeLabel = \Carbon\Carbon::createFromFormat('H:i', $record->VisitTime)->format('g:i A');
      $duration = $record->appointment?->duration_label;
    @endphp
    <div class="modal fade" id="viewModal{{ $record->RecordID }}" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content">

          <div class="modal-header">
            <div>
              <h5 class="modal-title">Patient Record</h5>
              <div class="modal-subtitle">Appointment details and dental chart</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>

          <div class="modal-body">

            <div class="section-label">
              <i class="bi bi-calendar-check" style="font-size:13px; color:var(--brand);"></i> Appointment Details
            </div>
            <div class="info-grid-edit">
              <div class="field-group">
                <label class="field-label"><i class="bi bi-calendar-event"></i> Visit Date</label>
                <input type="text" class="field-input" value="{{ $record->VisitDate->format('F j, Y') }}" disabled />
              </div>
              <div class="field-group">
                <label class="field-label"><i class="bi bi-clock"></i> Visit Time</label>
                <input type="text" class="field-input" value="{{ $timeLabel }}" disabled />
              </div>
              <div class="field-group">
                <label class="field-label"><i class="bi bi-hourglass-split"></i> Duration</label>
                <input type="text" class="field-input" value="{{ $duration ?: '—' }}" disabled />
              </div>
              <div class="field-group">
                <label class="field-label"><i class="bi bi-tooth"></i> Service</label>
                <input type="text" class="field-input" value="{{ $record->Service }}" disabled />
              </div>
            </div>

            {{-- Odontogram (interactive dental chart for this visit) --}}
            @include('partials.odontogram', ['record' => $record])

            <form method="POST" action="{{ route('patientRecords.update', $record->RecordID) }}">
              @csrf
              @include('partials.record-return-fields')

              <!-- Vital signs — measured at this visit -->
              <div class="section-label">
                <i class="bi bi-activity" style="font-size:13px; color:var(--brand);"></i> Vital Signs (this visit)
              </div>
              <div class="info-grid-edit mb-3">
                <div class="field-group">
                  <label class="field-label" for="bp{{ $record->RecordID }}"><i class="bi bi-heart-pulse"></i> Blood Pressure</label>
                  <input type="text" class="field-input" id="bp{{ $record->RecordID }}" name="blood_pressure" maxlength="20" placeholder="e.g. 120/80" value="{{ old('blood_pressure', $record->BloodPressure) }}" />
                </div>
                <div class="field-group">
                  <label class="field-label" for="bt{{ $record->RecordID }}"><i class="bi bi-droplet"></i> Bleeding Time</label>
                  <input type="text" class="field-input" id="bt{{ $record->RecordID }}" name="bleeding_time" maxlength="30" placeholder="e.g. 2 min" value="{{ old('bleeding_time', $record->BleedingTime) }}" />
                </div>
              </div>

              <!-- Dentist's Notes -->
              <div class="section-label">
                <i class="bi bi-journal-text" style="font-size:13px; color:var(--brand);"></i> Dentist's Notes
              </div>
              <div class="note-add-row">
                <textarea name="notes" rows="3" placeholder="Add notes for this visit...">{{ $record->Notes }}</textarea>
                <button type="submit" class="btn-brand" style="flex-shrink:0; height:40px; align-self:flex-end;">
                  <i class="bi bi-floppy"></i> Save
                </button>
              </div>
            </form>
          </div><!-- /modal-body -->

          <div class="modal-footer">
            <button type="button" class="btn-ghost" data-bs-dismiss="modal">
              <i class="bi bi-x"></i> Close
            </button>
            <button type="button" class="btn-brand" data-print-preview="{{ route('patientRecords.print', $record->RecordID) }}"
              data-print-title="Dental record · {{ $record->VisitDate->format('M j, Y') }}">
              <i class="bi bi-printer"></i> Print Record
            </button>
          </div>

        </div>
      </div>
    </div>
  @endforeach

  @include('partials.admin-notif-modal')
  @include('partials.confirm-action-modal')
  @include('partials.print-preview-modal')

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="{{ asset('js/booking-forms.js') }}"></script>

  <script>
    // After saving notes or the odontogram the controller redirects back with
    // a #viewModal<id> fragment — re-open that record's View modal so the
    // staff member stays in context.
    document.addEventListener('DOMContentLoaded', function () {
      const hash = window.location.hash;
      if (!hash.startsWith('#viewModal')) return;
      const el = document.getElementById(hash.slice(1));
      if (el && window.bootstrap) {
        bootstrap.Modal.getOrCreateInstance(el).show();
      }
    });
  </script>

  <script src="{{ asset('js/odontogram.js') }}"></script>

</body>

</html>
