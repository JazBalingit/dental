<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
  <link rel="icon" type="image/png" href="/images/puspus_logo.png">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Dentist Schedule • Dental Clinic</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
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
            @include('partials.admin-sidebar-nav', ['active' => 'dentistSchedule'])
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
                        <h2>{{ ($viewerIsDentist ?? false) ? 'My Schedule' : 'Dentist Schedule' }}</h2>
                        <div class="crumbs">{{ ($viewerIsDentist ?? false) ? 'View and manage your own availability.' : 'View and manage availability for all dentists.' }}</div>
                    </div>
                </div>

                @include('partials.flash-toasts')

                {{-- Same look as the Patient Portal / landing booking calendar
                     (partials.booking-calendar): one status badge per day, same
                     colors and legend — plus a small line with the day's
                     appointments, since this page is for managing them. --}}
                <style>
                    .booking-calendar .schedule-toolbar h4 { margin: 0 0 .15rem; font-weight: 700; color: var(--brand-700, #167d1d); }
                    .booking-calendar .ev.ev-day-open { background: #dcfce7; color: #15803d; border: 1px solid #86efac; }
                    .booking-calendar .ev.ev-day-partial { background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; }
                    .booking-calendar .ev.ev-day-full { background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; font-weight: 700; }
                    .booking-calendar .ev.ev-day-closed { background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; font-style: normal; }
                    .booking-calendar .day-cell .ds-day-note {
                        /* Hangs below the badge without changing the cell height, so every date in a row stays lined up. */
                        display: block; height: 1rem; margin: .2rem 8px -1.2rem; font-size: .7rem; font-weight: 600; line-height: 1rem; color: var(--ink-500, #64748b);
                        overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
                    }
                    .booking-calendar .ds-day-note .is-booked { color: #1d4ed8; }
                    .booking-calendar .ds-day-note .is-pending { color: #8a6100; }
                    .booking-calendar .ds-day-note .is-completed { color: #6d28d9; }
                    .booking-calendar .ds-day-note .is-off { color: #b02a37; }
                    /* Past / full / closed days still open their day window here. */
                    .booking-calendar button.day-cell.day-off:not(:disabled) { cursor: pointer; }
                    /* Phones: cells are too narrow for the extra line — tapping the day shows it all. */
                    @media (max-width: 575.98px) { .booking-calendar .day-cell .ds-day-note { display: none; } }
                </style>

                {{-- ===================== MONTH VIEW ===================== --}}
                <div class="schedule-wrap booking-calendar">
                    <div class="schedule-toolbar d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h4>{{ ($viewerIsDentist ?? false) ? 'My Schedule' : 'Dentist Schedule' }}</h4>
                            <div class="small text-muted-2">
                                {{ $current->format('F Y') }}
                                @if ($selectedDentist)
                                    · with <strong>{{ $selectedDentist->display_name }}</strong>
                                @elseif ($dentists->isEmpty())
                                    · No dentist accounts yet — add one under Staff Accounts.
                                @endif
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            {{-- Dentist filter — staff / super admin only; a dentist only ever sees their own grid --}}
                            @if (!($viewerIsDentist ?? false) && $dentists->isNotEmpty())
                                <select class="form-select form-select-sm" style="width: 200px;"
                                    onchange="window.location.href='{{ route('dentistSchedule') }}?month={{ $current->format('Y-m') }}&dentist=' + this.value">
                                    @foreach ($dentists as $dentist)
                                        <option value="{{ $dentist->UserID }}" {{ (int) $selectedDentistId === (int) $dentist->UserID ? 'selected' : '' }}>
                                            {{ $dentist->display_name }}
                                        </option>
                                    @endforeach
                                </select>
                            @endif

                            {{-- Month navigation kept together so it wraps as one unit on phones --}}
                            <div class="sched-monthnav">
                                {{-- Prev month --}}
                                <a href="{{ route('dentistSchedule', ['month' => $current->copy()->subMonth()->format('Y-m'), 'dentist' => $selectedDentistId]) }}"
                                    class="btn btn-outline-secondary btn-sm" aria-label="Previous month"><i
                                        class="bi bi-chevron-left"></i></a>

                                {{-- Year / month picker — plain GET form, no JS required --}}
                                <form method="GET" action="{{ route('dentistSchedule') }}"
                                    class="sched-monthnav-form d-flex align-items-center gap-2">
                                    <input type="hidden" name="dentist" value="{{ $selectedDentistId }}">
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

                                {{-- Next month --}}
                                <a href="{{ route('dentistSchedule', ['month' => $current->copy()->addMonth()->format('Y-m'), 'dentist' => $selectedDentistId]) }}"
                                    class="btn btn-outline-secondary btn-sm" aria-label="Next month"><i
                                        class="bi bi-chevron-right"></i></a>

                                <a href="{{ route('dentistSchedule', ['dentist' => $selectedDentistId]) }}" class="btn btn-outline-secondary btn-sm">Today</a>
                            </div>
                        </div>
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
                                    $isWeeklyOff = \App\Models\ClosedDate::isClosedWeekday($d);
                                    $isClinicClosed = \App\Models\ClosedDate::isClosed($d);
                                    $clinicClosedReason = \App\Models\ClosedDate::reasonFor($d);
                                    $isPast = $d->lt(\Carbon\Carbon::parse($today));
                                    $daySlots = $schedules[$dateStr] ?? collect();
                                    $lockedTimes = collect($slots)->filter(fn($label, $time) => isset($occupiedSlots[$dateStr . '_' . $time]) || (($daySlots[$time]->Status ?? 'Available') === 'Not Available'));
                                    $availableCount = $totalSlotsPerDay - $lockedTimes->count();
                                    $completedThatDay = $completedCountByDate[$dateStr] ?? 0;
                                    $modalId = 'modalDay' . $d->format('Ymd');

                                    // The day's appointments (one appointment spans several slots)
                                    // and the hours the dentist switched off.
                                    $dayAppts = collect($slots)->keys()
                                        ->map(fn ($t) => $occupiedSlots[$dateStr . '_' . $t] ?? null)
                                        ->filter()->unique(fn ($a) => $a->AppointmentID);
                                    $bookedAppts = $dayAppts->where('Status', 'Approved')->count();
                                    $pendingAppts = $dayAppts->where('Status', 'Pending')->count();
                                    $offHours = collect($slots)->keys()
                                        ->filter(fn ($t) => !isset($occupiedSlots[$dateStr . '_' . $t]) && (($daySlots[$t]->Status ?? 'Available') === 'Not Available'))
                                        ->count();

                                    // Same states and colors as the patient calendar:
                                    // green open · yellow some booked · red fully booked · grey closed/passed.
                                    $dayState = match (true) {
                                        !$inMonth => 'out',
                                        $isWeeklyOff || $isClinicClosed => 'closed',
                                        $isPast => 'passed',
                                        $availableCount <= 0 && $dayAppts->isNotEmpty() => 'full',
                                        $availableCount <= 0 => 'closed',
                                        $dayAppts->isNotEmpty() => 'partial',
                                        default => 'open',
                                    };
                                @endphp

                                @if ($dayState === 'out')
                                    <button class="day-cell border-0 text-start p-0 w-100 d-block disabled" disabled>
                                        <div class="n" style="margin-left: 8px;">{{ $d->day }}</div>
                                    </button>
                                @elseif ($isWeeklyOff || $isClinicClosed)
                                    {{-- Clinic closed — nothing to manage. --}}
                                    <button type="button" class="day-cell day-off border-0 text-start p-0 w-100 d-block {{ $dateStr === $today ? 'today' : '' }}" disabled>
                                        <div class="n" style="margin-left: 8px;">{{ $d->day }}</div>
                                        <span class="ev ev-day-closed">Closed{{ $isClinicClosed && $clinicClosedReason ? ' — ' . $clinicClosedReason : '' }}</span>
                                    </button>
                                @else
                                    <button type="button"
                                        class="day-cell {{ in_array($dayState, ['passed', 'closed', 'full'], true) ? 'day-off' : '' }} border-0 text-start p-0 w-100 d-block {{ $dateStr === $today ? 'today' : '' }}"
                                        data-bs-toggle="modal" data-bs-target="#{{ $modalId }}">
                                        <div class="n" style="margin-left: 8px;">{{ $d->day }}</div>
                                        @if ($dayState === 'passed')
                                            <span class="ev ev-day-closed">Date has passed</span>
                                        @elseif ($dayState === 'full')
                                            <span class="ev ev-day-full">Fully booked</span>
                                        @elseif ($dayState === 'closed')
                                            <span class="ev ev-day-closed">Closed</span>
                                        @else
                                            <span class="ev {{ $dayState === 'open' ? 'ev-day-open' : 'ev-day-partial' }}">
                                                {{ $availableCount }} slot{{ $availableCount === 1 ? '' : 's' }} available
                                            </span>
                                        @endif

                                        @php
                                            $notes = [];
                                            if ($dayState === 'passed') {
                                                if ($completedThatDay > 0) $notes[] = ['is-completed', $completedThatDay . ' completed'];
                                            } else {
                                                if ($bookedAppts) $notes[] = ['is-booked', $bookedAppts . ' booked'];
                                                if ($pendingAppts) $notes[] = ['is-pending', $pendingAppts . ' pending'];
                                                if ($offHours && $dayState !== 'closed') $notes[] = ['is-off', $offHours . ' unavailable'];
                                            }
                                        @endphp
                                        @if ($notes)
                                            <span class="ds-day-note">
                                                @foreach ($notes as [$cls, $text])<span class="{{ $cls }}">{{ $text }}</span>{{ $loop->last ? '' : ' · ' }}@endforeach
                                            </span>
                                        @endif
                                    </button>
                                @endif
                            @endforeach
                        @endforeach
                    </div>

                    {{-- Same legend as the patient calendar. --}}
                    <div class="p-3 d-flex justify-content-between flex-wrap gap-2">
                        <div class="legend">
                            <span><span class="dot" style="background: var(--brand-700);"></span>Today</span>
                            <span><span class="dot" style="background: #16a34a;"></span>Available</span>
                            <span><span class="dot" style="background: #d4a017;"></span>Some slots booked</span>
                            <span><span class="dot" style="background: #dc2626;"></span>Fully booked</span>
                            <span><span class="dot" style="background: #94a3b8;"></span>Closed / passed</span>
                        </div>
                        <div class="small text-muted-2">Available schedule this month: {{ $totalAvailableThisMonth }} schedules · Click a date to manage its hours.</div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    {{-- Compact day window (hour chips + merged appointment rows). --}}
    <style>
        .ds-day { max-width: 560px; margin: 0 auto; }
        .ds-stats { display: flex; flex-wrap: wrap; align-items: center; gap: .4rem; margin-bottom: .9rem; }
        .ds-stat { font-size: .78rem; font-weight: 600; padding: .25rem .6rem; border-radius: 999px; }
        .ds-stat b { font-weight: 800; }
        .ds-stat.is-open { background: #dcfce7; color: #15803d; }
        .ds-stat.is-closed { background: #fee2e2; color: #b91c1c; }
        .ds-stat.is-booked { background: #e3ecff; color: #1d4ed8; }
        .ds-stat.is-pending { background: #fff4d6; color: #8a6100; }
        .ds-stat.is-completed { background: #efe7ff; color: #6d28d9; }
        .ds-label { font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: var(--ink-500); margin: .9rem 0 .45rem; }
        .ds-hint { font-size: .72rem; font-weight: 500; text-transform: none; letter-spacing: 0; }
        .ds-blocks { display: flex; flex-direction: column; gap: .4rem; }
        .ds-block { border-radius: .6rem; padding: .55rem .8rem; border-left: 4px solid transparent; font-size: .85rem; }
        .ds-block-time { font-weight: 700; }
        .ds-block-main { display: flex; flex-wrap: wrap; align-items: center; gap: .4rem; margin-top: .1rem; }
        .ds-block-status { font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .03em; padding: .1rem .45rem; border-radius: 999px; background: rgba(255,255,255,.7); }
        .ds-block-id { font-size: .75rem; opacity: .75; }
        .ds-block-svc { font-size: .78rem; opacity: .85; margin-top: .1rem; }
        .ds-block.is-booked { background: #eef3ff; border-left-color: #1d4ed8; color: #1e3a8a; }
        .ds-block.is-pending { background: #fff8e6; border-left-color: #d4a017; color: #6b4b00; }
        .ds-block.is-completed { background: #f4efff; border-left-color: #6d28d9; color: #4c1d95; }
        .ds-group-label { font-size: .7rem; font-weight: 700; color: var(--ink-500); margin: .5rem 0 .35rem; display: flex; align-items: center; gap: .35rem; }
        .ds-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(96px, 1fr)); gap: .4rem; }
        .ds-grid form { display: contents; }
        .ds-chip {
            display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0;
            width: 100%; border-radius: .55rem; padding: .4rem .3rem; border: 1px solid transparent;
            font-size: .84rem; font-weight: 700; line-height: 1.2; transition: transform .1s ease, box-shadow .1s ease;
        }
        .ds-chip small { font-size: .66rem; font-weight: 600; opacity: .85; }
        button.ds-chip:not(:disabled):hover { transform: translateY(-1px); box-shadow: 0 4px 10px -6px rgba(15,23,42,.35); }
        button.ds-chip:disabled { cursor: not-allowed; opacity: .6; }
        .ds-chip.is-open { background: #f0fdf4; border-color: #86efac; color: #15803d; }
        .ds-chip.is-closed { background: #fef2f2; border-color: #fca5a5; color: #b91c1c; }
        /* Confirm popup stacks above the open day window. */
        #appConfirmModal { z-index: 1070; }
        .modal-backdrop.show + .modal-backdrop.show { z-index: 1065; }
        .ds-chip.is-booked { background: #eef3ff; border-color: #bfd0ff; color: #1d4ed8; cursor: default; }
        .ds-chip.is-pending { background: #fff8e6; border-color: #f5d784; color: #8a6100; cursor: default; }
        .ds-chip.is-completed { background: #f4efff; border-color: #d8c7ff; color: #6d28d9; cursor: default; }
        .ds-note { margin-top: 1rem; font-size: .78rem; color: var(--ink-500); background: #f8fafc; border-radius: .55rem; padding: .55rem .75rem; }
    </style>

    {{-- ===================== ONE MODAL PER DAY IN THE CURRENT MONTH (WEEKLY CLOSED DAYS SKIPPED) ===================== --}}
    {{-- Closing (or trying to close) a date/time slot flashes `openDay` back
    from the controller so the admin lands back inside that same day's modal
    instead of on the bare calendar — server-rendered "open" state, same
    pattern used for the OTP modals elsewhere in the app. --}}
    @if (session('openDay'))
        <div class="modal-backdrop fade show"></div>
    @endif
    @foreach ($weeks as $week)
        @foreach ($week as $d)
            @continue($d->month !== $current->month)
            @continue(\App\Models\ClosedDate::isClosedWeekday($d))
            @php
                $dateStr = $d->format('Y-m-d');
                $modalId = 'modalDay' . $d->format('Ymd');
                $daySlots = $schedules[$dateStr] ?? collect();
                $isPast = $d->lt(\Carbon\Carbon::parse($today));
                $isReopened = session('openDay') === $dateStr;
            @endphp

            @php
                $slotTimesForDay = array_keys($slots);
                // Times held by a COMPLETED appointment — those can't be reopened
                // or closed. Pending/Approved times can (closing cancels them).
                $completedTimesForDay = collect($slotTimesForDay)
                    ->filter(fn ($t) => ($occupiedSlots[$dateStr . '_' . $t] ?? null)?->Status === 'Completed');
                $editableTimesForDay = collect($slotTimesForDay)->diff($completedTimesForDay);
                // Distinct appointments (one appointment spans several slots).
                $openBookingsForDay = collect($slotTimesForDay)
                    ->map(fn ($t) => $occupiedSlots[$dateStr . '_' . $t] ?? null)
                    ->filter(fn ($a) => in_array($a?->Status, ['Pending', 'Approved'], true))
                    ->unique(fn ($a) => $a->AppointmentID)
                    ->count();
                $dayFullyClosed = $editableTimesForDay->isNotEmpty()
                    && $editableTimesForDay->every(fn ($t) =>
                        isset($occupiedSlots[$dateStr . '_' . $t])
                        || (($daySlots[$t]->Status ?? 'Available') === 'Not Available'));
            @endphp
            <div class="modal fade {{ $isReopened ? 'show' : '' }}" id="{{ $modalId }}" tabindex="-1"
                aria-hidden="{{ $isReopened ? 'false' : 'true' }}" style="{{ $isReopened ? 'display:block;' : '' }}">
                <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-sm-down">
                    <div class="modal-content">
                        <div class="modal-header">
                            <div>
                                <h5 class="modal-title fw-semibold">{{ $d->format('l, F j, Y') }}</h5>
                            </div>
                            @if (!$isPast && $editableTimesForDay->isNotEmpty())
                                <form method="POST" action="{{ route('dentistSchedule.toggleDay') }}"
                                    class="d-flex align-items-center gap-2 m-0 ms-auto me-3"
                                    @if (!$dayFullyClosed)
                                        data-ds-confirm-title="Make the whole day unavailable?"
                                        data-ds-confirm-message="{{ $openBookingsForDay > 0
                                            ? 'Every hour on ' . $d->format('F j, Y') . ' becomes unavailable, and ' . $openBookingsForDay . ' pending/booked appointment(s) will be cancelled with the patient(s) notified. Continue?'
                                            : 'Every hour on ' . $d->format('F j, Y') . ' becomes unavailable, so patients can\'t book this day. You can make it available again anytime.' }}"
                                        data-ds-confirm-label="Make Unavailable" data-ds-confirm-class="btn-pill-cancel"
                                    @else
                                        data-ds-confirm-title="Make the whole day available again?"
                                        data-ds-confirm-message="Every hour on {{ $d->format('F j, Y') }} becomes available, so patients can book this day with {{ $selectedDentist?->display_name ?? 'this dentist' }} again."
                                        data-ds-confirm-label="Make Available" data-ds-confirm-class="btn-pill-archive"
                                    @endif>
                                    @csrf
                                    <input type="hidden" name="date" value="{{ $dateStr }}">
                                    <input type="hidden" name="month" value="{{ $current->format('Y-m') }}">
                                    <input type="hidden" name="dentist_id" value="{{ $selectedDentistId }}">
                                    <label class="form-check form-switch d-flex align-items-center gap-2 m-0" style="cursor:pointer;">
                                        <input class="form-check-input" type="checkbox" role="switch" style="cursor:pointer;"
                                            {{ !$dayFullyClosed ? 'checked' : '' }} onchange="dsToggleDay(this)">
                                        <span class="small text-muted-2">{{ $dayFullyClosed ? 'Day unavailable' : 'Day available' }}</span>
                                    </label>
                                </form>
                            @endif
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            @php
                                // Compact day view: merged appointment rows + an hour-chip grid.
                                $dsBlocks = [];
                                $dsLast = null;
                                $dsCounts = ['open' => 0, 'closed' => 0, 'booked' => 0, 'pending' => 0, 'completed' => 0];
                                $dsChips = [];
                                foreach ($slots as $time => $label) {
                                    $appt = $occupiedSlots[$dateStr . '_' . $time] ?? null;
                                    $row = $daySlots[$time] ?? null;
                                    if ($appt) {
                                        $kind = $appt->Status === 'Completed' ? 'completed' : ($appt->Status === 'Approved' ? 'booked' : 'pending');
                                        $dsChips[$time] = ['label' => $label, 'kind' => $kind, 'name' => trim(($appt->patientInfo->FirstName ?? '') . ' ' . ($appt->patientInfo->LastName ?? ''))];
                                        $end = \Carbon\Carbon::parse($dateStr . ' ' . $time)->addMinutes(\App\Models\DentistSchedule::SLOT_MINUTES);
                                        if ($dsLast !== null && $dsBlocks[$dsLast]['id'] === $appt->AppointmentID) {
                                            $dsBlocks[$dsLast]['end'] = $end;
                                        } else {
                                            $dsBlocks[] = ['id' => $appt->AppointmentID, 'kind' => $kind, 'appt' => $appt,
                                                'start' => \Carbon\Carbon::parse($dateStr . ' ' . $time), 'end' => $end];
                                            $dsLast = array_key_last($dsBlocks);
                                            $dsCounts[$kind]++;
                                        }
                                    } else {
                                        $dsLast = null;
                                        $kind = ($row->Status ?? 'Available') === 'Not Available' ? 'closed' : 'open';
                                        $dsCounts[$kind]++;
                                        $dsChips[$time] = ['label' => $label, 'kind' => $kind];
                                    }
                                }
                                $dsGroups = [
                                    ['label' => 'Morning', 'icon' => 'bi-sunrise', 'times' => array_filter($dsChips, fn ($t) => (int) substr($t, 0, 2) < 12, ARRAY_FILTER_USE_KEY)],
                                    ['label' => 'Afternoon', 'icon' => 'bi-sun', 'times' => array_filter($dsChips, fn ($t) => (int) substr($t, 0, 2) >= 12, ARRAY_FILTER_USE_KEY)],
                                ];
                                $dsHours = \App\Models\DentistSchedule::clinicHours();
                                $dsKindText = ['booked' => 'Booked', 'pending' => 'Pending', 'completed' => 'Completed'];
                            @endphp
                            <div class="ds-day">
                                <div class="ds-stats">
                                    <span class="ds-stat is-open"><b>{{ $dsCounts['open'] }}</b> available</span>
                                    @if ($dsCounts['closed'])<span class="ds-stat is-closed"><b>{{ $dsCounts['closed'] }}</b> unavailable</span>@endif
                                    @if ($dsCounts['booked'])<span class="ds-stat is-booked"><b>{{ $dsCounts['booked'] }}</b> booked</span>@endif
                                    @if ($dsCounts['pending'])<span class="ds-stat is-pending"><b>{{ $dsCounts['pending'] }}</b> pending</span>@endif
                                    @if ($dsCounts['completed'])<span class="ds-stat is-completed"><b>{{ $dsCounts['completed'] }}</b> completed</span>@endif
                                    @if ($selectedDentist)<span class="small text-muted-2 ms-auto">{{ $selectedDentist->display_name }}</span>@endif
                                </div>

                                @if (!empty($dsBlocks))
                                    <div class="ds-label">Appointments</div>
                                    <div class="ds-blocks">
                                        @foreach ($dsBlocks as $b)
                                            @php $bp = $b['appt']->patientInfo; @endphp
                                            <div class="ds-block is-{{ $b['kind'] }}">
                                                <div class="ds-block-time">{{ $b['start']->format('g:i A') }} – {{ $b['end']->format('g:i A') }}</div>
                                                <div class="ds-block-main">
                                                    <span class="ds-block-status">{{ $dsKindText[$b['kind']] }}</span>
                                                    <span class="fw-semibold">{{ $bp ? trim($bp->FirstName . ' ' . $bp->LastName) : 'Patient' }}</span>
                                                    @if ($bp)<span class="ds-block-id">PT-{{ str_pad($bp->PatientID, 4, '0', STR_PAD_LEFT) }}</span>@endif
                                                </div>
                                                <div class="ds-block-svc">{{ $b['appt']->TypeOfAppointment ?: ($b['appt']->service?->ServiceName ?? '') }}</div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                <div class="ds-label d-flex justify-content-between align-items-baseline">
                                    <span>Hours</span>
                                    @unless ($isPast)<span class="ds-hint">Click an hour to make it unavailable · click again to make it available</span>@endunless
                                </div>
                                @foreach ($dsGroups as $group)
                                    @continue(empty($group['times']))
                                    <div class="ds-group-label"><i class="bi {{ $group['icon'] }}"></i> {{ $group['label'] }}</div>
                                    <div class="ds-grid">
                                        @foreach ($group['times'] as $time => $chip)
                                            @if (in_array($chip['kind'], ['open', 'closed'], true))
                                                <form method="POST" action="{{ route('dentistSchedule.toggle') }}" class="m-0"
                                                    @if ($chip['kind'] === 'open')
                                                        data-confirm-title="Make {{ $chip['label'] }} unavailable?"
                                                        data-confirm-message="Patients won't be able to book {{ $chip['label'] }} on {{ $d->format('F j, Y') }} with {{ $selectedDentist?->display_name ?? 'this dentist' }}. You can make it available again anytime."
                                                        data-confirm-label="Make Unavailable" data-confirm-class="btn-pill-cancel"
                                                    @else
                                                        data-confirm-title="Make {{ $chip['label'] }} available again?"
                                                        data-confirm-message="Patients will be able to book {{ $chip['label'] }} on {{ $d->format('F j, Y') }} with {{ $selectedDentist?->display_name ?? 'this dentist' }} again."
                                                        data-confirm-label="Make Available" data-confirm-class="btn-pill-archive"
                                                    @endif>
                                                    @csrf
                                                    <input type="hidden" name="date" value="{{ $dateStr }}">
                                                    <input type="hidden" name="time" value="{{ $time }}">
                                                    <input type="hidden" name="month" value="{{ $current->format('Y-m') }}">
                                                    <input type="hidden" name="dentist_id" value="{{ $selectedDentistId }}">
                                                    <button type="submit" class="ds-chip is-{{ $chip['kind'] }}" {{ $isPast ? 'disabled' : '' }}
                                                        title="{{ $isPast ? 'This date has passed' : ($chip['kind'] === 'open' ? 'Available — click to make this hour unavailable' : 'Unavailable — click to make this hour available again') }}">
                                                        <span>{{ $chip['label'] }}</span>
                                                        <small>{{ $chip['kind'] === 'open' ? 'Available' : 'Unavailable' }}</small>
                                                    </button>
                                                </form>
                                            @else
                                                <span class="ds-chip is-{{ $chip['kind'] }}" title="{{ $dsKindText[$chip['kind']] }}: {{ $chip['name'] ?: 'Patient' }} — appointment hours can't be toggled">
                                                    <span>{{ $chip['label'] }}</span>
                                                    <small>{{ $dsKindText[$chip['kind']] }}</small>
                                                </span>
                                            @endif
                                        @endforeach
                                    </div>
                                @endforeach

                                @if ($dsHours['lunchEnabled'])
                                    <div class="small text-muted-2 mt-3"><i class="bi bi-cup-hot me-1"></i> Lunch break
                                        {{ \Carbon\Carbon::createFromFormat('H:i', $dsHours['lunchStart'])->format('g:i A') }} – {{ \Carbon\Carbon::createFromFormat('H:i', $dsHours['lunchEnd'])->format('g:i A') }}</div>
                                @endif

                                <div class="ds-note">
                                    @if ($isPast)
                                        <i class="bi bi-lock me-1"></i> This date has already passed — hours are locked.
                                    @else
                                        <i class="bi bi-info-circle me-1"></i> Hours with an appointment can't be changed. To make the whole day unavailable, use the
                                        <strong>Day available</strong> switch above — it cancels this dentist's pending/booked appointments that day and notifies the patient(s).
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @endforeach

    @include('partials.admin-notif-modal')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/reopen-modal.js') }}"></script>
    @include('partials.confirm-action-modal')
<script>
        // Day available/unavailable switch: either direction asks first;
        // cancelling flips the switch back.
        async function dsToggleDay(cb) {
            var form = cb.closest('form');
            var title = form.dataset.dsConfirmTitle;
            if (title && window.appConfirm) {
                var ok = await window.appConfirm({
                    title: title,
                    message: form.dataset.dsConfirmMessage,
                    confirmLabel: form.dataset.dsConfirmLabel || 'Confirm',
                    confirmClass: form.dataset.dsConfirmClass || 'btn-pill-archive',
                });
                if (!ok) { cb.checked = !cb.checked; return; }
            }
            form.submit();
        }
    </script>
</body>

</html>
