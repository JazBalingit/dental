{{--
    Shared booking calendar — used by both the public landing page (online
    booking, $calendarMode = 'post') and the Walk-in Appointment wizard
    ($calendarMode = 'select'). Same visual calendar, two interaction modes:
    'post' submits a real booking immediately per slot; 'select' just fills
    hidden inputs on whatever form is wrapping this partial (used inside the
    walk-in wizard's single <form>, so it must never render a nested <form>).

    Expected variables: $bookWeeks, $bookCurrent, $bookSchedules,
    $bookOccupiedSlots, $bookSlots, $bookToday, $services,
    $bookCurrentPatientId, $calendarMode ('post'|'select')
--}}
@php
    $calendarMode = $calendarMode ?? 'post';
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

<div class="content">
    <div class="schedule-wrap booking-calendar">
        <div class="schedule-toolbar">
            <div>
                <h4>Doctor Schedule</h4>
                <div class="small text-muted-2">{{ $bookCurrent->format('F Y') }}</div>
            </div>

            @if ($calendarMode === 'post')
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('landingPage') }}?bookMonth={{ $bookCurrent->copy()->subMonth()->format('Y-m') }}#appointment"
                        class="btn btn-outline-secondary btn-sm" aria-label="Previous month"><i class="bi bi-chevron-left"></i></a>
                    <form method="GET" action="{{ route('landingPage') }}#appointment" id="bookMonthForm"
                        class="d-flex align-items-center gap-2">
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
                        <button type="submit" class="btn btn-brand btn-sm">Go</button>
                    </form>
                    <a href="{{ route('landingPage') }}?bookMonth={{ $bookCurrent->copy()->addMonth()->format('Y-m') }}#appointment"
                        class="btn btn-outline-secondary btn-sm" aria-label="Next month"><i class="bi bi-chevron-right"></i></a>
                </div>
            @else
                <div class="d-flex align-items-center gap-2">
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
                        $isPast = $d->lt(\Carbon\Carbon::parse($bookToday));
                    @endphp

                    @if ($inMonth && !$isSunday && !$isFullyClosed)
                        <button type="button"
                            class="day-cell border-0 text-start p-0 w-100 d-block {{ $dateStr === $bookToday ? 'today' : '' }}"
                            data-bs-toggle="modal" data-bs-target="#bookDay{{ $d->format('Ymd') }}{{ $calendarMode === 'select' ? 'Wi' : '' }}">
                            <div class="n" style="margin-left: 8px;">{{ $d->day }}</div>
                            @if ($calendarMode === 'post' && $isPast)
                                {{-- A day that's already passed can't be booked, so an available-slot
                                     count would be meaningless — say plainly why there's nothing to book. --}}
                                <span class="ev ev-unavailable">Date has passed</span>
                            @elseif ($calendarMode === 'post')
                                {{-- Patients just need to know how much room is left that day —
                                     a per-slot Completed/Pending/Booked list is admin-side detail. --}}
                                <span class="ev {{ $takenSlots->count() > 0 ? 'ev-pending' : 'ev-booked' }}">
                                    {{ $availableCount }} slot{{ $availableCount === 1 ? '' : 's' }} available
                                </span>
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
                            <span class="ev ev-unavailable">Closed</span>
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
                {{ $calendarMode === 'select' ? 'Click any date to view open times and select one.' : 'Click any date to view open times and book.' }}
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
        @endphp

        <div class="modal fade" id="bookDay{{ $d->format('Ymd') }}{{ $calendarMode === 'select' ? 'Wi' : '' }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-semibold">{{ $d->format('l, F j, Y') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body pt-2">
                        @if ($isPast)
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

                                    <div class="time">{{ $label }}</div>
                                    <div class="slot">

                                        @if ($isAvailable)
                                            @if ($calendarMode === 'select')
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
                                            <div class="slot-btn booking-status {{ $statusClass }} text-center">
                                                <div>{{ $statusLabel }}@if($isMine) · {{ $apptForSlot->TypeOfAppointment ?: ($apptForSlot->service?->ServiceName) }} · {{ $apptForSlot->duration_label }}@endif</div>
                                                @if($calendarMode === 'post' && $isMine && $isStartSlot && $apptForSlot->Status !== 'Completed')
                                                    <div class="d-flex justify-content-center gap-2 mt-2">
                                                        <button type="button" class="btn btn-sm text-white" style="background: var(--brand-700); border-color: var(--brand-700);"
                                                            data-bs-toggle="modal" data-bs-target="#landingRescheduleModal"
                                                            data-remove-url="{{ route('userAppointment.remove', $apptForSlot) }}">Reschedule</button>
                                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                                            data-bs-toggle="modal" data-bs-target="#landingCancelModal"
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

                            @if ($calendarMode === 'post' && session('user_email'))
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
