<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Appointments • Dental Clinic</title>
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
            <nav class="nav">
                <div class="nav-section">Main</div>
                <a href="{{ route('dashboard') }}"><i class="bi bi-grid-1x2-fill"></i> Dashboard</a>
                <a href="{{ route('staffAcc') }}"><i class="bi bi-people-fill"></i> Staff Accounts</a>
                <a href="{{ route('userAcc') }}"><i class="bi bi-people-fill"></i> User Accounts</a>
                <a href="{{ route('dentistSchedule') }}"><i class="bi bi-calendar3"></i> Dentist Schedule</a>
                <a href="{{ route('walkIn') }}"><i class="bi bi-calendar3"></i> Walk-in Appointments</a>
                <a href="{{ route('appointmentApproval') }}"><i class="bi bi-clipboard2-check"></i> Appointment
                    Approval</a>
                <a href="{{ route('appointments') }}" class="active"><i class="bi bi-clipboard2-check"></i> Appointments</a>
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
                        <h2>Appointments</h2>
                        <div class="crumbs">View and manage all the appointments of the patient.</div>
                    </div>
                </div>

                @include('partials.flash-toasts')

                <!-- mini stats -->
                <div class="row g-3 mb-3">
                    <div class="col-6 col-md-3">
                        <div class="stat-card">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="label">Total</div>
                                    <div class="value">{{ $stats['total'] }}</div>
                                </div>
                                <div class="icon"><i class="bi bi-calendar-day"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stat-card alt-1">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="label">Completed</div>
                                    <div class="value">{{ $stats['completed'] }}</div>
                                </div>
                                <div class="icon"><i class="bi bi-check2-circle"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stat-card alt-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="label">Approved</div>
                                    <div class="value">{{ $stats['approved'] }}</div>
                                </div>
                                <div class="icon">
                                    <i class="bi bi-hourglass"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stat-card alt-2">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="label">Cancelled</div>
                                    <div class="value">{{ $stats['cancelled'] }}</div>
                                </div>
                                <div class="icon"><i class="bi bi-x-circle"></i></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-soft p-3 p-md-4">
                    <form method="GET" action="{{ route('appointments') }}" class="data-toolbar">
                        <div class="left">
                            <span class="text-muted-2 small">Filter</span>
                            <select class="form-select" name="status" style="min-width:140px;" onchange="this.form.submit()">
                                <option value="" {{ !$status ? 'selected' : '' }}>All Status</option>
                                <option value="Pending" {{ $status === 'Pending' ? 'selected' : '' }}>Pending</option>
                                <option value="Approved" {{ $status === 'Approved' ? 'selected' : '' }}>Approved</option>
                                <option value="Completed" {{ $status === 'Completed' ? 'selected' : '' }}>Completed</option>
                                <option value="Declined" {{ $status === 'Declined' ? 'selected' : '' }}>Declined</option>
                                <option value="Cancelled" {{ $status === 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                        <div class="right">
                            <div class="input-icon search">
                                <i class="bi bi-search"></i>
                                <input class="form-control" name="search" value="{{ $search }}" placeholder="Search patient or treatment..."
                                    style="height:40px; padding-left:2.4rem;" />
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table-soft">
                            <thead>
                                <tr>
                                    <th>Date &amp; Time</th>
                                    <th>Patient</th>
                                    <th>Dentist</th>
                                    <th>Treatment</th>
                                    <th>Approved At</th>
                                    <th>Status</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($appointments as $appt)
                                    @php
                                        $p = $appt->patientInfo;
                                        $pillClass = match ($appt->Status) {
                                            'Approved' => 'pill-info',
                                            'Completed' => 'pill-success',
                                            'Declined', 'Cancelled' => 'pill-danger',
                                            default => 'pill-warning',
                                        };
                                        $timeLabel = \Carbon\Carbon::createFromFormat('H:i', $appt->AppointmentTime)->format('g:i A');
                                    @endphp
                                    <tr>
                                        <td><span class="fw-semibold">{{ $appt->AppointmentDate->format('M j, Y') }} &bull; {{ $timeLabel }}</span></td>
                                        <td><span><img class="avatar-initials" src="{{ $p->photo_url ?? asset('images/default.png') }}" alt=""></span>{{ $p->FirstName ?? '' }} {{ $p->LastName ?? '' }}
                                            @if ($appt->Source === 'Walk-in')
                                                <span class="pill pill-muted">Walk-in</span>
                                            @endif
                                        </td>
                                        <td>{{ $appt->dentist_name }}</td>
                                        <td>{{ $appt->TypeOfAppointment ?: ($appt->service->ServiceName ?? '—') }}</td>
                                        <td>{{ $appt->ApprovedAt ? $appt->ApprovedAt->format('M j, Y g:i A') : '—' }}</td>
                                        <td><span class="pill {{ $pillClass }}">{{ $appt->Status }}</span></td>
                                        <td class="text-end">
                                            @if ($appt->Status === 'Approved')
                                                <form method="POST" action="{{ route('appointments.complete', $appt->AppointmentID) }}" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-pill btn-pill-done me-1"><i class="bi bi-check2"></i> Done</button>
                                                </form>
                                            @endif
                                            @if (in_array($appt->Status, ['Pending', 'Approved']))
                                                <button type="button" class="btn btn-pill btn-pill-cancel" data-bs-toggle="modal"
                                                    data-bs-target="#cancelledReasonModal{{ $appt->AppointmentID }}"><i class="bi bi-x"></i> Cancel</button>
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
                            <a href="{{ $appointments->previousPageUrl() ?? '#' }}"><i class="bi bi-chevron-left"></i></a>
                            @for ($i = 1; $i <= $appointments->lastPage(); $i++)
                                <a href="{{ $appointments->url($i) }}" class="{{ $appointments->currentPage() === $i ? 'active' : '' }}">{{ $i }}</a>
                            @endfor
                            <a href="{{ $appointments->nextPageUrl() ?? '#' }}"><i class="bi bi-chevron-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    @include('partials.admin-notif-modal')

    {{-- ===================== PER-ROW CANCEL REASON MODALS ===================== --}}
    @foreach ($appointments as $appt)
        @if (in_array($appt->Status, ['Pending', 'Approved']))
            <div class="modal fade" id="cancelledReasonModal{{ $appt->AppointmentID }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header border-0 pb-0">
                            <div>
                                <h5 class="modal-title fw-semibold">Cancel Appointment</h5>
                                <div class="small text-muted">Let the patient know why this was cancelled</div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form method="POST" action="{{ route('appointments.cancel', $appt->AppointmentID) }}">
                            @csrf
                            <div class="modal-body pt-2">
                                <div class="section-label">Reason for Cancelation of Appointment</div>
                                <div class="mb-3">
                                    <label class="form-label">Message to patient</label>
                                    <textarea class="form-control" name="reason" rows="4" required
                                        placeholder="e.g. Requested time slot is no longer available. Please choose another schedule."></textarea>
                                </div>
                            </div>
                            <div class="modal-footer border-0 pt-0">
                                <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-pill btn-pill-cancel"><i class="bi bi-send"></i> Send &
                                    Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    @endforeach

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
