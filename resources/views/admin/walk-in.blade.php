<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
  <link rel="icon" type="image/png" href="/images/puspus_logo.png">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ $mode === 'followup' ? 'Follow-up Appointment' : 'Walk-in' }} • Dental Clinic</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">

    <!-- Page-specific additions only (everything else comes from styles.css already used across the app) -->
    <style>
        .wizard-steps { display: flex; align-items: center; gap: .5rem; margin-bottom: 1.5rem; flex-wrap: wrap; }
        .wizard-step-badge { display: flex; align-items: center; gap: .5rem; padding: .4rem .9rem; border-radius: 999px; background: var(--brand-50); border: 1px solid var(--brand-100); font-size: .85rem; font-weight: 600; color: var(--brand-700, #198754); }
        .wizard-step-badge.is-inactive { opacity: .45; }
        .wizard-step-sep { color: var(--muted, #9aa5a1); }
        .search-results { border: 1px solid var(--brand-100); border-radius: 10px; margin-top: .5rem; max-height: 220px; overflow-y: auto; }
        .search-result-item { padding: .6rem .9rem; cursor: pointer; border-bottom: 1px solid #f0f2f1; }
        .search-result-item:last-child { border-bottom: none; }
        .search-result-item:hover { background: var(--brand-50); }
        .review-row { display: flex; justify-content: space-between; padding: .5rem 0; border-bottom: 1px solid #f0f2f1; }
        .review-row:last-child { border-bottom: none; }

        /* The following few rules are copied from public/css/landing.css (not linked wholesale here,
           since it redefines .form-control/.form-select globally and would reskin this admin page). */
        .day-cell .ev.ev-pending { background: #fff8e6; color: #8a6100; border: 1px solid #f0d78c; }
        .booking-calendar .day-cell .ev.ev-unavailable { background: #fff0f1; color: #c93645; border: 1px solid #f2aeb6; }
        .booking-calendar .legend .dot { width: 10px; height: 10px; border-radius: 50%; }
        .booking-status { display: block; width: 100%; padding: .7rem 1rem; border-radius: 8px; font-weight: 700; }
        .booking-status-booked { background: #fff0f1; color: #c93645; border: 1px solid #f2aeb6; }
        .booking-status-pending { background: #fff8e6; color: #8a6100; border: 1px solid #f0d78c; }
    </style>
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
            @include('partials.admin-sidebar-nav', ['active' => $mode === 'followup' ? 'followUp' : 'walkIn'])
            @include('partials.admin-profile-badge')
        </aside>

        <main>
            <div class="topbar">
                <div class="left">
                    <button class="toggle d-lg-none" type="button" data-bs-toggle="offcanvas"
                        data-bs-target="#sidebarOffcanvas" aria-controls="sidebarOffcanvas">
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
                        @if ($mode === 'followup')
                        <h2>Follow-up Appointment</h2>
                        <div class="crumbs">Load an existing patient and book their follow-up into an open slot. They're notified by email and it shows in their account.</div>
                        @else
                        <h2>Walk-in Patient</h2>
                        <div class="crumbs">Register a patient who walked in and book them into an open slot. No email or notification is sent to walk-in patients.</div>
                        @endif
                    </div>
                </div>

                @include('partials.flash-toasts')

                @if ($mode === 'walkin')
                    <div class="alert alert-warning" id="wkClosedBanner" hidden>
                        <div class="d-flex align-items-start gap-2"><i class="bi bi-clock-history mt-1"></i><div id="wkClosedText"></div></div>
                    </div>
                @endif

                <!-- ===================== WIZARD STEP INDICATOR ===================== -->
                <div class="wizard-steps">
                    <span class="wizard-step-badge" data-step-badge="1">1. Patient Information</span>
                    <span class="wizard-step-sep"><i class="bi bi-chevron-right"></i></span>
                    <span class="wizard-step-badge is-inactive" data-step-badge="2">{{ $mode === 'followup' ? '2. Appointment Details' : '2. Services' }}</span>
                    <span class="wizard-step-sep"><i class="bi bi-chevron-right"></i></span>
                    <span class="wizard-step-badge is-inactive" data-step-badge="3">3. Review &amp; Confirm</span>
                </div>

                <!--  =================== WALK IN ======================= -->
                <div class="card-soft p-3 p-md-4">
                    <form id="walkinForm" method="POST" action="{{ route('walkIn.store') }}">
                        @csrf
                        <input type="hidden" name="booking_mode" value="{{ $mode }}">
                        <input type="hidden" name="patient_source" id="patient_source" value="{{ $mode === 'followup' ? 'existing' : 'new' }}">
                        <input type="hidden" name="patient_id" id="patient_id" value="">
                        <input type="hidden" name="date" id="wi_date" value="">
                        <input type="hidden" name="time" id="wi_time" value="">
                        <input type="hidden" name="dentist_id" id="wi_dentist_id" value="{{ $bookSelectedDentistId }}">
                        <div id="wi_service_ids_container"></div>

                        <!-- ===================== STEP 1: PATIENT INFORMATION ===================== -->
                        <div class="wizard-step" data-step="1">
                            <ul class="nav nav-tabs patient-tabs d-none" id="patientTab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="existing-tab" data-bs-toggle="tab"
                                        data-bs-target="#existing-pane" type="button" role="tab">Existing Patient</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="new-tab" data-bs-toggle="tab" data-bs-target="#new-pane"
                                        type="button" role="tab">New Patient</button>
                                </li>
                            </ul>

                            <div class="tab-content patient-tab-pane-wrap mb-4">

                                <!-- Existing patient lookup -->
                                <div class="tab-pane fade {{ $mode === 'followup' ? 'show active' : '' }}" id="existing-pane" role="tabpanel">
                                    <div class="row g-3 mb-3" id="patientSearchRow">
                                        <div class="col-md-9">
                                            <label class="form-label">Search patient (name or Patient ID)</label>
                                            <div class="input-icon"><i class="bi bi-search"></i>
                                                <input type="text" class="form-control" id="patientSearchInput"
                                                    placeholder="e.g. John Cruz or PT-0001" autocomplete="off">
                                            </div>
                                            <div class="invalid-feedback" id="patientSearchFeedback">Please search for and load a patient before continuing.</div>
                                            <div id="patientSearchResults" class="search-results d-none"></div>
                                        </div>
                                        <div class="col-md-3 d-flex align-items-end">
                                            <button type="button" class="btn btn-brand w-100" id="loadPatientBtn">Load Patient</button>
                                        </div>
                                    </div>

                                    <div class="alert alert-warning py-2 px-3 small" id="noPatientLoadedNotice">No patient loaded yet — search above and select a result.</div>

                                    <div class="d-flex align-items-center gap-3 mb-4" id="loadedPatientCard" style="display:none;">
                                        <img class="avatar-initials" src="/images/default.png" alt=""
                                            style="width:64px;height:64px;">
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold" id="loadedPatientName">—</div>
                                            <div class="small text-muted-2">Patient ID: <span id="loadedPatientId">—</span></div>
                                        </div>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" id="clearPatientBtn" title="Clear loaded patient and search for another">
                                            <i class="bi bi-x-lg"></i> Change Patient
                                        </button>
                                    </div>

                                    <div class="section-label">Personal Information</div>
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-4">
                                            <label class="form-label">Last name</label>
                                            <div class="input-icon"><i class="bi bi-person"></i><input type="text"
                                                    class="form-control" id="existing_last_name" disabled /></div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">First name</label>
                                            <div class="input-icon"><i class="bi bi-person"></i><input type="text"
                                                    class="form-control" id="existing_first_name" disabled /></div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Middle name</label>
                                            <div class="input-icon"><i class="bi bi-person"></i><input type="text"
                                                    class="form-control" id="existing_middle_name" disabled /></div>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label">Birthdate</label>
                                            <div class="input-icon"><i class="bi bi-calendar-event"></i><input type="date"
                                                    class="form-control" id="existing_birthdate" disabled data-age-target="#existing_age" /></div>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Age</label>
                                            <div class="input-icon"><i class="bi bi-person-vcard"></i><input type="text" class="form-control" id="existing_age" placeholder="—" disabled></div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Gender</label>
                                            <div class="input-icon">
                                                <select class="form-select" id="existing_gender" disabled>
                                                    <option selected>—</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label">Status</label>
                                            <div class="input-icon">
                                                <select class="form-select" disabled>
                                                    <option selected>Active</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label">Home address</label>
                                            <div class="input-icon"><i class="bi bi-geo-alt"></i><input class="form-control"
                                                    id="existing_address" disabled /></div>
                                        </div>
                                    </div>

                                    <div class="section-label mt-2">Contact Details</div>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Email address</label>
                                            <div class="input-icon"><i class="bi bi-envelope"></i><input type="email"
                                                    class="form-control" id="existing_email" disabled /></div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Cell/Mobile number</label>
                                            <div class="input-icon"><i class="bi bi-telephone"></i><input
                                                    class="form-control" id="existing_phone" disabled /></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- New patient registration -->
                                <div class="tab-pane fade {{ $mode === 'followup' ? '' : 'show active' }}" id="new-pane" role="tabpanel">
                                    <div class="section-label">Personal Information</div>
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-4">
                                            <label class="form-label">Last name <span class="text-danger">*</span></label>
                                            <div class="input-icon @error('last_name') has-error @enderror"><i class="bi bi-person"></i><input type="text" name="last_name"
                                                    class="form-control" value="{{ old('last_name') }}" placeholder="Dela Cruz" data-wi-required /></div>
                                            @error('last_name')
                                                <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div>
                                            @else
                                                <div class="invalid-feedback">This field is required.</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">First name <span class="text-danger">*</span></label>
                                            <div class="input-icon @error('first_name') has-error @enderror"><i class="bi bi-person"></i><input type="text" name="first_name"
                                                    class="form-control" value="{{ old('first_name') }}" placeholder="Maria" data-wi-required /></div>
                                            @error('first_name')
                                                <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div>
                                            @else
                                                <div class="invalid-feedback">This field is required.</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Middle name</label>
                                            <div class="input-icon @error('middle_name') has-error @enderror"><i class="bi bi-person"></i><input type="text" name="middle_name"
                                                    class="form-control" value="{{ old('middle_name') }}" placeholder="Reyes" /></div>
                                            @error('middle_name') <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div> @enderror
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label">Birthdate <span class="text-danger">*</span></label>
                                            <div class="input-icon @error('birthdate') has-error @enderror"><i class="bi bi-calendar-event"></i><input type="date" name="birthdate" id="wiBirthdate"
                                                    class="form-control" value="{{ old('birthdate') }}" max="2023-12-31" data-wi-required
                                                    data-age-target="#newPatientAge" /></div>
                                            @error('birthdate')
                                                <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div>
                                            @else
                                                <div class="invalid-feedback">This field is required.</div>
                                            @enderror
                                            <div class="form-text">Age is computed automatically from the birthdate.</div>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Age</label>
                                            <div class="input-icon"><i class="bi bi-person-vcard"></i><input type="text" class="form-control" id="newPatientAge" placeholder="—" disabled></div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Gender <span class="text-danger">*</span></label>
                                            <div class="input-icon @error('gender') has-error @enderror">
                                                <select class="form-select" name="gender" data-wi-required>
                                                    <option selected disabled value="">Select gender</option>
                                                    <option {{ old('gender') === 'Male' ? 'selected' : '' }}>Male</option>
                                                    <option {{ old('gender') === 'Female' ? 'selected' : '' }}>Female</option>
                                                </select>
                                            </div>
                                            @error('gender')
                                                <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div>
                                            @else
                                                <div class="invalid-feedback">This field is required.</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Street / House No.</label>
                                            <div class="input-icon"><i class="bi bi-signpost-2"></i><input class="form-control addr-part" name="addr_street"
                                                    value="{{ old('addr_street') }}" placeholder="123 Sample St." /></div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Barangay</label>
                                            <div class="input-icon"><i class="bi bi-geo"></i><input class="form-control addr-part" name="addr_barangay"
                                                    value="{{ old('addr_barangay') }}" placeholder="Barangay" /></div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">City / Municipality <span class="text-danger">*</span></label>
                                            <div class="input-icon"><i class="bi bi-buildings"></i><input class="form-control addr-part" name="addr_city"
                                                    value="{{ old('addr_city') }}" placeholder="City / Municipality" data-wi-required /></div>
                                            <div class="invalid-feedback">This field is required.</div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Province <span class="text-danger">*</span></label>
                                            <div class="input-icon"><i class="bi bi-map"></i><input class="form-control addr-part" name="addr_province"
                                                    value="{{ old('addr_province') }}" placeholder="Province" data-wi-required /></div>
                                            <div class="invalid-feedback">This field is required.</div>
                                        </div>
                                        <input type="hidden" name="address" class="@error('address') has-error @enderror" value="{{ old('address') }}">
                                        @error('address') <div class="col-12"><div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div></div> @enderror
                                    </div>

                                    <div id="wiAdultContact">
                                    <div class="section-label mt-2">Contact Details</div>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Email address <span class="text-muted-2 fw-normal">(optional, for records only)</span></label>
                                            <div class="input-icon @error('email') has-error @enderror"><i class="bi bi-envelope"></i><input type="email" name="email"
                                                    class="form-control" value="{{ old('email') }}" placeholder="name@email.com" /></div>
                                            @error('email') <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Cell/Mobile number <span class="text-danger">*</span></label>
                                            <div class="input-icon @error('phone') has-error @enderror"><i class="bi bi-telephone"></i><input
                                                    class="form-control" name="phone" value="{{ old('phone') }}" placeholder="+63 9XX XXX XXXX" data-wi-required /></div>
                                            @error('phone')
                                                <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div>
                                            @else
                                                <div class="invalid-feedback">This field is required.</div>
                                            @enderror
                                        </div>
                                    </div>
                                    </div>{{-- /wiAdultContact --}}

                                    {{-- Minors (under 18): parent/guardian instead of the patient's own phone/email --}}
                                    <div id="wiMinorSection" hidden>
                                        <div class="section-label mt-2">Parent / Guardian</div>
                                        <p class="text-muted small mb-3">The patient is under 18 based on the birthdate — a parent or guardian's details are recorded instead of the patient's own phone and email.</p>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Parent/Guardian's name <span class="text-danger">*</span></label>
                                                <div class="input-icon @error('guardian_name') has-error @enderror"><i class="bi bi-person-heart"></i><input type="text" name="guardian_name"
                                                        class="form-control" value="{{ old('guardian_name') }}" placeholder="Guardian's name" data-wi-minor-required /></div>
                                                @error('guardian_name')
                                                    <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div>
                                                @else
                                                    <div class="invalid-feedback">This field is required.</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Occupation <span class="text-muted-2 fw-normal">(optional)</span></label>
                                                <div class="input-icon @error('guardian_occupation') has-error @enderror"><i class="bi bi-briefcase"></i><input type="text" name="guardian_occupation"
                                                        class="form-control" value="{{ old('guardian_occupation') }}" placeholder="Occupation" /></div>
                                                @error('guardian_occupation') <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div> @enderror
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Contact number <span class="text-danger">*</span></label>
                                                <div class="input-icon @error('guardian_phone') has-error @enderror"><i class="bi bi-telephone"></i><input type="text" name="guardian_phone"
                                                        class="form-control" value="{{ old('guardian_phone') }}" placeholder="09XX XXX XXXX" data-wi-minor-required /></div>
                                                @error('guardian_phone')
                                                    <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div>
                                                @else
                                                    <div class="invalid-feedback">This field is required.</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Email address <span class="text-muted-2 fw-normal">(optional, for records only)</span></label>
                                                <div class="input-icon @error('guardian_email') has-error @enderror"><i class="bi bi-envelope"></i><input type="email" name="guardian_email"
                                                        class="form-control" value="{{ old('guardian_email') }}" placeholder="name@email.com" /></div>
                                                @error('guardian_email') <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div> @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="text-end">
                                <button type="button" class="btn btn-brand" id="toStep2Btn">{{ $mode === 'followup' ? 'Continue to Appointment' : 'Continue to Services' }}</button>
                            </div>
                        </div>

                        <!-- ===================== STEP 2: APPOINTMENT DETAILS ===================== -->
                        <div class="wizard-step" data-step="2" hidden>
                            @if ($mode === 'walkin')
                                {{-- Walk-in: no calendar. The visit starts at the half-hour slot the
                                     current time falls inside and runs for the services' duration. --}}
                                <div class="appointment-card mb-4">
                                    <div class="section-label mb-3">Dentist</div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <select id="wiWalkinDentist" class="form-select">
                                                @foreach ($bookDentists as $dn)
                                                    <option value="{{ $dn->UserID }}" @selected((int) $bookSelectedDentistId === (int) $dn->UserID)>{{ $dn->display_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="section-label mb-3">Services <span class="text-danger">*</span></div>
                                    <div class="row g-2">
                                        @foreach ($services as $svc)
                                            <div class="col-md-6">
                                                <label class="d-flex align-items-center gap-2 p-2 border rounded-3" style="cursor:pointer;">
                                                    <input type="checkbox" class="form-check-input wk-service-option m-0" value="{{ $svc->ServiceID }}"
                                                        data-name="{{ $svc->ServiceName }}" data-duration="{{ $svc->DurationMinutes }}">
                                                    <span class="flex-grow-1">{{ $svc->ServiceName }}</span>
                                                    <span class="small text-muted-2">{{ $svc->duration_label }}</span>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="text-danger small mt-2" id="wkServiceError" hidden>Select at least one service.</div>

                                    <div class="alert alert-success mt-3 mb-0 py-2 px-3" id="wkTimePreview">Pick the services to see the visit time.</div>
                                    <div class="form-text mt-2">The visit is booked automatically when you continue: it starts in the current half-hour slot (e.g. 11:12 AM → 11:00 AM) and lasts the total service duration, skipping the lunch break. If the dentist is busy right now, the next open time today is used.</div>
                                </div>
                            @endif

                            @if ($mode === 'followup')
                            @include('partials.booking-calendar', [
                                'calendarMode' => 'select',
                                'bookBaseUrl' => $mode === 'followup' ? route('followUp') : route('walkIn'),
                                'bookWeeks' => $bookWeeks,
                                'bookCurrent' => $bookCurrent,
                                'bookSchedules' => $bookSchedules,
                                'bookOccupiedSlots' => $bookOccupiedSlots,
                                'bookSlots' => $bookSlots,
                                'bookToday' => $bookToday,
                                'services' => $services,
                                'bookCurrentPatientId' => null,
                                'bookDentists' => $bookDentists,
                                'bookSelectedDentist' => $bookSelectedDentist,
                                'bookSelectedDentistId' => $bookSelectedDentistId,
                            ])
                            @endif


                            <div class="appointment-card mb-4 mt-4 {{ $mode === 'walkin' ? 'd-none' : '' }}">
                                <div class="section-label mb-3">Appointment Details</div>
                                <div class="form-text mb-3">Tip: click an open date above, then pick the service and time slot right there — just like online booking.</div>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Selected Service(s) <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="wi_service_display"
                                            placeholder="Pick service(s) from the calendar above" readonly>
                                        <div class="invalid-feedback">Select at least one service from the calendar above.</div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Select Day <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="wi_date_display"
                                            placeholder="Pick a date from the calendar above" readonly>
                                        <div class="invalid-feedback">Pick a date from the calendar above.</div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Choose Time of Visit <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="wi_time_display"
                                            placeholder="Pick a time from the calendar above" readonly>
                                        <div class="invalid-feedback">Pick a time from the calendar above.</div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between">
                                <button type="button" class="btn btn-ghost" id="backToStep1Btn">Back</button>
                                <button type="button" class="btn btn-brand" id="toStep3Btn">Continue to Review</button>
                            </div>
                        </div>

                        <!-- ===================== STEP 3: REVIEW & CONFIRM ===================== -->
                        <div class="wizard-step" data-step="3" hidden>
                            <div class="appointment-card mb-4">
                                <div class="section-label mb-3">Patient</div>
                                <div class="review-row"><span class="text-muted-2">Name</span><span class="fw-semibold" id="review_patient_name">—</span></div>
                                <div class="review-row"><span class="text-muted-2">Patient Type</span><span class="fw-semibold" id="review_patient_type">—</span></div>
                                <div class="review-row" id="review_patient_id_row"><span class="text-muted-2">Patient ID</span><span class="fw-semibold" id="review_patient_id">—</span></div>

                                <div class="section-label mb-3 mt-4">Appointment</div>
                                <div class="review-row"><span class="text-muted-2">Dentist</span><span class="fw-semibold" id="review_dentist">—</span></div>
                                <div class="review-row"><span class="text-muted-2">Service(s)</span><span class="fw-semibold" id="review_service">—</span></div>
                                <div class="review-row"><span class="text-muted-2">Date</span><span class="fw-semibold" id="review_date">—</span></div>
                                <div class="review-row"><span class="text-muted-2">Time</span><span class="fw-semibold" id="review_time">—</span></div>
                                <div class="review-row"><span class="text-muted-2">Duration</span><span class="fw-semibold" id="review_duration">—</span></div>
                                <div class="review-row"><span class="text-muted-2">Appointment Type</span><span class="fw-semibold">{{ $mode === 'followup' ? 'Follow-up appointment' : 'Walk-in' }}</span></div>
                            </div>

                            <div class="d-flex justify-content-between">
                                <button type="button" class="btn btn-ghost" id="backToStep2Btn">Back</button>
                                <button type="submit" class="btn btn-brand">
                                    <i class="fa-regular fa-paper-plane me-2"></i>{{ $mode === 'followup' ? 'Confirm Follow-up Appointment' : 'Confirm Walk-in' }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    @include('partials.admin-notif-modal')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/field-restrictions.js') }}"></script>
    <script src="{{ asset('js/address-sync.js') }}"></script>
    <script src="{{ asset('js/birthdate-age.js') }}"></script>
    <script>
    (function () {
        const form = document.getElementById('walkinForm');
        const steps = Array.from(document.querySelectorAll('.wizard-step'));
        const stepBadges = Array.from(document.querySelectorAll('[data-step-badge]'));

        function showStep(stepNum) {
            steps.forEach(s => { s.hidden = s.dataset.step !== String(stepNum); });
            stepBadges.forEach(b => { b.classList.toggle('is-inactive', b.dataset.stepBadge !== String(stepNum)); });
            window.scrollTo({ top: form.offsetTop - 100, behavior: 'smooth' });
            saveDraft();
        }

        // ---------- Step 1: patient source / tabs ----------
        const patientSourceInput = document.getElementById('patient_source');
        const existingTab = document.getElementById('existing-tab');
        const newTab = document.getElementById('new-tab');
        existingTab.addEventListener('shown.bs.tab', () => { patientSourceInput.value = 'existing'; saveDraft(); });
        newTab.addEventListener('shown.bs.tab', () => { patientSourceInput.value = 'new'; saveDraft(); });

        // ---------- Existing patient search ----------
        const searchRow = document.getElementById('patientSearchRow');
        const searchInput = document.getElementById('patientSearchInput');
        const searchResults = document.getElementById('patientSearchResults');
        const loadPatientBtn = document.getElementById('loadPatientBtn');
        const clearPatientBtn = document.getElementById('clearPatientBtn');
        const patientIdInput = document.getElementById('patient_id');
        const loadedPatientCard = document.getElementById('loadedPatientCard');
        const noPatientLoadedNotice = document.getElementById('noPatientLoadedNotice');
        const loadedPatientName = document.getElementById('loadedPatientName');
        const loadedPatientId = document.getElementById('loadedPatientId');

        const displayFields = {
            last_name: document.getElementById('existing_last_name'),
            first_name: document.getElementById('existing_first_name'),
            middle_name: document.getElementById('existing_middle_name'),
            birthdate: document.getElementById('existing_birthdate'),
            address: document.getElementById('existing_address'),
            email: document.getElementById('existing_email'),
            phone: document.getElementById('existing_phone'),
        };
        const existingGenderSelect = document.getElementById('existing_gender');

        // Applies a loaded patient's data to the display fields and locks the
        // search row so a second patient can't be picked without explicitly
        // clearing the first one. Also used by restoreDraft() after a page
        // refresh, which is why it takes a plain data object rather than
        // reading straight off the search results.
        function applyLoadedPatient(p) {
            patientIdInput.value = p.PatientID;
            loadedPatientName.textContent = `${p.FirstName} ${p.LastName}`;
            loadedPatientId.textContent = p.PatientID;
            loadedPatientCard.style.display = 'flex';
            noPatientLoadedNotice.style.display = 'none';

            displayFields.last_name.value = p.LastName || '';
            displayFields.first_name.value = p.FirstName || '';
            displayFields.middle_name.value = p.MiddleName || '';
            displayFields.birthdate.value = p.DateOfBirth || '';
            displayFields.birthdate.dispatchEvent(new Event('change'));
            displayFields.address.value = p.Address || '';
            displayFields.email.value = p.Email || '';
            displayFields.phone.value = p.PhoneNumber || '';
            existingGenderSelect.innerHTML = '<option selected>' + (p.Gender || '—') + '</option>';

            searchRow.hidden = true;
            searchResults.classList.add('d-none');
            searchResults.innerHTML = '';
            searchInput.classList.remove('is-invalid');
            document.getElementById('patientSearchFeedback').classList.remove('d-block');
        }

        function clearLoadedPatient() {
            patientIdInput.value = '';
            loadedPatientCard.style.display = 'none';
            noPatientLoadedNotice.style.display = '';

            Object.values(displayFields).forEach(el => { el.value = ''; });
            existingGenderSelect.innerHTML = '<option selected>—</option>';

            searchRow.hidden = false;
            searchInput.value = '';
            searchResults.classList.add('d-none');
            searchResults.innerHTML = '';
            saveDraft();
        }

        clearPatientBtn.addEventListener('click', clearLoadedPatient);

        function runSearch() {
            const q = searchInput.value.trim();
            if (!q) { searchResults.classList.add('d-none'); searchResults.innerHTML = ''; return; }

            fetch('{{ route("walkIn.search") }}?q=' + encodeURIComponent(q))
                .then(r => r.json())
                .then(data => {
                    if (!Array.isArray(data) || data.length === 0) {
                        searchResults.innerHTML = '<div class="search-result-item text-muted-2">No patients found.</div>';
                        searchResults.classList.remove('d-none');
                        return;
                    }
                    searchResults.innerHTML = data.map((p, i) => `
                        <div class="search-result-item" data-idx="${i}">
                            <div class="fw-semibold">${p.FirstName} ${p.LastName}</div>
                            <div class="small text-muted-2">Patient ID: ${p.PatientID}</div>
                        </div>
                    `).join('');
                    searchResults.dataset.results = JSON.stringify(data);
                    searchResults.classList.remove('d-none');
                })
                .catch(() => {
                    searchResults.innerHTML = '<div class="search-result-item text-danger">Search failed. Please try again.</div>';
                    searchResults.classList.remove('d-none');
                });
        }

        loadPatientBtn.addEventListener('click', runSearch);
        searchInput.addEventListener('keydown', (e) => { if (e.key === 'Enter') { e.preventDefault(); runSearch(); } });

        searchResults.addEventListener('click', (e) => {
            const item = e.target.closest('.search-result-item[data-idx]');
            if (!item) return;
            const results = JSON.parse(searchResults.dataset.results || '[]');
            const p = results[parseInt(item.dataset.idx, 10)];
            if (!p) return;

            applyLoadedPatient(p);
            saveDraft();
        });

        // ---------- Continue to Step 2 ----------
        // Note: none of the wizard's internal fields use the native `required` attribute.
        // Everything lives inside one big <form>, and a `required` field left inside a
        // closed Bootstrap tab-pane or modal (display:none) still silently blocks that
        // form's native submit in this browser — so every required check here is manual.
        const searchFeedback = document.getElementById('patientSearchFeedback');

        searchInput.addEventListener('input', () => {
            searchInput.classList.remove('is-invalid');
            searchFeedback.classList.remove('d-block');
        });

        document.getElementById('new-pane').addEventListener('input', (e) => {
            e.target.classList?.remove('is-invalid');
        });
        document.getElementById('new-pane').addEventListener('change', (e) => {
            e.target.classList?.remove('is-invalid');
        });

        document.getElementById('toStep2Btn').addEventListener('click', () => {
            if (patientSourceInput.value === 'existing') {
                if (!patientIdInput.value) {
                    searchInput.classList.add('is-invalid');
                    searchFeedback.classList.add('d-block');
                    searchInput.focus();
                    return;
                }
            } else {
                const newPane = document.getElementById('new-pane');
                const requiredFields = Array.from(newPane.querySelectorAll('[data-wi-required]'));
                requiredFields.forEach(el => el.classList.remove('is-invalid'));
                const missing = requiredFields.filter(el => !el.value);
                if (missing.length) {
                    missing.forEach(el => el.classList.add('is-invalid'));
                    missing[0].focus();
                    return;
                }
            }
            showStep(2);
        });

        // ---------- Step 2: calendar slot selection (mirrors the online booking modal) ----------
        const wiDateInput = document.getElementById('wi_date');
        const wiTimeInput = document.getElementById('wi_time');
        const wiServiceIdsContainer = document.getElementById('wi_service_ids_container');
        const wiDateDisplay = document.getElementById('wi_date_display');
        const wiTimeDisplay = document.getElementById('wi_time_display');
        const wiServiceDisplay = document.getElementById('wi_service_display');
        let wiServiceIds = [];
        let wiTotalMinutes = 0;

        function renderWiServiceInputs() {
            wiServiceIdsContainer.innerHTML = '';
            wiServiceIds.forEach((id) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'service_ids[]';
                input.value = id;
                wiServiceIdsContainer.appendChild(input);
            });
        }

        function updateWiServiceToggleLabel(wrapper) {
            const text = wrapper.querySelector('.wi-slot-service-toggle-text');
            const checked = wrapper.querySelectorAll('.wi-slot-service-option:checked');
            text.textContent = checked.length
                ? Array.from(checked).map((c) => c.dataset.name).join(', ')
                : 'Select services';
        }

        // Clinic slot grid, mirrors DentistSchedule on the server — used to
        // preview the actual end time (skipping the lunch-hour gap).
        const SLOT_TIMES = @json(\App\Models\DentistSchedule::slotTimes());
        const SLOT_MINUTES = {{ \App\Models\DentistSchedule::SLOT_MINUTES }};

        function formatTime12h(hours, minutes) {
            const period = hours >= 12 ? 'PM' : 'AM';
            const hour12 = hours % 12 || 12;
            return hour12 + ':' + String(minutes).padStart(2, '0') + ' ' + period;
        }

        function computeEndTimeLabel(startTime, totalMinutes) {
            const slotsNeeded = Math.max(1, Math.ceil(totalMinutes / SLOT_MINUTES));
            const startIndex = SLOT_TIMES.indexOf(startTime);
            if (startIndex === -1) return null;
            const lastIndex = startIndex + slotsNeeded - 1;
            if (lastIndex >= SLOT_TIMES.length) return null;
            const lastSlot = SLOT_TIMES[lastIndex].split(':').map(Number);
            const endMinutesTotal = lastSlot[0] * 60 + lastSlot[1] + SLOT_MINUTES;
            return formatTime12h(Math.floor(endMinutesTotal / 60), endMinutesTotal % 60);
        }

        // "1 hour 30 minutes" / "30 minutes" — mirrors DentistSchedule::formatSlotDuration().
        function formatDurationLabel(totalMinutes) {
            const hours = Math.floor(totalMinutes / 60);
            const minutes = totalMinutes % 60;
            const parts = [];
            if (hours > 0) parts.push(hours + ' hour' + (hours > 1 ? 's' : ''));
            if (minutes > 0) parts.push(minutes + ' minute' + (minutes > 1 ? 's' : ''));
            return parts.length ? parts.join(' ') : '0 minutes';
        }

        document.addEventListener('change', (e) => {
            if (e.target.matches('.wi-slot-service-option')) {
                const wrapper = e.target.closest('.wi-slot-service');
                updateWiServiceToggleLabel(wrapper);
                wrapper.querySelector('.wi-slot-service-toggle').classList.remove('is-invalid');
            }
        });

        document.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-wi-date][data-wi-time]');
            if (!btn) return;

            const slotRow = btn.closest('.d-flex');
            const wrapper = slotRow ? slotRow.querySelector('.wi-slot-service') : null;
            const checked = wrapper ? Array.from(wrapper.querySelectorAll('.wi-slot-service-option:checked')) : [];

            if (!checked.length) {
                if (wrapper) wrapper.querySelector('.wi-slot-service-toggle').classList.add('is-invalid');
                return;
            }
            wrapper.querySelector('.wi-slot-service-toggle').classList.remove('is-invalid');

            wiDateInput.value = btn.dataset.wiDate;
            wiTimeInput.value = btn.dataset.wiTime;
            wiDateDisplay.value = btn.dataset.wiDateLabel || btn.dataset.wiDate;
            const startLabel = btn.dataset.wiTimeLabel || btn.dataset.wiTime;
            wiTotalMinutes = checked.reduce((sum, c) => sum + (parseInt(c.dataset.duration, 10) || 60), 0);
            const endLabel = computeEndTimeLabel(btn.dataset.wiTime, wiTotalMinutes);
            wiTimeDisplay.value = endLabel ? (startLabel + ' - ' + endLabel) : startLabel;
            [wiServiceDisplay, wiDateDisplay, wiTimeDisplay].forEach(el => el.classList.remove('is-invalid'));

            wiServiceIds = checked.map((c) => c.value);
            wiServiceDisplay.value = checked.map((c) => c.dataset.name).join(', ');
            renderWiServiceInputs();

            const modalEl = btn.closest('.modal');
            const goToReview = () => document.getElementById('toStep3Btn').click();
            if (modalEl && window.bootstrap) {
                const instance = bootstrap.Modal.getInstance(modalEl);
                if (instance) {
                    // Picking a time is the last thing Step 2 needs, so carry
                    // straight on to Review & Confirm — but only once the day
                    // modal has finished closing, or its backdrop would linger
                    // over the next step.
                    modalEl.addEventListener('hidden.bs.modal', goToReview, { once: true });
                    instance.hide();
                } else {
                    goToReview();
                }
            } else {
                goToReview();
            }
            saveDraft();
        });

        // Month + dentist navigation while in select mode (walk-in wizard).
        // A full reload, but saveDraft() keeps the wizard where it was.
        const wiDentistSelect = document.getElementById('wiBookDentist');
        function wiCurrentDentist() {
            return wiDentistSelect ? wiDentistSelect.value
                : (document.getElementById('wiSelectedDentist')?.value || '');
        }
        function wiReloadCalendar(month) {
            saveDraft();
            window.location.href = '{{ $mode === 'followup' ? route('followUp') : route('walkIn') }}?bookMonth=' + month + '&dentist=' + wiCurrentDentist();
        }

        const wiGoBtn = document.getElementById('wiBookMonthGo');
        if (wiGoBtn) {
            wiGoBtn.addEventListener('click', () => {
                const m = String(document.getElementById('wiBookMonthNum').value).padStart(2, '0');
                const y = document.getElementById('wiBookYear').value;
                wiReloadCalendar(y + '-' + m);
            });
        }

        ['wiBookMonthPrev', 'wiBookMonthNext'].forEach((id) => {
            const btn = document.getElementById(id);
            if (!btn) return;
            btn.addEventListener('click', () => wiReloadCalendar(btn.dataset.month));
        });

        if (wiDentistSelect) {
            wiDentistSelect.addEventListener('change', () => {
                const dentistId = document.getElementById('wi_dentist_id');
                if (dentistId) dentistId.value = wiDentistSelect.value;
                wiReloadCalendar('{{ $bookCurrent->format('Y-m') }}');
            });
        }

        // ---------- Minor (under 18): parent/guardian replaces the patient's own phone/email ----------
        const wiBirthdate = document.getElementById('wiBirthdate');
        const wiMinorSection = document.getElementById('wiMinorSection');
        const wiAdultContact = document.getElementById('wiAdultContact');
        function wiIsMinor() {
            if (!wiBirthdate || !wiBirthdate.value) return false;
            const dob = new Date(wiBirthdate.value);
            if (isNaN(dob.getTime())) return false;
            const now = new Date();
            let age = now.getFullYear() - dob.getFullYear();
            const md = now.getMonth() - dob.getMonth();
            if (md < 0 || (md === 0 && now.getDate() < dob.getDate())) age--;
            return age < 18;
        }
        function syncMinor() {
            const minor = wiIsMinor();
            wiMinorSection.hidden = !minor;
            wiAdultContact.hidden = minor;
            // Hidden section's inputs are disabled so stale values never submit,
            // and only the visible section's required fields are enforced.
            wiMinorSection.querySelectorAll('input').forEach((el) => { el.disabled = !minor; });
            wiAdultContact.querySelectorAll('input').forEach((el) => { el.disabled = minor; });
            wiMinorSection.querySelectorAll('[data-wi-minor-required]').forEach((el) => {
                if (minor) el.setAttribute('data-wi-required', ''); else el.removeAttribute('data-wi-required');
            });
            const phone = wiAdultContact.querySelector('[name=phone]');
            if (phone) { if (minor) phone.removeAttribute('data-wi-required'); else phone.setAttribute('data-wi-required', ''); }
        }
        if (wiBirthdate) {
            wiBirthdate.addEventListener('input', syncMinor);
            wiBirthdate.addEventListener('change', syncMinor);
        }
        syncMinor();

        // ---------- Walk-in: services picker (no calendar) ----------
        const IS_WALKIN = {{ $mode === 'walkin' ? 'true' : 'false' }};
        const CLOSED_MSG = @json(\App\Models\DentistSchedule::walkInClosedMessage());
        // The half-hour slot the current time falls inside. null when the clinic
        // is closed (before opening, lunch, after closing) — no walk-in then.
        // Use the SERVER's clock (clinic time), not the browser's — a front-desk
        // PC in another timezone would otherwise preview the wrong slot.
        const SERVER_NOW = {
            minutes: {{ now()->hour * 60 + now()->minute }},
            date: '{{ today()->toDateString() }}',
            dow: {{ now()->dayOfWeek }},
            loadedAt: Date.now(),
        };
        function serverMinutesNow() {
            return (SERVER_NOW.minutes + Math.floor((Date.now() - SERVER_NOW.loadedAt) / 60000)) % 1440;
        }

        function walkinStartTime() {
            const mins = serverMinutesNow();
            for (const t of SLOT_TIMES) {
                const [h, m] = t.split(':').map(Number);
                if (mins >= h * 60 + m && mins < h * 60 + m + SLOT_MINUTES) return t;
            }
            return null;
        }

        function refreshWalkinPreview() {
            if (!IS_WALKIN) return;
            const boxes = Array.from(document.querySelectorAll('.wk-service-option:checked'));
            wiServiceIds = boxes.map((b) => b.value);
            wiTotalMinutes = boxes.reduce((sum, b) => sum + (parseInt(b.dataset.duration, 10) || 60), 0);
            wiServiceDisplay.value = boxes.map((b) => b.dataset.name).join(', ');
            renderWiServiceInputs();

            const preview = document.getElementById('wkTimePreview');
            const setPreview = (cls, html) => { preview.className = 'alert alert-' + cls + ' mt-3 mb-0 py-2 px-3'; preview.innerHTML = html; };
            const today = new Date(SERVER_NOW.date + 'T12:00:00');
            wiDateInput.value = SERVER_NOW.date;
            wiDateDisplay.value = today.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
            wiTimeInput.value = '';
            wiTimeDisplay.value = '';

            if (!boxes.length) { setPreview('success', 'Pick the services to see the visit time.'); return; }
            if (SERVER_NOW.dow === 0 || !walkinStartTime()) { setPreview('danger', CLOSED_MSG); return; }
            const start = walkinStartTime();
            const end = computeEndTimeLabel(start, wiTotalMinutes);
            const [sh, sm] = start.split(':').map(Number);
            const startLabel = formatTime12h(sh, sm);
            if (!end) { setPreview('danger', 'Starting at <strong>' + startLabel + '</strong>, these services (' + formatDurationLabel(wiTotalMinutes) + ') run past closing time. Remove a service or book a follow-up for another day.'); return; }
            wiTimeInput.value = start;
            wiTimeDisplay.value = startLabel + ' - ' + end;
            setPreview('success', '<strong>' + startLabel + ' – ' + end + '</strong> today &middot; ' + formatDurationLabel(wiTotalMinutes));
        }

        function updateWalkinClosed() {
            if (!IS_WALKIN) return;
            const closed = SERVER_NOW.dow === 0 || !walkinStartTime();
            document.getElementById('wkClosedBanner').hidden = !closed;
            document.getElementById('wkClosedText').textContent = CLOSED_MSG;
            document.getElementById('toStep2Btn').disabled = closed;
            const toReview = document.getElementById('toStep3Btn');
            if (toReview) toReview.disabled = closed;
        }
        if (IS_WALKIN) {
            updateWalkinClosed();
            setInterval(updateWalkinClosed, 15000);
        }

        if (IS_WALKIN) {
            document.addEventListener('change', (e) => {
                if (e.target.matches('.wk-service-option')) {
                    document.getElementById('wkServiceError').hidden = true;
                    refreshWalkinPreview();
                }
            });
            const walkinDentist = document.getElementById('wiWalkinDentist');
            if (walkinDentist) {
                document.getElementById('wi_dentist_id').value = walkinDentist.value;
                walkinDentist.addEventListener('change', () => { document.getElementById('wi_dentist_id').value = walkinDentist.value; });
            }
        }

        // ---------- Continue to Step 3 ----------
        // Extracted so restoreDraft() can rebuild the same summary after a page
        // refresh — previously the review text was only ever built inside the
        // "Continue to Review" click handler, so reloading while on Step 3
        // left the whole summary showing blank "—" placeholders even though
        // the underlying hidden fields (and the eventual submit) were fine.
        function buildReviewSummary() {
            const isExisting = patientSourceInput.value === 'existing';
            const patientName = isExisting
                ? loadedPatientName.textContent
                : `${document.querySelector('[name=first_name]').value} ${document.querySelector('[name=last_name]').value}`.trim();

            document.getElementById('review_patient_name').textContent = patientName || '—';
            document.getElementById('review_patient_type').textContent = isExisting ? 'Existing patient (Online)' : 'Walk-in patient';
            document.getElementById('review_patient_id_row').style.display = isExisting ? 'flex' : 'none';
            document.getElementById('review_patient_id').textContent = isExisting ? patientIdInput.value : '—';
            if (IS_WALKIN) refreshWalkinPreview();
            const dentistSel = document.getElementById('wiBookDentist') || document.getElementById('wiWalkinDentist');
            document.getElementById('review_dentist').textContent =
                dentistSel ? (dentistSel.options[dentistSel.selectedIndex]?.text || '—') : '—';
            document.getElementById('review_service').textContent = wiServiceDisplay.value;
            document.getElementById('review_date').textContent = wiDateDisplay.value;
            document.getElementById('review_time').textContent = wiTimeDisplay.value;
            document.getElementById('review_duration').textContent = wiTotalMinutes ? formatDurationLabel(wiTotalMinutes) : '—';
        }

        document.getElementById('toStep3Btn').addEventListener('click', () => {
            if (IS_WALKIN) {
                refreshWalkinPreview();
                if (!wiServiceIds.length) { document.getElementById('wkServiceError').hidden = false; return; }
                if (!wiTimeInput.value) return; // the preview already explains why (closed / too long)
            }            [wiServiceDisplay, wiDateDisplay, wiTimeDisplay].forEach(el => el.classList.remove('is-invalid'));

            let firstInvalid = null;
            if (!wiServiceIds.length) firstInvalid = firstInvalid || wiServiceDisplay;
            if (!wiDateInput.value) firstInvalid = firstInvalid || wiDateDisplay;
            if (!wiTimeInput.value) firstInvalid = firstInvalid || wiTimeDisplay;

            if (firstInvalid) {
                if (!wiServiceIds.length) wiServiceDisplay.classList.add('is-invalid');
                if (!wiDateInput.value) wiDateDisplay.classList.add('is-invalid');
                if (!wiTimeInput.value) wiTimeDisplay.classList.add('is-invalid');
                return;
            }

            buildReviewSummary();
            showStep(3);
        });

        // ---------- Back buttons ----------
        document.getElementById('backToStep1Btn').addEventListener('click', () => showStep(1));
        document.getElementById('backToStep2Btn').addEventListener('click', () => showStep(2));

        // ---------- Draft persistence so the calendar's month-nav reload doesn't lose progress ----------
        const DRAFT_KEY = 'walkinDraft_{{ $mode }}';
        const PATIENT_SOURCE = '{{ $mode === 'followup' ? 'existing' : 'new' }}';

        function saveDraft() {
            const activeStep = steps.find(s => !s.hidden);
            const isPatientLoaded = loadedPatientCard.style.display !== 'none';
            const draft = {
                step: activeStep ? activeStep.dataset.step : '1',
                patientSource: patientSourceInput.value,
                patientId: patientIdInput.value,
                // Store the whole loaded patient, not just the name/id — the display
                // textboxes are disabled inputs and don't submit with the form, so
                // without this a page refresh showed the loaded-patient card but
                // left every field underneath it blank.
                existingPatient: isPatientLoaded ? {
                    PatientID: patientIdInput.value,
                    FirstName: displayFields.first_name.value,
                    LastName: displayFields.last_name.value,
                    MiddleName: displayFields.middle_name.value,
                    DateOfBirth: displayFields.birthdate.value,
                    Gender: existingGenderSelect.value,
                    Address: displayFields.address.value,
                    Email: displayFields.email.value,
                    PhoneNumber: displayFields.phone.value,
                } : null,
                newFields: {
                    last_name: document.querySelector('[name=last_name]').value,
                    first_name: document.querySelector('[name=first_name]').value,
                    middle_name: document.querySelector('[name=middle_name]').value,
                    birthdate: document.querySelector('[name=birthdate]').value,
                    gender: document.querySelector('[name=gender]').value,
                    address: document.querySelector('[name=address]').value,
                    email: document.querySelector('[name=email]').value,
                    phone: document.querySelector('[name=phone]').value,
                    guardian_name: document.querySelector('[name=guardian_name]').value,
                    guardian_occupation: document.querySelector('[name=guardian_occupation]').value,
                    guardian_phone: document.querySelector('[name=guardian_phone]').value,
                    guardian_email: document.querySelector('[name=guardian_email]').value,
                },
                serviceIds: wiServiceIds,
                serviceLabel: wiServiceDisplay.value,
                totalMinutes: wiTotalMinutes,
                date: wiDateInput.value,
                time: wiTimeInput.value,
                dateLabel: wiDateDisplay.value,
                timeLabel: wiTimeDisplay.value,
            };
            try { sessionStorage.setItem(DRAFT_KEY, JSON.stringify(draft)); } catch (e) {}
        }

        function restoreDraft() {
            let draft;
            try { draft = JSON.parse(sessionStorage.getItem(DRAFT_KEY) || 'null'); } catch (e) { draft = null; }
            if (!draft) return;

            patientSourceInput.value = PATIENT_SOURCE;

            if (draft.existingPatient) {
                applyLoadedPatient(draft.existingPatient);
            }

            if (draft.newFields) {
                Object.entries(draft.newFields).forEach(([key, value]) => {
                    const el = document.querySelector(`[name=${key}]`);
                    if (el && value) el.value = value;
                });
                syncMinor();
            }

            if (draft.serviceIds && draft.serviceIds.length) {
                wiServiceIds = draft.serviceIds;
                renderWiServiceInputs();
            }
            if (draft.serviceLabel) wiServiceDisplay.value = draft.serviceLabel;
            if (draft.totalMinutes) wiTotalMinutes = draft.totalMinutes;
            if (draft.date) wiDateInput.value = draft.date;
            if (draft.time) wiTimeInput.value = draft.time;
            if (draft.dateLabel) wiDateDisplay.value = draft.dateLabel;
            if (draft.timeLabel) wiTimeDisplay.value = draft.timeLabel;
            if (IS_WALKIN) {
                document.querySelectorAll('.wk-service-option').forEach((b) => { b.checked = (draft.serviceIds || []).includes(b.value); });
                refreshWalkinPreview();
            }
            if (draft.step === '3') {
                buildReviewSummary();
            }
            if (draft.step && draft.step !== '1') {
                showStep(parseInt(draft.step, 10));
            }
        }

        form.addEventListener('input', saveDraft);
        form.addEventListener('change', saveDraft);

        // A successful confirm redirects to Appointment Approval (not back to this
        // page), so this script never runs again to know it worked — mark the draft
        // "submitted" instead of deleting it outright, so a validation/business-logic
        // failure (which DOES redirect back here) still has everything to restore.
        form.addEventListener('submit', function () {
            // Guards against a double-click firing two submissions — the second
            // would silently fail server-side (slot/patient already taken) with
            // no obvious feedback since the page is already mid-navigation.
            const submitBtn = form.querySelector('button[type=submit]');
            if (submitBtn && !submitBtn.disabled) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Booking…';
            }

            saveDraft();
            try {
                const raw = sessionStorage.getItem(DRAFT_KEY);
                if (raw) {
                    const draft = JSON.parse(raw);
                    draft.submitted = true;
                    sessionStorage.setItem(DRAFT_KEY, JSON.stringify(draft));
                }
            } catch (e) {}
        });

        // this request's response to a failed submit (old input echoed back) —
        // vs. a fresh visit or a mid-wizard reload (month nav), which aren't.
        const cameFromFailedSubmit = {{ session()->hasOldInput() ? 'true' : 'false' }};
        const errorStep = {{ session('walkin_error_step') ? (int) session('walkin_error_step') : 'null' }};

        let existingDraft = null;
        try { existingDraft = JSON.parse(sessionStorage.getItem(DRAFT_KEY) || 'null'); } catch (e) {}

        if (existingDraft && existingDraft.submitted && !cameFromFailedSubmit) {
            // The last submit from this tab went through — this draft is stale.
            try { sessionStorage.removeItem(DRAFT_KEY); } catch (e) {}
        } else if (existingDraft) {
            restoreDraft();
            if (cameFromFailedSubmit && errorStep) {
                // The error is about the appointment details, not who the
                // patient is — jump straight there instead of wherever the
                // draft's own step happened to be.
                showStep(errorStep);
            }
        }
    })();
    </script>
</body>

</html>
