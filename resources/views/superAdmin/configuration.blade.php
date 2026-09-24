<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <link rel="icon" type="image/png" href="/images/puspus_logo.png">
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Configuration • Dental Clinic</title>
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
      @include('partials.admin-sidebar-nav', ['active' => 'configuration'])
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
            <h2>Configuration</h2>
            <div class="crumbs">System information, services, legal content, appointment process, and the activity logs.</div>
          </div>
        </div>

        @include('partials.flash-toasts')

        <div class="row g-3">
          <div class="col-lg-3 col-xl-2">
            <div class="card-soft p-2 settings-tab-card">
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

          <div class="col-lg-9 col-xl-10">

            {{-- ===================== SYSTEM INFORMATION ===================== --}}
            <div class="settings-pane" data-settings-pane="about" @if ($settingsTab !== 'about') hidden @endif>

              <div class="card-soft">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                  <span><i class="bi bi-buildings me-2" style="color: var(--brand-700);"></i> Clinic Branding &amp; Information</span>
                  <span class="small text-muted-2">Logo, landing-page photos &amp; the public clinic details</span>
                </div>
                <div class="card-body">
                  <form method="POST" action="{{ route('configuration.about.update') }}" enctype="multipart/form-data"
                    data-confirm-title="Save clinic information?"
                    data-confirm-message="Save these changes to the clinic information? This updates what visitors see on the landing page.">
                    @csrf

                    <div class="section-label"><i class="bi bi-images"></i> Images</div>
                    <div class="cfg-img-grid">
                      @include('partials.config-image-field', [
                        'name' => 'logo',
                        'label' => 'Clinic Logo',
                        'current' => asset('images/puspus_logo.png') . '?v=' . $logoVersion,
                        'desc' => 'Appears in the sidebar and page headers. JPG, PNG or SVG.',
                        'accept' => '.jpg,.jpeg,.png,.svg',
                        'fit' => 'contain',
                      ])
                      @include('partials.config-image-field', [
                        'name' => 'hero_image',
                        'label' => 'Landing Page Image',
                        'current' => $aboutInfo['heroImage'],
                        'desc' => 'The large background photo at the top of the landing page.',
                      ])
                      @include('partials.config-image-field', [
                        'name' => 'about_image',
                        'label' => 'About Section Image',
                        'current' => $aboutInfo['image'],
                        'desc' => 'The photo shown next to the map in the “About the Clinic” section.',
                      ])
                    </div>

                    <div class="section-label mt-4"><i class="bi bi-megaphone"></i> Landing Page Hero</div>
                    <p class="small text-muted-2 mb-3">The headline text shown over the banner image at the very top
                      of the landing page.</p>
                    <div class="row g-3">
                      <div class="col-md-6">
                        <label class="form-label">Hero Title</label>
                        <input type="text" name="hero_title" class="form-control @error('hero_title') has-error @enderror"
                          value="{{ old('hero_title', $aboutInfo['heroTitle']) }}" maxlength="100">
                        @error('hero_title') <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div> @enderror
                      </div>
                      <div class="col-md-6">
                        <label class="form-label">Hero Subtitle</label>
                        <input type="text" name="hero_subtitle" class="form-control @error('hero_subtitle') has-error @enderror"
                          value="{{ old('hero_subtitle', $aboutInfo['heroSubtitle']) }}" maxlength="200">
                        @error('hero_subtitle') <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div> @enderror
                      </div>
                      <div class="col-12">
                        <label class="form-label">Hero Description</label>
                        <textarea name="hero_description" class="form-control @error('hero_description') has-error @enderror" rows="2"
                          maxlength="400">{{ old('hero_description', $aboutInfo['heroDescription']) }}</textarea>
                        @error('hero_description') <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div> @enderror
                      </div>
                    </div>

                    <div class="section-label mt-4"><i class="bi bi-card-text"></i> About the Clinic</div>
                    <div class="row g-3">
                      <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="about_description" class="form-control @error('about_description') has-error @enderror" rows="3"
                          placeholder="A short welcome paragraph shown beside the map on the landing page.">{{ old('about_description', $aboutInfo['description']) }}</textarea>
                        @error('about_description') <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div> @enderror
                      </div>
                      <div class="col-12">
                        <label class="form-label">Address</label>
                        <div class="input-icon @error('address') has-error @enderror"><i class="bi bi-geo-alt"></i><input type="text" name="address"
                            class="form-control" value="{{ old('address', $aboutInfo['address']) }}" required></div>
                        @error('address') <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div> @enderror
                      </div>
                      <div class="col-md-6">
                        <label class="form-label">Operating Days</label>
                        <div class="input-icon @error('operating_days') has-error @enderror"><i class="bi bi-calendar-week"></i><input type="text"
                            name="operating_days" class="form-control"
                            value="{{ old('operating_days', $aboutInfo['operatingDays']) }}" required></div>
                        @error('operating_days') <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div> @enderror
                      </div>
                    </div>

                    @php $clinicHours = \App\Models\DentistSchedule::clinicHours(); @endphp
                    <div class="section-label mt-4"><i class="bi bi-clock"></i> Appointment Booking Hours</div>
                    <p class="small text-muted-2 mb-3">Sets the open→close window every appointment slot grid is built
                      from — booking calendar, walk-ins, and the dentist schedule. Slots are 30 minutes. The public
                      “Operating Hours” line on the landing page is generated from this.</p>
                    <div class="row g-3">
                      <div class="col-md-3 col-6">
                        <label class="form-label">Opening Time</label>
                        <input type="time" name="booking_open_time" class="form-control @error('booking_open_time') has-error @enderror" step="1800" required
                          value="{{ old('booking_open_time', $clinicHours['open']) }}">
                        @error('booking_open_time') <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div> @enderror
                      </div>
                      <div class="col-md-3 col-6">
                        <label class="form-label">Closing Time</label>
                        <input type="time" name="booking_close_time" class="form-control @error('booking_close_time') has-error @enderror" step="1800" required
                          value="{{ old('booking_close_time', $clinicHours['close']) }}">
                        @error('booking_close_time') <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div> @enderror
                      </div>
                      <div class="col-md-6 d-flex align-items-end">
                        <div class="form-check">
                          <input type="hidden" name="lunch_enabled" value="0">
                          <input type="checkbox" class="form-check-input" id="lunchEnabledToggle" name="lunch_enabled"
                            value="1" {{ old('lunch_enabled', $clinicHours['lunchEnabled'] ? '1' : '0') === '1' ? 'checked' : '' }}>
                          <label class="form-check-label" for="lunchEnabledToggle">Close for a lunch break</label>
                        </div>
                      </div>
                      <div class="col-md-3 col-6">
                        <label class="form-label">Lunch Start</label>
                        <input type="time" name="booking_lunch_start" class="form-control @error('booking_lunch_start') has-error @enderror" step="1800"
                          value="{{ old('booking_lunch_start', $clinicHours['lunchStart']) }}">
                        @error('booking_lunch_start') <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div> @enderror
                      </div>
                      <div class="col-md-3 col-6">
                        <label class="form-label">Lunch End</label>
                        <input type="time" name="booking_lunch_end" class="form-control @error('booking_lunch_end') has-error @enderror" step="1800"
                          value="{{ old('booking_lunch_end', $clinicHours['lunchEnd']) }}">
                        @error('booking_lunch_end') <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div> @enderror
                      </div>
                    </div>

                    <div class="section-label mt-4"><i class="bi bi-telephone"></i> Contact Details</div>
                    <p class="small text-muted-2 mb-3">Shown in the landing page “Contact Us” section and the footer. The
                      email is also where “Contact Us” form messages are delivered.</p>
                    <div class="row g-3">
                      <div class="col-md-6">
                        <label class="form-label">Phone</label>
                        <div class="input-icon @error('contact_phone') has-error @enderror"><i class="bi bi-telephone"></i><input type="text" name="contact_phone"
                            class="form-control" value="{{ old('contact_phone', $aboutInfo['phone']) }}"
                            placeholder="(02) 8404-5642"></div>
                        @error('contact_phone') <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div> @enderror
                      </div>
                      <div class="col-md-6">
                        <label class="form-label">Mobile</label>
                        <div class="input-icon @error('contact_mobile') has-error @enderror"><i class="bi bi-phone"></i><input type="text" name="contact_mobile"
                            class="form-control" value="{{ old('contact_mobile', $aboutInfo['mobile']) }}"
                            placeholder="+63 9XX XXX XXXX"></div>
                        @error('contact_mobile') <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div> @enderror
                      </div>
                      <div class="col-12">
                        <label class="form-label">Clinic Email <span class="text-muted-2">(receives Contact Us messages)</span></label>
                        <div class="input-icon @error('contact_email') has-error @enderror"><i class="bi bi-envelope"></i><input type="email" name="contact_email"
                            class="form-control" value="{{ old('contact_email', $aboutInfo['email']) }}"
                            placeholder="clinic@example.com" required></div>
                        @error('contact_email') <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div> @enderror
                      </div>
                    </div>

                    <div class="section-label mt-4"><i class="bi bi-layout-text-window-reverse"></i> Landing Page Footer</div>
                    <p class="small text-muted-2 mb-3">The blurb and copyright line at the very bottom of the landing page.
                      The footer's quick links and contact block are generated from the navigation and the contact
                      details above — nothing to set there.</p>
                    <div class="row g-3">
                      <div class="col-12">
                        <label class="form-label">Footer Description</label>
                        <textarea name="footer_description" class="form-control @error('footer_description') has-error @enderror" rows="3"
                          placeholder="A short blurb about the clinic shown in the footer.">{{ old('footer_description', $aboutInfo['footerDescription']) }}</textarea>
                        @error('footer_description') <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div> @enderror
                      </div>
                      <div class="col-12">
                        <label class="form-label">Copyright Line</label>
                        <div class="input-icon @error('footer_copyright') has-error @enderror"><i class="bi bi-c-circle"></i><input type="text" name="footer_copyright"
                            class="form-control" value="{{ old('footer_copyright', $aboutInfo['footerCopyright']) }}"
                            placeholder="© {{ date('Y') }} Pus-Pus Britanico Dental Clinic. All rights reserved."></div>
                        @error('footer_copyright') <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div> @enderror
                      </div>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                      <button type="submit" class="btn btn-brand px-4"><i class="bi bi-floppy me-1"></i> Save Changes</button>
                    </div>
                  </form>
                </div>
              </div>

              {{-- ===================== CLOSED DATES ===================== --}}
              <div class="card-soft mt-3">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                  <span><i class="bi bi-calendar-x me-2" style="color: var(--brand-700);"></i> Closed Dates</span>
                  <button type="button" class="btn btn-brand px-3" id="closeDateToggleBtn">
                    <i class="bi bi-plus-lg"></i> <span id="closeDateToggleBtnLabel">Close a Date</span>
                  </button>
                </div>
                <div class="card-body p-3 p-md-4">
                  <p class="small text-muted-2 mb-3">Block an entire date &mdash; every dentist, every slot &mdash; from
                    being booked. Use this for holidays or other clinic-wide closures; per-dentist availability still
                    lives in Dentist Schedule.</p>

                  @php
                    $closeDateFailed = $errors->any() && old('form_source') === 'close_date';
                    $cdErr = fn ($field) => $closeDateFailed && $errors->has($field) ? 'has-error' : '';
                    $cdMsg = fn ($field) => $closeDateFailed && $errors->has($field) ? $errors->first($field) : null;
                  @endphp
                  <div id="closeDateFormWrap" class="mb-4 p-3" style="background:#fafbfa;border:1px solid #edf1ee;border-radius:12px;" {{ $closeDateFailed ? '' : 'hidden' }}>
                    <form method="POST" action="{{ route('configuration.closedDates.store') }}" class="row g-3 align-items-end"
                      data-confirm-title="Close this day?"
                      data-confirm-message="No one will be able to book any dentist on this date. Any pending or booked appointments already on it will be cancelled and the patient(s) notified. Continue?">
                      @csrf
                      <input type="hidden" name="form_source" value="close_date">
                      <div class="col-md-4 col-6">
                        <label class="form-label">Date</label>
                        <input type="date" name="date" class="form-control {{ $cdErr('date') }}" min="{{ now()->format('Y-m-d') }}"
                          value="{{ old('date') }}" required>
                        @if ($cdMsg('date')) <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $cdMsg('date') }}</div> @endif
                      </div>
                      <div class="col-md-5 col-12">
                        <label class="form-label">Reason <span class="text-muted-2">(optional)</span></label>
                        <input type="text" name="reason" class="form-control {{ $cdErr('reason') }}" maxlength="500"
                          placeholder="e.g. Christmas Day" value="{{ old('reason') }}">
                        @if ($cdMsg('reason')) <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $cdMsg('reason') }}</div> @endif
                      </div>
                      <div class="col-md-3 col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-brand px-3 flex-grow-1"><i class="bi bi-calendar-x me-1"></i> Close Day</button>
                        <button type="button" class="btn btn-outline-secondary px-3" id="closeDateCancelBtn">Cancel</button>
                      </div>
                    </form>
                  </div>

                  <form method="GET" action="{{ route('configuration') }}" class="data-toolbar">
                    <div class="left">
                      <ul class="nav nav-pills" data-tabgroup="closedDates" role="tablist">
                        <li class="nav-item" role="presentation">
                          <button class="nav-link {{ $closedDatesTab !== 'archived' ? 'active' : '' }}" type="button"
                            data-bs-toggle="pill" data-bs-target="#closedDatesActivePane" data-tab-value="active"
                            role="tab">Active</button>
                        </li>
                        <li class="nav-item" role="presentation">
                          <button class="nav-link {{ $closedDatesTab === 'archived' ? 'active' : '' }}" type="button"
                            data-bs-toggle="pill" data-bs-target="#closedDatesArchivedPane" data-tab-value="archived"
                            role="tab">Archived</button>
                        </li>
                      </ul>
                    </div>
                    <div class="right">
                      <input type="hidden" name="settingsTab" value="about">
                      <input type="hidden" name="closedDatesTab" id="closedDatesTabField" value="{{ $closedDatesTab }}">
                    </div>
                  </form>

                  <div class="tab-content mt-3">
                    <div class="tab-pane fade {{ $closedDatesTab !== 'archived' ? 'show active' : '' }}" id="closedDatesActivePane" role="tabpanel">
                      <div class="table-responsive">
                        <table class="table-soft" style="border-radius:0; box-shadow:none;">
                          <thead>
                            <tr>
                              <th>Date</th>
                              <th>Reason</th>
                              <th>Closed By</th>
                              <th class="text-end">Actions</th>
                            </tr>
                          </thead>
                          <tbody>
                            @forelse ($closedDates as $cd)
                              <tr>
                                <td class="fw-semibold">{{ $cd->Date->format('l, F j, Y') }}</td>
                                <td>{{ $cd->Reason ?: '—' }}</td>
                                <td>{{ $cd->closedBy?->display_name ?? '—' }}</td>
                                <td class="text-end">
                                  <button type="button" class="btn btn-pill btn-pill-edit me-1" data-bs-toggle="modal"
                                    data-bs-target="#editClosedDateModal{{ $cd->ClosedDateID }}"><i class="bi bi-pencil-square"></i> Edit</button>
                                  <button type="button" class="btn btn-pill btn-pill-archive" data-bs-toggle="modal"
                                    data-bs-target="#confirmActionModal"
                                    data-action-url="{{ route('configuration.closedDates.archive', $cd->ClosedDateID) }}"
                                    data-title="Open This Date"
                                    data-message="Reopen {{ $cd->Date->format('F j, Y') }} for booking?"
                                    data-confirm-label="Open Date" data-confirm-class="btn-pill-archive">
                                    <i class="bi bi-unlock"></i> Open This Date</button>
                                </td>
                              </tr>
                            @empty
                              <tr><td colspan="4" class="text-center text-muted-2 py-4">No closed dates right now.</td></tr>
                            @endforelse
                          </tbody>
                        </table>
                      </div>
                      <div class="pagination-soft">
                        <div>Showing {{ $closedDates->count() }} of {{ $closedDates->total() }} entries</div>
                        <div class="pages">@include('partials.pagination-pages', ['paginator' => $closedDates])</div>
                      </div>
                    </div>

                    <div class="tab-pane fade {{ $closedDatesTab === 'archived' ? 'show active' : '' }}" id="closedDatesArchivedPane" role="tabpanel">
                      <div class="table-responsive">
                        <table class="table-soft" style="border-radius:0; box-shadow:none;">
                          <thead>
                            <tr>
                              <th>Date</th>
                              <th>Reason</th>
                              <th>Closed By</th>
                              <th>Archive Reason</th>
                              <th class="text-end">Actions</th>
                            </tr>
                          </thead>
                          <tbody>
                            @forelse ($archivedClosedDates as $cd)
                              <tr>
                                <td class="fw-semibold">{{ $cd->Date->format('l, F j, Y') }}</td>
                                <td>{{ $cd->Reason ?: '—' }}</td>
                                <td>{{ $cd->closedBy?->display_name ?? '—' }}</td>
                                @include('partials.archive-reason-cell', ['row' => $cd])
                                <td class="text-end">
                                  <button type="button" class="btn btn-pill btn-pill-archive" data-bs-toggle="modal"
                                    data-bs-target="#confirmActionModal"
                                    data-action-url="{{ route('configuration.closedDates.unarchive', $cd->ClosedDateID) }}"
                                    data-title="Close Again"
                                    data-message="Close {{ $cd->Date->format('F j, Y') }} again?"
                                    data-confirm-label="Close Again" data-confirm-class="btn-pill-archive">
                                    <i class="bi bi-lock"></i> Close Again</button>
                                </td>
                              </tr>
                            @empty
                              <tr><td colspan="5" class="text-center text-muted-2 py-4">No archived (reopened) dates.</td></tr>
                            @endforelse
                          </tbody>
                        </table>
                      </div>
                      <div class="pagination-soft">
                        <div>Showing {{ $archivedClosedDates->count() }} of {{ $archivedClosedDates->total() }} entries</div>
                        <div class="pages">@include('partials.pagination-pages', ['paginator' => $archivedClosedDates])</div>
                      </div>
                    </div>
                  </div>
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
                      <select class="form-select" name="serviceCategory" style="min-width:160px; height:38px;" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        @foreach ($categories as $cat)
                          <option value="{{ $cat->CategoryID }}" {{ (string) ($serviceCategory ?? '') === (string) $cat->CategoryID ? 'selected' : '' }}>{{ $cat->Name }}</option>
                        @endforeach
                        <option value="none" {{ ($serviceCategory ?? '') === 'none' ? 'selected' : '' }}>Uncategorized</option>
                      </select>
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
                                <td>
                                  @if ($service->Description)
                                    <span class="d-inline-block text-truncate" style="max-width: 260px;"
                                      data-bs-toggle="tooltip" title="{{ $service->Description }}">{{ $service->Description }}</span>
                                  @else
                                    —
                                  @endif
                                </td>
                                <td class="text-end">
                                  <button type="button" class="btn btn-pill btn-pill-edit me-1" data-bs-toggle="modal"
                                    data-bs-target="#editServiceModal{{ $service->ServiceID }}"><i
                                      class="bi bi-pencil-square"></i> Edit</button>
                                  <button type="button" class="btn btn-pill btn-pill-archive" data-bs-toggle="modal"
                                    data-bs-target="#confirmActionModal"
                                    data-action-url="{{ route('configuration.services.archive', $service->ServiceID) }}"
                                    data-title="Archive Service"
                                    data-message="Archive &ldquo;{{ $service->ServiceName }}&rdquo;? Patients won't be able to select it when booking until you unarchive it."
                                    data-confirm-label="Archive" data-confirm-class="btn-pill-archive">
                                    <i class="bi bi-archive"></i> Archive</button>
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
                          @include('partials.pagination-pages', ['paginator' => $services])
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
                              <th>Archive Reason</th>
                              <th class="text-end">Actions</th>
                            </tr>
                          </thead>
                          <tbody>
                            @forelse ($archivedServices as $service)
                              <tr>
                                <td class="fw-semibold">{{ $service->ServiceName }}</td>
                                <td>{{ $service->category->Name ?? '—' }}</td>
                                <td>{{ $service->duration_label }}</td>
                                <td>
                                  @if ($service->Description)
                                    <span class="d-inline-block text-truncate" style="max-width: 260px;"
                                      data-bs-toggle="tooltip" title="{{ $service->Description }}">{{ $service->Description }}</span>
                                  @else
                                    —
                                  @endif
                                </td>
                                @include('partials.archive-reason-cell', ['row' => $service])
                                <td class="text-end">
                                  <button type="button" class="btn btn-pill btn-pill-edit me-1" data-bs-toggle="modal"
                                    data-bs-target="#editServiceModal{{ $service->ServiceID }}"><i
                                      class="bi bi-pencil-square"></i> Edit</button>
                                  <button type="button" class="btn btn-pill btn-pill-archive" data-bs-toggle="modal"
                                    data-bs-target="#confirmActionModal"
                                    data-action-url="{{ route('configuration.services.unarchive', $service->ServiceID) }}"
                                    data-title="Unarchive Service"
                                    data-message="Restore &ldquo;{{ $service->ServiceName }}&rdquo;? Patients will be able to select it when booking again."
                                    data-confirm-label="Unarchive" data-confirm-class="btn-pill-archive">
                                    <i class="bi bi-archive"></i> Unarchive</button>
                                </td>
                              </tr>
                            @empty
                              <tr>
                                <td colspan="6" class="text-center text-muted-2 py-4">No archived services.</td>
                              </tr>
                            @endforelse
                          </tbody>
                        </table>
                      </div>
                      <div class="pagination-soft">
                        <div>Showing {{ $archivedServices->count() }} of {{ $archivedServices->total() }} entries
                        </div>
                        <div class="pages">
                          @include('partials.pagination-pages', ['paginator' => $archivedServices])
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
                  <form method="POST" action="{{ route('configuration.privacyLegal.update') }}"
                    data-confirm-title="Save Privacy &amp; Legal Terms?"
                    data-confirm-message="Save these changes to the Privacy Policy and Legal Terms? This updates what patients see on the signup page.">
                    @csrf
                    <div class="mb-3">
                      <label class="form-label">Privacy Policy</label>
                      <textarea name="privacy_policy" class="form-control @error('privacy_policy') has-error @enderror" rows="10"
                        placeholder="Describe how patient data is collected, used, and protected.">{{ old('privacy_policy', $privacyLegal['privacyPolicy']) }}</textarea>
                      @error('privacy_policy')
                        <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div>
                      @else
                        <div class="small text-muted-2 mt-1">Shown to patients on the sign-up page.</div>
                      @enderror
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Legal Terms</label>
                      <textarea name="legal_terms" class="form-control @error('legal_terms') has-error @enderror" rows="10"
                        placeholder="Terms of use, liability, and other legal information.">{{ old('legal_terms', $privacyLegal['legalTerms']) }}</textarea>
                      @error('legal_terms') <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div> @enderror
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
                  <div class="d-flex align-items-center gap-2">
                    <span class="small text-muted-2 d-none d-md-inline">Shown on the landing page's "How to Book Your Appointment" section</span>
                    <button class="btn btn-brand px-3" data-bs-toggle="modal" data-bs-target="#addAppointmentStepModal">
                      <i class="bi bi-plus-lg"></i> Add Step
                    </button>
                  </div>
                </div>
                <div class="card-body p-3 p-md-4">
                  <form method="GET" action="{{ route('configuration') }}" class="data-toolbar">
                    <div class="left">
                      <ul class="nav nav-pills" data-tabgroup="appointmentSteps" role="tablist">
                        <li class="nav-item" role="presentation">
                          <button class="nav-link {{ $appointmentStepsTab !== 'archived' ? 'active' : '' }}" type="button"
                            data-bs-toggle="pill" data-bs-target="#appointmentStepsActivePane" data-tab-value="active"
                            role="tab">Active</button>
                        </li>
                        <li class="nav-item" role="presentation">
                          <button class="nav-link {{ $appointmentStepsTab === 'archived' ? 'active' : '' }}" type="button"
                            data-bs-toggle="pill" data-bs-target="#appointmentStepsArchivedPane" data-tab-value="archived"
                            role="tab">Archived</button>
                        </li>
                      </ul>
                    </div>
                    <div class="right">
                      <input type="hidden" name="settingsTab" value="appointment">
                      <input type="hidden" name="appointmentStepsTab" id="appointmentStepsTabField" value="{{ $appointmentStepsTab }}">
                    </div>
                  </form>

                  <div class="tab-content mt-3">
                    <div class="tab-pane fade {{ $appointmentStepsTab !== 'archived' ? 'show active' : '' }}"
                      id="appointmentStepsActivePane" role="tabpanel">
                      <div class="table-responsive">
                        <table class="table-soft" style="border-radius:0; box-shadow:none;">
                          <thead>
                            <tr>
                              <th>#</th>
                              <th>Title</th>
                              <th>Description</th>
                              <th class="text-end">Actions</th>
                            </tr>
                          </thead>
                          <tbody>
                            @forelse ($appointmentSteps as $step)
                              <tr>
                                <td>{{ $step->DisplayOrder }}</td>
                                <td class="fw-semibold">{{ $step->Title }}</td>
                                <td>
                                  <span class="d-inline-block text-truncate" style="max-width: 320px;"
                                    data-bs-toggle="tooltip" title="{{ $step->Description }}">{{ $step->Description }}</span>
                                </td>
                                <td class="text-end">
                                  <button type="button" class="btn btn-pill btn-pill-edit me-1" data-bs-toggle="modal"
                                    data-bs-target="#editAppointmentStepModal{{ $step->StepID }}"><i
                                      class="bi bi-pencil-square"></i> Edit</button>
                                  <button type="button" class="btn btn-pill btn-pill-archive" data-bs-toggle="modal"
                                    data-bs-target="#confirmActionModal"
                                    data-action-url="{{ route('configuration.appointmentSteps.archive', $step->StepID) }}"
                                    data-title="Archive Step"
                                    data-message="Archive &ldquo;{{ $step->Title }}&rdquo;? It will no longer show on the landing page until you unarchive it."
                                    data-confirm-label="Archive" data-confirm-class="btn-pill-archive">
                                    <i class="bi bi-archive"></i> Archive</button>
                                </td>
                              </tr>
                            @empty
                              <tr>
                                <td colspan="4" class="text-center text-muted-2 py-4">No steps yet — add one above.</td>
                              </tr>
                            @endforelse
                          </tbody>
                        </table>
                      </div>
                      <div class="pagination-soft">
                        <div>Showing {{ $appointmentSteps->count() }} of {{ $appointmentSteps->total() }} entries</div>
                        <div class="pages">
                          @include('partials.pagination-pages', ['paginator' => $appointmentSteps])
                        </div>
                      </div>
                    </div>

                    <div class="tab-pane fade {{ $appointmentStepsTab === 'archived' ? 'show active' : '' }}"
                      id="appointmentStepsArchivedPane" role="tabpanel">
                      <div class="table-responsive">
                        <table class="table-soft" style="border-radius:0; box-shadow:none;">
                          <thead>
                            <tr>
                              <th>#</th>
                              <th>Title</th>
                              <th>Description</th>
                              <th>Archive Reason</th>
                              <th class="text-end">Actions</th>
                            </tr>
                          </thead>
                          <tbody>
                            @forelse ($archivedAppointmentSteps as $step)
                              <tr>
                                <td>{{ $step->DisplayOrder }}</td>
                                <td class="fw-semibold">{{ $step->Title }}</td>
                                <td>
                                  <span class="d-inline-block text-truncate" style="max-width: 320px;"
                                    data-bs-toggle="tooltip" title="{{ $step->Description }}">{{ $step->Description }}</span>
                                </td>
                                @include('partials.archive-reason-cell', ['row' => $step])
                                <td class="text-end">
                                  <button type="button" class="btn btn-pill btn-pill-edit me-1" data-bs-toggle="modal"
                                    data-bs-target="#editAppointmentStepModal{{ $step->StepID }}"><i
                                      class="bi bi-pencil-square"></i> Edit</button>
                                  <button type="button" class="btn btn-pill btn-pill-archive" data-bs-toggle="modal"
                                    data-bs-target="#confirmActionModal"
                                    data-action-url="{{ route('configuration.appointmentSteps.unarchive', $step->StepID) }}"
                                    data-title="Unarchive Step"
                                    data-message="Restore &ldquo;{{ $step->Title }}&rdquo;? It will show on the landing page again."
                                    data-confirm-label="Unarchive" data-confirm-class="btn-pill-archive">
                                    <i class="bi bi-archive"></i> Unarchive</button>
                                </td>
                              </tr>
                            @empty
                              <tr>
                                <td colspan="5" class="text-center text-muted-2 py-4">No archived steps.</td>
                              </tr>
                            @endforelse
                          </tbody>
                        </table>
                      </div>
                      <div class="pagination-soft">
                        <div>Showing {{ $archivedAppointmentSteps->count() }} of {{ $archivedAppointmentSteps->total() }} entries</div>
                        <div class="pages">
                          @include('partials.pagination-pages', ['paginator' => $archivedAppointmentSteps])
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            {{-- ===================== ACTIVITY LOGS ===================== --}}
            <div class="settings-pane" data-settings-pane="activity" @if ($settingsTab !== 'activity') hidden @endif>

              <div class="card-soft">
                <div class="card-header d-flex align-items-center justify-content-between">
                  <span><i class="bi bi-activity me-2" style="color: var(--brand-700);"></i> Activity Logs</span>
                  <span class="small text-muted-2">Every sign-in, sign-out, failed login, and action taken across the system.</span>
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
                      <select class="form-select" name="activityType" style="height:38px; min-width:170px;"
                        onchange="this.form.submit()">
                        <option value="">All activity</option>
                        @foreach ($actionTypes as $type)
                          <option value="{{ $type }}" {{ $activityType === $type ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                      </select>
                      <div class="input-icon search">
                        <i class="bi bi-search"></i>
                        <input class="form-control" name="activitySearch" value="{{ $activitySearch }}"
                          placeholder="Search name, email, or details..."
                          style="height:38px; padding-left:2.3rem; min-width:240px;" />
                      </div>
                    </div>
                  </form>

                  <div class="tab-content mt-3">
                    @foreach (['active' => $activityLogs, 'archived' => $archivedActivityLogs] as $paneKey => $rows)
                      <div class="tab-pane fade {{ ($activityTab === 'archived' ? 'archived' : 'active') === $paneKey ? 'show active' : '' }}"
                        id="activity{{ ucfirst($paneKey) }}Pane" role="tabpanel">
                        <div class="table-responsive">
                          <table class="table-soft" style="border-radius:0; box-shadow:none;">
                            <thead>
                              <tr>
                                <th>User</th>
                                <th>Type</th>
                                <th>Activity</th>
                                <th>When</th>
                                @if ($paneKey === 'archived')
                                    <th>Archive Reason</th>
                                @endif
                                <th class="text-end">Actions</th>
                              </tr>
                            </thead>
                            <tbody>
                              @forelse ($rows as $log)
                                @php
                                  $ua = $log->userAccount;
                                  $isStaff = $ua?->AccountType === 'Staff';
                                  $info = $isStaff ? $ua?->staffInfo : $ua?->patientInfo;
                                  $name = $log->ActorName
                                      ?: ($info ? trim($info->FirstName . ' ' . $info->LastName) : ($ua->Email ?? 'Unknown'));
                                  $when = $log->LoggedInTime ?? $log->created_at;
                                  $isFail = str_starts_with($log->ActivityType, 'Failed');
                                  $pillClass = $isFail ? 'pill-danger' : ($isStaff ? 'pill-info' : 'pill-success');
                                @endphp
                                <tr>
                                  <td>
                                    <span><img class="avatar-initials"
                                        src="{{ $info->photo_url ?? asset('images/default.png') }}" alt=""></span>
                                    {{ $name }}
                                  </td>
                                  <td><span class="pill {{ $pillClass }}">{{ $log->ActivityType }}</span></td>
                                  <td>
                                    @if ($log->Description)
                                      <span class="d-inline-block text-truncate" style="max-width: 320px;"
                                        data-bs-toggle="tooltip" title="{{ $log->Description }}">{{ $log->Description }}</span>
                                    @else
                                      —
                                    @endif
                                    @if ($log->ActivityType === 'Login' && $log->LoggedOutTime)
                                      <div class="small text-muted-2">Signed out {{ $log->LoggedOutTime->format('M j, Y g:i A') }}</div>
                                    @elseif ($log->ActivityType === 'Login')
                                      <div class="small text-muted-2">Session still active</div>
                                    @endif
                                  </td>
                                  <td>{{ optional($when)->format('M j, Y g:i A') ?? '—' }}</td>
                                  @if ($paneKey === 'archived')
                                      @include('partials.archive-reason-cell', ['row' => $log])
                                  @endif
                                  <td class="text-end">
                                    <button type="button" class="btn btn-pill btn-pill-archive" data-bs-toggle="modal"
                                      data-bs-target="#confirmActionModal"
                                      data-action-url="{{ $paneKey === 'archived'
                                          ? route('configuration.activityLogs.unarchive', $log->ActivityLogsID)
                                          : route('configuration.activityLogs.archive', $log->ActivityLogsID) }}"
                                      data-title="{{ $paneKey === 'archived' ? 'Unarchive' : 'Archive' }} Log Entry"
                                      data-message="{{ $paneKey === 'archived' ? 'Restore' : 'Archive' }} this activity log entry?"
                                      data-confirm-label="{{ $paneKey === 'archived' ? 'Unarchive' : 'Archive' }}"
                                      data-confirm-class="btn-pill-archive">
                                      <i class="bi bi-archive"></i> {{ $paneKey === 'archived' ? 'Unarchive' : 'Archive' }}</button>
                                  </td>
                                </tr>
                              @empty
                                <tr>
                                  <td colspan="{{ $paneKey === 'archived' ? 6 : 5 }}" class="text-center text-muted-2 py-4">
                                    {{ $paneKey === 'archived' ? 'No archived activity.' : 'No activity recorded yet.' }}
                                  </td>
                                </tr>
                              @endforelse
                            </tbody>
                          </table>
                        </div>
                        <div class="pagination-soft">
                          <div>Showing {{ $rows->count() }} of {{ $rows->total() }} entries</div>
                          <div class="pages">
                            @include('partials.pagination-pages', ['paginator' => $rows])
                          </div>
                        </div>
                      </div>
                    @endforeach
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

  <!-- ===================== ADD APPOINTMENT STEP MODAL ===================== -->
  <div class="modal fade" id="addAppointmentStepModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <div>
            <h5 class="modal-title fw-semibold">Add Step</h5>
            <div class="small text-muted">Add a step to "How to Book Your Appointment"</div>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        @php
          $addStepFailed = $errors->any() && old('form_source') === 'add_appointment_step';
          $adsErr = fn ($field) => $addStepFailed && $errors->has($field) ? 'has-error' : '';
          $adsMsg = fn ($field) => $addStepFailed && $errors->has($field) ? $errors->first($field) : null;
          $adsOld = fn ($field, $default = null) => $addStepFailed ? old($field) : $default;
        @endphp
        <form method="POST" action="{{ route('configuration.appointmentSteps.store') }}">
          @csrf
          <input type="hidden" name="form_source" value="add_appointment_step">
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Step title</label>
              <div class="input-icon {{ $adsErr('title') }}"><i class="bi bi-list-check"></i><input type="text" name="title"
                  class="form-control" value="{{ $adsOld('title') }}" placeholder="e.g. Create an Account" required maxlength="150"></div>
              @if ($adsMsg('title')) <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $adsMsg('title') }}</div> @endif
            </div>
            <div class="mb-3">
              <label class="form-label">Description</label>
              <textarea name="description" class="form-control {{ $adsErr('description') }}" rows="3" required
                maxlength="500">{{ $adsOld('description') }}</textarea>
              @if ($adsMsg('description')) <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $adsMsg('description') }}</div> @endif
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-brand">Add Step</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- ===================== ONE EDIT MODAL PER APPOINTMENT STEP (active + archived) ===================== -->
  @foreach ($appointmentSteps->merge($archivedAppointmentSteps) as $step)
    @php
      $editStepFailed = $errors->any() && old('form_source') === 'edit_appointment_step_' . $step->StepID;
      $edsErr = fn ($field) => $editStepFailed && $errors->has($field) ? 'has-error' : '';
      $edsMsg = fn ($field) => $editStepFailed && $errors->has($field) ? $errors->first($field) : null;
      $edsOld = fn ($field, $default = null) => $editStepFailed ? old($field) : $default;
    @endphp
    <div class="modal fade" id="editAppointmentStepModal{{ $step->StepID }}" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <div>
              <h5 class="modal-title fw-semibold">Edit Step</h5>
              <div class="small text-muted">Update this step's details</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form method="POST" action="{{ route('configuration.appointmentSteps.update', $step->StepID) }}">
            @csrf
            <input type="hidden" name="form_source" value="edit_appointment_step_{{ $step->StepID }}">
            <div class="modal-body">
              <div class="mb-3">
                <label class="form-label">Step title</label>
                <div class="input-icon {{ $edsErr('title') }}"><i class="bi bi-list-check"></i><input type="text" name="title"
                    class="form-control" value="{{ $edsOld('title', $step->Title) }}" required maxlength="150"></div>
                @if ($edsMsg('title')) <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $edsMsg('title') }}</div> @endif
              </div>
              <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control {{ $edsErr('description') }}" rows="3" required
                  maxlength="500">{{ $edsOld('description', $step->Description) }}</textarea>
                @if ($edsMsg('description')) <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $edsMsg('description') }}</div> @endif
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-brand">Save Changes</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  @endforeach

  <!-- ===================== ADD SERVICE MODAL ===================== -->
  <div class="modal fade" id="addServiceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <div>
            <h5 class="modal-title fw-semibold">Add Service</h5>
            <div class="small text-muted">Create a new clinic service</div>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        @php
          $addServiceFailed = $errors->any() && old('form_source') === 'add_service';
          $asErr = fn ($field) => $addServiceFailed && $errors->has($field) ? 'has-error' : '';
          $asMsg = fn ($field) => $addServiceFailed && $errors->has($field) ? $errors->first($field) : null;
          $asOld = fn ($field, $default = null) => $addServiceFailed ? old($field) : $default;
        @endphp
        <form method="POST" action="{{ route('configuration.services.store') }}">
          @csrf
          <input type="hidden" name="form_source" value="add_service">
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Service name</label>
              <div class="input-icon {{ $asErr('service_name') }}"><i class="bi bi-heart-pulse"></i><input type="text" name="service_name"
                  class="form-control" value="{{ $asOld('service_name') }}" placeholder="e.g. Tooth Extraction" required></div>
              @if ($asMsg('service_name')) <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $asMsg('service_name') }}</div> @endif
            </div>
            <div class="mb-3">
              <label class="form-label">Category</label>
              <div class="input-icon {{ $asErr('category_id') }}"><i class="bi bi-tags"></i>
                <select name="category_id" class="form-select">
                  <option value="">— Uncategorized —</option>
                  @foreach ($categories as $category)
                    <option value="{{ $category->CategoryID }}" {{ (string) $asOld('category_id') === (string) $category->CategoryID ? 'selected' : '' }}>{{ $category->Name }}</option>
                  @endforeach
                </select>
              </div>
              @if ($asMsg('category_id')) <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $asMsg('category_id') }}</div> @endif
              <div class="small text-muted-2 mt-1">Groups this service on the landing page's "Our Services" section. <a href="#" data-bs-toggle="modal" data-bs-target="#manageCategoriesModal" data-bs-dismiss="modal">Manage categories</a>.</div>
            </div>
            <div class="mb-3">
              <label class="form-label">Duration</label>
              <div class="input-icon {{ $asErr('duration_minutes') }}"><i class="bi bi-hourglass-split"></i>
                <select name="duration_minutes" class="form-select" required>
                  @foreach ($serviceDurationOptions as $minutes => $optLabel)
                    <option value="{{ $minutes }}" {{ (int) $asOld('duration_minutes', 60) === $minutes ? 'selected' : '' }}>{{ $optLabel }}</option>
                  @endforeach
                </select>
              </div>
              @if ($asMsg('duration_minutes')) <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $asMsg('duration_minutes') }}</div> @endif
              <div class="small text-muted-2 mt-1">How long this service takes — used to block the right amount of time when a patient books it.</div>
            </div>
            <div class="mb-3">
              <label class="form-label">Description</label>
              <textarea name="description" class="form-control {{ $asErr('description') }}" rows="3" placeholder="Optional description">{{ $asOld('description') }}</textarea>
              @if ($asMsg('description')) <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $asMsg('description') }}</div> @endif
            </div>
          </div>
          <div class="modal-footer">
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
        <div class="modal-header">
          <div>
            <h5 class="modal-title fw-semibold">Manage Categories</h5>
            <div class="small text-muted">Group services for the landing page's "Our Services" section</div>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <ul class="nav nav-pills mb-3" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active" type="button" data-bs-toggle="pill"
                data-bs-target="#categoriesActivePane" role="tab">Active</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" type="button" data-bs-toggle="pill"
                data-bs-target="#categoriesArchivedPane" role="tab">Archived</button>
            </li>
          </ul>
          <div class="tab-content mb-4">
            <div class="tab-pane fade show active" id="categoriesActivePane" role="tabpanel">
              <div class="table-responsive">
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
                          <button type="button" class="btn btn-pill btn-pill-archive" data-bs-toggle="modal"
                            data-bs-target="#confirmActionModal" data-bs-dismiss="modal"
                            data-action-url="{{ route('configuration.categories.archive', $category->CategoryID) }}"
                            data-title="Archive Category"
                            data-message="Archive &ldquo;{{ $category->Name }}&rdquo;? Its services will show as Uncategorized until you unarchive it."
                            data-confirm-label="Archive" data-confirm-class="btn-pill-archive">
                            <i class="bi bi-archive"></i> Archive</button>
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
            </div>
            <div class="tab-pane fade" id="categoriesArchivedPane" role="tabpanel">
              <div class="table-responsive">
                <table class="table-soft" style="border-radius:0; box-shadow:none;">
                  <thead>
                    <tr>
                      <th>Icon</th>
                      <th>Category</th>
                      <th>Services</th>
                      <th>Archive Reason</th>
                      <th class="text-end">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse ($archivedCategories as $category)
                      <tr>
                        <td><i class="{{ $category->Icon ?: 'fa-solid fa-tooth' }}" style="color: var(--brand-700);"></i></td>
                        <td class="fw-semibold">{{ $category->Name }}</td>
                        <td>{{ $category->services_count }}</td>
                        @include('partials.archive-reason-cell', ['row' => $category])
                        <td class="text-end">
                          <button type="button" class="btn btn-pill btn-pill-edit me-1" data-bs-toggle="modal"
                            data-bs-target="#editCategoryModal{{ $category->CategoryID }}" data-bs-dismiss="modal"><i
                              class="bi bi-pencil-square"></i> Edit</button>
                          <button type="button" class="btn btn-pill btn-pill-archive" data-bs-toggle="modal"
                            data-bs-target="#confirmActionModal" data-bs-dismiss="modal"
                            data-action-url="{{ route('configuration.categories.unarchive', $category->CategoryID) }}"
                            data-title="Unarchive Category"
                            data-message="Restore &ldquo;{{ $category->Name }}&rdquo;? It will be assignable to services again."
                            data-confirm-label="Unarchive" data-confirm-class="btn-pill-archive">
                            <i class="bi bi-archive"></i> Unarchive</button>
                        </td>
                      </tr>
                    @empty
                      <tr>
                        <td colspan="5" class="text-center text-muted-2 py-3">No archived categories.</td>
                      </tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          @php
            $addCategoryFailed = $errors->any() && old('form_source') === 'add_category';
          @endphp
          <div class="section-label mb-2">Add a New Category</div>
          <form method="POST" action="{{ route('configuration.categories.store') }}" class="row g-2 align-items-end">
            @csrf
            <input type="hidden" name="form_source" value="add_category">
            <div class="col-md-6">
              <label class="form-label">Name</label>
              <input type="text" name="name" class="form-control {{ $addCategoryFailed && $errors->has('name') ? 'has-error' : '' }}"
                value="{{ $addCategoryFailed ? old('name') : '' }}" placeholder="e.g. General Dentistry" required maxlength="100">
              @if ($addCategoryFailed && $errors->has('name'))
                <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $errors->first('name') }}</div>
              @endif
            </div>
            <div class="col-md-4">
              <label class="form-label">Icon</label>
              <select name="icon" class="form-select {{ $addCategoryFailed && $errors->has('icon') ? 'has-error' : '' }}">
                @foreach (\App\Models\ServiceCategory::iconOptions() as $value => $label)
                  <option value="{{ $value }}" {{ $addCategoryFailed && old('icon') === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
              </select>
              @if ($addCategoryFailed && $errors->has('icon'))
                <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $errors->first('icon') }}</div>
              @endif
            </div>
            <div class="col-md-2">
              <button type="submit" class="btn btn-brand w-100">Add</button>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  @foreach ($categories->merge($archivedCategories) as $category)
    @php
      $editCategoryFailed = $errors->any() && old('form_source') === 'edit_category_' . $category->CategoryID;
    @endphp
    <div class="modal fade" id="editCategoryModal{{ $category->CategoryID }}" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title fw-semibold">Edit Category</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form method="POST" action="{{ route('configuration.categories.update', $category->CategoryID) }}">
            @csrf
            <input type="hidden" name="form_source" value="edit_category_{{ $category->CategoryID }}">
            <div class="modal-body">
              <div class="mb-3">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control {{ $editCategoryFailed && $errors->has('name') ? 'has-error' : '' }}"
                  value="{{ $editCategoryFailed ? old('name') : $category->Name }}" required maxlength="100">
                @if ($editCategoryFailed && $errors->has('name'))
                  <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $errors->first('name') }}</div>
                @endif
              </div>
              <div class="mb-3">
                <label class="form-label">Icon</label>
                <select name="icon" class="form-select {{ $editCategoryFailed && $errors->has('icon') ? 'has-error' : '' }}">
                  @foreach (\App\Models\ServiceCategory::iconOptions() as $value => $label)
                    <option value="{{ $value }}" {{ ($editCategoryFailed ? old('icon') : $category->Icon) === $value ? 'selected' : '' }}>{{ $label }}</option>
                  @endforeach
                </select>
                @if ($editCategoryFailed && $errors->has('icon'))
                  <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $errors->first('icon') }}</div>
                @endif
              </div>
            </div>
            <div class="modal-footer">
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
    @php
      $editServiceFailed = $errors->any() && old('form_source') === 'edit_service_' . $service->ServiceID;
      $esErr = fn ($field) => $editServiceFailed && $errors->has($field) ? 'has-error' : '';
      $esMsg = fn ($field) => $editServiceFailed && $errors->has($field) ? $errors->first($field) : null;
      $esOld = fn ($field, $default = null) => $editServiceFailed ? old($field) : $default;
    @endphp
    <div class="modal fade" id="editServiceModal{{ $service->ServiceID }}" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <div>
              <h5 class="modal-title fw-semibold">Edit Service</h5>
              <div class="small text-muted">Update service details</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form method="POST" action="{{ route('configuration.services.update', $service->ServiceID) }}">
            @csrf
            <input type="hidden" name="form_source" value="edit_service_{{ $service->ServiceID }}">
            <div class="modal-body">
              <div class="mb-3">
                <label class="form-label">Service name</label>
                <div class="input-icon {{ $esErr('service_name') }}"><i class="bi bi-heart-pulse"></i><input type="text" name="service_name"
                    class="form-control" value="{{ $esOld('service_name', $service->ServiceName) }}" required></div>
                @if ($esMsg('service_name')) <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $esMsg('service_name') }}</div> @endif
              </div>
              <div class="mb-3">
                <label class="form-label">Category</label>
                <div class="input-icon {{ $esErr('category_id') }}"><i class="bi bi-tags"></i>
                  <select name="category_id" class="form-select">
                    <option value="">— Uncategorized —</option>
                    @foreach ($categories as $category)
                      <option value="{{ $category->CategoryID }}" {{ (string) $esOld('category_id', $service->CategoryID) === (string) $category->CategoryID ? 'selected' : '' }}>{{ $category->Name }}</option>
                    @endforeach
                    @if ($service->category && $service->category->IsArchived)
                      <option value="{{ $service->category->CategoryID }}" selected>{{ $service->category->Name }} (archived)</option>
                    @endif
                  </select>
                </div>
                @if ($esMsg('category_id')) <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $esMsg('category_id') }}</div> @endif
              </div>
              <div class="mb-3">
                <label class="form-label">Duration</label>
                <div class="input-icon {{ $esErr('duration_minutes') }}"><i class="bi bi-hourglass-split"></i>
                  <select name="duration_minutes" class="form-select" required>
                    @foreach ($serviceDurationOptions as $minutes => $optLabel)
                      <option value="{{ $minutes }}" {{ (int) $esOld('duration_minutes', $service->DurationMinutes) === $minutes ? 'selected' : '' }}>{{ $optLabel }}</option>
                    @endforeach
                  </select>
                </div>
                @if ($esMsg('duration_minutes')) <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $esMsg('duration_minutes') }}</div> @endif
                <div class="small text-muted-2 mt-1">How long this service takes — used to block the right amount of time when a patient books it.</div>
              </div>
              <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control {{ $esErr('description') }}" rows="3">{{ $esOld('description', $service->Description) }}</textarea>
                @if ($esMsg('description')) <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $esMsg('description') }}</div> @endif
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-brand">Save Changes</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  @endforeach

  @foreach ($closedDates as $cd)
    @php
      $editClosedDateFailed = $errors->any() && old('form_source') === 'edit_closed_date_' . $cd->ClosedDateID;
      $ecdErr = fn ($field) => $editClosedDateFailed && $errors->has($field) ? 'has-error' : '';
      $ecdMsg = fn ($field) => $editClosedDateFailed && $errors->has($field) ? $errors->first($field) : null;
      $ecdOld = fn ($field, $default = null) => $editClosedDateFailed ? old($field) : $default;
    @endphp
    <div class="modal fade" id="editClosedDateModal{{ $cd->ClosedDateID }}" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title fw-semibold">Edit Closed Date</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form method="POST" action="{{ route('configuration.closedDates.update', $cd->ClosedDateID) }}">
            @csrf
            <input type="hidden" name="form_source" value="edit_closed_date_{{ $cd->ClosedDateID }}">
            <div class="modal-body">
              <div class="mb-3">
                <label class="form-label">Date</label>
                <input type="date" name="date" class="form-control {{ $ecdErr('date') }}" min="{{ now()->format('Y-m-d') }}"
                  value="{{ $ecdOld('date', $cd->Date->format('Y-m-d')) }}" required>
                @if ($ecdMsg('date')) <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $ecdMsg('date') }}</div> @endif
              </div>
              <div class="mb-3">
                <label class="form-label">Reason <span class="text-muted-2">(optional)</span></label>
                <input type="text" name="reason" class="form-control {{ $ecdErr('reason') }}" maxlength="500"
                  value="{{ $ecdOld('reason', $cd->Reason) }}">
                @if ($ecdMsg('reason')) <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $ecdMsg('reason') }}</div> @endif
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-brand">Save Changes</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  @endforeach

  @include('partials.admin-notif-modal')
  @include('partials.confirm-action-modal')

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Quick text filter above the small tables that have no server-side
    // search (appointment steps + service categories, active and archived).
    ['appointmentStepsActivePane', 'appointmentStepsArchivedPane', 'categoriesActivePane', 'categoriesArchivedPane'].forEach(function (id) {
      var pane = document.getElementById(id);
      var wrap = pane && pane.querySelector('.table-responsive');
      if (!wrap) return;
      var box = document.createElement('div');
      box.className = 'input-icon search mb-3';
      box.innerHTML = '<i class="bi bi-search"></i><input type="search" class="form-control" placeholder="Filter this list..." style="height:38px; padding-left:2.3rem; max-width:280px;">';
      wrap.parentNode.insertBefore(box, wrap);
      box.querySelector('input').addEventListener('input', function () {
        var term = this.value.trim().toLowerCase();
        wrap.querySelectorAll('tbody tr').forEach(function (row) {
          row.hidden = term !== '' && row.textContent.toLowerCase().indexOf(term) === -1;
        });
      });
    });

    // Long table cells (Service / Activity Log descriptions) are truncated
    // to one line with a Bootstrap tooltip showing the full text on hover.
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
      new bootstrap.Tooltip(el);
    });

    // Image tiles — preview the chosen file and show its name.
    document.querySelectorAll('[data-cfg-img]').forEach(function (input) {
      input.addEventListener('change', function () {
        var file = this.files && this.files[0];
        if (!file) return;
        var card = this.closest('.cfg-img-card');
        var img = card.querySelector('.cfg-img-preview img');
        var picked = card.querySelector('[data-cfg-filename]');
        img.classList.remove('is-broken');
        img.src = URL.createObjectURL(file);
        if (picked) {
          picked.querySelector('span').textContent = file.name;
          picked.hidden = false;
        }
      });
    });

    document.querySelectorAll('[data-tabgroup]').forEach(function (group) {
      var field = document.getElementById(group.dataset.tabgroup + 'TabField');
      group.querySelectorAll('button[data-bs-toggle="pill"]').forEach(function (btn) {
        btn.addEventListener('shown.bs.tab', function () {
          if (field) field.value = btn.dataset.tabValue;
        });
      });
    });

    // "Close a Date" reveals the date/reason form in place — no page nav,
    // no modal. The Cancel button (and clicking the toggle again) hides it.
    (function () {
      var wrap = document.getElementById('closeDateFormWrap');
      var toggleBtn = document.getElementById('closeDateToggleBtn');
      var toggleLabel = document.getElementById('closeDateToggleBtnLabel');
      var cancelBtn = document.getElementById('closeDateCancelBtn');
      if (!wrap || !toggleBtn) return;

      function setOpen(open) {
        wrap.hidden = !open;
        if (toggleLabel) toggleLabel.textContent = open ? 'Cancel' : 'Close a Date';
        if (open) wrap.querySelector('input[name="date"]')?.focus();
      }

      toggleBtn.addEventListener('click', function () { setOpen(wrap.hidden); });
      cancelBtn?.addEventListener('click', function () { setOpen(false); });
    })();

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
