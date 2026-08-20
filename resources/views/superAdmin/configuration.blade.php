<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Configuration • Dental Clinic</title>
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
        <a href="{{ route('patientRecords') }}"><i class="bi bi-folder2-open"></i> Patient Records</a>
        <div class="nav-section">System</div>
        <a href="{{ route('configuration') }}" class="active"><i class="bi bi-sliders2"></i> Configuration</a>
      </nav>
      <div class="footer">© PUS-PUS BRITANICO DENTAL CLINIC</div>
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

      <div class="content">
        <div class="page-head">
          <div>
            <h2>Configuration</h2>
            <div class="crumbs">Branding, services, activity & audit logs.</div>
          </div>
        </div>

        @if (session('success'))
          <div class="alert alert-success py-2">{{ session('success') }}</div>
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

        <!-- LOGO -->
        <div class="card-soft mb-4">
          <div class="card-header d-flex align-items-center justify-content-between">
            <span><i class="bi bi-image me-2" style="color: var(--brand-700);"></i> Change Clinic Logo</span>
            <span class="pill pill-info">JPG / PNG / SVG • Max 2MB</span>
          </div>
          <div class="card-body">
            <form method="POST" action="{{ route('configuration.logo') }}" enctype="multipart/form-data">
              @csrf
              <div class="row g-4 align-items-center">
                <div class="col-md-3 text-center">
                  <div class="logo-preview mx-auto">
                    <img src="{{ asset('images/puspus_logo.png') }}?v={{ $logoVersion }}" alt="Current logo"
                      style="max-width:100%; max-height:100%;">
                  </div>
                  <div class="small text-muted-2 mt-2">Current logo</div>
                </div>
                <div class="col-md-9">
                  <label class="upload-zone d-block" style="cursor:pointer;">
                    <i class="bi bi-cloud-arrow-up-fill mb-2 d-block"></i>
                    <div class="fw-semibold" id="logoFileLabel">Drag & drop your new logo here</div>
                    <div class="small text-muted-2 mb-3">or click to browse from your computer</div>
                    <span class="btn btn-brand px-4"><i class="bi bi-folder2-open me-1"></i> Browse Files</span>
                    <input type="file" name="logo" id="logoInput" accept=".jpg,.jpeg,.png,.svg" class="d-none" required>
                  </label>
                  <div class="d-flex gap-2 mt-3 justify-content-end">
                    <button type="reset" class="btn btn-ghost px-3">Reset</button>
                    <button type="submit" class="btn btn-brand px-3">Save Changes</button>
                  </div>
                </div>
              </div>
            </form>
          </div>
        </div>

        <!-- SERVICES -->
        <div class="card-soft mb-4">
          <div class="card-header d-flex align-items-center justify-content-between">
            <span><i class="bi bi-heart-pulse me-2" style="color: var(--brand-700);"></i> Services</span>
            <button class="btn btn-brand px-3" data-bs-toggle="modal" data-bs-target="#addServiceModal">
              <i class="bi bi-plus-lg"></i> Add Service
            </button>
          </div>
          <div class="card-body p-3 p-md-4">
            <form method="GET" action="{{ route('configuration') }}" class="data-toolbar">
              <div class="left">
                <ul class="nav nav-pills" data-tabgroup="services" role="tablist">
                  <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $servicesTab !== 'archived' ? 'active' : '' }}" type="button"
                      data-bs-toggle="pill" data-bs-target="#servicesActivePane" data-tab-value="active" role="tab">Active</button>
                  </li>
                  <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $servicesTab === 'archived' ? 'active' : '' }}" type="button"
                      data-bs-toggle="pill" data-bs-target="#servicesArchivedPane" data-tab-value="archived" role="tab">Archived</button>
                  </li>
                </ul>
              </div>
              <div class="right">
                <input type="hidden" name="servicesTab" id="servicesTabField" value="{{ $servicesTab }}">
                <input type="hidden" name="activityTab" value="{{ $activityTab }}">
                <input type="hidden" name="activitySearch" value="{{ $activitySearch }}">
                <input type="hidden" name="auditTab" value="{{ $auditTab }}">
                <input type="hidden" name="auditSearch" value="{{ $auditSearch }}">
                <input type="hidden" name="auditType" value="{{ $auditType }}">
                <div class="input-icon search">
                  <i class="bi bi-search"></i>
                  <input class="form-control" name="serviceSearch" value="{{ $serviceSearch }}"
                    placeholder="Search services..." style="height:38px; padding-left:2.3rem; min-width:220px;" />
                </div>
              </div>
            </form>

            <div class="tab-content mt-3">
              <div class="tab-pane fade {{ $servicesTab !== 'archived' ? 'show active' : '' }}" id="servicesActivePane" role="tabpanel">
                <div class="table-responsive">
                  <table class="table-soft" style="border-radius:0; box-shadow:none;">
                    <thead>
                      <tr>
                        <th>Service</th>
                        <th>Description</th>
                        <th>Price</th>
                        <th class="text-end">Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      @forelse ($services as $service)
                        <tr>
                          <td class="fw-semibold">{{ $service->ServiceName }}</td>
                          <td>{{ $service->Description ?: '—' }}</td>
                          <td>{{ $service->Price !== null ? '₱' . number_format($service->Price, 2) : '—' }}</td>
                          <td class="text-end">
                            <button type="button" class="btn btn-pill btn-pill-edit me-1" data-bs-toggle="modal"
                              data-bs-target="#editServiceModal{{ $service->ServiceID }}"><i class="bi bi-pencil-square"></i> Edit</button>
                            <form method="POST" action="{{ route('configuration.services.archive', $service->ServiceID) }}" class="d-inline">
                              @csrf
                              <button type="submit" class="btn btn-pill btn-pill-archive"><i class="bi bi-archive"></i> Archive</button>
                            </form>
                          </td>
                        </tr>
                      @empty
                        <tr><td colspan="4" class="text-center text-muted-2 py-4">No services yet.</td></tr>
                      @endforelse
                    </tbody>
                  </table>
                </div>
                <div class="pagination-soft">
                  <div>Showing {{ $services->count() }} of {{ $services->total() }} entries</div>
                  <div class="pages">
                    <a href="{{ $services->previousPageUrl() ?? '#' }}"><i class="bi bi-chevron-left"></i></a>
                    @for ($i = 1; $i <= $services->lastPage(); $i++)
                      <a href="{{ $services->url($i) }}" class="{{ $services->currentPage() === $i ? 'active' : '' }}">{{ $i }}</a>
                    @endfor
                    <a href="{{ $services->nextPageUrl() ?? '#' }}"><i class="bi bi-chevron-right"></i></a>
                  </div>
                </div>
              </div>

              <div class="tab-pane fade {{ $servicesTab === 'archived' ? 'show active' : '' }}" id="servicesArchivedPane" role="tabpanel">
                <div class="table-responsive">
                  <table class="table-soft" style="border-radius:0; box-shadow:none;">
                    <thead>
                      <tr>
                        <th>Service</th>
                        <th>Description</th>
                        <th>Price</th>
                        <th class="text-end">Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      @forelse ($archivedServices as $service)
                        <tr>
                          <td class="fw-semibold">{{ $service->ServiceName }}</td>
                          <td>{{ $service->Description ?: '—' }}</td>
                          <td>{{ $service->Price !== null ? '₱' . number_format($service->Price, 2) : '—' }}</td>
                          <td class="text-end">
                            <button type="button" class="btn btn-pill btn-pill-edit me-1" data-bs-toggle="modal"
                              data-bs-target="#editServiceModal{{ $service->ServiceID }}"><i class="bi bi-pencil-square"></i> Edit</button>
                            <form method="POST" action="{{ route('configuration.services.unarchive', $service->ServiceID) }}" class="d-inline">
                              @csrf
                              <button type="submit" class="btn btn-pill btn-pill-archive"><i class="bi bi-archive"></i> Unarchive</button>
                            </form>
                          </td>
                        </tr>
                      @empty
                        <tr><td colspan="4" class="text-center text-muted-2 py-4">No archived services.</td></tr>
                      @endforelse
                    </tbody>
                  </table>
                </div>
                <div class="pagination-soft">
                  <div>Showing {{ $archivedServices->count() }} of {{ $archivedServices->total() }} entries</div>
                  <div class="pages">
                    <a href="{{ $archivedServices->previousPageUrl() ?? '#' }}"><i class="bi bi-chevron-left"></i></a>
                    @for ($i = 1; $i <= $archivedServices->lastPage(); $i++)
                      <a href="{{ $archivedServices->url($i) }}" class="{{ $archivedServices->currentPage() === $i ? 'active' : '' }}">{{ $i }}</a>
                    @endfor
                    <a href="{{ $archivedServices->nextPageUrl() ?? '#' }}"><i class="bi bi-chevron-right"></i></a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- ACTIVITY LOGS -->
        <div class="card-soft mb-4">
          <div class="card-header d-flex align-items-center justify-content-between">
            <span><i class="bi bi-activity me-2" style="color: var(--brand-700);"></i> Activity Logs</span>
          </div>
          <div class="card-body p-3 p-md-4">
            <form method="GET" action="{{ route('configuration') }}" class="data-toolbar">
              <div class="left">
                <ul class="nav nav-pills" data-tabgroup="activity" role="tablist">
                  <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activityTab !== 'archived' ? 'active' : '' }}" type="button"
                      data-bs-toggle="pill" data-bs-target="#activityActivePane" data-tab-value="active" role="tab">Active</button>
                  </li>
                  <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activityTab === 'archived' ? 'active' : '' }}" type="button"
                      data-bs-toggle="pill" data-bs-target="#activityArchivedPane" data-tab-value="archived" role="tab">Archived</button>
                  </li>
                </ul>
              </div>
              <div class="right">
                <input type="hidden" name="activityTab" id="activityTabField" value="{{ $activityTab }}">
                <input type="hidden" name="servicesTab" value="{{ $servicesTab }}">
                <input type="hidden" name="serviceSearch" value="{{ $serviceSearch }}">
                <input type="hidden" name="auditTab" value="{{ $auditTab }}">
                <input type="hidden" name="auditSearch" value="{{ $auditSearch }}">
                <input type="hidden" name="auditType" value="{{ $auditType }}">
                <div class="input-icon search">
                  <i class="bi bi-search"></i>
                  <input class="form-control" name="activitySearch" value="{{ $activitySearch }}"
                    placeholder="Search by name or email..." style="height:38px; padding-left:2.3rem; min-width:240px;" />
                </div>
              </div>
            </form>

            <div class="tab-content mt-3">
              <div class="tab-pane fade {{ $activityTab !== 'archived' ? 'show active' : '' }}" id="activityActivePane" role="tabpanel">
                <div class="table-responsive">
                  <table class="table-soft" style="border-radius:0; box-shadow:none;">
                    <thead>
                      <tr>
                        <th>User</th>
                        <th>Account Type</th>
                        <th>Logged In</th>
                        <th>Logged Out</th>
                        <th class="text-end">Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      @forelse ($activityLogs as $log)
                        @php
                          $ua = $log->userAccount;
                          $isStaff = $ua?->AccountType === 'Staff';
                          $info = $isStaff ? $ua?->staffInfo : $ua?->patientInfo;
                          $name = $info ? trim($info->FirstName . ' ' . $info->LastName) : ($ua->Email ?? 'Unknown');
                        @endphp
                        <tr>
                          <td><span><img class="avatar-initials" src="{{ $info->photo_url ?? asset('images/default.png') }}" alt=""></span> {{ $name }}</td>
                          <td><span class="pill {{ $isStaff ? 'pill-info' : 'pill-success' }}">{{ $isStaff ? 'Staff' : 'User' }}</span></td>
                          <td>{{ optional($log->LoggedInTime)->format('M j, Y g:i A') ?? '—' }}</td>
                          <td>{{ optional($log->LoggedOutTime)->format('M j, Y g:i A') ?? '—' }}</td>
                          <td class="text-end">
                            <form method="POST" action="{{ route('configuration.activityLogs.archive', $log->ActivityLogsID) }}" class="d-inline">
                              @csrf
                              <button type="submit" class="btn btn-pill btn-pill-archive"><i class="bi bi-archive"></i> Archive</button>
                            </form>
                          </td>
                        </tr>
                      @empty
                        <tr><td colspan="5" class="text-center text-muted-2 py-4">No activity yet.</td></tr>
                      @endforelse
                    </tbody>
                  </table>
                </div>
                <div class="pagination-soft">
                  <div>Showing {{ $activityLogs->count() }} of {{ $activityLogs->total() }} entries</div>
                  <div class="pages">
                    <a href="{{ $activityLogs->previousPageUrl() ?? '#' }}"><i class="bi bi-chevron-left"></i></a>
                    @for ($i = 1; $i <= $activityLogs->lastPage(); $i++)
                      <a href="{{ $activityLogs->url($i) }}" class="{{ $activityLogs->currentPage() === $i ? 'active' : '' }}">{{ $i }}</a>
                    @endfor
                    <a href="{{ $activityLogs->nextPageUrl() ?? '#' }}"><i class="bi bi-chevron-right"></i></a>
                  </div>
                </div>
              </div>

              <div class="tab-pane fade {{ $activityTab === 'archived' ? 'show active' : '' }}" id="activityArchivedPane" role="tabpanel">
                <div class="table-responsive">
                  <table class="table-soft" style="border-radius:0; box-shadow:none;">
                    <thead>
                      <tr>
                        <th>User</th>
                        <th>Account Type</th>
                        <th>Logged In</th>
                        <th>Logged Out</th>
                        <th class="text-end">Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      @forelse ($archivedActivityLogs as $log)
                        @php
                          $ua = $log->userAccount;
                          $isStaff = $ua?->AccountType === 'Staff';
                          $info = $isStaff ? $ua?->staffInfo : $ua?->patientInfo;
                          $name = $info ? trim($info->FirstName . ' ' . $info->LastName) : ($ua->Email ?? 'Unknown');
                        @endphp
                        <tr>
                          <td><span><img class="avatar-initials" src="{{ $info->photo_url ?? asset('images/default.png') }}" alt=""></span> {{ $name }}</td>
                          <td><span class="pill {{ $isStaff ? 'pill-info' : 'pill-success' }}">{{ $isStaff ? 'Staff' : 'User' }}</span></td>
                          <td>{{ optional($log->LoggedInTime)->format('M j, Y g:i A') ?? '—' }}</td>
                          <td>{{ optional($log->LoggedOutTime)->format('M j, Y g:i A') ?? '—' }}</td>
                          <td class="text-end">
                            <form method="POST" action="{{ route('configuration.activityLogs.unarchive', $log->ActivityLogsID) }}" class="d-inline">
                              @csrf
                              <button type="submit" class="btn btn-pill btn-pill-archive"><i class="bi bi-archive"></i> Unarchive</button>
                            </form>
                          </td>
                        </tr>
                      @empty
                        <tr><td colspan="5" class="text-center text-muted-2 py-4">No archived activity.</td></tr>
                      @endforelse
                    </tbody>
                  </table>
                </div>
                <div class="pagination-soft">
                  <div>Showing {{ $archivedActivityLogs->count() }} of {{ $archivedActivityLogs->total() }} entries</div>
                  <div class="pages">
                    <a href="{{ $archivedActivityLogs->previousPageUrl() ?? '#' }}"><i class="bi bi-chevron-left"></i></a>
                    @for ($i = 1; $i <= $archivedActivityLogs->lastPage(); $i++)
                      <a href="{{ $archivedActivityLogs->url($i) }}" class="{{ $archivedActivityLogs->currentPage() === $i ? 'active' : '' }}">{{ $i }}</a>
                    @endfor
                    <a href="{{ $archivedActivityLogs->nextPageUrl() ?? '#' }}"><i class="bi bi-chevron-right"></i></a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- AUDIT LOGS -->
        <div class="card-soft">
          <div class="card-header d-flex align-items-center justify-content-between">
            <span><i class="bi bi-shield-check me-2" style="color: var(--brand-700);"></i> Audit Logs</span>
          </div>
          <div class="card-body p-3 p-md-4">
            <form method="GET" action="{{ route('configuration') }}" class="data-toolbar">
              <div class="left">
                <ul class="nav nav-pills" data-tabgroup="audit" role="tablist">
                  <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $auditTab !== 'archived' ? 'active' : '' }}" type="button"
                      data-bs-toggle="pill" data-bs-target="#auditActivePane" data-tab-value="active" role="tab">Active</button>
                  </li>
                  <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $auditTab === 'archived' ? 'active' : '' }}" type="button"
                      data-bs-toggle="pill" data-bs-target="#auditArchivedPane" data-tab-value="archived" role="tab">Archived</button>
                  </li>
                </ul>
              </div>
              <div class="right">
                <input type="hidden" name="auditTab" id="auditTabField" value="{{ $auditTab }}">
                <input type="hidden" name="servicesTab" value="{{ $servicesTab }}">
                <input type="hidden" name="serviceSearch" value="{{ $serviceSearch }}">
                <input type="hidden" name="activityTab" value="{{ $activityTab }}">
                <input type="hidden" name="activitySearch" value="{{ $activitySearch }}">
                <select class="form-select" name="auditType" style="height:38px; min-width:150px;" onchange="this.form.submit()">
                  <option value="">All Actions</option>
                  @foreach ($actionTypes as $type)
                    <option value="{{ $type }}" {{ $auditType === $type ? 'selected' : '' }}>{{ $type }}</option>
                  @endforeach
                </select>
                <div class="input-icon search">
                  <i class="bi bi-search"></i>
                  <input class="form-control" name="auditSearch" value="{{ $auditSearch }}"
                    placeholder="Search staff or description..." style="height:38px; padding-left:2.3rem; min-width:240px;" />
                </div>
              </div>
            </form>

            <div class="tab-content mt-3">
              <div class="tab-pane fade {{ $auditTab !== 'archived' ? 'show active' : '' }}" id="auditActivePane" role="tabpanel">
                <div class="table-responsive">
                  <table class="table-soft" style="border-radius:0; box-shadow:none;">
                    <thead>
                      <tr>
                        <th>Staff</th>
                        <th>Action</th>
                        <th>Description</th>
                        <th>Timestamp</th>
                        <th class="text-end">Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      @forelse ($auditLogs as $log)
                        @php
                          $si = $log->staffAccount?->staffInfo;
                          $name = $si ? trim($si->FirstName . ' ' . $si->LastName) : ($log->staffAccount->Email ?? 'Unknown');
                        @endphp
                        <tr>
                          <td>{{ $name }}</td>
                          <td><span class="pill pill-info">{{ $log->ActionType }}</span></td>
                          <td>{{ $log->Description }}</td>
                          <td>{{ $log->created_at->format('M j, Y g:i A') }}</td>
                          <td class="text-end">
                            <form method="POST" action="{{ route('configuration.auditLogs.archive', $log->AuditLogID) }}" class="d-inline">
                              @csrf
                              <button type="submit" class="btn btn-pill btn-pill-archive"><i class="bi bi-archive"></i> Archive</button>
                            </form>
                          </td>
                        </tr>
                      @empty
                        <tr><td colspan="5" class="text-center text-muted-2 py-4">No audit activity yet.</td></tr>
                      @endforelse
                    </tbody>
                  </table>
                </div>
                <div class="pagination-soft">
                  <div>Showing {{ $auditLogs->count() }} of {{ $auditLogs->total() }} entries</div>
                  <div class="pages">
                    <a href="{{ $auditLogs->previousPageUrl() ?? '#' }}"><i class="bi bi-chevron-left"></i></a>
                    @for ($i = 1; $i <= $auditLogs->lastPage(); $i++)
                      <a href="{{ $auditLogs->url($i) }}" class="{{ $auditLogs->currentPage() === $i ? 'active' : '' }}">{{ $i }}</a>
                    @endfor
                    <a href="{{ $auditLogs->nextPageUrl() ?? '#' }}"><i class="bi bi-chevron-right"></i></a>
                  </div>
                </div>
              </div>

              <div class="tab-pane fade {{ $auditTab === 'archived' ? 'show active' : '' }}" id="auditArchivedPane" role="tabpanel">
                <div class="table-responsive">
                  <table class="table-soft" style="border-radius:0; box-shadow:none;">
                    <thead>
                      <tr>
                        <th>Staff</th>
                        <th>Action</th>
                        <th>Description</th>
                        <th>Timestamp</th>
                        <th class="text-end">Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      @forelse ($archivedAuditLogs as $log)
                        @php
                          $si = $log->staffAccount?->staffInfo;
                          $name = $si ? trim($si->FirstName . ' ' . $si->LastName) : ($log->staffAccount->Email ?? 'Unknown');
                        @endphp
                        <tr>
                          <td>{{ $name }}</td>
                          <td><span class="pill pill-info">{{ $log->ActionType }}</span></td>
                          <td>{{ $log->Description }}</td>
                          <td>{{ $log->created_at->format('M j, Y g:i A') }}</td>
                          <td class="text-end">
                            <form method="POST" action="{{ route('configuration.auditLogs.unarchive', $log->AuditLogID) }}" class="d-inline">
                              @csrf
                              <button type="submit" class="btn btn-pill btn-pill-archive"><i class="bi bi-archive"></i> Unarchive</button>
                            </form>
                          </td>
                        </tr>
                      @empty
                        <tr><td colspan="5" class="text-center text-muted-2 py-4">No archived audit activity.</td></tr>
                      @endforelse
                    </tbody>
                  </table>
                </div>
                <div class="pagination-soft">
                  <div>Showing {{ $archivedAuditLogs->count() }} of {{ $archivedAuditLogs->total() }} entries</div>
                  <div class="pages">
                    <a href="{{ $archivedAuditLogs->previousPageUrl() ?? '#' }}"><i class="bi bi-chevron-left"></i></a>
                    @for ($i = 1; $i <= $archivedAuditLogs->lastPage(); $i++)
                      <a href="{{ $archivedAuditLogs->url($i) }}" class="{{ $archivedAuditLogs->currentPage() === $i ? 'active' : '' }}">{{ $i }}</a>
                    @endfor
                    <a href="{{ $archivedAuditLogs->nextPageUrl() ?? '#' }}"><i class="bi bi-chevron-right"></i></a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>

  <!-- ===================== ADD SERVICE MODAL ===================== -->
  <div class="modal fade" id="addServiceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header border-0 pb-0">
          <div>
            <h5 class="modal-title fw-semibold">Add Service</h5>
            <div class="small text-muted">Create a new clinic service</div>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form method="POST" action="{{ route('configuration.services.store') }}">
          @csrf
          <div class="modal-body pt-2">
            <div class="mb-3">
              <label class="form-label">Service name</label>
              <div class="input-icon"><i class="bi bi-heart-pulse"></i><input type="text" name="service_name"
                  class="form-control" placeholder="e.g. Tooth Extraction" required></div>
            </div>
            <div class="mb-3">
              <label class="form-label">Description</label>
              <textarea name="description" class="form-control" rows="3" placeholder="Optional description"></textarea>
            </div>
            <div class="mb-3">
              <label class="form-label">Price</label>
              <div class="input-icon"><i class="bi bi-cash"></i><input type="number" name="price" class="form-control"
                  step="0.01" min="0" placeholder="0.00"></div>
            </div>
          </div>
          <div class="modal-footer border-0 pt-0">
            <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-brand">Create Service</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- ===================== ONE EDIT MODAL PER SERVICE (active + archived) ===================== -->
  @foreach ($services->merge($archivedServices) as $service)
    <div class="modal fade" id="editServiceModal{{ $service->ServiceID }}" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header border-0 pb-0">
            <div>
              <h5 class="modal-title fw-semibold">Edit Service</h5>
              <div class="small text-muted">Update service details</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form method="POST" action="{{ route('configuration.services.update', $service->ServiceID) }}">
            @csrf
            <div class="modal-body pt-2">
              <div class="mb-3">
                <label class="form-label">Service name</label>
                <div class="input-icon"><i class="bi bi-heart-pulse"></i><input type="text" name="service_name"
                    class="form-control" value="{{ $service->ServiceName }}" required></div>
              </div>
              <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3">{{ $service->Description }}</textarea>
              </div>
              <div class="mb-3">
                <label class="form-label">Price</label>
                <div class="input-icon"><i class="bi bi-cash"></i><input type="number" name="price" class="form-control"
                    step="0.01" min="0" value="{{ $service->Price }}"></div>
              </div>
            </div>
            <div class="modal-footer border-0 pt-0">
              <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-brand">Save Changes</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  @endforeach

  @include('partials.admin-notif-modal')

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.getElementById('logoInput')?.addEventListener('change', function () {
      if (this.files[0]) {
        document.getElementById('logoFileLabel').textContent = 'Selected: ' + this.files[0].name;
      }
    });

    document.querySelectorAll('[data-tabgroup]').forEach(function (group) {
      var field = document.getElementById(group.dataset.tabgroup + 'TabField');
      group.querySelectorAll('button[data-bs-toggle="pill"]').forEach(function (btn) {
        btn.addEventListener('shown.bs.tab', function () {
          if (field) field.value = btn.dataset.tabValue;
        });
      });
    });
  </script>
</body>

</html>
