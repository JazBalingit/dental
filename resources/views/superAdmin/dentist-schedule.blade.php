<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Doctor Schedule • Dental Clinic</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
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
                <a href="{{ route('dentistSchedule') }}" class="active"><i class="bi bi-calendar3"></i> Dentist
                    Schedule</a>
                <a href="{{ route('walkIn') }}"><i class="bi bi-calendar3"></i> Walk-in Appointments</a>
                <a href="{{ route('appointmentApproval') }}"><i class="bi bi-clipboard2-check"></i> Appointment
                    Approval</a>
                <a href="{{ route('appointments') }}"><i class="bi bi-clipboard2-check"></i> Appointments</a>
                <a href="{{ route('patientRecords') }}"><i class="bi bi-folder2-open"></i> Patient Records</a>
                <div class="nav-section">System</div>
                <a href="{{ route('configuration') }}"><i class="bi bi-sliders2"></i> Configuration</a>
            </nav>
            <div class="footer">© PUS-PUS BRITANICO DENTAL CLINIC</div>
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
                    {{-- Notifications left untouched, still static per your request --}}
                    @include('partials.admin-notif-dropdown')
                    <div class="dropdown">
                        <button class="user-chip" type="button" data-bs-toggle="dropdown" aria-expanded="false"
                            style="all:unset; cursor:pointer; display:flex; align-items:center; gap:.6rem; padding:.35rem .8rem .35rem .35rem; border-radius:999px; background:var(--brand-50); border:1px solid var(--brand-100); font-family:inherit;">
                            <div><img class="avatar" src="/images/default.png" alt=""></div>
                            <div class="meta">
                                <div class="name">{{ session('user_email', 'Admin') }}</div>
                                <div class="role">{{ session('account_type') === 'staff' ? 'Staff' : 'Administrator' }}</div>
                            </div>
                            <i class="bi bi-chevron-down ms-1 text-muted-2"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                            <li><a class="dropdown-item small" href="{{ route('staffProfile') }}"><i class="bi bi-person me-2"></i>My Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}" class="m-0">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger small"><i class="bi bi-box-arrow-right me-1"></i> Log Out</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="content">
                <div class="page-head">
                    <div>
                        <h2>Doctor Schedule</h2>
                        <div class="crumbs">View and manage availability for all dentists.</div>
                    </div>
                </div>

                <!-- @if (session('success'))
                    <div class="alert alert-success py-2">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger py-2">{{ session('error') }}</div>
                @endif -->

                {{-- ===================== MONTH VIEW ===================== --}}
                <div class="schedule-wrap">
                    <div class="schedule-toolbar d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <div class="fw-semibold">{{ $current->format('F Y') }}</div>
                            <div class="small text-muted-2">Monthly overview</div>
                        </div>

                        {{-- Year / month picker — plain GET form, no JS required --}}
                        <form method="GET" action="{{ route('dentistSchedule') }}"
                            class="d-flex align-items-center gap-2">
                            <select name="monthNum" class="form-select form-select-sm" style="width: 140px;">
                                @foreach (range(1, 12) as $m)
                                    <option value="{{ $m }}" {{ $current->month === $m ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                                    </option>
                                @endforeach
                            </select>
                            <input type="number" name="year" class="form-control form-control-sm" style="width: 100px;"
                                min="2000" max="2100" value="{{ $current->year }}">
                            <button type="submit" class="btn btn-brand btn-sm">Go</button>
                        </form>
                    </div>

                    <div class="month-grid p-3">
                        <div class="mh">Mon</div>
                        <div class="mh">Tue</div>
                        <div class="mh">Wed</div>
                        <div class="mh">Thu</div>
                        <div class="mh">Fri</div>
                        <div class="mh">Sat</div>
                        <div class="mh">Sun</div>

                        @foreach ($weeks as $week)
                            @foreach ($week as $d)
                                @php
                                    $dateStr = $d->format('Y-m-d');
                                    $inMonth = $d->month === $current->month;
                                    $isSunday = $d->isSunday();
                                    $daySlots = $schedules[$dateStr] ?? collect();
                                    $notAvailable = $daySlots->where('Status', 'Not Available');
                                    $lockedTimes = collect($slots)->filter(fn($label, $time) => isset($occupiedSlots[$dateStr . '_' . $time]) || (($daySlots[$time]->Status ?? 'Available') === 'Not Available'));
                                    $availableCount = $totalSlotsPerDay - $lockedTimes->count();
                                    $modalId = 'modalDay' . $d->format('Ymd');

                                    $countClass = 'count';
                                    if ($availableCount <= 0) {
                                        $countClass .= ' count-danger';
                                    } elseif ($notAvailable->count() > 0) {
                                        $countClass .= ' count-warning';
                                    }
                                @endphp

                                @if ($inMonth && !$isSunday)
                                    <button type="button"
                                        class="day-cell border-0 text-start p-0 w-100 d-block {{ $dateStr === $today ? 'today' : '' }}"
                                        data-bs-toggle="modal" data-bs-target="#{{ $modalId }}">
                                        <div class="n" style="margin-left: 8px;">{{ $d->day }}</div>
                                        <span class="{{ $countClass }}">{{ $availableCount }}</span>
                                        @foreach ($lockedTimes->take(3) as $time => $label)
                                            @php $slotAppointment = $occupiedSlots[$dateStr . '_' . $time] ?? null; @endphp
                                            <span class="ev ev-unavailable">{{ \Carbon\Carbon::createFromFormat('H:i', $time)->format('g:i A') }}{{ $slotAppointment ? ' · ' . ($slotAppointment->Status === 'Approved' ? 'Booked' : 'Pending') : '' }}</span>
                                        @endforeach
                                    </button>
                                @elseif ($inMonth && $isSunday)
                                    <button type="button" class="day-cell day-off border-0 text-start p-0 w-100 d-block" disabled>
                                        <div class="n" style="margin-left: 8px;">{{ $d->day }}</div>
                                        <span class="ev ev-unavailable">Day Off</span>
                                    </button>
                                @else
                                    <button class="day-cell border-0 text-start p-0 w-100 d-block disabled" disabled>
                                        <div class="n" style="margin-left: 8px;">{{ $d->day }}</div>
                                    </button>
                                @endif
                            @endforeach
                        @endforeach
                    </div>

                    <div class="p-3 d-flex justify-content-between flex-wrap gap-2">
                        <div class="legend">
                            <span><span class="dot" style="background: var(--brand-700);"></span>Today</span>
                            <span><span class="dot" style="background: #b9ffbd;"></span>Available slots a day</span>
                            <span><span class="dot" style="background: #ffe69bbd !important;"></span>Day with Unavailable Slots</span>
                            <span><span class="dot" style="background: #dc3545;"></span>Has Unavailable Slots</span>
                            <span><span class="dot" style="background: #e9ecef;"></span>Day Off (Sunday)</span>
                        </div>
                        <div class="small text-muted-2">Available schedule this month: {{ $totalAvailableThisMonth }}
                            schedules</div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    {{-- ===================== ONE MODAL PER DAY IN THE CURRENT MONTH (SUNDAYS SKIPPED) ===================== --}}
    @foreach ($weeks as $week)
        @foreach ($week as $d)
            @continue($d->month !== $current->month)
            @continue($d->isSunday())
            @php
                $dateStr = $d->format('Y-m-d');
                $modalId = 'modalDay' . $d->format('Ymd');
                $daySlots = $schedules[$dateStr] ?? collect();
                $isPast = $d->lt(\Carbon\Carbon::parse($today));
            @endphp

            <div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-xl">
                    <div class="modal-content">
                        <div class="modal-header border-0 pb-0">
                            <div>
                                <h5 class="modal-title fw-semibold">{{ $d->format('l, F j, Y') }}</h5>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body pt-2">
                            <div class="schedule-wrap mb-0">
                                <div class="week-grid">
                                    <div class="wh">Time</div>
                                    <div class="wh day">{{ $d->format('D') }} <span class="num">{{ $d->day }}</span></div>

                                    @foreach ($slots as $time => $label)
                                        @php
                                            $row = $daySlots[$time] ?? null;
                                            $appointmentForSlot = $occupiedSlots[$dateStr . '_' . $time] ?? null;
                                            $isHeld = $appointmentForSlot !== null;
                                            $status = $isHeld ? ($appointmentForSlot->Status === 'Approved' ? 'Booked' : 'Pending') : ($row->Status ?? 'Available');
                                            $isUnavailable = $status === 'Not Available' || $isHeld;
                                            $patientName = $isHeld ? trim(($appointmentForSlot->patientInfo->FirstName ?? '') . ' ' . ($appointmentForSlot->patientInfo->LastName ?? '')) : '';
                                        @endphp
                                        <div class="time">{{ $label }}</div>
                                        <div class="slot">
                                            @if ($isHeld)
                                                <div class="slot-btn w-100 is-unavailable text-start px-3 py-2" title="Appointment slots cannot be edited">
                                                    <div class="fw-semibold">{{ $status }}</div>
                                                    <small>{{ $status === 'Booked' ? 'Booked by:' : 'Pending:' }} {{ $patientName ?: 'Patient' }}</small>
                                                </div>
                                            @else
                                                <form method="POST" action="{{ route('dentistSchedule.toggle') }}" class="w-100">
                                                    @csrf
                                                    <input type="hidden" name="date" value="{{ $dateStr }}">
                                                    <input type="hidden" name="time" value="{{ $time }}">
                                                    <input type="hidden" name="month" value="{{ $current->format('Y-m') }}">
                                                    <button type="submit"
                                                        class="slot-btn w-100 {{ $isUnavailable ? 'is-unavailable' : 'is-available' }}"
                                                        {{ $isPast ? 'disabled' : '' }}>
                                                        {{ $status }}
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>

                                <div class="p-3 d-flex justify-content-between flex-wrap gap-2">
                                    <div class="small text-muted-2">
                                        @if ($isPast)
                                            This date has already passed — slots are locked.
                                        @else
                                            Appointment slots show the patient and are locked. Only open or manually unavailable slots can be edited.
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer border-0 pt-0">
                            <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @endforeach

    @include('partials.admin-notif-modal')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
