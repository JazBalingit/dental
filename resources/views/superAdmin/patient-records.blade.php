<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Patient Records • Dental Clinic</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link
    href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
</head>

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
      <nav class="nav">
        <div class="nav-section">Main</div>
        <a href="{{ route('dashboard') }}"><i class="bi bi-grid-1x2-fill"></i> Dashboard</a>
        <a href="{{ route('staffAcc') }}"><i class="bi bi-people-fill"></i> Staff Accounts</a>
        <a href="{{ route('userAcc') }}"><i class="bi bi-people-fill"></i> User Accounts</a>
        <a href="{{ route('dentistSchedule') }}"><i class="bi bi-calendar3"></i> Dentist Schedule</a>
        <a href="{{ route('walkIn') }}"><i class="bi bi-calendar3"></i> Walk-in Appointments</a>
        <a href="{{ route('appointmentApproval') }}"><i class="bi bi-clipboard2-check"></i> Appointment
          Approval</a>
        <a href="{{ route('appointments') }}"><i class="bi bi-clipboard2-check"></i> Appointments</a>
        <a href="{{ route('patientRecords') }}" class="active"><i class="bi bi-folder2-open"></i> Patient Records</a>
        <div class="nav-section">System</div>
        <a href="{{ route('configuration') }}"><i class="bi bi-sliders2"></i> Configuration</a>
      </nav>
      <div class="footer">© PUS-PUS BRITANICO DENTAL CLINIC</div>
    </aside>

    <!-- ── MAIN ── -->
    <main>

      <!-- Topbar -->
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
              <li><a class="dropdown-item small" href="{{ route('staffProfile') }}"><i class="bi bi-person me-2"></i>My Profile</a></li>
              <li><hr class="dropdown-divider"></li>
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

      <!-- Content -->
      <div class="content">

        <!-- Page head -->
        <div class="page-head">
          <div>
            <h2>Patient Records</h2>
            <div class="crumbs">Browse and manage all patient files and treatment history.</div>
          </div>
          <button class="btn-brand" data-bs-toggle="modal" data-bs-target="#archivesModal">
            <i class="bi bi-archive"></i> Archives
          </button>
        </div>

        @if (session('success'))
          <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
          <div class="alert alert-danger">
            <ul class="mb-0 ps-3">
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <!-- Table card -->
        <div class="card-soft p-3 p-md-4">
          <form method="GET" action="{{ route('patientRecords') }}" class="data-toolbar">
            <div class="left">
              <span class="text-muted-2 small">Completed patient visits</span>
            </div>
            <div class="right">
              <div class="input-icon search">
                <i class="bi bi-search"></i>
                <input class="form-control" name="search" value="{{ $search }}" placeholder="Search patient or treatment..." style="height:36px; padding-left:2.4rem;" />
              </div>
            </div>
          </form>

          <div class="table-responsive">
            <table class="table-soft">
              <thead>
                <tr>
                  <th>Patient ID</th>
                  <th>Patient</th>
                  <th>Email</th>
                  <th>Visit Date &amp; Time</th>
                  <th>Treatment</th>
                  <th>Status</th>
                  <th class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($records as $record)
                    @php $p = $record->patientInfo; @endphp
                    <tr>
                        <td><span style="font-size:12px; color:#9ca3af; font-weight:500;">PT-{{ str_pad($record->PatientID, 4, '0', STR_PAD_LEFT) }}</span></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <span><img class="avatar-initials" src="{{ $p->photo_url ?? asset('images/default.png') }}" alt=""></span>
                                <span class="fw-semibold">{{ $p->FirstName ?? '' }} {{ $p->LastName ?? '' }}</span>
                            </div>
                        </td>
                        <td>{{ $p->userAccount->Email ?? '—' }}</td>
                        <td>{{ $record->VisitDate->format('M j, Y') }} &bull; {{ \Carbon\Carbon::createFromFormat('H:i', $record->VisitTime)->format('g:i A') }}</td>
                        <td>{{ $record->Service }}</td>
                        <td><span class="pill pill-success">{{ $record->Status }}</span></td>
                        <td class="text-end">
                            <button type="button" class="btn-pill btn-pill-edit me-1" data-bs-toggle="modal" data-bs-target="#viewModal{{ $record->RecordID }}"><i
                                    class="bi bi-eye"></i> View</button>
                            <form method="POST" action="{{ route('patientRecords.archive', $record->RecordID) }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn-pill btn-pill-archive"><i class="bi bi-archive"></i> Archive</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted-2 py-4">No patient records yet.</td>
                    </tr>
                @endforelse
              </tbody>
            </table>
          </div>

          <div class="pagination-soft">
            <div>Showing {{ $records->count() }} of {{ $records->total() }} entries</div>
            <div class="pages">
              <a href="{{ $records->previousPageUrl() ?? '#' }}"><i class="bi bi-chevron-left"></i></a>
              @for ($i = 1; $i <= $records->lastPage(); $i++)
                <a href="{{ $records->url($i) }}" class="{{ $records->currentPage() === $i ? 'active' : '' }}">{{ $i }}</a>
              @endfor
              <a href="{{ $records->nextPageUrl() ?? '#' }}"><i class="bi bi-chevron-right"></i></a>
            </div>
          </div>
        </div><!-- /card-soft -->

      </div><!-- /content -->
    </main>
  </div><!-- /app -->


  {{-- ══════════════════════════════════════════════
     VIEW MODAL — one per record (active + archived)
══════════════════════════════════════════════ --}}
  @foreach ($records->merge($archivedRecords) as $record)
    @php
        $p = $record->patientInfo;
        $timeLabel = \Carbon\Carbon::createFromFormat('H:i', $record->VisitTime)->format('g:i A');
        $duration = $record->appointment->DurationHours ?? null;
    @endphp
    <div class="modal fade" id="viewModal{{ $record->RecordID }}" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content">

          <div class="modal-header">
            <div>
              <h5 class="modal-title">Patient Record</h5>
              <div class="modal-subtitle">Visit and patient information</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>

          <div class="modal-body px-4 py-3">

            <div class="patient-hero">
              <div><img class="hero-avatar-wrap" src="{{ $p->photo_url ?? asset('images/default.png') }}" alt=""></div>
              <div>
                <div class="hero-name">{{ $p->FirstName ?? '' }} {{ $p->LastName ?? '' }}</div>
                <div class="hero-id">PT-{{ str_pad($record->PatientID, 4, '0', STR_PAD_LEFT) }}</div>
                <div class="hero-badges">
                  <span class="pill pill-success"><i class="bi bi-check-circle"></i> {{ $record->Status }}</span>
                  <span class="pill pill-muted"><i class="bi bi-tooth"></i> {{ $record->Service }}</span>
                </div>
              </div>
            </div>

            <div class="section-label">
              <i class="bi bi-calendar-check" style="font-size:13px; color:var(--brand);"></i> Visit Details
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
                <label class="field-label"><i class="bi bi-hourglass-split"></i> Timeframe</label>
                <input type="text" class="field-input" value="{{ $duration ? $duration . ' hour(s)' : '—' }}" disabled />
              </div>
              <div class="field-group">
                <label class="field-label"><i class="bi bi-tooth"></i> Service</label>
                <input type="text" class="field-input" value="{{ $record->Service }}" disabled />
              </div>
            </div>

            <div class="section-label">
              <i class="bi bi-person" style="font-size:13px; color:var(--brand);"></i> Patient Information
            </div>

            <div class="info-grid-edit">
              <div class="field-group">
                <label class="field-label"><i class="bi bi-telephone"></i> Phone</label>
                <input type="tel" class="field-input" value="{{ $p->PhoneNumber ?? '' }}" disabled />
              </div>
              <div class="field-group">
                <label class="field-label"><i class="bi bi-envelope"></i> Email</label>
                <input type="email" class="field-input" value="{{ $p->userAccount->Email ?? '' }}" disabled />
              </div>
              <div class="field-group">
                <label class="field-label"><i class="bi bi-calendar-date"></i> Date of Birth</label>
                <input type="text" class="field-input" value="{{ optional($p->DateOfBirth ?? null)->format('M j, Y') }}" disabled />
              </div>
              <div class="field-group">
                <label class="field-label"><i class="bi bi-calendar3"></i> Age</label>
                <input type="text" class="field-input" value="{{ $p->Age ?? '' }}" disabled />
              </div>
              <div class="field-group">
                <label class="field-label"><i class="bi bi-person-fill"></i> Gender</label>
                <input type="text" class="field-input" value="{{ ucfirst($p->Gender ?? '') }}" disabled />
              </div>
              <div class="field-group">
                <label class="field-label"><i class="bi bi-flag"></i> Nationality</label>
                <input type="text" class="field-input" value="{{ $p->Nationality ?? '' }}" disabled />
              </div>
              <div class="field-group">
                <label class="field-label"><i class="bi bi-geo-alt"></i> Address</label>
                <input type="text" class="field-input" value="{{ $p->Address ?? '' }}" disabled />
              </div>
            </div>

            <!-- Doctor's Notes -->
            <div class="section-label">
              <i class="bi bi-journal-text" style="font-size:13px; color:var(--brand);"></i> Doctor's Notes
            </div>
            <form method="POST" action="{{ route('patientRecords.update', $record->RecordID) }}">
              @csrf
              <div class="note-add-row">
                <textarea name="notes" rows="3" placeholder="Add notes for this visit...">{{ $record->Notes }}</textarea>
                <button type="submit" class="btn-brand" style="flex-shrink:0; height:40px; align-self:flex-end;">
                  <i class="bi bi-floppy"></i> Save Notes
                </button>
              </div>
            </form>
          </div><!-- /modal-body -->

          <div class="modal-footer" style="border-top:1px solid var(--border); padding:14px 24px; gap:8px;">
            <button type="button" class="btn-ghost" data-bs-dismiss="modal">
              <i class="bi bi-x"></i> Close
            </button>
          </div>

        </div>
      </div>
    </div>
  @endforeach


  <!-- ══════════════════════════════════════════════
     ARCHIVES MODAL
══════════════════════════════════════════════ -->
  <div class="modal fade" id="archivesModal" tabindex="-1" aria-labelledby="archivesLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
      <div class="modal-content">

        <div class="modal-header" style="border-bottom:1px solid var(--border);">
          <div>
            <h5 class="modal-title fw-semibold" id="archivesLabel">Patient Records - Archives</h5>
            <div class="modal-subtitle">View and restore archived patient records</div>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <!-- Table card -->
          <div class="card-soft ">
            <div class="data-toolbar">
              <div class="left">
                <span class="text-muted-2 small">Show</span>
                <select class="form-select" style="width:80px;">
                  <option>10</option>
                  <option>25</option>
                  <option>50</option>
                </select>
                <span class="text-muted-2 small">entries</span>
              </div>
              <div class="right">
                <select class="form-select" style="min-width:160px;">
                  <option>All Treatments</option>
                  <option>Cleaning</option>
                  <option>Filling</option>
                  <option>Extraction</option>
                  <option>Whitening</option>
                </select>
                <select class="form-select" style="min-width:140px;">
                  <option>All Status</option>
                  <option>Completed</option>
                  <option>Scheduled</option>
                  <option>Cancelled</option>
                </select>
                <div class="input-icon search">
                  <i class="bi bi-search"></i>
                  <input class="form-control" placeholder="Search patient..."
                    style="height:36px; padding-left:2.4rem;" />
                </div>
              </div>
            </div>
          </div><!-- /card-soft -->

          <div class="table-responsive">
            <table class="table-soft">
              <thead>
                <tr>
                  <th>Patient ID</th>
                  <th>Patient</th>
                  <th>Email</th>
                  <th>Visit Date &amp; Time</th>
                  <th>Treatment</th>
                  <th>Status</th>
                  <th class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($archivedRecords as $record)
                    @php $p = $record->patientInfo; @endphp
                    <tr>
                        <td><span style="font-size:12px; color:#9ca3af; font-weight:500;">PT-{{ str_pad($record->PatientID, 4, '0', STR_PAD_LEFT) }}</span></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <span><img class="avatar-initials" src="{{ $p->photo_url ?? asset('images/default.png') }}" alt=""></span>
                                <span class="fw-semibold">{{ $p->FirstName ?? '' }} {{ $p->LastName ?? '' }}</span>
                            </div>
                        </td>
                        <td>{{ $p->userAccount->Email ?? '—' }}</td>
                        <td>{{ $record->VisitDate->format('M j, Y') }} &bull; {{ \Carbon\Carbon::createFromFormat('H:i', $record->VisitTime)->format('g:i A') }}</td>
                        <td>{{ $record->Service }}</td>
                        <td><span class="pill pill-muted">{{ $record->Status }}</span></td>
                        <td class="text-end">
                            <button type="button" class="btn-pill btn-pill-edit me-1" data-bs-toggle="modal" data-bs-target="#viewModal{{ $record->RecordID }}"><i
                                    class="bi bi-eye"></i> View</button>
                            <form method="POST" action="{{ route('patientRecords.unarchive', $record->RecordID) }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn-pill btn-pill-archive"><i class="bi bi-archive"></i> Unarchive</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted-2 py-4">No archived records.</td>
                    </tr>
                @endforelse
              </tbody>
            </table>
          </div>

          <div class="pagination-soft mb-3">
            <div>Showing {{ $archivedRecords->count() }} of {{ $archivedRecords->total() }} entries</div>
            <div class="pages">
              <a href="{{ $archivedRecords->previousPageUrl() ?? '#' }}"><i class="bi bi-chevron-left"></i></a>
              @for ($i = 1; $i <= $archivedRecords->lastPage(); $i++)
                <a href="{{ $archivedRecords->url($i) }}" class="{{ $archivedRecords->currentPage() === $i ? 'active' : '' }}">{{ $i }}</a>
              @endfor
              <a href="{{ $archivedRecords->nextPageUrl() ?? '#' }}"><i class="bi bi-chevron-right"></i></a>
            </div>
          </div>

          <div class="modal-footer border-0 pt-0">
            <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Close</button>
          </div>

        </div>
      </div>
    </div>
  </div>

  @include('partials.admin-notif-modal')

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>
