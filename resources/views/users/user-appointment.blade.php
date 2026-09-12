<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointments — Patient Portal — Pus-Pus Britanico</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <link rel="stylesheet" href="/css/user_appointments.css">
    <link rel="stylesheet" href="/css/odontogram.css">
    <style>
        .booking-locked { display: flex; align-items: center; gap: .85rem; padding: 1rem 1.25rem; background: var(--warning-bg, #fdf3df); border: 1px solid #f3e0ad; border-radius: .85rem; color: #7a5b12; font-size: .9rem; }
        .booking-locked i { font-size: 1.25rem; color: #c98a13; }
    </style>
    <link rel="stylesheet" href="{{ asset('css/mobile.css') }}">
</head>

<body>
    <div class="app">
        <aside class="sidebar offcanvas position-sticky" tabindex="-1" id="sidebarOffcanvas">
            <div class="brand">
                <div><img class="logo" src="/images/puspus_logo.png" alt=""></div>
                <div>
                    <div class="name">PUS-PUS BRITANICO</div>
                    <div class="sub">PATIENT PORTAL</div>
                </div>
            </div>
            @include('partials.patient-sidebar-nav', ['active' => 'appointments'])
            @include('partials.patient-profile-badge')
        </aside>

        <main>
            <div class="topbar">
                <div class="left">
                    <button class="toggle d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas" aria-controls="sidebarOffcanvas">
                        <i class="bi bi-list"></i>
                    </button>
                </div>
                <div class="right">
                    @include('partials.user-notif-dropdown')
                </div>
            </div>

            <div class="content">
                @include('partials.flash-toasts')
                <div class="page-head">
                    <div>
                        <h2>Appointments</h2>
                        <div class="crumbs">Book a new appointment and manage your existing ones.</div>
                    </div>
                </div>

                <!-- Stats -->
        <div class="stats-row">
            <div class="stat-box">
                <div class="stat-ico" style="background:rgba(59,217,101,0.1);color:#0f7a33"><i
                        class="fas fa-calendar-day"></i></div>
                <div>
                    <div class="stat-val">{{ $history->whereIn('Status', ['Pending', 'Approved'])->count() }}</div>
                    <div class="stat-lbl">Upcoming</div>
                </div>
            </div>
            <div class="stat-box">
                <div class="stat-ico" style="background:rgba(34,197,94,0.1);color:#22c55e"><i
                        class="fas fa-check-circle"></i></div>
                <div>
                    <div class="stat-val">{{ $history->where('Status', 'Completed')->count() }}</div>
                    <div class="stat-lbl">Completed</div>
                </div>
            </div>
            <div class="stat-box">
                <div class="stat-ico" style="background:rgba(239,68,68,0.1);color:#ef4444"><i
                        class="fas fa-times-circle"></i></div>
                <div>
                    <div class="stat-val">{{ $history->where('Status', 'Declined')->count() }}</div>
                    <div class="stat-lbl">Cancelled</div>
                </div>
            </div>
            <div class="stat-box">
                <div class="stat-ico" style="background:rgba(15,76,122,0.08);color:#0f4c7a"><i
                        class="fas fa-clipboard-list"></i></div>
                <div>
                    <div class="stat-val">{{ $history->total() }}</div>
                    <div class="stat-lbl">Total</div>
                </div>
            </div>
        </div>

        <!-- Current Appointment -->
        <div class="section-card">
            <div class="card-hd">
                <div class="card-hd-left">
                    <div class="card-hd-icon"><i class="fas fa-clock"></i></div>
                    <div>
                        <h4>Current Appointment</h4>
                        <p>Your next scheduled visit</p>
                    </div>
                </div>
            </div>
            @if ($current)
            <div class="appt-body">
                <div class="date-block">
                    <div class="date-day">{{ $current->AppointmentDate->format('d') }}</div>
                    <div class="date-mon">{{ $current->AppointmentDate->format('M Y') }}</div>
                </div>
                <div class="appt-detail">
                    <h3>{{ $current->TypeOfAppointment ?: ($current->service->ServiceName ?? '') }}</h3>
                    <div class="appt-meta">
                        <span><i class="fas fa-user-md"></i> {{ $current->dentist_name }}</span>
                        <span><i class="fas fa-calendar"></i> {{ $current->AppointmentDate->format('F j, Y') }}</span>
                        <span><i class="fas fa-clock"></i> {{ \Carbon\Carbon::createFromFormat('H:i', $current->AppointmentTime)->format('g:i A') }}</span>
                        <span><i class="fas fa-hourglass-half"></i> {{ $current->duration_label }}</span>
                    </div>
                    <div style="margin-top:10px">
                        <span class="badge-pill badge-scheduled">{{ $current->Status === 'Approved' ? 'Booked' : 'Pending' }}</span>
                    </div>
                </div>
                <div class="appt-actions">
                    <button class="btn-outline" data-bs-toggle="modal" data-bs-target="#apptModal{{ $current->AppointmentID }}">
                        <i class="fas fa-eye"></i> View Appointment Information
                    </button>
                    <button class="btn-outline" data-bs-toggle="modal" data-bs-target="#rescheduleModal">
                        <i class="fas fa-calendar-alt"></i> Reschedule
                    </button>
                    <button class="btn-danger" data-bs-toggle="modal" data-bs-target="#cancelModal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>
            </div>
            @else <div class="appt-body"><p class="mb-0 text-muted">You have no current appointment.</p></div> @endif
        </div>

        <!-- Book an Appointment -->
        <div class="section-card">
            <div class="card-hd">
                <div class="card-hd-left">
                    <div class="card-hd-icon"><i class="fas fa-calendar-plus"></i></div>
                    <div>
                        <h4>Book an Appointment</h4>
                        <p>Pick an open date and time on the dentist's schedule</p>
                    </div>
                </div>
            </div>
            @if ($current)
                <div class="appt-body">
                    <div class="booking-locked">
                        <i class="fas fa-circle-info"></i>
                        <div>You already have an active appointment above — book another once it's completed or cancelled.</div>
                    </div>
                </div>
            @else
                @include('partials.booking-calendar', [
                    'calendarMode' => 'post',
                    'readOnly' => false,
                    'bookBaseUrl' => route('userAppointment'),
                    'bookHash' => '',
                    'rescheduleModalId' => 'rescheduleModal',
                    'cancelModalId' => 'cancelModal',
                    'bookWeeks' => $bookWeeks,
                    'bookCurrent' => $bookCurrent,
                    'bookSchedules' => $bookSchedules,
                    'bookOccupiedSlots' => $bookOccupiedSlots,
                    'bookSlots' => $bookSlots,
                    'bookToday' => $bookToday,
                    'services' => $services,
                    'bookCurrentPatientId' => $bookCurrentPatientId,
                    'bookDentists' => $bookDentists,
                    'bookSelectedDentist' => $bookSelectedDentist,
                    'bookSelectedDentistId' => $bookSelectedDentistId,
                ])
            @endif
        </div>

        <!-- Appointment History -->
        <div class="section-card">
            <div class="card-hd">
                <div class="card-hd-left">
                    <div class="card-hd-icon"><i class="fas fa-history"></i></div>
                    <div>
                        <h4>Appointment History</h4>
                        <p>A record of your past dental visits</p>
                    </div>
                </div>
                <form method="GET" class="history-filters"><div class="history-search"><i class="fas fa-search"></i><input class="form-control" name="search" value="{{ $search }}" placeholder="Search service"></div><select class="form-select" name="status"><option value="">All statuses</option>@foreach(['Pending','Approved','Completed','Declined'] as $option)<option value="{{ $option }}" @selected($status === $option)>{{ $option === 'Approved' ? 'Booked' : $option }}</option>@endforeach</select><button class="btn-prim"><i class="fas fa-filter"></i> Filter</button></form>
            </div>
            <div style="overflow-x:auto">
                <table class="appt-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Duration</th>
                            <th>Service</th>
                            <th>Dentist</th>
                            <th>Status</th>
                            <th class="text-end">Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($history as $appointment)
                            <tr>
                                <td style="font-weight:600;color:#0f4c7a">{{ $appointment->AppointmentDate->format('M j, Y') }}</td>
                                <td>{{ \Carbon\Carbon::createFromFormat('H:i', $appointment->AppointmentTime)->format('g:i A') }}</td>
                                <td>{{ $appointment->duration_label }}</td>
                                <td><span class="service-tag">{{ $appointment->TypeOfAppointment ?: ($appointment->service->ServiceName ?? '') }}</span></td>
                                <td>{{ $appointment->dentist_name }}</td>
                                <td><span class="badge-pill {{ $appointment->Status === 'Completed' ? 'badge-completed' : ($appointment->Status === 'Declined' ? 'badge-cancelled' : 'badge-scheduled') }}">{{ $appointment->Status === 'Approved' ? 'Booked' : $appointment->Status }}</span></td>
                                <td class="text-end">
                                    <button type="button" class="btn-view-appt" data-bs-toggle="modal" data-bs-target="#apptModal{{ $appointment->AppointmentID }}">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted">No appointments found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="history-footer">
                <small>Showing <strong>{{ $history->count() }}</strong> of <strong>{{ $history->total() }}</strong> appointments</small>
                @if ($history->lastPage() > 1)
                    <div class="pages">
                        <a href="{{ $history->previousPageUrl() ?? '#' }}"><i class="bi bi-chevron-left"></i></a>
                        @for ($i = 1; $i <= $history->lastPage(); $i++)
                            <a href="{{ $history->url($i) }}" class="{{ $history->currentPage() === $i ? 'active' : '' }}">{{ $i }}</a>
                        @endfor
                        <a href="{{ $history->nextPageUrl() ?? '#' }}"><i class="bi bi-chevron-right"></i></a>
                    </div>
                @endif
            </div>
        </div>
            </div>
        </main>
    </div>

    {{-- ===================== APPOINTMENT INFORMATION MODALS ===================== --}}
    @php
        $modalAppointments = $history->getCollection();
        if ($current && ! $modalAppointments->contains('AppointmentID', $current->AppointmentID)) {
            $modalAppointments = $modalAppointments->concat([$current]);
        }
    @endphp
    @foreach ($modalAppointments as $appointment)
        @php
            $rec = $appointment->patientRecord;
            $statusLabel = $appointment->Status === 'Approved' ? 'Booked' : $appointment->Status;
            $badgeClass = $appointment->Status === 'Completed'
                ? 'badge-completed'
                : ($appointment->Status === 'Declined' ? 'badge-cancelled' : 'badge-scheduled');
        @endphp
        <div class="modal fade" id="apptModal{{ $appointment->AppointmentID }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-circle-info me-2" style="color:#0f7a33"></i>Appointment Information</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">

                        <div class="appt-info-grid">
                            <div class="appt-info-cell"><span class="appt-info-lbl">Service</span><span class="appt-info-val">{{ $appointment->TypeOfAppointment ?: ($appointment->service->ServiceName ?? '—') }}</span></div>
                            <div class="appt-info-cell"><span class="appt-info-lbl">Dentist</span><span class="appt-info-val">{{ $appointment->dentist_name }}</span></div>
                            <div class="appt-info-cell"><span class="appt-info-lbl">Date</span><span class="appt-info-val">{{ $appointment->AppointmentDate->format('F j, Y') }}</span></div>
                            <div class="appt-info-cell"><span class="appt-info-lbl">Time</span><span class="appt-info-val">{{ \Carbon\Carbon::createFromFormat('H:i', $appointment->AppointmentTime)->format('g:i A') }}</span></div>
                            <div class="appt-info-cell"><span class="appt-info-lbl">Duration</span><span class="appt-info-val">{{ $appointment->duration_label }}</span></div>
                            <div class="appt-info-cell"><span class="appt-info-lbl">Status</span><span class="appt-info-val"><span class="badge-pill {{ $badgeClass }}">{{ $statusLabel }}</span></span></div>
                            @if ($appointment->ApprovedAt)
                                <div class="appt-info-cell"><span class="appt-info-lbl">Approved On</span><span class="appt-info-val">{{ $appointment->ApprovedAt->format('F j, Y g:i A') }}</span></div>
                            @endif
                            @if ($appointment->Status === 'Declined' && $appointment->DeclineReason)
                                <div class="appt-info-cell" style="grid-column:1/-1"><span class="appt-info-lbl">Reason</span><span class="appt-info-val">{{ $appointment->DeclineReason }}</span></div>
                            @endif
                        </div>

                        <div class="appt-info-divider"></div>

                        @if ($rec)
                            @include('partials.odontogram', ['record' => $rec, 'readonly' => true])

                            <div class="odontogram-heading"><i class="bi bi-journal-text"></i> Dentist's Notes</div>
                            <div class="odontogram-detail-readvalue">{{ $rec->Notes ?: 'No notes were recorded for this visit.' }}</div>
                        @else
                            <div class="appt-info-empty">
                                <i class="fas fa-tooth"></i>
                                <p>Your dental chart and dentist's notes will appear here once this appointment is completed.</p>
                            </div>
                        @endif

                    </div>
                    <div class="modal-footer gap-2">
                        <button class="btn-sec" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <!-- RESCHEDULE MODAL -->
    <div class="modal fade" id="rescheduleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-calendar-alt me-2" style="color:#0f7a33"></i>Reschedule Appointment
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p>Rescheduling removes this appointment and releases its time. You can then choose a new available time from the booking calendar.</p>
                </div>
                <div class="modal-footer gap-2">
                    <button class="btn-sec" data-bs-dismiss="modal">Discard</button>
                    @if($current)<form method="POST" action="{{ route('userAppointment.remove', $current) }}">@csrf<input type="hidden" name="action" value="reschedule"><button class="btn-prim"><i class="fas fa-calendar-alt me-1"></i> Reschedule</button></form>@endif
                </div>
            </div>
        </div>
    </div>

    <!-- CANCEL MODAL -->
    <div class="modal fade" id="cancelModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2" style="color:#ef4444"></i>Cancel
                        Appointment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p style="font-size:14px;line-height:1.7">Are you sure you want to cancel your
                        @if($current)<strong style="color:#0f4c7a">{{ $current->AppointmentDate->format('M j') }} – {{ $current->TypeOfAppointment ?: ($current->service->ServiceName ?? '') }}</strong>@endif
                        appointment? This cannot be undone.</p>
                </div>
                <div class="modal-footer gap-2">
                    <button class="btn-sec" data-bs-dismiss="modal">Keep It</button>
                    @if($current)<form method="POST" action="{{ route('userAppointment.remove', $current) }}">@csrf<input type="hidden" name="action" value="cancel"><button class="btn-prim" style="background:linear-gradient(135deg,#b91c1c,#ef4444);box-shadow:0 4px 12px rgba(239,68,68,0.3)"><i class="fas fa-times me-1"></i> Yes, Cancel</button></form>@endif
                </div>
            </div>
        </div>
    </div>

    @include('partials.user-notif-modal')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/js/odontogram.js"></script>
</body>

</html>
