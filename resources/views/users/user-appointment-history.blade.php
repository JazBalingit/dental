<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="/images/puspus_logo.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment History — Patient Portal — Pus-Pus Britanico</title>
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
            @include('partials.patient-sidebar-nav', ['active' => 'appointments-history'])
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
                        <h2>Appointment History</h2>
                        <div class="crumbs">A record of your past dental visits.</div>
                    </div>
                </div>

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
    @foreach ($history as $appointment)
        @include('partials.appointment-info-modal', ['appointment' => $appointment])
    @endforeach

    @include('partials.user-notif-modal')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/js/odontogram.js"></script>
</body>

</html>
