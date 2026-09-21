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

        <div class="card-soft p-3 p-md-4">
          <form method="GET" action="{{ route('patientRecords.history', $patient->PatientID) }}" class="data-toolbar">
            <div class="left">
              <ul class="nav nav-pills" id="recordsTabs" role="tablist">
                <li class="nav-item" role="presentation">
                  <button class="nav-link {{ $tab !== 'archived' ? 'active' : '' }}" id="active-tab-btn"
                    data-bs-toggle="pill" data-bs-target="#activePane" type="button" role="tab">
                    Active
                  </button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link {{ $tab === 'archived' ? 'active' : '' }}" id="archived-tab-btn"
                    data-bs-toggle="pill" data-bs-target="#archivedPane" type="button" role="tab">
                    Archived
                  </button>
                </li>
              </ul>
            </div>
            <div class="right">
              <input type="hidden" name="tab" id="activeTabField" value="{{ $tab }}">
              <div class="input-icon search">
                <i class="bi bi-search"></i>
                <input class="form-control" name="search" value="{{ $search }}" placeholder="Search treatment..." style="height:40px; padding-left:2.4rem;" />
              </div>
            </div>
          </form>

          <div class="tab-content mt-3">
            @foreach ([
              ['id' => 'activePane', 'list' => $records, 'archived' => false, 'empty' => 'No patient records yet.'],
              ['id' => 'archivedPane', 'list' => $archivedRecords, 'archived' => true, 'empty' => 'No archived records.'],
            ] as $pane)
              <div class="tab-pane fade {{ ($tab === 'archived') === $pane['archived'] ? 'show active' : '' }}" id="{{ $pane['id'] }}" role="tabpanel">
                <div class="table-responsive">
                  <table class="table-soft">
                    <thead>
                      <tr>
                        <th>Visit Date &amp; Time</th>
                        <th>Treatment</th>
                        <th>Completed On</th>
                        <th>Status</th>
                        @if ($pane['archived'])
                            <th>Archive Reason</th>
                        @endif
                        <th class="text-end">Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      @forelse ($pane['list'] as $record)
                        <tr>
                          <td>{{ $record->VisitDate->format('M j, Y') }} &bull; {{ \Carbon\Carbon::createFromFormat('H:i', $record->VisitTime)->format('g:i A') }}</td>
                          <td>{{ $record->Service }}</td>
                          <td>{{ $record->created_at->format('M j, Y g:i A') }}</td>
                          <td><span class="pill {{ $pane['archived'] ? 'pill-muted' : 'pill-success' }}">{{ $record->Status }}</span></td>
                          @if ($pane['archived'])
                              @include('partials.archive-reason-cell', ['row' => $record])
                          @endif
                          <td class="text-end">
                            <button type="button" class="btn-pill btn-pill-edit me-1" data-bs-toggle="modal" data-bs-target="#viewModal{{ $record->RecordID }}"><i
                                class="bi bi-eye"></i> View</button>
                            <button type="button" class="btn-pill btn-pill-archive" data-bs-toggle="modal"
                              data-bs-target="#confirmActionModal"
                              data-action-url="{{ route($pane['archived'] ? 'patientRecords.unarchive' : 'patientRecords.archive', $record->RecordID) }}"
                              data-title="{{ $pane['archived'] ? 'Unarchive' : 'Archive' }} Patient Record"
                              data-message="{{ $pane['archived'] ? 'Restore' : 'Archive' }} this record for {{ $fullName }}?"
                              data-confirm-label="{{ $pane['archived'] ? 'Unarchive' : 'Archive' }}" data-confirm-class="btn-pill-archive">
                              <i class="bi bi-archive"></i> {{ $pane['archived'] ? 'Unarchive' : 'Archive' }}</button>
                          </td>
                        </tr>
                      @empty
                        <tr>
                          <td colspan="{{ $pane['archived'] ? 6 : 5 }}" class="text-center text-muted-2 py-4">{{ $pane['empty'] }}</td>
                        </tr>
                      @endforelse
                    </tbody>
                  </table>
                </div>

                <div class="pagination-soft">
                  <div>Showing {{ $pane['list']->count() }} of {{ $pane['list']->total() }} entries</div>
                  <div class="pages">
                    @include('partials.pagination-pages', ['paginator' => $pane['list']])
                  </div>
                </div>
              </div>
            @endforeach
          </div>

          <script>
            document.querySelectorAll('#recordsTabs button').forEach(function (btn) {
                btn.addEventListener('shown.bs.tab', function () {
                    document.getElementById('activeTabField').value = btn.id === 'archived-tab-btn' ? 'archived' : 'active';
                });
            });
          </script>
        </div><!-- /card-soft -->

      </div><!-- /content -->
    </main>
  </div><!-- /app -->

  {{-- ══════════════════════════════════════════════
     VIEW MODAL — one per record (active + archived).
     Appointment details + odontogram (+ the dentist's notes);
     the patient's own information is deliberately left out.
══════════════════════════════════════════════ --}}
  @foreach ($records->merge($archivedRecords) as $record)
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

            <!-- Dentist's Notes -->
            <div class="section-label">
              <i class="bi bi-journal-text" style="font-size:13px; color:var(--brand);"></i> Dentist's Notes
            </div>
            <form method="POST" action="{{ route('patientRecords.update', $record->RecordID) }}">
              @csrf
              @include('partials.record-return-fields')
              <div class="note-add-row">
                <textarea name="notes" rows="3" placeholder="Add notes for this visit...">{{ $record->Notes }}</textarea>
                <button type="submit" class="btn-brand" style="flex-shrink:0; height:40px; align-self:flex-end;">
                  <i class="bi bi-floppy"></i> Save Notes
                </button>
              </div>
            </form>
          </div><!-- /modal-body -->

          <div class="modal-footer">
            <button type="button" class="btn-ghost" data-bs-dismiss="modal">
              <i class="bi bi-x"></i> Close
            </button>
          </div>

        </div>
      </div>
    </div>
  @endforeach

  @include('partials.admin-notif-modal')
  @include('partials.confirm-action-modal')

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

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
