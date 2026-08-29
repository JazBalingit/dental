<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Walk-in Appointment • Dental Clinic</title>
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
                <a href="{{ route('walkIn') }}"  class="active"><i class="bi bi-calendar3"></i> Walk-in Appointments</a>
                <a href="{{ route('appointmentApproval') }}"><i class="bi bi-clipboard2-check"></i> Appointment
                    Approval</a>
                <a href="{{ route('appointments') }}"><i class="bi bi-clipboard2-check"></i> Appointments</a>
                <a href="{{ route('patientRecords') }}"><i class="bi bi-folder2-open"></i> Patient Records</a>
                <div class="nav-section">System</div>
                <a href="{{ route('configuration') }}"><i class="bi bi-sliders2"></i> Configuration</a>
            </nav>
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
                        <h2>Walk-in Appointment</h2>
                        <div class="crumbs">Register a walk-in patient and book them into an open slot.</div>
                    </div>
                </div>

                @include('partials.flash-toasts')

                <!-- ===================== WIZARD STEP INDICATOR ===================== -->
                <div class="wizard-steps">
                    <span class="wizard-step-badge" data-step-badge="1">1. Patient Information</span>
                    <span class="wizard-step-sep"><i class="bi bi-chevron-right"></i></span>
                    <span class="wizard-step-badge is-inactive" data-step-badge="2">2. Appointment Details</span>
                    <span class="wizard-step-sep"><i class="bi bi-chevron-right"></i></span>
                    <span class="wizard-step-badge is-inactive" data-step-badge="3">3. Review &amp; Confirm</span>
                </div>

                <!--  =================== WALK IN ======================= -->
                <div class="card-soft p-3 p-md-4">
                    <form id="walkinForm" method="POST" action="{{ route('walkIn.store') }}">
                        @csrf
                        <input type="hidden" name="patient_source" id="patient_source" value="existing">
                        <input type="hidden" name="patient_id" id="patient_id" value="">
                        <input type="hidden" name="date" id="wi_date" value="">
                        <input type="hidden" name="time" id="wi_time" value="">
                        <input type="hidden" name="dentist_id" id="wi_dentist_id" value="{{ $bookSelectedDentistId }}">
                        <div id="wi_service_ids_container"></div>

                        <!-- ===================== STEP 1: PATIENT INFORMATION ===================== -->
                        <div class="wizard-step" data-step="1">
                            <ul class="nav nav-tabs patient-tabs" id="patientTab" role="tablist">
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
                                <div class="tab-pane fade show active" id="existing-pane" role="tabpanel">
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

                                        <div class="col-md-6">
                                            <label class="form-label">Birthdate</label>
                                            <div class="input-icon"><i class="bi bi-calendar-event"></i><input type="date"
                                                    class="form-control" id="existing_birthdate" disabled /></div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Gender</label>
                                            <div class="input-icon">
                                                <select class="form-select" id="existing_gender" disabled>
                                                    <option selected>—</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
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
                                <div class="tab-pane fade" id="new-pane" role="tabpanel">
                                    <div class="section-label">Personal Information</div>
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-4">
                                            <label class="form-label">Last name <span class="text-danger">*</span></label>
                                            <div class="input-icon"><i class="bi bi-person"></i><input type="text" name="last_name"
                                                    class="form-control" placeholder="Dela Cruz" data-wi-required /></div>
                                            <div class="invalid-feedback">This field is required.</div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">First name <span class="text-danger">*</span></label>
                                            <div class="input-icon"><i class="bi bi-person"></i><input type="text" name="first_name"
                                                    class="form-control" placeholder="Maria" data-wi-required /></div>
                                            <div class="invalid-feedback">This field is required.</div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Middle name</label>
                                            <div class="input-icon"><i class="bi bi-person"></i><input type="text" name="middle_name"
                                                    class="form-control" placeholder="Reyes" /></div>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Birthdate <span class="text-danger">*</span></label>
                                            <div class="input-icon"><i class="bi bi-calendar-event"></i><input type="date" name="birthdate"
                                                    class="form-control" data-wi-required /></div>
                                            <div class="invalid-feedback">This field is required.</div>
                                            <div class="form-text">Age is computed automatically from the birthdate.</div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Gender <span class="text-danger">*</span></label>
                                            <div class="input-icon">
                                                <select class="form-select" name="gender" data-wi-required>
                                                    <option selected disabled value="">Select gender</option>
                                                    <option>Male</option>
                                                    <option>Female</option>
                                                </select>
                                            </div>
                                            <div class="invalid-feedback">This field is required.</div>
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label">Home address <span class="text-danger">*</span></label>
                                            <div class="input-icon"><i class="bi bi-geo-alt"></i><input class="form-control" name="address"
                                                    placeholder="Street, Barangay, San Pedro, Laguna" data-wi-required /></div>
                                            <div class="invalid-feedback">This field is required.</div>
                                        </div>
                                    </div>

                                    <div class="section-label mt-2">Contact Details</div>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Email address</label>
                                            <div class="input-icon"><i class="bi bi-envelope"></i><input type="email" name="email"
                                                    class="form-control" placeholder="name@email.com" /></div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Cell/Mobile number <span class="text-danger">*</span></label>
                                            <div class="input-icon"><i class="bi bi-telephone"></i><input
                                                    class="form-control" name="phone" placeholder="+63 9XX XXX XXXX" data-wi-required /></div>
                                            <div class="invalid-feedback">This field is required.</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="text-end">
                                <button type="button" class="btn btn-brand" id="toStep2Btn">Continue to Appointment</button>
                            </div>
                        </div>

                        <!-- ===================== STEP 2: APPOINTMENT DETAILS ===================== -->
                        <div class="wizard-step" data-step="2" hidden>
                            @include('partials.booking-calendar', [
                                'calendarMode' => 'select',
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


                            <div class="appointment-card mb-4 mt-4">
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
                                <div class="review-row"><span class="text-muted-2">Appointment Type</span><span class="fw-semibold">Walk-in</span></div>
                            </div>

                            <div class="d-flex justify-content-between">
                                <button type="button" class="btn btn-ghost" id="backToStep2Btn">Back</button>
                                <button type="submit" class="btn btn-brand">
                                    <i class="fa-regular fa-paper-plane me-2"></i>Confirm Walk-in Appointment
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
            if (modalEl && window.bootstrap) {
                const instance = bootstrap.Modal.getInstance(modalEl);
                if (instance) instance.hide();
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
            window.location.href = '{{ route("walkIn") }}?bookMonth=' + month + '&dentist=' + wiCurrentDentist();
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
            document.getElementById('review_patient_type').textContent = isExisting ? 'Existing Patient' : 'New Patient';
            document.getElementById('review_patient_id_row').style.display = isExisting ? 'flex' : 'none';
            document.getElementById('review_patient_id').textContent = isExisting ? patientIdInput.value : '—';
            const dentistSel = document.getElementById('wiBookDentist');
            document.getElementById('review_dentist').textContent =
                dentistSel ? (dentistSel.options[dentistSel.selectedIndex]?.text || '—') : '—';
            document.getElementById('review_service').textContent = wiServiceDisplay.value;
            document.getElementById('review_date').textContent = wiDateDisplay.value;
            document.getElementById('review_time').textContent = wiTimeDisplay.value;
            document.getElementById('review_duration').textContent = wiTotalMinutes ? formatDurationLabel(wiTotalMinutes) : '—';
        }

        document.getElementById('toStep3Btn').addEventListener('click', () => {
            [wiServiceDisplay, wiDateDisplay, wiTimeDisplay].forEach(el => el.classList.remove('is-invalid'));

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
        const DRAFT_KEY = 'walkinDraft';

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

            patientSourceInput.value = draft.patientSource || 'existing';
            if (draft.patientSource === 'new' && window.bootstrap) {
                new bootstrap.Tab(newTab).show();
            }

            if (draft.existingPatient) {
                applyLoadedPatient(draft.existingPatient);
            }

            if (draft.newFields) {
                Object.entries(draft.newFields).forEach(([key, value]) => {
                    const el = document.querySelector(`[name=${key}]`);
                    if (el && value) el.value = value;
                });
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
