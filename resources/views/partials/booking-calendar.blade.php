{{--
    Shared booking calendar — used by both the public landing page (online
    booking, $calendarMode = 'post') and the Walk-in Appointment wizard
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
                        $isSunday = $d->isSunday();
                        $daySlots = $bookSchedules[$dateStr] ?? collect();
                        $takenSlots = collect($bookSlots)->filter(function ($label, $time) use ($daySlots, $dateStr, $bookOccupiedSlots) {
                            // A slot whose start time has already gone by today can't be
                            // booked either — count it alongside actually-taken slots so
                            // "N slots available" doesn't include times that have passed.
                            $hasPassed = $dateStr === now()->format('Y-m-d') && \Carbon\Carbon::parse($dateStr . ' ' . $time)->lt(now());

                            return $hasPassed || isset($bookOccupiedSlots[$dateStr . '_' . $time]) || (($daySlots[$time]->Status ?? 'Available') === 'Not Available');
                        });
                        // Every slot that day is either held by an appointment or manually
                        // disabled by the dentist — treat the whole day as closed, same as Sunday.
                        $isFullyClosed = $takenSlots->count() === count($bookSlots);
                        $availableCount = count($bookSlots) - $takenSlots->count();

                        // When a day is full, why? If any appointment holds a slot it's
                        // "Fully booked"; if not, the dentist closed the whole day → "Closed".
                        $dayHasAppointment = collect($bookOccupiedSlots)
                            ->contains(fn ($a, $key) => str_starts_with($key, $dateStr . '_'));
                        $isPast = $d->lt(\Carbon\Carbon::parse($bookToday));

                        // "Day is over" = a past date, or it's today and every slot's start
                        // time has already gone by (e.g. it's past 6 PM). Used to say
                        // "Date has passed" instead of "Closed".
                        $allSlotsPassed = $dateStr === now()->format('Y-m-d')
                            && collect($bookSlots)->every(fn ($l, $t) => \Carbon\Carbon::parse($dateStr . ' ' . $t)->lt(now()));
                        $dayIsOver = $isPast || $allSlotsPassed;

                        // Completed appointments on this day (distinct).
                        $completedThatDay = collect($bookOccupiedSlots)
                            ->filter(fn ($a, $key) => str_starts_with($key, $dateStr . '_') && $a->Status === 'Completed')
                            ->unique(fn ($a) => $a->AppointmentID)
                            ->count();
                        $myCompletedThatDay = collect($bookOccupiedSlots)
                            ->contains(fn ($a, $key) => str_starts_with($key, $dateStr . '_')
                                && $a->Status === 'Completed'
                                && $a->PatientID === $bookCurrentPatientId);
                    @endphp

                    @if ($inMonth && !$isSunday && !$isFullyClosed)
                        <button type="button"
                            class="day-cell border-0 text-start p-0 w-100 d-block {{ $dateStr === $bookToday ? 'today' : '' }}"
                            data-bs-toggle="modal" data-bs-target="#bookDay{{ $d->format('Ymd') }}{{ $calendarMode === 'select' ? 'Wi' : '' }}">
                            <div class="n" style="margin-left: 8px;">{{ $d->day }}</div>
                            @if ($calendarMode === 'post' && $myCompletedThatDay)
                                {{-- The patient already had a visit this day — just say so. --}}
                                <span class="ev ev-completed">Appointment completed</span>
                            @elseif ($calendarMode === 'post' && $dayIsOver)
                                {{-- A day that's already passed can't be booked, so an available-slot
                                     count would be meaningless — say plainly why there's nothing to book. --}}
                                <span class="ev ev-unavailable">Date has passed</span>
                            @elseif ($calendarMode === 'post')
                                {{-- Patients just need to know how much room is left that day —
                                     a per-slot Completed/Pending/Booked list is admin-side detail. --}}
                                <span class="ev {{ $takenSlots->count() > 0 ? 'ev-pending' : 'ev-booked' }}">
                                    {{ $availableCount }} slot{{ $availableCount === 1 ? '' : 's' }} available
                                </span>
                            @elseif ($dayIsOver)
                                {{-- Walk-in calendar, past day: collapse the per-slot list into a
                                     single completed summary instead of a wall of time chips. --}}
                                <span class="ev ev-past">Date passed</span>
                                @if ($completedThatDay > 0)
                                    <span class="ev ev-completed">{{ $completedThatDay }} appointment{{ $completedThatDay === 1 ? '' : 's' }} completed</span>
                                @endif
                            @else
                                @foreach ($takenSlots->take(3) as $time => $label)
                                    @php
                                        $apptKey = $dateStr . '_' . $time;
                                        $apptForSlot = $bookOccupiedSlots[$apptKey] ?? null;
                                        $isMine = $apptForSlot && $apptForSlot->PatientID === $bookCurrentPatientId;
                                        if ($apptForSlot && $apptForSlot->Status === 'Completed') {
                                            $label = 'Completed';
                                            $evClass = 'ev-completed';
                                        } elseif ($isMine) {
                                            $label = $apptForSlot->Status === 'Approved' ? 'Booked by you' : 'Appointment Pending';
                                            $evClass = $apptForSlot->Status === 'Approved' ? 'ev-booked' : 'ev-pending';
                                        } elseif ($apptForSlot) {
                                            $label = $apptForSlot->Status === 'Approved' ? 'Booked' : 'Appointment Pending for other patient';
                                            $evClass = $apptForSlot->Status === 'Approved' ? 'ev-booked' : 'ev-pending';
                                        } else {
                                            $label = 'Not available';
                                            $evClass = 'ev-unavailable';
                                        }
                                    @endphp
                                    <span
                                        class="ev {{ $evClass }}">{{ \Carbon\Carbon::createFromFormat('H:i', $time)->format('g:i A') }}
                                        · {{ $label }}</span>
                                @endforeach
                            @endif
                        </button>
                    @elseif ($inMonth && ($isSunday || $isFullyClosed))
                        <button type="button" class="day-cell day-off border-0 text-start p-0 w-100 d-block" disabled>
                            <div class="n" style="margin-left: 8px;">{{ $d->day }}</div>
                            @if ($isSunday)
                                <span class="ev ev-unavailable">Closed</span>
                            @elseif ($calendarMode === 'post' && $myCompletedThatDay)
                                <span class="ev ev-completed">Appointment completed</span>
                            @elseif ($dayIsOver)
                                <span class="ev ev-unavailable">Date has passed</span>
                            @else
                                <span class="ev ev-unavailable">{{ $dayHasAppointment ? 'Fully booked' : 'Closed' }}</span>
                            @endif
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
                <span><span class="dot" style="background: #dc3545;"></span>Booked</span>
                <span><span class="dot" style="background: #e0a800;"></span>Pending</span>
            </div>
            <div class="small text-muted-2">
                @if ($readOnly)
                    Click any date to view open times.
                @elseif ($calendarMode === 'select')
                    Click any date to view open times and select one.
                @else
                    Click any date to view open times and book.
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ===================== ONE MODAL PER DAY ===================== --}}
@foreach ($bookWeeks as $week)
    @foreach ($week as $d)
        @continue($d->month !== $bookCurrent->month)
        @continue($d->isSunday())
        @php
            $dateStr = $d->format('Y-m-d');
            $daySlots = $bookSchedules[$dateStr] ?? collect();
            $isPast = $d->lt(\Carbon\Carbon::parse($bookToday));
            $allSlotsPassed = $dateStr === now()->format('Y-m-d')
                && collect($bookSlots)->every(fn ($l, $t) => \Carbon\Carbon::parse($dateStr . ' ' . $t)->lt(now()));
            $dayIsOver = $isPast || $allSlotsPassed;
        @endphp

        <div class="modal fade book-day-modal" id="bookDay{{ $d->format('Ymd') }}{{ $calendarMode === 'select' ? 'Wi' : '' }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-sm-down">
                <div class="modal-content">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-semibold">{{ $d->format('l, F j, Y') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body pt-2">
                        @if ($dayIsOver)
                            <p class="text-muted-2 small">This date has already passed.</p>
                        @else
                            <div class="book-slots-view">
                            <div class="schedule-wrap mb-0">
                                <div class="week-grid">
                                    <div class="wh">Time</div>
                                    <div class="wh day">{{ $d->format('D') }} <span class="num">{{ $d->day }}</span></div>
                                @foreach ($bookSlots as $time => $label)
                                    @php
                                        $row = $daySlots[$time] ?? null;
                                        $apptKey = $dateStr . '_' . $time;
                                        $apptForSlot = $bookOccupiedSlots[$apptKey] ?? null;
                                        // A slot earlier today than right now can't be booked either,
                                        // even though nothing occupies it — the clock has moved past it.
                                        $slotHasPassed = $dateStr === now()->format('Y-m-d') && \Carbon\Carbon::parse($dateStr . ' ' . $time)->lt(now());
                                        $isAvailable = !$slotHasPassed && !$apptForSlot && (!$row || $row->Status === 'Available');
                                        $isMine = $apptForSlot && $apptForSlot->PatientID === $bookCurrentPatientId;
                                        $isStartSlot = $apptForSlot && $apptForSlot->AppointmentTime === $time;

                                        if ($slotHasPassed && !$apptForSlot) {
                                            $statusLabel = 'This time has already passed';
                                            $statusClass = 'booking-status-unavailable';
                                        } elseif ($apptForSlot && $apptForSlot->Status === 'Completed') {
                                            $statusLabel = 'Completed';
                                            $statusClass = 'booking-status-completed';
                                        } elseif ($isMine) {
                                            $statusLabel = $apptForSlot->Status === 'Approved' ? 'Booked by you' : 'Appointment Pending';
                                            $statusClass = $apptForSlot->Status === 'Approved' ? 'booking-status-booked' : 'booking-status-pending';
                                        } elseif ($apptForSlot) {
                                            $statusLabel = $apptForSlot->Status === 'Approved' ? 'Booked by another patient' : 'Appointment Pending for other patient';
                                            $statusClass = $apptForSlot->Status === 'Approved' ? 'booking-status-booked' : 'booking-status-pending';
                                        } else {
                                            $statusLabel = 'This schedule is not available';
                                            $statusClass = 'booking-status-unavailable';
                                        }
                                    @endphp

                                    <div class="time {{ $isAvailable ? 'js-slot-head' : 'slot-time-taken' }}"
                                        @if ($isAvailable) role="button" tabindex="0" aria-expanded="false" @endif>
                                        {{ $label }}
                                    </div>
                                    <div class="slot {{ $isAvailable ? 'js-slot-pane' : 'slot-taken' }}">

                                        @if ($isAvailable)
                                            @if ($readOnly && session('user_email'))
                                                <span class="slot-btn is-available text-center" style="cursor:default;pointer-events:none;">Available</span>
                                            @elseif ($readOnly)
                                                <a href="{{ route('login') }}" class="slot-btn is-available text-center">Available</a>
                                            @elseif ($calendarMode === 'select')
                                                <div class="d-flex gap-2 w-100 align-items-center flex-wrap px-3 py-2" style="background:#eaf8ec;border:1px solid #198754;border-radius:8px;">
                                                    <div class="dropdown wi-slot-service" style="max-width:240px;">
                                                        <button type="button" class="form-select form-select-sm wi-slot-service-toggle" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                                                            <span class="wi-slot-service-toggle-text">Select services</span>
                                                            <i class="bi bi-chevron-down chevron"></i>
                                                        </button>
                                                        <div class="dropdown-menu" style="max-height:220px;overflow:auto;min-width:240px;">
                                                            @foreach ($services as $service)
                                                                <label class="dropdown-item">
                                                                    <input class="form-check-input wi-slot-service-option" type="checkbox" value="{{ $service->ServiceID }}" data-name="{{ $service->ServiceName }}" data-duration="{{ $service->DurationMinutes }}">
                                                                    <span class="svc-name">{{ $service->ServiceName }}</span>
                                                                    <span class="svc-duration">{{ $service->duration_label }}</span>
                                                                </label>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                    <button type="button" class="btn btn-sm btn-brand ms-auto"
                                                        data-wi-date="{{ $dateStr }}"
                                                        data-wi-time="{{ $time }}"
                                                        data-wi-date-label="{{ $d->format('l, F j, Y') }}"
                                                        data-wi-time-label="{{ $label }}">
                                                        Select
                                                    </button>
                                                </div>
                                            @elseif (session('user_email'))
                                                <div class="d-flex gap-2 w-100 align-items-center flex-wrap px-3 py-2" style="background:#eaf8ec;border:1px solid #198754;border-radius:8px;">
                                                    <i class="bi bi-clipboard2-pulse" style="color:#198754;"></i>
                                                    <div class="dropdown book-slot-service" style="max-width:240px;">
                                                        <button type="button" class="form-select form-select-sm book-slot-service-toggle" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                                                            <span class="book-slot-service-toggle-text">Select services</span>
                                                            <i class="bi bi-chevron-down chevron"></i>
                                                        </button>
                                                        <div class="dropdown-menu" style="max-height:220px;overflow:auto;min-width:240px;">
                                                            @foreach ($services as $service)
                                                                <label class="dropdown-item">
                                                                    <input class="form-check-input book-slot-service-option" type="checkbox" value="{{ $service->ServiceID }}" data-name="{{ $service->ServiceName }}" data-duration="{{ $service->DurationMinutes }}">
                                                                    <span class="svc-name">{{ $service->ServiceName }}</span>
                                                                    <span class="svc-duration">{{ $service->duration_label }}</span>
                                                                </label>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                    <button type="button" class="btn btn-sm btn-brand ms-auto book-select-btn"
                                                        data-date="{{ $dateStr }}"
                                                        data-time="{{ $time }}"
                                                        data-time-label="{{ $label }}">
                                                        Select <i class="bi bi-arrow-right-short"></i>
                                                    </button>
                                                </div>
                                            @else
                                                <a href="{{ route('login') }}" class="slot-btn is-available text-center">Log in to book this slot</a>
                                            @endif
                                        @else
                                            <div class="slot-btn booking-status {{ $statusClass }} text-center" role="button" tabindex="0"
                                                data-slot-notice="{{ $statusLabel }} — this time can't be selected. Please choose an open slot instead.">
                                                <div>{{ $statusLabel }}@if($isMine) · {{ $apptForSlot->TypeOfAppointment ?: ($apptForSlot->service?->ServiceName) }} · {{ $apptForSlot->duration_label }}@endif</div>
                                                @if(!$readOnly && $calendarMode === 'post' && $isMine && $isStartSlot && $apptForSlot->Status !== 'Completed')
                                                    <div class="d-flex justify-content-center gap-2 mt-2">
                                                        <button type="button" class="btn btn-sm text-white" style="background: var(--brand-700); border-color: var(--brand-700);"
                                                            data-bs-toggle="modal" data-bs-target="#{{ $rescheduleModalId }}"
                                                            data-remove-url="{{ route('userAppointment.remove', $apptForSlot) }}">Reschedule</button>
                                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                                            data-bs-toggle="modal" data-bs-target="#{{ $cancelModalId }}"
                                                            data-remove-url="{{ route('userAppointment.remove', $apptForSlot) }}"
                                                            data-appt-date="{{ $d->format('M j') }}"
                                                            data-appt-service="{{ $apptForSlot->TypeOfAppointment ?: ($apptForSlot->service->ServiceName ?? '') }}">Cancel</button>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                                </div>
                            </div>
                            </div>

                            @if (!$readOnly && $calendarMode === 'post' && session('user_email'))
                                <div class="book-confirm-view" hidden>
                                    <div class="text-center mb-3">
                                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-2"
                                            style="width:52px;height:52px;background:var(--brand-50);color:var(--brand-700);">
                                            <i class="bi bi-calendar2-check fs-4"></i>
                                        </div>
                                        <div class="fw-semibold fs-5">Review Your Appointment</div>
                                        <div class="small text-muted-2">Double-check the details, then confirm to book this slot.</div>
                                    </div>

                                    <div class="rounded-3 p-3 mb-3" style="background:#fafbfa;border:1px solid #edf1ee;">
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

                                    <form method="POST" action="{{ route('booking.store') }}" class="d-flex gap-2">
                                        @csrf
                                        <input type="hidden" name="date" class="book-confirm-date-input">
                                        <input type="hidden" name="time" class="book-confirm-time-input">
                                        <input type="hidden" name="dentist_id" value="{{ $bookSelectedDentistId }}">
                                        <div class="book-confirm-service-inputs"></div>
                                        <button type="button" class="btn btn-ghost book-confirm-back-btn"><i class="bi bi-arrow-left me-1"></i>Back</button>
                                        <button type="submit" class="btn btn-brand flex-grow-1"><i class="bi bi-check2-circle me-1"></i>Confirm Booking</button>
                                    </form>
                                </div>
                            @endif
                        @endif
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endforeach

{{--
    Phone-only slot accordion. On a narrow screen the day modal would
    otherwise be 30+ full service-picker cards stacked vertically. Instead
    each open time collapses to a tappable row; tapping one reveals its
    service picker + Select button (one open at a time). Pure class toggle —
    the CSS that acts on `.is-open` only exists inside the ≤575.98px media
    query, so this is a no-op on tablet/desktop. Covers both the landing
    booking modal and the walk-in wizard (both @include this partial).
--}}
<script>
(function () {
    if (window.__bookingSlotAccordion) return;
    window.__bookingSlotAccordion = true;

    var PHONE = '(max-width: 575.98px)';

    function closeAll(scope) {
        scope.querySelectorAll('.js-slot-head.is-open, .js-slot-pane.is-open')
            .forEach(function (el) {
                el.classList.remove('is-open');
                if (el.classList.contains('js-slot-head')) el.setAttribute('aria-expanded', 'false');
            });
    }

    function toggle(head) {
        var grid = head.closest('.week-grid');
        if (!grid) return;
        var pane = head.nextElementSibling;
        var wasOpen = head.classList.contains('is-open');
        closeAll(grid);
        if (!wasOpen) {
            head.classList.add('is-open');
            head.setAttribute('aria-expanded', 'true');
            if (pane) pane.classList.add('is-open');
            head.scrollIntoView({ block: 'nearest' });
        }
    }

    document.addEventListener('click', function (e) {
        if (!window.matchMedia(PHONE).matches) return;
        var head = e.target.closest('.js-slot-head');
        if (head) toggle(head);
    });

    document.addEventListener('keydown', function (e) {
        if ((e.key !== 'Enter' && e.key !== ' ') || !window.matchMedia(PHONE).matches) return;
        var head = e.target.closest('.js-slot-head');
        if (head) { e.preventDefault(); toggle(head); }
    });

    // Every time a day modal opens, start from the collapsed list.
    document.addEventListener('show.bs.modal', function (e) {
        if (e.target.querySelector) closeAll(e.target);
    });
})();
</script>

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
        var row = selectBtn.closest('.d-flex');
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
      }
    });

    // Reset every day-modal back to the slot list whenever it's (re)opened.
    document.addEventListener('show.bs.modal', function (e) {
      var slotsView = e.target.querySelector('.book-slots-view');
      var confirmView = e.target.querySelector('.book-confirm-view');
      if (slotsView) slotsView.hidden = false;
      if (confirmView) confirmView.hidden = true;
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
        + 'padding:.85rem 1.1rem;max-width:320px;font-size:.85rem;color:#334155;';
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
      var btn = form.querySelector('button[type=submit]');
      if (btn && !btn.disabled) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Booking…';
      }
    });
})();
</script>
