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
    <link rel="stylesheet" href="{{ asset('css/guest-booking.css') }}">
    <link rel="stylesheet" href="{{ asset('css/medical-history.css') }}">
    <style>
        .book-for-tabs { display: inline-flex; gap: .35rem; padding: .3rem; background: #f1f5f2; border-radius: 999px; margin-bottom: 1rem; flex-wrap: wrap; }
        .book-for-tabs a { padding: .45rem 1rem; border-radius: 999px; font-weight: 600; font-size: .88rem; color: #3f5146; text-decoration: none; }
        .book-for-tabs a.is-active { background: #fff; color: #167d1d; box-shadow: 0 1px 4px rgba(0,0,0,.08); }
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
            @include('partials.patient-sidebar-nav', ['active' => $forOther ? 'appointments-book-other' : 'appointments-book'])
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
                        <h2>{{ $forOther ? 'Book for Someone Else' : 'Book Appointment' }}</h2>
                        <div class="crumbs">
                            @if ($forOther)
                                Book for your child, parent, spouse or anyone you look after — you'll get every update.
                            @else
                                Pick an open date and time on the dentist's schedule.
                            @endif
                        </div>
                    </div>
                </div>

                <div class="book-for-tabs">
                    <a href="{{ route('userAppointment.book') }}" class="{{ $forOther ? '' : 'is-active' }}"><i class="fas fa-user me-1"></i> For Myself</a>
                    <a href="{{ route('userAppointment.book', ['for' => 'other']) }}" class="{{ $forOther ? 'is-active' : '' }}"><i class="fas fa-user-friends me-1"></i> Book for Someone Else</a>
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
                    @if ($current && !$forOther)
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
                        // Same picker as Follow-up; with an active appointment the
                        // picker explains why Continue is locked instead.
                        'bookBlocked' => (bool) $current && !$forOther,
                        'bookBaseUrl' => route('userAppointment.book', $forOther ? ['for' => 'other'] : []),
                        'bookExtraView' => $forOther ? 'users.partials.book-for-other' : 'users.partials.book-medical-self',
                        'bookExtraHidden' => $forOther ? ['booking_for' => 'other'] : [],
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
    <script src="{{ asset('js/booking-forms.js') }}"></script>
</body>

</html>
