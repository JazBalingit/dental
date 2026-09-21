<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="/images/puspus_logo.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment — Patient Portal — Pus-Pus Britanico</title>
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
    <style>
        .booking-locked { display: flex; align-items: center; gap: .85rem; padding: 1rem 1.25rem; background: var(--warning-bg, #fdf3df); border: 1px solid #f3e0ad; border-radius: .85rem; color: #7a5b12; font-size: .9rem; }
        .booking-locked i { font-size: 1.25rem; color: #c98a13; }
        .booking-locked a { color: #167d1d; font-weight: 600; }
        .booking-locked a:hover { color: #0f5c14; }
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
                    <div class="sub">PATIENT PORTAL</div>
                </div>
            </div>
            @include('partials.patient-sidebar-nav', ['active' => 'appointments-book'])
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
                        <h2>Book Appointment</h2>
                        <div class="crumbs">Pick an open date and time on the dentist's schedule.</div>
                    </div>
                </div>

                <div class="section-card">
                    <div class="card-hd">
                        <div class="card-hd-left">
                            <div class="card-hd-icon"><i class="fas fa-calendar-plus"></i></div>
                            <div>
                                <h4>Dentist Schedule</h4>
                                <p>Choose a dentist, then an open slot</p>
                            </div>
                        </div>
                    </div>
                    @if ($current)
                        <div class="appt-body">
                            <div class="booking-locked">
                                <i class="fas fa-circle-info"></i>
                                <div>
                                    You already have an active appointment — book another once it's completed or cancelled.
                                    <a href="{{ route('userAppointment') }}">View your current appointment</a>.
                                    You can still browse the schedule below to plan a reschedule.
                                </div>
                            </div>
                        </div>
                    @endif
                    @include('partials.booking-calendar', [
                        'calendarMode' => 'post',
                        'readOnly' => (bool) $current,
                        'bookBaseUrl' => route('userAppointment.book'),
                        'bookHash' => '',
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
                </div>
            </div>
        </main>
    </div>

    @include('partials.user-notif-modal')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
