<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>AppointmentApproval • Dental Clinic</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
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
            @include('partials.admin-sidebar-nav', ['active' => 'appointmentApproval'])
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
                        <h2>Appointments Approval</h2>
                        <div class="crumbs">Review and approve pending appointment requests.</div>
                    </div>
                </div>

                @include('partials.flash-toasts')

                <!-- mini stats -->
                <div class="row g-3 mb-3">
                    <div class="col-6 col-md-3">
                        <div class="stat-card">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="label">Today</div>
                                    <div class="value">{{ $stats['today'] }}</div>
                                </div>
                                <div class="icon"><i class="bi bi-calendar-day"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stat-card alt-1">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="label">Approved</div>
                                    <div class="value">{{ $stats['approved'] }}</div>
                                </div>
                                <div class="icon"><i class="bi bi-check2-circle"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stat-card alt-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="label">Pending</div>
                                    <div class="value">{{ $stats['pending'] }}</div>
                                </div>
                                <div class="icon"><i class="bi bi-hourglass"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stat-card alt-2">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="label">Declined</div>
                                    <div class="value">{{ $stats['declined'] }}</div>
                                </div>
                                <div class="icon"><i class="bi bi-x-circle"></i></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-soft p-3 p-md-4">
                    <form method="GET" action="{{ route('appointmentApproval') }}" class="data-toolbar">
                        <div class="left d-flex align-items-center gap-2">
                            <span class="text-muted-2 small">Filter</span>
                            <select class="form-select" name="status" style="min-width:140px;">
                                <option value="" {{ !$status ? 'selected' : '' }}>All Status</option>
                                <option value="Pending" {{ $status === 'Pending' ? 'selected' : '' }}>Pending</option>
                                <option value="Approved" {{ $status === 'Approved' ? 'selected' : '' }}>Booked</option>
                                <option value="Declined" {{ $status === 'Declined' ? 'selected' : '' }}>Declined</option>
                                <option value="Completed" {{ $status === 'Completed' ? 'selected' : '' }}>Completed</option>
                            </select>
                        </div>
                        <div class="right d-flex gap-2">
                            <div class="input-icon search">
                                <i class="bi bi-search"></i>
                                <input class="form-control" name="search" value="{{ $search }}"
                                    placeholder="Search patient or service..."
                                    style="height:40px; padding-left:2.4rem;" />
                            </div>
                            <button type="submit" class="btn btn-brand">Apply</button>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table-soft">
                            <thead>
                                <tr>
                                    <th>Patient</th>
                                    <th>Dentist</th>
                                    <th>Service</th>
                                    <th>Date &amp; Time</th>
                                    <th>Booked At</th>
                                    <th>Status</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($appointments as $appt)
                                    @php
                                        $p = $appt->patientInfo;
                                        $pillClass = match ($appt->Status) {
                                            'Approved' => 'pill-success',
                                            'Declined' => 'pill-danger',
                                            default => 'pill-warning',
                                        };
                                    @endphp
                                    <tr>
                                        <td><span><img class="avatar-initials"
                                                    src="{{ $p->photo_url ?? asset('images/default.png') }}"
                                                    alt=""></span>{{ $p->FirstName }} {{ $p->LastName }}
                                            @if ($appt->Source === 'Walk-in')
                                                <span class="pill pill-muted">Walk-in</span>
                                            @endif
                                        </td>
                                        <td>{{ $appt->dentist_name }}</td>
                                        <td>{{ $appt->TypeOfAppointment ?: ($appt->service->ServiceName ?? '—') }}</td>
                                        <td>{{ $appt->AppointmentDate->format('F j, Y') }} &bull; {{ $appt->time_range_label }}</td>
                                        <td>{{ $appt->created_at->format('M j, Y g:i A') }}</td>
                                        <td><span class="pill {{ $pillClass }}">{{ $appt->Status === 'Approved' ? 'Booked' : $appt->Status }}</span></td>
                                        <td class="text-end">
                                            <button class="btn btn-pill btn-pill-edit me-1" data-bs-toggle="modal"
                                                data-bs-target="#patientInfoModal{{ $appt->AppointmentID }}"><i
                                                    class="bi bi-eye"></i> View</button>
                                            @if ($appt->Status === 'Pending')
                                                <button class="btn btn-pill btn-pill-done" data-bs-toggle="modal"
                                                    data-bs-target="#reviewAppointmentModal{{ $appt->AppointmentID }}"><i
                                                        class="bi bi-clipboard2-check"></i> Review</button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted-2 py-4">No appointments found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="pagination-soft">
                        <div>Showing {{ $appointments->count() }} of {{ $appointments->total() }} appointments</div>
                        <div class="pages">
                            <a href="{{ $appointments->previousPageUrl() ?? '#' }}"
                                class="{{ $appointments->onFirstPage() ? 'disabled' : '' }}"><i
                                    class="bi bi-chevron-left"></i></a>
                            @for ($i = 1; $i <= $appointments->lastPage(); $i++)
                                <a href="{{ $appointments->url($i) }}"
                                    class="{{ $appointments->currentPage() === $i ? 'active' : '' }}">{{ $i }}</a>
                            @endfor
                            <a href="{{ $appointments->nextPageUrl() ?? '#' }}"
                                class="{{ !$appointments->hasMorePages() ? 'disabled' : '' }}"><i
                                    class="bi bi-chevron-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    {{-- ===================== PER-ROW MODALS ===================== --}}
    @foreach ($appointments as $appt)
        @php
            $p = $appt->patientInfo;
        @endphp

        <!-- PATIENT INFO -->
        <div class="modal fade" id="patientInfoModal{{ $appt->AppointmentID }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header border-0 pb-0">
                        <div>
                            <h5 class="modal-title fw-semibold">Patient Information</h5>
                            <div class="small text-muted">{{ $p->FirstName }} {{ $p->LastName }}'s account details</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body pt-2">
                        <div class="d-flex align-items-center gap-3 mb-4">
                            <img class="avatar-initials" src="{{ $p->photo_url ?? asset('images/default.png') }}" alt=""
                                style="width:64px;height:64px;">
                            <div>
                                <div class="fw-semibold">{{ $p->FirstName }} {{ $p->LastName }}
                                    @if ($appt->Source === 'Walk-in')
                                        <span class="pill pill-muted">Walk-in</span>
                                    @endif
                                </div>
                                <div class="small text-muted-2">Patient ID:
                                    PT-{{ str_pad($p->PatientID, 4, '0', STR_PAD_LEFT) }}</div>
                            </div>
                        </div>

                        <div class="section-label">Personal Information</div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Last name</label>
                                <div class="input-icon"><i class="bi bi-person"></i><input type="text" class="form-control"
                                        value="{{ $p->LastName }}" disabled /></div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">First name</label>
                                <div class="input-icon"><i class="bi bi-person"></i><input type="text" class="form-control"
                                        value="{{ $p->FirstName }}" disabled /></div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Middle name</label>
                                <div class="input-icon"><i class="bi bi-person"></i><input type="text" class="form-control"
                                        value="{{ $p->MiddleName }}" disabled /></div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Birthdate</label>
                                <div class="input-icon"><i class="bi bi-calendar-event"></i><input type="date"
                                        class="form-control" value="{{ optional($p->DateOfBirth)->format('Y-m-d') }}"
                                        disabled /></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Age</label>
                                <div class="input-icon"><i class="bi bi-calendar3"></i><input type="text"
                                        class="form-control" value="{{ optional($p->DateOfBirth)->age }}" disabled /></div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Gender</label>
                                <div class="input-icon"><i class="bi bi-person-badge"></i><input type="text"
                                        class="form-control" value="{{ ucfirst($p->Gender) }}" disabled /></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Religion</label>
                                <div class="input-icon"><i class="bi bi-book"></i><input class="form-control"
                                        value="{{ $p->Religion }}" disabled /></div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Nationality</label>
                                <div class="input-icon"><i class="bi bi-flag"></i><input class="form-control"
                                        value="{{ $p->Nationality }}" disabled /></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Occupation</label>
                                <div class="input-icon"><i class="bi bi-briefcase"></i><input class="form-control"
                                        value="{{ $p->Occupation }}" disabled /></div>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Home address</label>
                                <div class="input-icon"><i class="bi bi-geo-alt"></i><input class="form-control"
                                        value="{{ $p->Address }}" disabled /></div>
                            </div>
                        </div>

                        <div class="section-label mt-2">Contact Details</div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Email address</label>
                                <div class="input-icon"><i class="bi bi-envelope"></i><input type="email"
                                        class="form-control" value="{{ $p->userAccount?->Email ?? $p->Email ?? '' }}" disabled /></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Cell/Mobile number</label>
                                <div class="input-icon"><i class="bi bi-telephone"></i><input class="form-control"
                                        value="{{ $p->PhoneNumber }}" disabled /></div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        @if ($appt->Status === 'Pending')
            <!-- REVIEW APPOINTMENT -->
            <div class="modal fade" id="reviewAppointmentModal{{ $appt->AppointmentID }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header border-0 pb-0">
                            <div>
                                <h5 class="modal-title fw-semibold">Review Appointment</h5>
                                <div class="small text-muted">{{ $p->FirstName }} {{ $p->LastName }}'s appointment request</div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form method="POST" action="{{ route('appointmentApproval.approve', $appt->AppointmentID) }}">
                            @csrf
                            <div class="modal-body pt-2">
                                <div class="section-label">Appointment Details</div>
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Dentist</label>
                                        <div class="input-icon"><i class="bi bi-person-badge"></i><input type="text"
                                                class="form-control" value="{{ $appt->dentist_name }}" disabled /></div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Service</label>
                                        <div class="input-icon"><i class="bi bi-heart-pulse"></i><input type="text"
                                                class="form-control" value="{{ $appt->TypeOfAppointment ?: ($appt->service->ServiceName ?? '') }}" disabled />
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Date</label>
                                        <div class="input-icon"><i class="bi bi-calendar-event"></i><input type="text"
                                                class="form-control" value="{{ $appt->AppointmentDate->format('F j, Y') }}"
                                                disabled /></div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Time</label>
                                        <div class="input-icon"><i class="bi bi-clock"></i><input type="text"
                                                class="form-control" value="{{ $appt->time_range_label }}" disabled /></div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer border-0 pt-0">
                                <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-pill btn-pill-cancel" data-bs-toggle="modal"
                                    data-bs-target="#declineReasonModal{{ $appt->AppointmentID }}">
                                    <i class="bi bi-x"></i> Decline
                                </button>
                                <button type="submit" class="btn btn-pill btn-pill-done"><i class="bi bi-check2"></i>
                                    Approve</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- DECLINE REASON -->
            <div class="modal fade" id="declineReasonModal{{ $appt->AppointmentID }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header border-0 pb-0">
                            <div>
                                <h5 class="modal-title fw-semibold">Decline Appointment</h5>
                                <div class="small text-muted">Let the patient know why this was declined</div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form method="POST" action="{{ route('appointmentApproval.decline', $appt->AppointmentID) }}">
                            @csrf
                            <div class="modal-body pt-2">
                                <div class="section-label">Reason for Decline</div>
                                <div class="mb-3">
                                    <label class="form-label">Message to patient</label>
                                    <textarea class="form-control" name="reason" rows="4" required
                                        placeholder="e.g. Requested time slot is no longer available. Please choose another schedule."></textarea>
                                </div>
                            </div>
                            <div class="modal-footer border-0 pt-0">
                                <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-pill btn-pill-cancel"><i class="bi bi-send"></i> Send &
                                    Decline</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    @endforeach

    @include('partials.admin-notif-modal')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
