<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="/images/puspus_logo.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Current Appointments — Patient Portal — Pus-Pus Britanico</title>
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
                    <div class="sub">PATIENT PORTAL</div>
                </div>
            </div>
            @include('partials.patient-sidebar-nav', ['active' => 'appointments-current'])
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
                        <h2>Current Appointments</h2>
                        <div class="crumbs">Your next scheduled visit and its status.</div>
                    </div>
                </div>

                <!-- Stats -->
                <div class="stats-row">
                    <div class="stat-box">
                        <div class="stat-ico" style="background:rgba(59,217,101,0.1);color:#0f7a33"><i
                                class="fas fa-calendar-day"></i></div>
                        <div>
                            <div class="stat-val">{{ $counts['upcoming'] }}</div>
                            <div class="stat-lbl">Upcoming</div>
                        </div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-ico" style="background:rgba(34,197,94,0.1);color:#22c55e"><i
                                class="fas fa-check-circle"></i></div>
                        <div>
                            <div class="stat-val">{{ $counts['completed'] }}</div>
                            <div class="stat-lbl">Completed</div>
                        </div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-ico" style="background:rgba(239,68,68,0.1);color:#ef4444"><i
                                class="fas fa-times-circle"></i></div>
                        <div>
                            <div class="stat-val">{{ $counts['cancelled'] }}</div>
                            <div class="stat-lbl">Cancelled</div>
                        </div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-ico" style="background:rgba(15,76,122,0.08);color:#0f4c7a"><i
                                class="fas fa-clipboard-list"></i></div>
                        <div>
                            <div class="stat-val">{{ $counts['total'] }}</div>
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
                                <span class="badge-pill {{ $current->Status === 'Approved' ? 'badge-approved' : 'badge-pending' }}">{{ $current->Status === 'Approved' ? 'Booked' : 'Pending' }}</span>
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
                    @else
                        <div class="appt-body">
                            <p class="mb-2 text-muted">You have no current appointment.</p>
                            <a href="{{ route('userAppointment.book') }}" class="btn-prim d-inline-flex align-items-center gap-2" style="text-decoration:none;width:fit-content;">
                                <i class="fas fa-calendar-plus"></i> Book an Appointment
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </main>
    </div>

    @if ($current)
        @include('partials.appointment-info-modal', ['appointment' => $current])

        <!-- RESCHEDULE MODAL -->
        <div class="modal fade" id="rescheduleModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-calendar-alt me-2" style="color:#0f7a33"></i>Reschedule Appointment
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Rescheduling removes this appointment and releases its time. You can then choose a new available time from the booking calendar.</p>
                    </div>
                    <div class="modal-footer">
                        <button class="btn-sec" data-bs-dismiss="modal">Discard</button>
                        <form method="POST" action="{{ route('userAppointment.remove', $current) }}">@csrf<input type="hidden" name="action" value="reschedule"><button class="btn-prim"><i class="fas fa-calendar-alt me-1"></i> Reschedule</button></form>
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
                    <div class="modal-body">
                        <p style="font-size:14px;line-height:1.7">Are you sure you want to cancel your
                            <strong style="color:#0f4c7a">{{ $current->AppointmentDate->format('M j') }} – {{ $current->TypeOfAppointment ?: ($current->service->ServiceName ?? '') }}</strong>
                            appointment? This cannot be undone.</p>
                    </div>
                    <div class="modal-footer">
                        <button class="btn-sec" data-bs-dismiss="modal">Keep It</button>
                        <form method="POST" action="{{ route('userAppointment.remove', $current) }}">@csrf<input type="hidden" name="action" value="cancel"><button class="btn-prim" style="background:linear-gradient(135deg,#b91c1c,#ef4444);box-shadow:0 4px 12px rgba(239,68,68,0.3)"><i class="fas fa-times me-1"></i> Yes, Cancel</button></form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @include('partials.user-notif-modal')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/js/odontogram.js"></script>
</body>

</html>
