{{--
    Shared booking calendar — used by both the public landing page (online
    booking, $calendarMode = 'post') and the Walk-in wizard
    ($calendarMode = 'select'). Same visual calendar, two interaction modes:
    'post' submits a real booking immediately per slot; 'select' just fills
    hidden inputs on whatever form is wrapping this partial (used inside the
    walk-in wizard's single <form>, so it must never render a nested <form>).

    Expected variables: $bookWeeks, $bookCurrent, $bookSchedules,
    $bookOccupiedSlots, $bookSlots, $bookToday, $services,
    $bookCurrentPatientId, $calendarMode ('post'|'select').

    Optional: $readOnly (default false) — renders the calendar as a pure
    display (no service picker/Select button/login link on available slots,
    no Reschedule/Cancel on your own slots, no confirm-booking form). Used
    for the guest schedule preview on the public landing page. $bookBaseUrl
    lets a 'post'-mode caller other than the landing page (e.g. the Patient
    Portal's Appointments page) point month navigation at its own route.
--}}
@php
    $calendarMode = $calendarMode ?? 'post';
    $readOnly = $readOnly ?? false;
    // Patient already has an active appointment: the day picker still shows
    // (same as Follow-up) but explains why Continue stays locked.
    $bookBlocked = $bookBlocked ?? false;
    // Can this visitor pick a slot? Signed-in patients always; the public
    // landing page and the guest reschedule page turn it on for guests.
    $bookInteractive = $bookInteractive ?? (bool) session('user_email');
    // Extra fields rendered inside the confirm form (e.g. "who is this for").
    $bookExtraView = $bookExtraView ?? null;
    // Reschedule mode (staff proposal / patient picking a new time): the same
    // picker, but the confirm step moves an existing appointment instead of
    // creating one — custom form target, no photos/notes, services pre-ticked.
    $bookMode = $bookMode ?? 'book';
    $isReschedule = $bookMode === 'reschedule';
    $bookConfirmAction = $bookConfirmAction ?? route('booking.store');
    $bookPreselect = $bookPreselect ?? [];
    $bookAskReason = $bookAskReason ?? false;
    $bookSubmitLabel = $bookSubmitLabel ?? ($isReschedule ? 'Confirm New Time' : 'Confirm Booking');
    $bookExtraHidden = $bookExtraHidden ?? [];
    // The "mine" slot's Reschedule/Cancel buttons target these modal IDs —
    // default to the landing page's dynamic pair (populated via JS from
    // data-remove-url on show.bs.modal). A caller with its own static
    // modals already bound to the current appointment (e.g. the Patient
    // Portal's Appointments page) can override these instead.
    $rescheduleModalId = $rescheduleModalId ?? 'landingRescheduleModal';
    $cancelModalId = $cancelModalId ?? 'landingCancelModal';
@endphp

<style>
    /* Multi-service picker toggle (looks like a <select> but is a <button>
       holding a checkbox dropdown). Bootstrap's .form-select chevron is a
       background-image sized/positioned for a native <select>'s own box
       model — on a <button> that math doesn't line up the same way and
       long text runs straight into it. Instead: kill that background image
       entirely and lay the button out as its own flex row with a real
       chevron icon in a dedicated slot, so the label truncates in its own
       space and never touches the icon. */
    .book-slot-service-toggle,
    .wi-slot-service-toggle {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .5rem;
        background-color: #fff;
        background-image: none !important;
        cursor: pointer;
    }
    .book-slot-service-toggle-text,
    .wi-slot-service-toggle-text {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        min-width: 0;
    }
    .book-slot-service-toggle .chevron,
    .wi-slot-service-toggle .chevron {
        flex: none;
        font-size: .65rem;
        color: var(--ink-500, #64748b);
        transition: transform .15s ease;
    }
    .book-slot-service-toggle[aria-expanded="true"] .chevron,
    .wi-slot-service-toggle[aria-expanded="true"] .chevron {
        transform: rotate(180deg);
    }
    .book-slot-service-toggle.is-invalid,
    .wi-slot-service-toggle.is-invalid {
        border-color: #dc3545;
    }
    .book-slot-service .dropdown-menu,
    .wi-slot-service .dropdown-menu {
        border-radius: .6rem;
        box-shadow: 0 12px 28px -10px rgba(15, 23, 42, .2);
        border: 1px solid #e5e9e6;
        padding: .4rem;
    }
    .book-slot-service .dropdown-item,
    .wi-slot-service .dropdown-item {
        display: flex;
        align-items: center;
        gap: .55rem;
        border-radius: .4rem;
        cursor: pointer;
        padding: .45rem .6rem;
        white-space: normal;
    }
    .book-slot-service .dropdown-item:hover,
    .wi-slot-service .dropdown-item:hover {
        background-color: #eaf8ec;
    }
    .book-slot-service .dropdown-item input,
    .wi-slot-service .dropdown-item input {
        cursor: pointer;
        flex: none;
    }
    .book-slot-service .dropdown-item .svc-name,
    .wi-slot-service .dropdown-item .svc-name {
        font-weight: 500;
    }
    .book-slot-service .dropdown-item .svc-duration,
    .wi-slot-service .dropdown-item .svc-duration {
        margin-left: auto;
        font-size: .75rem;
        color: var(--ink-500, #64748b);
        flex: none;
        padding-left: .5rem;
    }

    /* Review & Confirm — landscape: summary (left) | note + photos + buttons (right). */
    .book-confirm-view:not([hidden]) { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1.25fr); gap: 1rem 1.25rem; align-items: start; }
    .book-confirm-head { grid-column: 1 / -1; padding-bottom: .75rem; border-bottom: 1px solid #edf1ee; }
    /* Reschedule review has nothing beside the summary — keep it one column. */
    .book-confirm-view.is-single:not([hidden]) { grid-template-columns: minmax(0, 1fr); gap: .85rem; }
    .book-confirm-summary .py-2 { padding-top: .45rem !important; padding-bottom: .45rem !important; }
    .book-confirm-form .book-photos { margin-bottom: .75rem !important; }
    @media (max-width: 767.98px) {
        .book-confirm-view:not([hidden]) { grid-template-columns: minmax(0, 1fr); }
    }

    /* Optional booking photos (Review & Confirm step). */
    .book-photos { border: 1px dashed #cfe9d3; border-radius: .75rem; padding: .75rem .85rem; background: #fbfefb; text-align: left; }
    .book-photos-grid { display: flex; flex-wrap: wrap; gap: .5rem; }
    .book-photos-add, .book-photos-thumb {
        width: 84px; height: 84px; border-radius: .6rem; flex: none; position: relative; overflow: hidden;
    }
    .book-photos-add {
        border: 1.5px dashed #9fd3a7; background: #f3fbf4; color: var(--brand-700, #167d1d);
        display: flex; flex-direction: column; align-items: center; justify-content: center; gap: .15rem;
        font-size: .72rem; font-weight: 600; transition: background .12s ease;
    }
    .book-photos-add i { font-size: 1.25rem; }
    .book-photos-add:hover { background: #e3f6e6; }
    .book-photos-add.is-busy { pointer-events: none; opacity: .6; }
    .book-photos-thumb { border: 1px solid #e5e9e6; background: #f1f5f9; }
    .book-photos-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .book-photos-remove {
        position: absolute; top: 4px; right: 4px; width: 22px; height: 22px; border-radius: 50%; border: 0;
        background: rgba(15, 23, 42, .7); color: #fff; font-size: .7rem; display: flex; align-items: center; justify-content: center;
    }

    /* One look on every page. The landing page loads landing.css instead of
       styles.css, and its own copy of the calendar rules drifted (closed-day
       shading, toolbar controls, greens) — pin the Patient Portal's values. */
    .booking-calendar { border-radius: 16px; box-shadow: 0 2px 8px rgba(15, 76, 122, .06); }
    .booking-calendar .schedule-toolbar h4 { color: #0f7a2f; }
    .booking-calendar .schedule-toolbar .small { color: #64748b; }
    .booking-calendar .schedule-toolbar .form-select,
    .booking-calendar .schedule-toolbar .form-control {
        height: auto; border: 1px solid #e7ecf3; border-radius: 10px;
        font-size: .88rem; line-height: 1.5; padding-top: 9px; padding-bottom: 9px; padding-left: 12px;
    }
    .booking-calendar .schedule-toolbar .form-select { padding-right: 36px; }
    .booking-calendar .schedule-toolbar .form-control { padding-right: 12px; }
    .booking-calendar .schedule-toolbar .btn-brand {
        background: #167d1d; border: 1px solid #167d1d; border-radius: 10px; color: #fff;
        box-shadow: 0 4px 10px rgba(22, 125, 29, .25);
    }
    .booking-calendar .schedule-toolbar .btn-brand:hover { background: #12671a; border-color: #12671a; }
    .booking-calendar .month-grid .mh { color: #167d1d; }
    .booking-calendar .month-grid .day-cell.day-off { background: #f4f5f4; color: #9aa39c; }
    .booking-calendar .month-grid .day-cell.day-off .n { color: #9aa39c; }
    .booking-calendar .month-grid .day-cell.today .n { background: #37a03e; }

    /* Month-grid day badges — one scheme for every booking calendar. */
    .booking-calendar .ev.ev-day-open { background: #dcfce7; color: #15803d; border: 1px solid #86efac; }
    .booking-calendar .ev.ev-day-partial { background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; }
    .booking-calendar .ev.ev-day-full { background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; font-weight: 700; }
    .booking-calendar .ev.ev-day-closed { background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; font-style: normal; }

    /* Simple day picker: ① pick a time (dropdown) → ② pick service(s) → Continue. */
    .book-picker { max-width: 520px; margin: 0 auto; }
    .book-picker-step { display: flex; gap: .85rem; align-items: flex-start; padding: .9rem 0; }
    .book-picker-step + .book-picker-step { border-top: 1px dashed #e5e9e6; }
    .book-picker-num {
        flex: none; width: 28px; height: 28px; border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center;
        background: var(--brand-700, #167d1d); color: #fff; font-weight: 700; font-size: .85rem;
    }
    .book-picker-step.is-locked .book-picker-num { background: #cbd5e1; }
    .book-picker-step.is-locked .form-label { color: var(--ink-500, #64748b); }
    .book-picker .book-slot-service,
    .book-picker .wi-slot-service { max-width: none !important; }
    .book-picker .book-slot-service-toggle,
    .book-picker .wi-slot-service-toggle { width: 100%; }
    .book-picker .dropdown-menu { width: 100%; }
    .book-picker-summary { border-radius: .6rem; padding: .55rem .75rem; background: #eaf8ec; color: #14532d; }
    .book-picker-summary.is-warning { background: #fff8e1; color: #8a6100; }
    /* Time dropdown: a select-looking button opening a tidy grid of time chips. */
    .book-time-toggle {
        width: 100%; display: flex; align-items: center; gap: .6rem;
        background: #fff; border: 1px solid #d6ddd8; border-radius: .6rem;
        padding: .6rem .85rem; font-weight: 600; color: var(--ink-900, #0f172a); text-align: left;
        transition: border-color .15s ease, box-shadow .15s ease;
    }
    .book-time-toggle:hover { border-color: var(--brand-500, #33bd3c); }
    .book-time-toggle:focus, .book-time-toggle[aria-expanded="true"] {
        outline: none; border-color: var(--brand-700, #167d1d); box-shadow: 0 0 0 .2rem rgba(22, 125, 29, .15);
    }
    .book-time-toggle .bi-clock { color: var(--brand-700, #167d1d); }
    .book-time-toggle .placeholder-text { color: var(--ink-500, #64748b); font-weight: 500; }
    .book-time-toggle .chevron { margin-left: auto; font-size: .7rem; color: var(--ink-500, #64748b); transition: transform .15s ease; }
    .book-time-toggle[aria-expanded="true"] .chevron { transform: rotate(180deg); }
    .book-time-menu {
        width: 100%; padding: .75rem; border-radius: .75rem; border: 1px solid #e5e9e6;
        box-shadow: 0 16px 36px -14px rgba(15, 23, 42, .25); max-height: 320px; overflow: auto;
    }
    .book-time-group + .book-time-group { margin-top: .65rem; }
    .book-time-group-label {
        font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em;
        color: var(--ink-500, #64748b); margin-bottom: .4rem; display: flex; align-items: center; gap: .35rem;
    }
    .book-time-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(92px, 1fr)); gap: .4rem; }
    .book-time-chip {
        border: 1px solid #cfe9d3; background: #f3fbf4; color: #14532d; border-radius: .5rem;
        padding: .45rem .3rem; font-size: .85rem; font-weight: 600; transition: all .12s ease;
    }
    .book-time-chip:hover { background: #e3f6e6; border-color: var(--brand-500, #33bd3c); }
    .book-time-chip.is-selected { background: var(--brand-700, #167d1d); border-color: var(--brand-700, #167d1d); color: #fff; }
    .book-picker .book-slot-service-toggle,
    .book-picker .wi-slot-service-toggle {
        border: 1px solid #d6ddd8; border-radius: .6rem; padding: .6rem .85rem; font-weight: 600; min-height: 44px;
    }
    .book-picker .book-slot-service-toggle:disabled,
    .book-picker .wi-slot-service-toggle:disabled { background: #f6f7f6; cursor: not-allowed; opacity: .75; }
    /* Read-only day summary (guest preview). */
    .book-ro { max-width: 520px; margin: 0 auto; }
    .book-ro-stats { display: flex; flex-wrap: wrap; align-items: center; gap: .4rem; margin-bottom: .9rem; }
    .book-ro-stat { font-size: .78rem; font-weight: 600; padding: .25rem .6rem; border-radius: 999px; }
    .book-ro-stat b { font-weight: 800; }
    .book-ro-stat.is-open { background: #e3f6e6; color: #0f7a33; }
    .book-ro-stat.is-booked { background: #e3ecff; color: #1d4ed8; }
    .book-ro-stat.is-pending { background: #fff4d6; color: #8a6100; }
    .book-ro-stat.is-completed { background: #efe7ff; color: #6d28d9; }
    .book-ro-section-label {
        font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em;
        color: var(--ink-500, #64748b); margin: .9rem 0 .4rem;
    }
    .book-ro-blocks { display: flex; flex-direction: column; gap: .35rem; }
    .book-ro-block {
        display: flex; flex-wrap: wrap; align-items: baseline; gap: .25rem .75rem;
        border-radius: .55rem; padding: .5rem .75rem; font-size: .85rem; border-left: 4px solid transparent;
    }
    .book-ro-time { font-weight: 700; white-space: nowrap; min-width: 150px; }
    .book-ro-detail { opacity: .8; font-weight: 500; }
    .book-ro-block.is-booked { background: #eef3ff; border-left-color: #1d4ed8; color: #1e3a8a; }
    .book-ro-block.is-pending { background: #fff8e6; border-left-color: #d4a017; color: #6b4b00; }
    .book-ro-block.is-completed { background: #f4efff; border-left-color: #6d28d9; color: #4c1d95; }
    .book-ro-block.is-unavailable { background: #fdf0f1; border-left-color: #b02a37; color: #7f1d1d; }
    .book-ro-block.is-passed { background: #f3f4f6; border-left-color: #9ca3af; color: #4b5563; }
    .book-time-chip.is-static { display: inline-block; text-align: center; cursor: default; }
    .book-time-chip.is-static:hover { background: #f3fbf4; border-color: #cfe9d3; }
    .book-picker-mine {
        max-width: 520px; margin: 0 auto 1rem; border-radius: .75rem; padding: .75rem 1rem;
        background: #f5f8ff; border: 1px solid #dbe5ff;
    }
</style>

@php
    $bookBaseUrl = $bookBaseUrl ?? ($calendarMode === 'select' ? route('walkIn') : route('landingPage'));
    $bookHash = $bookHash ?? ($calendarMode === 'select' ? '' : '#appointment');
@endphp
<div class="content">
    <div class="schedule-wrap booking-calendar">
        <div class="schedule-toolbar flex-wrap gap-2">
            <div>
                <h4>Dentist Schedule</h4>
                <div class="small text-muted-2">
                    {{ $bookCurrent->format('F Y') }}
                    @if ($bookSelectedDentist)
                        · with <strong>{{ $bookSelectedDentist->display_name }}</strong>
                    @endif
                </div>
            </div>

            <div class="d-flex align-items-center gap-2 flex-wrap">
                {{-- Dentist filter — each dentist keeps their own availability. --}}
                @if ($bookDentists->isNotEmpty())
                    <select id="{{ $calendarMode === 'select' ? 'wiBookDentist' : 'bookDentistSelect' }}"
                        class="form-select form-select-sm" style="width: 200px;"
                        @if ($calendarMode === 'post')
                            onchange="window.location.href='{{ $bookBaseUrl }}?bookMonth={{ $bookCurrent->format('Y-m') }}&dentist=' + this.value + '{{ $bookHash }}'"
                        @endif>
                        @foreach ($bookDentists as $dentist)
                            <option value="{{ $dentist->UserID }}" {{ (int) $bookSelectedDentistId === (int) $dentist->UserID ? 'selected' : '' }}>
                                {{ $dentist->display_name }}
                            </option>
                        @endforeach
                    </select>
                @endif

                {{-- Month navigation kept together so it wraps as one unit on phones --}}
                <div class="sched-monthnav">
                @if ($calendarMode === 'post')
                    <a href="{{ $bookBaseUrl }}?bookMonth={{ $bookCurrent->copy()->subMonth()->format('Y-m') }}&dentist={{ $bookSelectedDentistId }}{{ $bookHash }}"
                        class="btn btn-outline-secondary btn-sm" aria-label="Previous month"><i class="bi bi-chevron-left"></i></a>
                    <form method="GET" action="{{ $bookBaseUrl }}{{ $bookHash }}" id="bookMonthForm"
                        class="sched-monthnav-form d-flex align-items-center gap-2">
                        <select name="bookMonthNum" id="bookMonthNum" class="form-select form-select-sm" style="width: 140px;">
                            @foreach (range(1, 12) as $m)
                                <option value="{{ $m }}" {{ $bookCurrent->month === $m ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                                </option>
                            @endforeach
                        </select>
                        <input type="number" name="bookYear" id="bookYear" class="form-control form-control-sm"
                            style="width: 100px;" min="2000" max="2100" value="{{ $bookCurrent->year }}">
                        <input type="hidden" name="bookMonth" id="bookMonth" value="{{ $bookCurrent->format('Y-m') }}">
                        <input type="hidden" name="dentist" value="{{ $bookSelectedDentistId }}">
                        <button type="submit" class="btn btn-brand btn-sm">Go</button>
                    </form>
                    <a href="{{ $bookBaseUrl }}?bookMonth={{ $bookCurrent->copy()->addMonth()->format('Y-m') }}&dentist={{ $bookSelectedDentistId }}{{ $bookHash }}"
                        class="btn btn-outline-secondary btn-sm" aria-label="Next month"><i class="bi bi-chevron-right"></i></a>
                @else
                    <input type="hidden" id="wiSelectedDentist" value="{{ $bookSelectedDentistId }}">
                    <div class="sched-monthnav-form d-flex align-items-center gap-2">
                        <button type="button" id="wiBookMonthPrev" class="btn btn-outline-secondary btn-sm"
                            data-month="{{ $bookCurrent->copy()->subMonth()->format('Y-m') }}" aria-label="Previous month"><i class="bi bi-chevron-left"></i></button>
                        <select id="wiBookMonthNum" class="form-select form-select-sm" style="width: 140px;">
                            @foreach (range(1, 12) as $m)
                                <option value="{{ $m }}" {{ $bookCurrent->month === $m ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                                </option>
                            @endforeach
                        </select>
                        <input type="number" id="wiBookYear" class="form-control form-control-sm"
                            style="width: 100px;" min="2000" max="2100" value="{{ $bookCurrent->year }}">
                        <button type="button" id="wiBookMonthGo" class="btn btn-brand btn-sm">Go</button>
                        <button type="button" id="wiBookMonthNext" class="btn btn-outline-secondary btn-sm"
                            data-month="{{ $bookCurrent->copy()->addMonth()->format('Y-m') }}" aria-label="Next month"><i class="bi bi-chevron-right"></i></button>
                    </div>
                @endif
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

            @foreach ($bookWeeks as $week)
                @foreach ($week as $d)
                    @php
                        $dateStr = $d->format('Y-m-d');
                        $inMonth = $d->month === $bookCurrent->month;
                        $isWeeklyOff = \App\Models\ClosedDate::isClosedWeekday($d);
                        // Clinic-wide closure (holiday, etc.) — blocks every dentist on
                        // this date, separate from a single dentist's own Not Available
                        // slots below. See Configuration → Closed Dates.
                        $isClinicClosed = \App\Models\ClosedDate::isClosed($d);
                        $clinicClosedReason = \App\Models\ClosedDate::reasonFor($d);
                        $daySlots = $bookSchedules[$dateStr] ?? collect();
                        // Slot math for the day badge (same for every calendar mode):
                        //   capacity = the dentist's working slots (their "Not Available"
                        //              hours are simply not counted),
                        //   booked   = working slots held by an appointment,
                        //   open     = working slots still bookable (not booked, not passed).
                        $capacity = 0;
                        $bookedCount = 0;
                        $openCount = 0;
                        foreach ($bookSlots as $time => $slotLabel) {
                            if (($daySlots[$time]->Status ?? 'Available') === 'Not Available' && !isset($bookOccupiedSlots[$dateStr . '_' . $time])) {
                                continue; // dentist closed this hour
                            }
                            $capacity++;
                            if (isset($bookOccupiedSlots[$dateStr . '_' . $time])) {
                                $bookedCount++;
                            } elseif (!($dateStr === now()->format('Y-m-d') && \Carbon\Carbon::parse($dateStr . ' ' . $time)->lt(now()))) {
                                $openCount++;
                            }
                        }
                        $isPast = $d->lt(\Carbon\Carbon::parse($bookToday));
                        // "Day is over" = a past date, or it's today and every slot's start
                        // time has already gone by (e.g. it's past 6 PM).
                        $allSlotsPassed = $dateStr === now()->format('Y-m-d')
                            && collect($bookSlots)->every(fn ($l, $t) => \Carbon\Carbon::parse($dateStr . ' ' . $t)->lt(now()));
                        $dayIsOver = $isPast || $allSlotsPassed;

                        // green  = open, nobody has booked yet
                        // yellow = open, but some of the working slots are booked
                        // red    = every working slot is booked → "Fully booked"
                        // grey   = closed (dentist closed every hour / clinic / weekly) or passed
                        $dayState = match (true) {
                            $isWeeklyOff || $isClinicClosed => 'closed',
                            $dayIsOver => 'passed',
                            $capacity === 0 => 'closed',
                            $openCount === 0 && $bookedCount > 0 => 'full',
                            $openCount === 0 => 'closed',
                            $bookedCount > 0 => 'partial',
                            default => 'open',
                        };
                    @endphp

                    @if (!$inMonth)
                        <button class="day-cell border-0 text-start p-0 w-100 d-block disabled" disabled>
                            <div class="n" style="margin-left: 8px;">{{ $d->day }}</div>
                        </button>
                    @elseif (in_array($dayState, ['open', 'partial'], true))
                        <button type="button"
                            class="day-cell border-0 text-start p-0 w-100 d-block {{ $dateStr === $bookToday ? 'today' : '' }}"
                            data-bs-toggle="modal" data-bs-target="#bookDay{{ $d->format('Ymd') }}{{ $calendarMode === 'select' ? 'Wi' : '' }}">
                            <div class="n" style="margin-left: 8px;">{{ $d->day }}</div>
                            <span class="ev {{ $dayState === 'open' ? 'ev-day-open' : 'ev-day-partial' }}">
                                {{ $openCount }} slot{{ $openCount === 1 ? '' : 's' }} available
                            </span>
                        </button>
                    @else
                        <button type="button" class="day-cell day-off border-0 text-start p-0 w-100 d-block {{ $dateStr === $bookToday ? 'today' : '' }}" disabled>
                            <div class="n" style="margin-left: 8px;">{{ $d->day }}</div>
                            @if ($dayState === 'full')
                                <span class="ev ev-day-full">Fully booked</span>
                            @elseif ($dayState === 'passed')
                                <span class="ev ev-day-closed">Date has passed</span>
                            @else
                                <span class="ev ev-day-closed">Closed{{ $isClinicClosed && $clinicClosedReason ? ' — ' . $clinicClosedReason : '' }}</span>
                            @endif
                        </button>
                    @endif
                @endforeach
            @endforeach
        </div>

        @php
            // Every dot below uses the EXACT same color as its badge's text
            // color in public/css/styles.css (.ev-*) — never an approximated
            // one picked independently, so the legend can't drift out of
            // sync with what the calendar actually shows again.
            // One legend for every calendar (patient portal, landing preview,
            // follow-up) — matches the .ev-day-* badge colors below.
            $legendItems = [
                ['color' => '#37a03e', 'label' => 'Today'],
                ['color' => '#16a34a', 'label' => 'Available'],
                ['color' => '#d4a017', 'label' => 'Some slots booked'],
                ['color' => '#dc2626', 'label' => 'Fully booked'],
                ['color' => '#94a3b8', 'label' => 'Closed / passed'],
            ];
        @endphp
        <div class="p-3 d-flex justify-content-between flex-wrap gap-2">
            <div class="legend">
                @foreach ($legendItems as $item)
                    <span><span class="dot" style="background: {{ $item['color'] }};"></span>{{ $item['label'] }}</span>
                @endforeach
            </div>
            <div class="small text-muted-2">
                {{ $readOnly ? 'Click a date to view its open times.' : 'Click a date to pick a time and service.' }}
            </div>
        </div>
    </div>
</div>

{{-- ===================== ONE MODAL PER DAY ===================== --}}
@foreach ($bookWeeks as $week)
    @foreach ($week as $d)
        @continue($d->month !== $bookCurrent->month)
        @continue(\App\Models\ClosedDate::isClosedWeekday($d))
        @php
            $dateStr = $d->format('Y-m-d');
            $daySlots = $bookSchedules[$dateStr] ?? collect();
            $isPast = $d->lt(\Carbon\Carbon::parse($bookToday));
            $allSlotsPassed = $dateStr === now()->format('Y-m-d')
                && collect($bookSlots)->every(fn ($l, $t) => \Carbon\Carbon::parse($dateStr . ' ' . $t)->lt(now()));
            $dayIsOver = $isPast || $allSlotsPassed;
        @endphp

        <div class="modal fade book-day-modal" id="bookDay{{ $d->format('Ymd') }}{{ $calendarMode === 'select' ? 'Wi' : '' }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title fw-semibold">{{ $d->format('l, F j, Y') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        @if ($dayIsOver)
                            <p class="text-muted-2 small">This date has already passed.</p>
                        @else
                            @php
                                // Anyone who can actually book (patient portal, follow-up)
                                // gets the compact picker; the read-only guest preview keeps
                                // the full time grid below.
                                $useSimplePicker = !$readOnly && ($calendarMode === 'select' || $bookInteractive);
                            @endphp
                            @if ($useSimplePicker)
                                @php
                                    $openTimes = [];
                                    $myAppointments = [];
                                    foreach (\App\Models\DentistSchedule::slotLabels() as $time => $label) {
                                        $row = $daySlots[$time] ?? null;
                                        $apptForSlot = $bookOccupiedSlots[$dateStr . '_' . $time] ?? null;
                                        $slotHasPassed = $dateStr === now()->format('Y-m-d') && \Carbon\Carbon::parse($dateStr . ' ' . $time)->lt(now());
                                        if (!$slotHasPassed && !$apptForSlot && (!$row || $row->Status === 'Available')) {
                                            $openTimes[$time] = $label;
                                        }
                                        if ($apptForSlot && $apptForSlot->PatientID === $bookCurrentPatientId && $apptForSlot->AppointmentTime === $time) {
                                            $myAppointments[] = $apptForSlot;
                                        }
                                    }
                                    $timeGroups = [
                                        ['label' => 'Morning', 'icon' => 'bi-sunrise', 'times' => array_filter($openTimes, fn ($t) => (int) substr($t, 0, 2) < 12, ARRAY_FILTER_USE_KEY)],
                                        ['label' => 'Afternoon', 'icon' => 'bi-sun', 'times' => array_filter($openTimes, fn ($t) => (int) substr($t, 0, 2) >= 12, ARRAY_FILTER_USE_KEY)],
                                    ];
                                    $isWi = $calendarMode === 'select';
                                @endphp
                                <div class="book-slots-view">
                                    @foreach ($myAppointments as $mine)
                                        <div class="book-picker-mine">
                                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                                <i class="bi bi-calendar2-check" style="color:#1d4ed8;"></i>
                                                <div class="flex-grow-1">
                                                    <div class="fw-semibold small">{{ $isReschedule ? 'Current time — being moved' : ($mine->Status === 'Approved' ? 'Your booked appointment' : ($mine->Status === 'Completed' ? 'Your completed appointment' : 'Your pending appointment')) }}</div>
                                                    <div class="small text-muted-2">{{ $mine->time_range_label }} · {{ $mine->TypeOfAppointment ?: ($mine->service?->ServiceName) }}</div>
                                                </div>
                                                @if ($calendarMode === 'post' && !$isReschedule && $mine->Status !== 'Completed')
                                                    <button type="button" class="btn btn-sm text-white" style="background: var(--brand-700); border-color: var(--brand-700);"
                                                        data-bs-toggle="modal" data-bs-target="#{{ $rescheduleModalId }}"
                                                        data-remove-url="{{ route('userAppointment.remove', $mine) }}">Reschedule</button>
                                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                                        data-bs-toggle="modal" data-bs-target="#{{ $cancelModalId }}"
                                                        data-remove-url="{{ route('userAppointment.remove', $mine) }}"
                                                        data-appt-date="{{ $d->format('M j') }}"
                                                        data-appt-service="{{ $mine->TypeOfAppointment ?: ($mine->service->ServiceName ?? '') }}">Cancel</button>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach

                                    @if (empty($openTimes))
                                        <div class="text-center py-4 text-muted-2">
                                            <i class="bi bi-calendar-x fs-2 d-block mb-2"></i>
                                            No open times left on this day. Please pick another date.
                                        </div>
                                    @else
                                        <div class="book-picker" data-open-times='@json(array_keys($openTimes))'>
                                            {{-- ① Time --}}
                                            <div class="book-picker-step">
                                                <span class="book-picker-num">1</span>
                                                <div class="flex-grow-1" style="min-width:0;">
                                                    <label class="form-label fw-semibold mb-1">Choose a time</label>
                                                    <input type="hidden" class="book-picker-time" value="">
                                                    <div class="dropdown">
                                                        <button type="button" class="book-time-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                            <i class="bi bi-clock"></i>
                                                            <span class="book-time-toggle-text placeholder-text">Select a time</span>
                                                            <i class="bi bi-chevron-down chevron"></i>
                                                        </button>
                                                        <div class="dropdown-menu book-time-menu">
                                                            @foreach ($timeGroups as $group)
                                                                @continue(empty($group['times']))
                                                                <div class="book-time-group">
                                                                    <div class="book-time-group-label"><i class="bi {{ $group['icon'] }}"></i> {{ $group['label'] }}</div>
                                                                    <div class="book-time-grid">
                                                                        @foreach ($group['times'] as $time => $label)
                                                                            <button type="button" class="book-time-chip" data-time="{{ $time }}" data-label="{{ $label }}">{{ $label }}</button>
                                                                        @endforeach
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                    <div class="small text-muted-2 mt-1">{{ count($openTimes) }} open {{ count($openTimes) === 1 ? 'time' : 'times' }} with {{ $bookSelectedDentist?->display_name ?? 'this dentist' }}</div>
                                                </div>
                                            </div>

                                            {{-- ② Service(s) — unlocked once a time is picked --}}
                                            <div class="book-picker-step book-picker-service-step is-locked">
                                                <span class="book-picker-num">2</span>
                                                <div class="flex-grow-1" style="min-width:0;">
                                                    <label class="form-label fw-semibold mb-1">Choose service(s)</label>
                                                    <div class="dropdown {{ $isWi ? 'wi-slot-service' : 'book-slot-service' }}">
                                                        <button type="button" class="form-select {{ $isWi ? 'wi-slot-service-toggle' : 'book-slot-service-toggle' }}"
                                                            data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" disabled>
                                                            <span class="{{ $isWi ? 'wi-slot-service-toggle-text' : 'book-slot-service-toggle-text' }}">Select services</span>
                                                            <i class="bi bi-chevron-down chevron"></i>
                                                        </button>
                                                        <div class="dropdown-menu" style="max-height:240px;overflow:auto;">
                                                            @foreach ($services as $service)
                                                                <label class="dropdown-item">
                                                                    <input class="form-check-input {{ $isWi ? 'wi-slot-service-option' : 'book-slot-service-option' }}" type="checkbox"
                                                                        value="{{ $service->ServiceID }}" data-name="{{ $service->ServiceName }}" data-duration="{{ $service->DurationMinutes }}">
                                                                    <span class="svc-name">{{ $service->ServiceName }}</span>
                                                                    <span class="svc-duration">{{ $service->duration_label }}</span>
                                                                </label>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                    <div class="book-picker-summary small mt-2" hidden></div>
                                                </div>
                                            </div>

                                            @if ($bookBlocked)
                                                <div class="small mt-2 p-2 rounded-3" style="background:#f5f8ff;color:#1e3a8a;">
                                                    <i class="bi bi-info-circle me-1"></i> You already have an upcoming appointment — reschedule or cancel it to book a different time.
                                                </div>
                                            @endif
                                            <div class="d-flex justify-content-end pt-2">
                                                <button type="button" class="btn btn-brand px-4 book-picker-continue {{ $isWi ? '' : 'book-select-btn' }}" disabled
                                                    @if ($bookBlocked) data-blocked="1" @endif
                                                    @if ($isWi)
                                                        data-wi-date="{{ $dateStr }}" data-wi-time="" data-wi-date-label="{{ $d->format('l, F j, Y') }}" data-wi-time-label=""
                                                    @else
                                                        data-date="{{ $dateStr }}" data-time="" data-time-label=""
                                                    @endif>
                                                    Continue <i class="bi bi-arrow-right-short"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @else
                                {{-- Read-only day summary (guest / schedule preview): booked
                                     and pending visits merged into one row each, then the open
                                     times as a compact chip grid — no long half-hour list. --}}
                                @php
                                    $roOpen = [];
                                    $roBlocks = [];   // merged busy ranges, in time order
                                    $last = null;
                                    foreach (\App\Models\DentistSchedule::slotLabels() as $time => $label) {
                                        $row = $daySlots[$time] ?? null;
                                        $appt = $bookOccupiedSlots[$dateStr . '_' . $time] ?? null;
                                        $passed = $dateStr === now()->format('Y-m-d') && \Carbon\Carbon::parse($dateStr . ' ' . $time)->lt(now());
                                        if (!$passed && !$appt && (!$row || $row->Status === 'Available')) {
                                            $roOpen[$time] = $label;
                                            $last = null;
                                            continue;
                                        }
                                        if ($appt) {
                                            $mine = $appt->PatientID === $bookCurrentPatientId;
                                            [$kind, $text] = match (true) {
                                                $appt->Status === 'Completed' => ['completed', 'Completed'],
                                                $appt->Status === 'Approved' => ['booked', $mine ? 'Booked by you' : 'Booked'],
                                                default => ['pending', $mine ? 'Your appointment · Pending' : 'Pending'],
                                            };
                                            $key = 'a' . $appt->AppointmentID;
                                            $detail = $mine ? trim(($appt->TypeOfAppointment ?: ($appt->service?->ServiceName ?? '')) . ' · ' . $appt->duration_label, ' ·') : null;
                                        } else {
                                            [$kind, $text, $key, $detail] = $passed ? ['passed', 'Time has passed', 'passed', null] : ['unavailable', 'Not available', 'na', null];
                                        }
                                        $end = \Carbon\Carbon::parse($dateStr . ' ' . $time)->addMinutes(\App\Models\DentistSchedule::SLOT_MINUTES);
                                        // Extend the previous block when it's the same appointment/state
                                        // and directly follows it (an appointment can span lunch).
                                        if ($last !== null && $roBlocks[$last]['key'] === $key) {
                                            $roBlocks[$last]['end'] = $end;
                                        } else {
                                            $roBlocks[] = ['key' => $key, 'kind' => $kind, 'text' => $text, 'detail' => $detail,
                                                'start' => \Carbon\Carbon::parse($dateStr . ' ' . $time), 'end' => $end];
                                            $last = array_key_last($roBlocks);
                                        }
                                    }
                                    $roCounts = collect($roBlocks)->groupBy('kind')->map->count();
                                    $roGroups = [
                                        ['label' => 'Morning', 'icon' => 'bi-sunrise', 'times' => array_filter($roOpen, fn ($t) => (int) substr($t, 0, 2) < 12, ARRAY_FILTER_USE_KEY)],
                                        ['label' => 'Afternoon', 'icon' => 'bi-sun', 'times' => array_filter($roOpen, fn ($t) => (int) substr($t, 0, 2) >= 12, ARRAY_FILTER_USE_KEY)],
                                    ];
                                    $roHours = \App\Models\DentistSchedule::clinicHours();
                                @endphp
                                <div class="book-slots-view book-ro">
                                    <div class="book-ro-stats">
                                        <span class="book-ro-stat is-open"><b>{{ count($roOpen) }}</b> open</span>
                                        @if ($roCounts['booked'] ?? 0)<span class="book-ro-stat is-booked"><b>{{ $roCounts['booked'] }}</b> booked</span>@endif
                                        @if ($roCounts['pending'] ?? 0)<span class="book-ro-stat is-pending"><b>{{ $roCounts['pending'] }}</b> pending</span>@endif
                                        @if ($roCounts['completed'] ?? 0)<span class="book-ro-stat is-completed"><b>{{ $roCounts['completed'] }}</b> completed</span>@endif
                                        @if ($bookSelectedDentist)<span class="small text-muted-2 ms-auto">with {{ $bookSelectedDentist->display_name }}</span>@endif
                                    </div>

                                    @if (!empty($roBlocks))
                                        <div class="book-ro-section-label">Taken</div>
                                        <div class="book-ro-blocks">
                                            @foreach ($roBlocks as $b)
                                                <div class="book-ro-block is-{{ $b['kind'] }}">
                                                    <span class="book-ro-time">{{ $b['start']->format('g:i A') }} – {{ $b['end']->format('g:i A') }}</span>
                                                    <span class="book-ro-label">{{ $b['text'] }}@if ($b['detail']) <span class="book-ro-detail">· {{ $b['detail'] }}</span>@endif</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    <div class="book-ro-section-label">Open times</div>
                                    @if (empty($roOpen))
                                        <div class="small text-muted-2 py-2">No open times left on this day.</div>
                                    @else
                                        @foreach ($roGroups as $group)
                                            @continue(empty($group['times']))
                                            <div class="book-time-group">
                                                <div class="book-time-group-label"><i class="bi {{ $group['icon'] }}"></i> {{ $group['label'] }}</div>
                                                <div class="book-time-grid">
                                                    @foreach ($group['times'] as $time => $label)
                                                        <span class="book-time-chip is-static">{{ $label }}</span>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif

                                    @if ($roHours['lunchEnabled'])
                                        <div class="small text-muted-2 mt-3"><i class="bi bi-cup-hot me-1"></i> Lunch break
                                            {{ \Carbon\Carbon::createFromFormat('H:i', $roHours['lunchStart'])->format('g:i A') }} – {{ \Carbon\Carbon::createFromFormat('H:i', $roHours['lunchEnd'])->format('g:i A') }}</div>
                                    @endif

                                    @if (session('user_email') && $bookBaseUrl !== route('landingPage'))
                                        {{-- Patient Portal shows this read-only view while the patient
                                             already has an active appointment. --}}
                                        <div class="small mt-3 p-2 rounded-3" style="background:#f5f8ff;color:#1e3a8a;">
                                            <i class="bi bi-info-circle me-1"></i> You already have an upcoming appointment — reschedule or cancel it to book a different time.
                                        </div>
                                    @elseif (!empty($roOpen))
                                        <div class="d-flex justify-content-end mt-3">
                                            @if (session('user_email'))
                                                <a href="{{ route('userAppointment.book', ['bookMonth' => $d->format('Y-m'), 'dentist' => $bookSelectedDentistId]) }}" class="btn btn-brand px-4">
                                                    <i class="bi bi-calendar-plus me-1"></i> Book this day</a>
                                            @else
                                                <a href="{{ route('login') }}" class="btn btn-brand px-4"><i class="bi bi-box-arrow-in-right me-1"></i> Log in to book</a>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @endif

                            @if (!$readOnly && $calendarMode === 'post' && $bookInteractive)
                                {{-- Landscape review: summary on the left, note/photos + buttons on the right
                                     (stacks to one column on phones). --}}
                                <div class="book-confirm-view {{ $isReschedule ? 'is-single' : '' }}" hidden>
                                    <div class="book-confirm-head d-flex align-items-center gap-3">
                                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle flex-none"
                                            style="width:44px;height:44px;background:var(--brand-50);color:var(--brand-700);flex:none;">
                                            <i class="bi bi-calendar2-check fs-5"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold fs-5 lh-sm">{{ $isReschedule ? 'Review the New Time' : 'Review Your Appointment' }}</div>
                                            <div class="small text-muted-2">{{ $isReschedule ? 'Double-check the new date and time, then confirm.' : 'Double-check the details, then confirm to book this slot.' }}</div>
                                        </div>
                                    </div>

                                    <div class="book-confirm-summary rounded-3 p-3" style="background:#fafbfa;border:1px solid #edf1ee;">
                                        <div class="d-flex align-items-center gap-3 py-2 border-bottom" style="border-color:#e5e9e6 !important;">
                                            <i class="bi bi-person-badge fs-5" style="color:var(--brand-700);width:22px;"></i>
                                            <div>
                                                <div class="small text-muted-2">Dentist</div>
                                                <div class="fw-semibold">{{ $bookSelectedDentist?->display_name ?? '—' }}</div>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center gap-3 py-2 border-bottom" style="border-color:#e5e9e6 !important;">
                                            <i class="bi bi-clipboard2-pulse fs-5" style="color:var(--brand-700);width:22px;"></i>
                                            <div>
                                                <div class="small text-muted-2">Service(s)</div>
                                                <div class="fw-semibold book-confirm-service">—</div>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center gap-3 py-2 border-bottom" style="border-color:#e5e9e6 !important;">
                                            <i class="bi bi-calendar-event fs-5" style="color:var(--brand-700);width:22px;"></i>
                                            <div>
                                                <div class="small text-muted-2">Date</div>
                                                <div class="fw-semibold book-confirm-date">—</div>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center gap-3 py-2 border-bottom" style="border-color:#e5e9e6 !important;">
                                            <i class="bi bi-clock fs-5" style="color:var(--brand-700);width:22px;"></i>
                                            <div>
                                                <div class="small text-muted-2">Time</div>
                                                <div class="fw-semibold book-confirm-time">—</div>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center gap-3 py-2">
                                            <i class="bi bi-hourglass-split fs-5" style="color:var(--brand-700);width:22px;"></i>
                                            <div>
                                                <div class="small text-muted-2">Duration</div>
                                                <div class="fw-semibold book-confirm-duration">—</div>
                                            </div>
                                        </div>
                                    </div>

                                    <form method="POST" action="{{ $bookConfirmAction }}" enctype="multipart/form-data" class="book-confirm-form">
                                        @csrf
                                        <input type="hidden" name="date" class="book-confirm-date-input">
                                        <input type="hidden" name="time" class="book-confirm-time-input">
                                        <input type="hidden" name="dentist_id" value="{{ $bookSelectedDentistId }}">
                                        <div class="book-confirm-service-inputs"></div>
                                        @foreach ($bookExtraHidden as $hName => $hValue)
                                            <input type="hidden" name="{{ $hName }}" value="{{ $hValue }}">
                                        @endforeach

                                        @if ($isReschedule)
                                            @if ($bookAskReason)
                                                <div class="book-photos mb-3">
                                                    <label class="fw-semibold small mb-1 d-block" for="rsReason{{ $d->format('Ymd') }}"><i class="bi bi-chat-left-text me-1" style="color:var(--brand-700);"></i> Reason for the patient <span class="text-muted-2 fw-normal">(optional)</span></label>
                                                    <p class="small text-muted-2 mb-1">Shown in the patient's notification, e.g. "Dr. Rudinas is unavailable that day."</p>
                                                    <textarea class="form-control form-control-sm" id="rsReason{{ $d->format('Ymd') }}" name="reason" rows="3" maxlength="500"></textarea>
                                                </div>
                                            @endif
                                        @else

                                        @if ($bookExtraView)
                                            @include($bookExtraView, ['dayKey' => $d->format('Ymd')])
                                        @endif

                                        {{-- Help your dentist prepare: a note and/or photos — at least one is required.
                                             Photos are shrunk in the browser before upload. --}}
                                        <div class="book-photos mb-3">
                                            <div class="fw-semibold small mb-1"><i class="bi bi-clipboard2-heart me-1" style="color:var(--brand-700);"></i> Help your dentist prepare</div>
                                            <p class="small text-muted-2 mb-2">
                                                Write a short note, attach photos, or both — <strong>at least one is required</strong>.
                                                With a photo the note is optional; with a note the photos are optional. Only you and the clinic can see these.
                                            </p>

                                            <label class="fw-semibold small mb-1 d-block" for="bookNotes{{ $d->format('Ymd') }}"><i class="bi bi-journal-text me-1" style="color:var(--brand-700);"></i> Notes for your dentist</label>
                                            <p class="small text-muted-2 mb-1">
                                                Where it hurts, how long you've had it, or what you'd like done.
                                            </p>
                                            <textarea class="form-control form-control-sm book-notes-input mb-1" id="bookNotes{{ $d->format('Ymd') }}" name="patient_notes"
                                                rows="3" maxlength="1000" placeholder="e.g. My lower left back tooth hurts when I drink something cold, for about two weeks now."></textarea>
                                            <div class="small text-muted-2 text-end mb-2"><span class="book-notes-count">0</span> / 1000</div>

                                            <div class="d-flex align-items-center justify-content-between gap-2 mb-1">
                                                <div class="fw-semibold small"><i class="bi bi-images me-1" style="color:var(--brand-700);"></i> Photos</div>
                                                <span class="small text-muted-2"><span class="book-photos-count">0 / {{ \App\Models\AppointmentAttachment::MAX_FILES }}</span> · JPG, PNG or WebP</span>
                                            </div>
                                            <p class="small text-muted-2 mb-2">
                                                For example a clear photo of the affected tooth or gums, a dental X-ray, or your previous dental records.
                                            </p>
                                            <input type="file" class="book-photos-input" name="attachments[]" accept="image/jpeg,image/png,image/webp" multiple hidden>
                                            <div class="book-photos-grid">
                                                <button type="button" class="book-photos-add">
                                                    <i class="bi bi-camera"></i><span>Add photo</span>
                                                </button>
                                            </div>
                                            <div class="small mt-1 book-photos-error" style="color:#b91c1c;" hidden></div>
                                        </div>
                                        @endif

                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-ghost book-confirm-back-btn"><i class="bi bi-arrow-left me-1"></i>Back</button>
                                            <button type="submit" class="btn btn-brand flex-grow-1"><i class="bi bi-check2-circle me-1"></i>{{ $bookSubmitLabel }}</button>
                                        </div>
                                    </form>
                                </div>
                            @endif
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endforeach


{{--
    'post'-mode booking interactions — month-nav form, the multi-service
    dropdown label, and the Review & Confirm step inside a day modal
    (select service(s) -> review -> submit). Lives here (not on the
    consuming page) so every 'post'-mode caller — the landing page's guest
    preview and the Patient Portal's Appointments page — gets a working
    booking flow without duplicating this script. Guarded so including the
    partial more than once per page only wires it up once.
--}}
<script>
(function () {
    if (window.__bookingCalendarInteractions) return;
    window.__bookingCalendarInteractions = true;

    document.getElementById('bookMonthForm')?.addEventListener('submit', function () {
      var month = document.getElementById('bookMonthNum').value.padStart(2, '0');
      var year = document.getElementById('bookYear').value;
      document.getElementById('bookMonth').value = year + '-' + month;
    });

    // ---------- Clinic slot grid, mirrors DentistSchedule on the server — used to
    // preview the actual end time (skipping the lunch-hour gap) before submitting ----------
    var SLOT_TIMES = @json(\App\Models\DentistSchedule::slotTimes());
    var SLOT_MINUTES = {{ \App\Models\DentistSchedule::SLOT_MINUTES }};

    function formatTime12h(hours, minutes) {
      var period = hours >= 12 ? 'PM' : 'AM';
      var hour12 = hours % 12 || 12;
      return hour12 + ':' + String(minutes).padStart(2, '0') + ' ' + period;
    }

    // "1 hour 30 minutes" / "30 minutes" — mirrors DentistSchedule::formatSlotDuration().
    function formatDurationLabel(totalMinutes) {
      var hours = Math.floor(totalMinutes / 60);
      var minutes = totalMinutes % 60;
      var parts = [];
      if (hours > 0) parts.push(hours + ' hour' + (hours > 1 ? 's' : ''));
      if (minutes > 0) parts.push(minutes + ' minute' + (minutes > 1 ? 's' : ''));
      return parts.length ? parts.join(' ') : '0 minutes';
    }

    // The last reserved slot's end time for a booking of totalMinutes
    // starting at startTime — null if it would run past closing (the
    // actual check still happens server-side; this is just a preview).
    function computeEndTimeLabel(startTime, totalMinutes) {
      var slotsNeeded = Math.max(1, Math.ceil(totalMinutes / SLOT_MINUTES));
      var startIndex = SLOT_TIMES.indexOf(startTime);
      if (startIndex === -1) return null;
      var lastIndex = startIndex + slotsNeeded - 1;
      if (lastIndex >= SLOT_TIMES.length) return null;
      var lastSlot = SLOT_TIMES[lastIndex].split(':').map(Number);
      var endMinutesTotal = lastSlot[0] * 60 + lastSlot[1] + SLOT_MINUTES;
      return formatTime12h(Math.floor(endMinutesTotal / 60), endMinutesTotal % 60);
    }

    // ---------- Multi-service dropdown: keep each toggle button's label in sync ----------
    function updateServiceToggleLabel(wrapper) {
      var text = wrapper.querySelector('.book-slot-service-toggle-text');
      var checked = wrapper.querySelectorAll('.book-slot-service-option:checked');
      text.textContent = checked.length
        ? Array.from(checked).map(function (c) { return c.dataset.name; }).join(', ')
        : 'Select services';
    }

    document.addEventListener('change', function (e) {
      if (e.target.matches('.book-slot-service-option')) {
        var wrapper = e.target.closest('.book-slot-service');
        updateServiceToggleLabel(wrapper);
        wrapper.querySelector('.book-slot-service-toggle').classList.remove('is-invalid');
      }
    });

    // ---------- Review & Confirm inside the day modal (select service(s) -> review -> confirm) ----------
    document.addEventListener('click', function (e) {
      var selectBtn = e.target.closest('.book-select-btn');
      if (selectBtn) {
        var row = selectBtn.closest('.book-picker') || selectBtn.closest('.d-flex');
        var wrapper = row ? row.querySelector('.book-slot-service') : null;
        var checked = wrapper ? Array.from(wrapper.querySelectorAll('.book-slot-service-option:checked')) : [];

        if (!checked.length) {
          if (wrapper) wrapper.querySelector('.book-slot-service-toggle').classList.add('is-invalid');
          return;
        }
        wrapper.querySelector('.book-slot-service-toggle').classList.remove('is-invalid');

        var modalEl = selectBtn.closest('.modal');
        if (!modalEl) return;

        var slotsView = modalEl.querySelector('.book-slots-view');
        var confirmView = modalEl.querySelector('.book-confirm-view');
        if (!slotsView || !confirmView) return;

        var dateLabel = modalEl.querySelector('.modal-title')?.textContent || selectBtn.dataset.date;
        var totalMinutes = checked.reduce(function (sum, c) { return sum + (parseInt(c.dataset.duration, 10) || 60); }, 0);
        var startLabel = selectBtn.dataset.timeLabel || selectBtn.dataset.time;
        var endLabel = computeEndTimeLabel(selectBtn.dataset.time, totalMinutes);

        confirmView.querySelector('.book-confirm-service').textContent = checked.map(function (c) { return c.dataset.name; }).join(', ');
        confirmView.querySelector('.book-confirm-date').textContent = dateLabel;
        confirmView.querySelector('.book-confirm-time').textContent = endLabel ? (startLabel + ' - ' + endLabel) : startLabel;
        confirmView.querySelector('.book-confirm-duration').textContent = formatDurationLabel(totalMinutes);
        confirmView.querySelector('.book-confirm-date-input').value = selectBtn.dataset.date;
        confirmView.querySelector('.book-confirm-time-input').value = selectBtn.dataset.time;

        var inputsContainer = confirmView.querySelector('.book-confirm-service-inputs');
        inputsContainer.innerHTML = '';
        checked.forEach(function (c) {
          var input = document.createElement('input');
          input.type = 'hidden';
          input.name = 'service_ids[]';
          input.value = c.value;
          inputsContainer.appendChild(input);
        });

        slotsView.hidden = true;
        confirmView.hidden = false;
        // Landscape review: widen the window while the review step is showing,
        // and drop the bottom Close bar (✕ and Back cover it) to save height.
        if (!confirmView.classList.contains('is-single')) modalEl.querySelector('.modal-dialog').classList.add('modal-xl');
        modalEl.querySelector('.modal-footer')?.classList.add('d-none');
        return;
      }

      var backBtn = e.target.closest('.book-confirm-back-btn');
      if (backBtn) {
        var modal = backBtn.closest('.modal');
        if (!modal) return;
        var slots = modal.querySelector('.book-slots-view');
        var confirm = modal.querySelector('.book-confirm-view');
        if (slots) slots.hidden = false;
        if (confirm) confirm.hidden = true;
        modal.querySelector('.modal-dialog').classList.remove('modal-xl');
        modal.querySelector('.modal-footer')?.classList.remove('d-none');
      }
    });

    // ---------- Compact day picker: ① time (chip dropdown) → ② service(s) → Continue ----------
    // Works for both modes: the Continue button carries data-time (patient
    // portal → Review step above) or data-wi-time (follow-up form).
    function refreshPicker(picker) {
      var time = picker.querySelector('.book-picker-time').value;
      var chip = time ? picker.querySelector('.book-time-chip[data-time="' + time + '"]') : null;
      var label = chip ? chip.dataset.label : '';
      var openTimes = JSON.parse(picker.dataset.openTimes || '[]');

      var toggleText = picker.querySelector('.book-time-toggle-text');
      toggleText.textContent = label || 'Select a time';
      toggleText.classList.toggle('placeholder-text', !label);
      picker.querySelectorAll('.book-time-chip').forEach(function (c) { c.classList.toggle('is-selected', c === chip); });

      var step2 = picker.querySelector('.book-picker-service-step');
      step2.classList.toggle('is-locked', !time);
      step2.querySelector('[data-bs-toggle="dropdown"]').disabled = !time;

      var checked = Array.from(picker.querySelectorAll('.book-slot-service-option:checked, .wi-slot-service-option:checked'));
      var total = checked.reduce(function (sum, c) { return sum + (parseInt(c.dataset.duration, 10) || 60); }, 0);

      // Does the whole booking fit in back-to-back open slots from this time?
      var fits = false, endLabel = null;
      if (time && checked.length) {
        var start = SLOT_TIMES.indexOf(time);
        var needed = Math.max(1, Math.ceil(total / SLOT_MINUTES));
        fits = start !== -1 && start + needed <= SLOT_TIMES.length;
        for (var i = 0; fits && i < needed; i++) {
          if (openTimes.indexOf(SLOT_TIMES[start + i]) === -1) fits = false;
        }
        endLabel = computeEndTimeLabel(time, total);
      }

      var summary = picker.querySelector('.book-picker-summary');
      summary.hidden = !(time && checked.length);
      summary.classList.toggle('is-warning', !fits);
      if (time && checked.length) {
        summary.innerHTML = fits
          ? '<i class="bi bi-check-circle me-1"></i>' + label + (endLabel ? ' – ' + endLabel : '') + ' · ' + formatDurationLabel(total)
          : '<i class="bi bi-exclamation-triangle me-1"></i>Not enough open time from ' + label + ' for ' + formatDurationLabel(total) + '. Pick an earlier time or fewer services.';
      }

      var btn = picker.querySelector('.book-picker-continue');
      if (btn.hasAttribute('data-wi-date')) { btn.dataset.wiTime = time; btn.dataset.wiTimeLabel = label; }
      else { btn.dataset.time = time; btn.dataset.timeLabel = label; }
      btn.disabled = !(time && checked.length && fits) || btn.dataset.blocked === '1';
    }

    document.addEventListener('click', function (e) {
      var chip = e.target.closest('.book-time-chip');
      if (!chip) return;
      var picker = chip.closest('.book-picker');
      picker.querySelector('.book-picker-time').value = chip.dataset.time;
      var toggle = picker.querySelector('.book-time-toggle');
      if (window.bootstrap) bootstrap.Dropdown.getOrCreateInstance(toggle).hide();
      refreshPicker(picker);
    });

    document.addEventListener('change', function (e) {
      if (!e.target.matches('.book-slot-service-option, .wi-slot-service-option')) return;
      var picker = e.target.closest('.book-picker');
      if (picker) refreshPicker(picker);
    });

    // ---------- Optional booking photos ----------
    // Each photo is shrunk in the browser (max 1600px, JPEG, under ~900 KB)
    // before it's attached, so uploads are quick on phones and always fit
    // the server's per-photo limit. The kept files live on form._photos and
    // are mirrored into the real <input type=file> for the form submit.
    var PHOTO_MAX = {{ \App\Models\AppointmentAttachment::MAX_FILES }};
    var PHOTO_TARGET_BYTES = 900 * 1024;

    function loadImage(file) {
      return new Promise(function (resolve, reject) {
        var url = URL.createObjectURL(file);
        var img = new Image();
        img.onload = function () { resolve({ img: img, url: url }); };
        img.onerror = function () { URL.revokeObjectURL(url); reject(new Error('unreadable')); };
        img.src = url;
      });
    }

    function canvasToBlob(canvas, quality) {
      return new Promise(function (resolve) { canvas.toBlob(resolve, 'image/jpeg', quality); });
    }

    async function compressPhoto(file) {
      var loaded = await loadImage(file);
      var maxSide = 1600;
      var w = loaded.img.naturalWidth, h = loaded.img.naturalHeight;
      var blob = null;
      for (var attempt = 0; attempt < 6; attempt++) {
        var scale = Math.min(1, maxSide / Math.max(w, h));
        var canvas = document.createElement('canvas');
        canvas.width = Math.round(w * scale);
        canvas.height = Math.round(h * scale);
        var ctx = canvas.getContext('2d');
        ctx.fillStyle = '#fff';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        ctx.drawImage(loaded.img, 0, 0, canvas.width, canvas.height);
        blob = await canvasToBlob(canvas, attempt < 3 ? 0.82 - attempt * 0.12 : 0.6);
        if (blob && blob.size <= PHOTO_TARGET_BYTES) break;
        maxSide = Math.round(maxSide * 0.75);
      }
      URL.revokeObjectURL(loaded.url);
      if (!blob || blob.size > PHOTO_TARGET_BYTES) throw new Error('too big');
      var name = (file.name || 'photo').replace(/\.[^.]+$/, '') + '.jpg';
      return new File([blob], name, { type: 'image/jpeg' });
    }

    function renderPhotos(form) {
      var box = form.querySelector('.book-photos');
      if (!box) return;
      var photos = form._photos || [];
      var input = box.querySelector('.book-photos-input');
      var dt = new DataTransfer();
      photos.forEach(function (f) { dt.items.add(f); });
      input.files = dt.files;

      var grid = box.querySelector('.book-photos-grid');
      grid.querySelectorAll('.book-photos-thumb').forEach(function (t) { URL.revokeObjectURL(t.dataset.url); t.remove(); });
      var add = grid.querySelector('.book-photos-add');
      photos.forEach(function (f, i) {
        var url = URL.createObjectURL(f);
        var thumb = document.createElement('div');
        thumb.className = 'book-photos-thumb';
        thumb.dataset.url = url;
        thumb.innerHTML = '<img alt=""><button type="button" class="book-photos-remove" aria-label="Remove photo"><i class="bi bi-x-lg"></i></button>';
        thumb.querySelector('img').src = url;
        thumb.querySelector('button').dataset.index = i;
        grid.insertBefore(thumb, add);
      });
      add.hidden = photos.length >= PHOTO_MAX;
      box.querySelector('.book-photos-count').textContent = photos.length + ' / ' + PHOTO_MAX;
    }

    function photoError(form, msg) {
      var el = form.querySelector('.book-photos-error');
      if (!el) return;
      el.textContent = msg || '';
      el.hidden = !msg;
    }

    document.addEventListener('click', function (e) {
      var addBtn = e.target.closest('.book-photos-add');
      if (addBtn) { addBtn.closest('.book-photos').querySelector('.book-photos-input').click(); return; }
      var rm = e.target.closest('.book-photos-remove');
      if (rm) {
        var form = rm.closest('form');
        form._photos.splice(parseInt(rm.dataset.index, 10), 1);
        photoError(form, '');
        renderPhotos(form);
      }
    });

    // Note: live character count, and clear the "note or photo" error once typed.
    document.addEventListener('input', function (e) {
      if (!e.target.matches('.book-notes-input')) return;
      var form = e.target.closest('form');
      form.querySelector('.book-notes-count').textContent = e.target.value.length;
      if (e.target.value.trim()) { e.target.classList.remove('is-invalid'); photoError(form, ''); }
    });

    document.addEventListener('change', async function (e) {
      if (!e.target.matches('.book-photos-input')) return;
      var input = e.target;
      var form = input.closest('form');
      var picked = Array.from(input.files || []).filter(function (f) { return (form._photos || []).indexOf(f) === -1; });
      form._photos = form._photos || [];
      var room = PHOTO_MAX - form._photos.length;
      if (!picked.length) return;
      var addBtn = form.querySelector('.book-photos-add');
      addBtn.classList.add('is-busy');
      photoError(form, picked.length > room ? 'Only ' + PHOTO_MAX + ' photos can be attached — extra ones were skipped.' : '');
      for (var i = 0; i < Math.min(room, picked.length); i++) {
        var f = picked[i];
        if (!/^image\/(jpeg|png|webp)$/.test(f.type)) { photoError(form, '"' + f.name + '" isn\'t a JPG, PNG or WebP photo.'); continue; }
        try { form._photos.push(await compressPhoto(f)); }
        catch (err) { photoError(form, 'Couldn\'t use "' + f.name + '". Please try a different photo.'); }
      }
      addBtn.classList.remove('is-busy');
      renderPhotos(form);
      if (form._photos.length) form.querySelector('.book-notes-input')?.classList.remove('is-invalid');
    });

    // Reset every day-modal back to a fresh picker / slot list whenever it's (re)opened.
    document.addEventListener('show.bs.modal', function (e) {
      var slotsView = e.target.querySelector('.book-slots-view');
      var confirmView = e.target.querySelector('.book-confirm-view');
      if (slotsView) slotsView.hidden = false;
      if (confirmView) confirmView.hidden = true;
      if (e.target.classList.contains('book-day-modal')) {
        e.target.querySelector('.modal-dialog')?.classList.remove('modal-xl');
        e.target.querySelector('.modal-footer')?.classList.remove('d-none');
      }

      var photoForm = e.target.querySelector('.book-photos') && e.target.querySelector('.book-photos').closest('form');
      if (photoForm) {
        photoForm._photos = []; photoError(photoForm, ''); renderPhotos(photoForm);
        var notesEl = photoForm.querySelector('.book-notes-input');
        if (notesEl) { notesEl.value = ''; notesEl.classList.remove('is-invalid'); photoForm.querySelector('.book-notes-count').textContent = '0'; }
      }

      var picker = e.target.querySelector('.book-picker');
      if (picker) {
        picker.querySelector('.book-picker-time').value = '';
        picker.querySelectorAll('.book-slot-service-option, .wi-slot-service-option').forEach(function (c) { c.checked = false; });
        var svcText = picker.querySelector('.book-slot-service-toggle-text, .wi-slot-service-toggle-text');
        if (svcText) svcText.textContent = 'Select services';
        refreshPicker(picker);
      }
    });

    // ---------- Taken/unavailable slots gave zero feedback on click (nothing
    // visibly happened, no explanation) — show a small toast instead so a
    // second attempt to book an already-booked slot is obviously rejected. ----------
    function showSlotNotice(message) {
      var existing = document.getElementById('slotNoticeToast');
      if (existing) existing.remove();
      var el = document.createElement('div');
      el.id = 'slotNoticeToast';
      el.setAttribute('role', 'alert');
      el.style.cssText = 'position:fixed;top:84px;right:1.25rem;z-index:1080;background:#fff;'
        + 'border-left:4px solid #dc3545;border-radius:12px;box-shadow:0 18px 40px -12px rgba(15,23,42,.18);'
        + 'padding:.85rem 1.1rem;max-width:min(320px, calc(100vw - 1.5rem));font-size:.85rem;color:#334155;'
        + 'overflow-wrap:anywhere;line-height:1.45;';
      // Phones: span the width with even margins instead of hugging the right edge.
      if (window.matchMedia('(max-width: 575.98px)').matches) {
        el.style.left = '.75rem'; el.style.right = '.75rem'; el.style.maxWidth = 'none';
      }
      el.textContent = message;
      document.body.appendChild(el);
      setTimeout(function () { el.remove(); }, 4000);
    }

    document.addEventListener('click', function (e) {
      if (e.target.closest('button')) return;
      var notice = e.target.closest('[data-slot-notice]');
      if (notice) showSlotNotice(notice.dataset.slotNotice);
    });
    document.addEventListener('keydown', function (e) {
      if (e.key !== 'Enter' && e.key !== ' ') return;
      var notice = e.target.closest && e.target.closest('[data-slot-notice]');
      if (notice) { e.preventDefault(); showSlotNotice(notice.dataset.slotNotice); }
    });

    // ---------- Prevent double-booking from a double-click: disable the
    // Confirm Booking submit the moment it's pressed. ----------
    document.addEventListener('submit', function (e) {
      var form = e.target;
      if (!(form instanceof HTMLFormElement) || !form.closest('.book-confirm-view')) return;

      // A note or at least one photo is required (whichever the patient has).
      var notes = form.querySelector('.book-notes-input');
      if (notes && !notes.value.trim() && !(form._photos || []).length) {
        e.preventDefault();
        photoError(form, 'Please write a note or add a photo so your dentist knows what you need.');
        notes.classList.add('is-invalid');
        notes.focus();
        return;
      }

      var btn = form.querySelector('button[type=submit]');
      if (btn && !btn.disabled) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Please wait…';
      }
    });
})();
</script>

@include('partials.service-limit-script')

@if (!empty($bookPreselect))
    {{-- Reschedule: tick the appointment's original services each time a day opens
         (after the picker's own reset), so staff/patients only change what's needed. --}}
    <script>
        (function () {
            var preselect = @json(array_values(array_map('strval', $bookPreselect)));
            document.addEventListener('shown.bs.modal', function (e) {
                if (!e.target.classList.contains('book-day-modal')) return;
                e.target.querySelectorAll('.book-slot-service-option, .wi-slot-service-option').forEach(function (box) {
                    if (preselect.indexOf(box.value) !== -1 && !box.checked) {
                        box.checked = true;
                        box.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                });
            });
        })();
    </script>
@endif
