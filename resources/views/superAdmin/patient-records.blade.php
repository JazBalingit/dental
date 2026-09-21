<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <link rel="icon" type="image/png" href="/images/puspus_logo.png">
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Patient Records • Dental Clinic</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link
    href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modals.css') }}">
    <link rel="stylesheet" href="{{ asset('css/mobile.css') }}">
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
      @include('partials.admin-sidebar-nav', ['active' => 'patientRecords'])
      @include('partials.admin-profile-badge')
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
        </div>

        @include('partials.flash-toasts')

        <!-- Table card -->
        <div class="card-soft p-3 p-md-4">
          <form method="GET" action="{{ route('patientRecords') }}" class="data-toolbar">
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
              <select class="form-select" name="type" style="min-width:140px; height:40px;" onchange="this.form.submit()"><option value="">All Patients</option><option value="registered" {{ ($type ?? null) === 'registered' ? 'selected' : '' }}>Registered</option><option value="walkin" {{ ($type ?? null) === 'walkin' ? 'selected' : '' }}>Walk-in</option></select>
              <div class="d-flex align-items-center gap-1 flex-wrap" title="Filter by visit date">
                <span class="small text-muted-2">Visit</span>
                <input type="date" class="form-control" name="from" value="{{ $from ?? '' }}" style="width:auto; height:40px;" aria-label="Visit date from" onchange="this.form.submit()">
                <span class="small text-muted-2">to</span>
                <input type="date" class="form-control" name="to" value="{{ $to ?? '' }}" style="width:auto; height:40px;" aria-label="Visit date to" onchange="this.form.submit()">
              </div>
              <div class="input-icon search">
                <i class="bi bi-search"></i>
                <input class="form-control" name="search" value="{{ $search }}" placeholder="Search patient or treatment..." style="height:40px; padding-left:2.4rem;" />
              </div>
            </div>
          </form>

          <div class="tab-content mt-3">
            @foreach ([
              ['id' => 'activePane', 'list' => $patients, 'archived' => false, 'empty' => 'No patient records yet.'],
              ['id' => 'archivedPane', 'list' => $archivedPatients, 'archived' => true, 'empty' => 'No archived records.'],
            ] as $pane)
              <div class="tab-pane fade {{ ($tab === 'archived') === $pane['archived'] ? 'show active' : '' }}" id="{{ $pane['id'] }}" role="tabpanel">
                <div class="table-responsive">
                  <table class="table-soft">
                    <thead>
                      <tr>
                        <th>Patient ID</th>
                        <th>Patient</th>
                        <th>Age / Gender</th>
                        <th>Contact</th>
                        <th>Last Visit</th>
                        <th>Total Records</th>
                        <th>Status</th>
                        @if ($pane['archived'])
                          <th>Archive Reason</th>
                        @endif
                        <th class="text-end">Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      @forelse ($pane['list'] as $p)
                        @php $fullName = trim($p->FirstName . ' ' . $p->LastName); @endphp
                        <tr>
                          <td><span style="font-size:12px; color:#9ca3af; font-weight:500;">PT-{{ str_pad($p->PatientID, 4, '0', STR_PAD_LEFT) }}</span></td>
                          <td>
                            <div class="d-flex align-items-center gap-2">
                              <span><img class="avatar-initials" src="{{ $p->photo_url }}" alt=""></span>
                              <span class="fw-semibold">{{ $fullName }}</span>
                              @if ($p->IsWalkIn)
                                <span class="pill pill-muted">Walk-in</span>
                              @endif
                            </div>
                          </td>
                          <td class="text-nowrap">{{ $p->age_years !== null ? $p->age_years . ' yrs' : '—' }}<div class="small text-muted-2">{{ $p->Gender ? ucfirst($p->Gender) : '—' }}</div></td>
                          <td>
                            {{ $p->PhoneNumber ?: ($p->ParentsContactNumber ?: '—') }}
                            <div class="small text-muted-2">{{ $p->userAccount?->Email ?? $p->Email ?? '—' }}</div>
                          </td>
                          <td>{{ $p->last_visit ? \Carbon\Carbon::parse($p->last_visit)->format('M j, Y') : '—' }}</td>
                          <td><span class="pill {{ $pane['archived'] ? 'pill-muted' : 'pill-success' }}">{{ $p->records_count }} {{ \Illuminate\Support\Str::plural('record', $p->records_count) }}</span></td>
                          <td>
                            @if ($pane['archived'])
                              <span class="pill pill-muted">Archived</span>
                            @elseif ($p->is_inactive)
                              <span class="pill pill-warning" title="No appointment in the last 6 months">Inactive</span>
                            @else
                              <span class="pill pill-success">Active</span>
                            @endif
                          </td>
                          @if ($pane['archived'])
                            @include('partials.archive-reason-cell', ['reason' => $p->archive_reason ?? null, 'at' => $p->archived_at ?? null])
                          @endif
                          <td class="text-end text-nowrap">
                            <a href="{{ route('patientRecords.history', $p->PatientID) }}" class="btn-pill btn-pill-edit d-inline-block text-decoration-none me-1"><i class="bi bi-folder2-open"></i> View Records</a>
                            <button type="button" class="btn-pill btn-pill-edit me-1" data-bs-toggle="modal" data-bs-target="#editPatientModal{{ $p->PatientID }}"><i class="bi bi-pencil-square"></i> Edit</button>
                            <button type="button" class="btn-pill btn-pill-archive" data-bs-toggle="modal"
                              data-bs-target="#confirmActionModal"
                              data-action-url="{{ route($pane['archived'] ? 'patientRecords.patient.unarchive' : 'patientRecords.patient.archive', $p->PatientID) }}"
                              data-title="{{ $pane['archived'] ? 'Unarchive' : 'Archive' }} Patient Records"
                              data-message="{{ $pane['archived'] ? 'Restore' : 'Archive' }} all records for {{ $fullName }}?"
                              data-confirm-label="{{ $pane['archived'] ? 'Unarchive' : 'Archive' }}" data-confirm-class="btn-pill-archive">
                              <i class="bi bi-archive"></i> {{ $pane['archived'] ? 'Unarchive' : 'Archive' }}</button>
                          </td>
                        </tr>
                      @empty
                        <tr>
                          <td colspan="{{ $pane['archived'] ? 9 : 8 }}" class="text-center text-muted-2 py-4">{{ $pane['empty'] }}</td>
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

  {{-- One edit modal per patient (active + archived) --}}
  @foreach ($patients->getCollection()->merge($archivedPatients->getCollection()) as $p)
    @include('partials.patient-edit-modal', ['patient' => $p])
  @endforeach

  @include('partials.admin-notif-modal')
  @include('partials.confirm-action-modal')

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>
