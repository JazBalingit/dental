<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Settings • Dental Clinic</title>
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
        <a href="{{ route('configuration') }}" class="active"><i class="bi bi-sliders2"></i> Settings</a>
      </nav>
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
            <h2>Settings</h2>
            <div class="crumbs">System information, services, legal content, appointment process, and logs.</div>
          </div>
        </div>

        @include('partials.flash-toasts')

        <div class="row g-3">
          <div class="col-lg-3">
            <div class="card-soft p-2">
              <div class="nav flex-column nav-pills" id="settingsTabNav" role="tablist" aria-orientation="vertical">
                <button class="nav-link text-start {{ $settingsTab === 'about' ? 'active' : '' }}"
                  data-settings-tab="about" type="button"><i class="bi bi-info-circle me-2"></i>System
                  Information</button>
                <button class="nav-link text-start {{ $settingsTab === 'services' ? 'active' : '' }}"
                  data-settings-tab="services" type="button"><i class="bi bi-heart-pulse me-2"></i>Services</button>
                <button class="nav-link text-start {{ $settingsTab === 'privacy' ? 'active' : '' }}"
                  data-settings-tab="privacy" type="button"><i class="bi bi-shield-lock me-2"></i>Privacy and Legal
                  Terms</button>
                <button class="nav-link text-start {{ $settingsTab === 'appointment' ? 'active' : '' }}"
                  data-settings-tab="appointment" type="button"><i class="bi bi-list-check me-2"></i>Appointment
                  Process</button>
                <button class="nav-link text-start {{ $settingsTab === 'activity' ? 'active' : '' }}"
                  data-settings-tab="activity" type="button"><i class="bi bi-activity me-2"></i>Activity Logs</button>
              </div>
            </div>
          </div>

          <div class="col-lg-9">

            {{-- ===================== SYSTEM INFORMATION ===================== --}}
            <div class="settings-pane" data-settings-pane="about" @if ($settingsTab !== 'about') hidden @endif>

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
                          <input type="file" name="logo" id="logoInput" accept=".jpg,.jpeg,.png,.svg" class="d-none"
                            required>
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

              <!-- ABOUT SECTION INFO -->
              <div class="card-soft">
                <div class="card-header d-flex align-items-center justify-content-between">
                  <span><i class="bi bi-geo-alt me-2" style="color: var(--brand-700);"></i> About Section</span>
                  <span class="small text-muted-2">Shown on the landing page's "About the Clinic" section</span>
                </div>
                <div class="card-body">
                  <form method="POST" action="{{ route('configuration.about.update') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-4 align-items-center mb-3">
                      <div class="col-md-3 text-center">
                        <div class="logo-preview mx-auto" style="width:120px;height:90px;">
                          <img src="{{ $aboutInfo['image'] }}" alt="About section image"
                            style="max-width:100%; max-height:100%; object-fit:cover;">
                        </div>
                        <div class="small text-muted-2 mt-2">Current image</div>
                      </div>
                      <div class="col-md-9">
                        <label class="upload-zone d-block" style="cursor:pointer;">
                          <i class="bi bi-cloud-arrow-up-fill mb-2 d-block"></i>
                          <div class="fw-semibold" id="aboutImageFileLabel">Drag & drop a new photo here</div>
                          <div class="small text-muted-2 mb-3">or click to browse from your computer</div>
                          <span class="btn btn-brand px-4"><i class="bi bi-folder2-open me-1"></i> Browse Files</span>
                          <input type="file" name="about_image" id="aboutImageInput" accept=".jpg,.jpeg,.png"
                            class="d-none">
                        </label>
                      </div>
                    </div>

                    <div class="row g-3">
                      <div class="col-12">
                        <label class="form-label">Address</label>
                        <div class="input-icon"><i class="bi bi-geo-alt"></i><input type="text" name="address"
                            class="form-control" value="{{ old('address', $aboutInfo['address']) }}" required></div>
                      </div>
                      <div class="col-md-6">
                        <label class="form-label">Operating Days</label>
                        <div class="input-icon"><i class="bi bi-calendar-week"></i><input type="text"
                            name="operating_days" class="form-control"
                            value="{{ old('operating_days', $aboutInfo['operatingDays']) }}" required></div>
                      </div>
                      <div class="col-md-6">
                        <label class="form-label">Operating Hours</label>
                        <div class="input-icon"><i class="bi bi-clock"></i><input type="text" name="operating_hours"
                            class="form-control" value="{{ old('operating_hours', $aboutInfo['operatingHours']) }}"
                            required></div>
                      </div>
                    </div>

                    <div class="d-flex justify-content-end mt-3">
                      <button type="submit" class="btn btn-brand px-3">Save Changes</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>

            {{-- ===================== SERVICES ===================== --}}
            <div class="settings-pane" data-settings-pane="services" @if ($settingsTab !== 'services') hidden @endif>
              <div class="card-soft">
                <div class="card-header d-flex align-items-center justify-content-between">
                  <span><i class="bi bi-heart-pulse me-2" style="color: var(--brand-700);"></i> Services</span>
                  <div class="d-flex gap-2">
                    <button class="btn btn-outline-secondary px-3" data-bs-toggle="modal" data-bs-target="#manageCategoriesModal">
                      <i class="bi bi-tags"></i> Manage Categories
                    </button>
                    <button class="btn btn-brand px-3" data-bs-toggle="modal" data-bs-target="#addServiceModal">
                      <i class="bi bi-plus-lg"></i> Add Service
                    </button>
                  </div>
                </div>
                <div class="card-body p-3 p-md-4">
                  <form method="GET" action="{{ route('configuration') }}" class="data-toolbar">
                    <div class="left">
                      <ul class="nav nav-pills" data-tabgroup="services" role="tablist">
                        <li class="nav-item" role="presentation">
                          <button class="nav-link {{ $servicesTab !== 'archived' ? 'active' : '' }}" type="button"
                            data-bs-toggle="pill" data-bs-target="#servicesActivePane" data-tab-value="active"
                            role="tab">Active</button>
                        </li>
                        <li class="nav-item" role="presentation">
                          <button class="nav-link {{ $servicesTab === 'archived' ? 'active' : '' }}" type="button"
                            data-bs-toggle="pill" data-bs-target="#servicesArchivedPane" data-tab-value="archived"
                            role="tab">Archived</button>
                        </li>
                      </ul>
                    </div>
                    <div class="right">
                      <input type="hidden" name="settingsTab" value="services">
                      <input type="hidden" name="servicesTab" id="servicesTabField" value="{{ $servicesTab }}">
                      <div class="input-icon search">
                        <i class="bi bi-search"></i>
                        <input class="form-control" name="serviceSearch" value="{{ $serviceSearch }}"
                          placeholder="Search services..." style="height:38px; padding-left:2.3rem; min-width:220px;" />
                      </div>
                    </div>
                  </form>

                  <div class="tab-content mt-3">
                    <div class="tab-pane fade {{ $servicesTab !== 'archived' ? 'show active' : '' }}"
                      id="servicesActivePane" role="tabpanel">
                      <div class="table-responsive">
                        <table class="table-soft" style="border-radius:0; box-shadow:none;">
                          <thead>
                            <tr>
                              <th>Service</th>
                              <th>Category</th>
                              <th>Duration</th>
                              <th>Description</th>
                              <th class="text-end">Actions</th>
                            </tr>
                          </thead>
                          <tbody>
                            @forelse ($services as $service)
                              <tr>
                                <td class="fw-semibold">{{ $service->ServiceName }}</td>
                                <td>{{ $service->category->Name ?? '—' }}</td>
                                <td>{{ $service->duration_label }}</td>
                                <td>{{ $service->Description ?: '—' }}</td>
                                <td class="text-end">
                                  <button type="button" class="btn btn-pill btn-pill-edit me-1" data-bs-toggle="modal"
                                    data-bs-target="#editServiceModal{{ $service->ServiceID }}"><i
                                      class="bi bi-pencil-square"></i> Edit</button>
                                  <form method="POST"
                                    action="{{ route('configuration.services.archive', $service->ServiceID) }}"
                                    class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-pill btn-pill-archive"><i
                                        class="bi bi-archive"></i> Archive</button>
                                  </form>
                                </td>
                              </tr>
                            @empty
                              <tr>
                                <td colspan="3" class="text-center text-muted-2 py-4">No services yet.</td>
                              </tr>
                            @endforelse
                          </tbody>
                        </table>
                      </div>
                      <div class="pagination-soft">
                        <div>Showing {{ $services->count() }} of {{ $services->total() }} entries</div>
                        <div class="pages">
                          <a href="{{ $services->previousPageUrl() ?? '#' }}"><i class="bi bi-chevron-left"></i></a>
                          @for ($i = 1; $i <= $services->lastPage(); $i++)
                            <a href="{{ $services->url($i) }}"
                              class="{{ $services->currentPage() === $i ? 'active' : '' }}">{{ $i }}</a>
                          @endfor
                          <a href="{{ $services->nextPageUrl() ?? '#' }}"><i class="bi bi-chevron-right"></i></a>
                        </div>
                      </div>
                    </div>

                    <div class="tab-pane fade {{ $servicesTab === 'archived' ? 'show active' : '' }}"
                      id="servicesArchivedPane" role="tabpanel">
                      <div class="table-responsive">
                        <table class="table-soft" style="border-radius:0; box-shadow:none;">
                          <thead>
                            <tr>
                              <th>Service</th>
                              <th>Category</th>
                              <th>Duration</th>
                              <th>Description</th>
                              <th class="text-end">Actions</th>
                            </tr>
                          </thead>
                          <tbody>
                            @forelse ($archivedServices as $service)
                              <tr>
                                <td class="fw-semibold">{{ $service->ServiceName }}</td>
                                <td>{{ $service->category->Name ?? '—' }}</td>
                                <td>{{ $service->duration_label }}</td>
                                <td>{{ $service->Description ?: '—' }}</td>
                                <td class="text-end">
                                  <button type="button" class="btn btn-pill btn-pill-edit me-1" data-bs-toggle="modal"
                                    data-bs-target="#editServiceModal{{ $service->ServiceID }}"><i
                                      class="bi bi-pencil-square"></i> Edit</button>
                                  <form method="POST"
                                    action="{{ route('configuration.services.unarchive', $service->ServiceID) }}"
                                    class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-pill btn-pill-archive"><i
                                        class="bi bi-archive"></i> Unarchive</button>
                                  </form>
                                </td>
                              </tr>
                            @empty
                              <tr>
                                <td colspan="5" class="text-center text-muted-2 py-4">No archived services.</td>
                              </tr>
                            @endforelse
                          </tbody>
                        </table>
                      </div>
                      <div class="pagination-soft">
                        <div>Showing {{ $archivedServices->count() }} of {{ $archivedServices->total() }} entries
                        </div>
                        <div class="pages">
                          <a href="{{ $archivedServices->previousPageUrl() ?? '#' }}"><i
                              class="bi bi-chevron-left"></i></a>
                          @for ($i = 1; $i <= $archivedServices->lastPage(); $i++)
                            <a href="{{ $archivedServices->url($i) }}"
                              class="{{ $archivedServices->currentPage() === $i ? 'active' : '' }}">{{ $i }}</a>
                          @endfor
                          <a href="{{ $archivedServices->nextPageUrl() ?? '#' }}"><i
                              class="bi bi-chevron-right"></i></a>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            {{-- ===================== PRIVACY AND LEGAL TERMS ===================== --}}
            <div class="settings-pane" data-settings-pane="privacy" @if ($settingsTab !== 'privacy') hidden @endif>
              <div class="card-soft">
                <div class="card-header">
                  <i class="bi bi-shield-lock me-2" style="color: var(--brand-700);"></i> Privacy and Legal Terms
                </div>
                <div class="card-body">
                  <form method="POST" action="{{ route('configuration.privacyLegal.update') }}">
                    @csrf
                    <div class="mb-3">
                      <label class="form-label">Privacy Policy</label>
                      <textarea name="privacy_policy" class="form-control" rows="10"
                        placeholder="Describe how patient data is collected, used, and protected.">{{ old('privacy_policy', $privacyLegal['privacyPolicy']) }}</textarea>
                      <div class="small text-muted-2 mt-1">Shown to patients on the sign-up page.</div>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Legal Terms</label>
                      <textarea name="legal_terms" class="form-control" rows="10"
                        placeholder="Terms of use, liability, and other legal information.">{{ old('legal_terms', $privacyLegal['legalTerms']) }}</textarea>
                    </div>
                    <div class="d-flex justify-content-end">
                      <button type="submit" class="btn btn-brand px-3">Save Changes</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>

            {{-- ===================== APPOINTMENT PROCESS ===================== --}}
            <div class="settings-pane" data-settings-pane="appointment"
              @if ($settingsTab !== 'appointment') hidden @endif>
              <div class="card-soft">
                <div class="card-header d-flex align-items-center justify-content-between">
                  <span><i class="bi bi-list-check me-2" style="color: var(--brand-700);"></i> Appointment
                    Process</span>
                  <span class="small text-muted-2">Shown on the landing page's "How to Book Your Appointment"
                    section</span>
                </div>
                <div class="card-body">
                  <form method="POST" action="{{ route('configuration.appointmentSteps.update') }}">
                    @csrf
                    <div id="appointmentStepsContainer">
                      @foreach ($appointmentSteps as $n => $step)
                        <div class="row g-3 align-items-start mb-3 pb-3 appointment-step-row"
                          style="border-bottom:1px solid var(--ink-100);">
                          <div class="col-md-1 text-center">
                            <span class="step-badge-admin">{{ $n }}</span>
                          </div>
                          <div class="col-md-4">
                            <label class="form-label step-title-label">Step {{ $n }} Title</label>
                            <input type="text" name="steps[{{ $n }}][title]" class="form-control"
                              value="{{ old("steps.$n.title", $step['title']) }}" required maxlength="150">
                          </div>
                          <div class="col-md-6">
                            <label class="form-label step-desc-label">Step {{ $n }} Description</label>
                            <textarea name="steps[{{ $n }}][desc]" class="form-control" rows="2" required
                              maxlength="500">{{ old("steps.$n.desc", $step['desc']) }}</textarea>
                          </div>
                          <div class="col-md-1 text-end">
                            <label class="form-label d-block">&nbsp;</label>
                            <button type="button" class="btn btn-outline-danger btn-sm remove-appointment-step"
                              title="Remove step">
                              <i class="bi bi-trash"></i>
                            </button>
                          </div>
                        </div>
                      @endforeach
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                      <button type="button" id="addAppointmentStep" class="btn btn-outline-secondary px-3">
                        <i class="bi bi-plus-lg me-1"></i> Add Step
                      </button>
                      <button type="submit" class="btn btn-brand px-3">Save Changes</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>

            {{-- ===================== ACTIVITY LOGS ===================== --}}
            <div class="settings-pane" data-settings-pane="activity" @if ($settingsTab !== 'activity') hidden @endif>

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
                            data-bs-toggle="pill" data-bs-target="#activityActivePane" data-tab-value="active"
                            role="tab">Active</button>
                        </li>
                        <li class="nav-item" role="presentation">
                          <button class="nav-link {{ $activityTab === 'archived' ? 'active' : '' }}" type="button"
                            data-bs-toggle="pill" data-bs-target="#activityArchivedPane" data-tab-value="archived"
                            role="tab">Archived</button>
                        </li>
                      </ul>
                    </div>
                    <div class="right">
                      <input type="hidden" name="settingsTab" value="activity">
                      <input type="hidden" name="activityTab" id="activityTabField" value="{{ $activityTab }}">
                      <div class="input-icon search">
                        <i class="bi bi-search"></i>
                        <input class="form-control" name="activitySearch" value="{{ $activitySearch }}"
                          placeholder="Search by name or email..."
                          style="height:38px; padding-left:2.3rem; min-width:240px;" />
                      </div>
                    </div>
                  </form>

                  <div class="tab-content mt-3">
                    <div class="tab-pane fade {{ $activityTab !== 'archived' ? 'show active' : '' }}"
                      id="activityActivePane" role="tabpanel">
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
                                <td><span><img class="avatar-initials" src="{{ $info->photo_url ?? asset('images/default.png') }}"
                                      alt=""></span> {{ $name }}</td>
                                <td><span class="pill {{ $isStaff ? 'pill-info' : 'pill-success' }}">{{ $isStaff ? 'Staff' : 'User' }}</span>
                                </td>
                                <td>{{ optional($log->LoggedInTime)->format('M j, Y g:i A') ?? '—' }}</td>
                                <td>{{ optional($log->LoggedOutTime)->format('M j, Y g:i A') ?? '—' }}</td>
                                <td class="text-end">
                                  <form method="POST"
                                    action="{{ route('configuration.activityLogs.archive', $log->ActivityLogsID) }}"
                                    class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-pill btn-pill-archive"><i
                                        class="bi bi-archive"></i> Archive</button>
                                  </form>
                                </td>
                              </tr>
                            @empty
                              <tr>
                                <td colspan="5" class="text-center text-muted-2 py-4">No activity yet.</td>
                              </tr>
                            @endforelse
                          </tbody>
                        </table>
                      </div>
                      <div class="pagination-soft">
                        <div>Showing {{ $activityLogs->count() }} of {{ $activityLogs->total() }} entries</div>
                        <div class="pages">
                          <a href="{{ $activityLogs->previousPageUrl() ?? '#' }}"><i
                              class="bi bi-chevron-left"></i></a>
                          @for ($i = 1; $i <= $activityLogs->lastPage(); $i++)
                            <a href="{{ $activityLogs->url($i) }}"
                              class="{{ $activityLogs->currentPage() === $i ? 'active' : '' }}">{{ $i }}</a>
                          @endfor
                          <a href="{{ $activityLogs->nextPageUrl() ?? '#' }}"><i class="bi bi-chevron-right"></i></a>
                        </div>
                      </div>
                    </div>

                    <div class="tab-pane fade {{ $activityTab === 'archived' ? 'show active' : '' }}"
                      id="activityArchivedPane" role="tabpanel">
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
                                <td><span><img class="avatar-initials" src="{{ $info->photo_url ?? asset('images/default.png') }}"
                                      alt=""></span> {{ $name }}</td>
                                <td><span class="pill {{ $isStaff ? 'pill-info' : 'pill-success' }}">{{ $isStaff ? 'Staff' : 'User' }}</span>
                                </td>
                                <td>{{ optional($log->LoggedInTime)->format('M j, Y g:i A') ?? '—' }}</td>
                                <td>{{ optional($log->LoggedOutTime)->format('M j, Y g:i A') ?? '—' }}</td>
                                <td class="text-end">
                                  <form method="POST"
                                    action="{{ route('configuration.activityLogs.unarchive', $log->ActivityLogsID) }}"
                                    class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-pill btn-pill-archive"><i
                                        class="bi bi-archive"></i> Unarchive</button>
                                  </form>
                                </td>
                              </tr>
                            @empty
                              <tr>
                                <td colspan="5" class="text-center text-muted-2 py-4">No archived activity.</td>
                              </tr>
                            @endforelse
                          </tbody>
                        </table>
                      </div>
                      <div class="pagination-soft">
                        <div>Showing {{ $archivedActivityLogs->count() }} of {{ $archivedActivityLogs->total() }}
                          entries</div>
                        <div class="pages">
                          <a href="{{ $archivedActivityLogs->previousPageUrl() ?? '#' }}"><i
                              class="bi bi-chevron-left"></i></a>
                          @for ($i = 1; $i <= $archivedActivityLogs->lastPage(); $i++)
                            <a href="{{ $archivedActivityLogs->url($i) }}"
                              class="{{ $archivedActivityLogs->currentPage() === $i ? 'active' : '' }}">{{ $i }}</a>
                          @endfor
                          <a href="{{ $archivedActivityLogs->nextPageUrl() ?? '#' }}"><i
                              class="bi bi-chevron-right"></i></a>
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
                            data-bs-toggle="pill" data-bs-target="#auditActivePane" data-tab-value="active"
                            role="tab">Active</button>
                        </li>
                        <li class="nav-item" role="presentation">
                          <button class="nav-link {{ $auditTab === 'archived' ? 'active' : '' }}" type="button"
                            data-bs-toggle="pill" data-bs-target="#auditArchivedPane" data-tab-value="archived"
                            role="tab">Archived</button>
                        </li>
                      </ul>
                    </div>
                    <div class="right">
                      <input type="hidden" name="settingsTab" value="activity">
                      <input type="hidden" name="auditTab" id="auditTabField" value="{{ $auditTab }}">
                      <select class="form-select" name="auditType" style="height:38px; min-width:150px;"
                        onchange="this.form.submit()">
                        <option value="">All Actions</option>
                        @foreach ($actionTypes as $type)
                          <option value="{{ $type }}" {{ $auditType === $type ? 'selected' : '' }}>{{ $type }}
                          </option>
                        @endforeach
                      </select>
                      <div class="input-icon search">
                        <i class="bi bi-search"></i>
                        <input class="form-control" name="auditSearch" value="{{ $auditSearch }}"
                          placeholder="Search staff or description..."
                          style="height:38px; padding-left:2.3rem; min-width:240px;" />
                      </div>
                    </div>
                  </form>

                  <div class="tab-content mt-3">
                    <div class="tab-pane fade {{ $auditTab !== 'archived' ? 'show active' : '' }}" id="auditActivePane"
                      role="tabpanel">
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
                                $name = $si ? trim($si->FirstName . ' ' . $si->LastName) : ($log->staffAccount?->Email ?? 'Unknown');
                              @endphp
                              <tr>
                                <td>{{ $name }}</td>
                                <td><span class="pill pill-info">{{ $log->ActionType }}</span></td>
                                <td>{{ $log->Description }}</td>
                                <td>{{ $log->created_at->format('M j, Y g:i A') }}</td>
                                <td class="text-end">
                                  <form method="POST"
                                    action="{{ route('configuration.auditLogs.archive', $log->AuditLogID) }}"
                                    class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-pill btn-pill-archive"><i
                                        class="bi bi-archive"></i> Archive</button>
                                  </form>
                                </td>
                              </tr>
                            @empty
                              <tr>
                                <td colspan="5" class="text-center text-muted-2 py-4">No audit activity yet.</td>
                              </tr>
                            @endforelse
                          </tbody>
                        </table>
                      </div>
                      <div class="pagination-soft">
                        <div>Showing {{ $auditLogs->count() }} of {{ $auditLogs->total() }} entries</div>
                        <div class="pages">
                          <a href="{{ $auditLogs->previousPageUrl() ?? '#' }}"><i class="bi bi-chevron-left"></i></a>
                          @for ($i = 1; $i <= $auditLogs->lastPage(); $i++)
                            <a href="{{ $auditLogs->url($i) }}"
                              class="{{ $auditLogs->currentPage() === $i ? 'active' : '' }}">{{ $i }}</a>
                          @endfor
                          <a href="{{ $auditLogs->nextPageUrl() ?? '#' }}"><i class="bi bi-chevron-right"></i></a>
                        </div>
                      </div>
                    </div>

                    <div class="tab-pane fade {{ $auditTab === 'archived' ? 'show active' : '' }}"
                      id="auditArchivedPane" role="tabpanel">
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
                                $name = $si ? trim($si->FirstName . ' ' . $si->LastName) : ($log->staffAccount?->Email ?? 'Unknown');
                              @endphp
                              <tr>
                                <td>{{ $name }}</td>
                                <td><span class="pill pill-info">{{ $log->ActionType }}</span></td>
                                <td>{{ $log->Description }}</td>
                                <td>{{ $log->created_at->format('M j, Y g:i A') }}</td>
                                <td class="text-end">
                                  <form method="POST"
                                    action="{{ route('configuration.auditLogs.unarchive', $log->AuditLogID) }}"
                                    class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-pill btn-pill-archive"><i
                                        class="bi bi-archive"></i> Unarchive</button>
                                  </form>
                                </td>
                              </tr>
                            @empty
                              <tr>
                                <td colspan="5" class="text-center text-muted-2 py-4">No archived audit activity.</td>
                              </tr>
                            @endforelse
                          </tbody>
                        </table>
                      </div>
                      <div class="pagination-soft">
                        <div>Showing {{ $archivedAuditLogs->count() }} of {{ $archivedAuditLogs->total() }} entries
                        </div>
                        <div class="pages">
                          <a href="{{ $archivedAuditLogs->previousPageUrl() ?? '#' }}"><i
                              class="bi bi-chevron-left"></i></a>
                          @for ($i = 1; $i <= $archivedAuditLogs->lastPage(); $i++)
                            <a href="{{ $archivedAuditLogs->url($i) }}"
                              class="{{ $archivedAuditLogs->currentPage() === $i ? 'active' : '' }}">{{ $i }}</a>
                          @endfor
                          <a href="{{ $archivedAuditLogs->nextPageUrl() ?? '#' }}"><i
                              class="bi bi-chevron-right"></i></a>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>
    </main>
  </div>

  @php
    // Half-hour increments, matching the booking calendar's 30-minute slot
    // grid — a service's duration always lines up with a whole number of
    // slots. Up to 8 hours (the clinic's whole day, lunch break aside).
    $serviceDurationOptions = [];
    for ($minutes = 30; $minutes <= 480; $minutes += 30) {
        $serviceDurationOptions[$minutes] = \App\Models\DentistSchedule::formatSlotDuration($minutes / \App\Models\DentistSchedule::SLOT_MINUTES);
    }
  @endphp

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
              <label class="form-label">Category</label>
              <div class="input-icon"><i class="bi bi-tags"></i>
                <select name="category_id" class="form-select">
                  <option value="">— Uncategorized —</option>
                  @foreach ($categories as $category)
                    <option value="{{ $category->CategoryID }}">{{ $category->Name }}</option>
                  @endforeach
                </select>
              </div>
              <div class="small text-muted-2 mt-1">Groups this service on the landing page's "Our Services" section. <a href="#" data-bs-toggle="modal" data-bs-target="#manageCategoriesModal" data-bs-dismiss="modal">Manage categories</a>.</div>
            </div>
            <div class="mb-3">
              <label class="form-label">Duration</label>
              <div class="input-icon"><i class="bi bi-hourglass-split"></i>
                <select name="duration_minutes" class="form-select" required>
                  @foreach ($serviceDurationOptions as $minutes => $optLabel)
                    <option value="{{ $minutes }}" {{ $minutes === 60 ? 'selected' : '' }}>{{ $optLabel }}</option>
                  @endforeach
                </select>
              </div>
              <div class="small text-muted-2 mt-1">How long this service takes — used to block the right amount of time when a patient books it.</div>
            </div>
            <div class="mb-3">
              <label class="form-label">Description</label>
              <textarea name="description" class="form-control" rows="3" placeholder="Optional description"></textarea>
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

  <!-- ===================== MANAGE CATEGORIES ===================== -->
  <div class="modal fade" id="manageCategoriesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header border-0 pb-0">
          <div>
            <h5 class="modal-title fw-semibold">Manage Categories</h5>
            <div class="small text-muted">Group services for the landing page's "Our Services" section</div>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body pt-2">
          <div class="table-responsive mb-4">
            <table class="table-soft" style="border-radius:0; box-shadow:none;">
              <thead>
                <tr>
                  <th>Icon</th>
                  <th>Category</th>
                  <th>Services</th>
                  <th class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($categories as $category)
                  <tr>
                    <td><i class="{{ $category->Icon ?: 'fa-solid fa-tooth' }}" style="color: var(--brand-700);"></i></td>
                    <td class="fw-semibold">{{ $category->Name }}</td>
                    <td>{{ $category->services_count }}</td>
                    <td class="text-end">
                      <button type="button" class="btn btn-pill btn-pill-edit me-1" data-bs-toggle="modal"
                        data-bs-target="#editCategoryModal{{ $category->CategoryID }}" data-bs-dismiss="modal"><i
                          class="bi bi-pencil-square"></i> Edit</button>
                      <form method="POST" action="{{ route('configuration.categories.destroy', $category->CategoryID) }}"
                        class="d-inline" onsubmit="return confirm('Delete this category? Its services will become Uncategorized, not deleted.');">
                        @csrf
                        <button type="submit" class="btn btn-pill btn-pill-archive"><i class="bi bi-trash"></i> Delete</button>
                      </form>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="4" class="text-center text-muted-2 py-3">No categories yet — add one below.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>

          <div class="section-label mb-2">Add a New Category</div>
          <form method="POST" action="{{ route('configuration.categories.store') }}" class="row g-2 align-items-end">
            @csrf
            <div class="col-md-6">
              <label class="form-label">Name</label>
              <input type="text" name="name" class="form-control" placeholder="e.g. General Dentistry" required maxlength="100">
            </div>
            <div class="col-md-4">
              <label class="form-label">Icon</label>
              <select name="icon" class="form-select">
                @foreach (\App\Models\ServiceCategory::iconOptions() as $value => $label)
                  <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-2">
              <button type="submit" class="btn btn-brand w-100">Add</button>
            </div>
          </form>
        </div>
        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  @foreach ($categories as $category)
    <div class="modal fade" id="editCategoryModal{{ $category->CategoryID }}" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header border-0 pb-0">
            <h5 class="modal-title fw-semibold">Edit Category</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form method="POST" action="{{ route('configuration.categories.update', $category->CategoryID) }}">
            @csrf
            <div class="modal-body pt-2">
              <div class="mb-3">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" value="{{ $category->Name }}" required maxlength="100">
              </div>
              <div class="mb-3">
                <label class="form-label">Icon</label>
                <select name="icon" class="form-select">
                  @foreach (\App\Models\ServiceCategory::iconOptions() as $value => $label)
                    <option value="{{ $value }}" {{ $category->Icon === $value ? 'selected' : '' }}>{{ $label }}</option>
                  @endforeach
                </select>
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
                <label class="form-label">Category</label>
                <div class="input-icon"><i class="bi bi-tags"></i>
                  <select name="category_id" class="form-select">
                    <option value="">— Uncategorized —</option>
                    @foreach ($categories as $category)
                      <option value="{{ $category->CategoryID }}" {{ $service->CategoryID === $category->CategoryID ? 'selected' : '' }}>{{ $category->Name }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="mb-3">
                <label class="form-label">Duration</label>
                <div class="input-icon"><i class="bi bi-hourglass-split"></i>
                  <select name="duration_minutes" class="form-select" required>
                    @foreach ($serviceDurationOptions as $minutes => $optLabel)
                      <option value="{{ $minutes }}" {{ $service->DurationMinutes === $minutes ? 'selected' : '' }}>{{ $optLabel }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="small text-muted-2 mt-1">How long this service takes — used to block the right amount of time when a patient books it.</div>
              </div>
              <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3">{{ $service->Description }}</textarea>
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

    document.getElementById('aboutImageInput')?.addEventListener('change', function () {
      if (this.files[0]) {
        document.getElementById('aboutImageFileLabel').textContent = 'Selected: ' + this.files[0].name;
      }
    });

    (function () {
      var container = document.getElementById('appointmentStepsContainer');
      var addBtn = document.getElementById('addAppointmentStep');
      if (!container || !addBtn) return;

      var nextIndex = container.querySelectorAll('.appointment-step-row').length;

      function renumber() {
        var rows = container.querySelectorAll('.appointment-step-row');
        rows.forEach(function (row, i) {
          row.querySelector('.step-badge-admin').textContent = i + 1;
          row.querySelector('.step-title-label').textContent = 'Step ' + (i + 1) + ' Title';
          row.querySelector('.step-desc-label').textContent = 'Step ' + (i + 1) + ' Description';
        });
        var removeBtns = container.querySelectorAll('.remove-appointment-step');
        removeBtns.forEach(function (btn) {
          btn.disabled = rows.length <= 1;
        });
      }

      function bindRemove(btn) {
        btn.addEventListener('click', function () {
          var rows = container.querySelectorAll('.appointment-step-row');
          if (rows.length <= 1) return;
          btn.closest('.appointment-step-row').remove();
          renumber();
        });
      }

      container.querySelectorAll('.remove-appointment-step').forEach(bindRemove);

      addBtn.addEventListener('click', function () {
        var rows = container.querySelectorAll('.appointment-step-row');
        var clone = rows[rows.length - 1].cloneNode(true);
        nextIndex++;

        clone.querySelectorAll('input, textarea').forEach(function (field) {
          field.name = field.name.replace(/steps\[\d+\]/, 'steps[' + nextIndex + ']');
          field.value = '';
        });

        container.appendChild(clone);
        bindRemove(clone.querySelector('.remove-appointment-step'));
        renumber();
        clone.querySelector('input')?.focus();
      });

      renumber();
    })();

    document.querySelectorAll('[data-tabgroup]').forEach(function (group) {
      var field = document.getElementById(group.dataset.tabgroup + 'TabField');
      group.querySelectorAll('button[data-bs-toggle="pill"]').forEach(function (btn) {
        btn.addEventListener('shown.bs.tab', function () {
          if (field) field.value = btn.dataset.tabValue;
        });
      });
    });

    document.querySelectorAll('[data-settings-tab]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var tab = btn.dataset.settingsTab;

        document.querySelectorAll('[data-settings-tab]').forEach(function (b) {
          b.classList.toggle('active', b === btn);
        });
        document.querySelectorAll('[data-settings-pane]').forEach(function (pane) {
          pane.hidden = pane.dataset.settingsPane !== tab;
        });

        var url = new URL(window.location.href);
        url.searchParams.set('settingsTab', tab);
        window.history.replaceState({}, '', url);
      });
    });
  </script>
</body>

</html>
