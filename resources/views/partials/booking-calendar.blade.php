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

<div class="content">
    <div class="schedule-wrap booking-calendar">
        <div class="schedule-toolbar">
            <div>
                <h4>Doctor Schedule</h4>
                <div class="small text-muted-2">{{ $bookCurrent->format('F Y') }}</div>
            </div>

            @if ($calendarMode === 'post')
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
            @else
                <div class="d-flex align-items-center gap-2">
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
                            return isset($bookOccupiedSlots[$dateStr . '_' . $time]) || (($daySlots[$time]->Status ?? 'Available') === 'Not Available');
                        });
                    @endphp

                    @if ($inMonth && !$isSunday)
                        <button type="button"
                            class="day-cell border-0 text-start p-0 w-100 d-block {{ $dateStr === $bookToday ? 'today' : '' }}"
                            data-bs-toggle="modal" data-bs-target="#bookDay{{ $d->format('Ymd') }}{{ $calendarMode === 'select' ? 'Wi' : '' }}">
                            <div class="n" style="margin-left: 8px;">{{ $d->day }}</div>
                            @foreach ($takenSlots->take(3) as $time => $label)
                                @php
                                    $apptKey = $dateStr . '_' . $time;
                                    $apptForSlot = $bookOccupiedSlots[$apptKey] ?? null;
                                    $isMine = $apptForSlot && $apptForSlot->PatientID === $bookCurrentPatientId;
                                    $label = $isMine ? ($apptForSlot->Status === 'Approved' ? 'Booked by you' : 'Appointment Pending') : ($apptForSlot && $apptForSlot->Status === 'Approved' ? 'Booked' : 'Appointment Pending for other patient');
                                @endphp
                                <span
                                    class="ev {{ $label === 'Booked' ? 'ev-unavailable' : 'ev-pending' }}">{{ \Carbon\Carbon::createFromFormat('H:i', $time)->format('g:i A') }}
                                    · {{ $label }}</span>
                            @endforeach
                        </button>
                    @elseif ($inMonth && $isSunday)
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
                                        $isAvailable = !$apptForSlot && (!$row || $row->Status === 'Available');
                                        $isMine = $apptForSlot && $apptForSlot->PatientID === $bookCurrentPatientId;
                                        $isStartSlot = $apptForSlot && $apptForSlot->AppointmentTime === $time;
                                        $statusLabel = $isMine ? ($apptForSlot->Status === 'Approved' ? 'Booked by you' : 'Appointment Pending') : ($apptForSlot && $apptForSlot->Status === 'Approved' ? 'Booked by another patient' : 'Appointment Pending for other patient');
                                    @endphp

                                    <div class="time">{{ $label }}</div>
                                    <div class="slot">

                                        @if ($isAvailable)
                                            @if ($calendarMode === 'select')
                                                <div class="d-flex gap-2 w-100 align-items-center flex-wrap px-3 py-2" style="background:#eaf8ec;border:1px solid #198754;border-radius:8px;">
                                                    {{-- No `required` here on purpose: this select lives inside #walkinForm
                                                         via a Bootstrap modal, and a `required` field inside a closed
                                                         (display:none) modal still silently blocks the outer form's
                                                         native submit in some browsers. JS validates it manually instead. --}}
                                                    <select class="form-select form-select-sm wi-slot-service" style="max-width:220px;">
                                                        <option value="" disabled selected>Select service</option>
                                                        @foreach ($services as $service)
                                                            <option value="{{ $service->ServiceID }}">{{ $service->ServiceName }}</option>
                                                        @endforeach
                                                    </select>
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
                                                    <select class="form-select form-select-sm book-slot-service" style="max-width:220px;" required>
                                                        <option value="" disabled selected>Select service</option>
                                                        @foreach ($services as $service)
                                                            <option value="{{ $service->ServiceID }}">{{ $service->ServiceName }}</option>
                                                        @endforeach
                                                    </select>
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
                                            <div class="slot-btn booking-status {{ $apptForSlot && $apptForSlot->Status === 'Approved' ? 'booking-status-booked' : 'booking-status-pending' }} text-center">
                                                <div>{{ $statusLabel }}@if($isMine && $apptForSlot->Status === 'Approved') · {{ $apptForSlot->service?->ServiceName ?? $apptForSlot->TypeOfAppointment }} · {{ $apptForSlot->DurationHours ?? 1 }} hour(s)@endif</div>
                                                @if($calendarMode === 'post' && $isMine && $isStartSlot)
                                                    <div class="d-flex justify-content-center gap-2 mt-2">
                                                        <form method="POST" action="{{ route('userAppointment.remove', $apptForSlot) }}">@csrf<input type="hidden" name="action" value="reschedule"><button class="btn btn-sm text-white" style="background: var(--brand-700); border-color: var(--brand-700);">Reschedule</button></form>
                                                        <form method="POST" action="{{ route('userAppointment.remove', $apptForSlot) }}">@csrf<input type="hidden" name="action" value="cancel"><button class="btn btn-sm btn-outline-danger">Cancel</button></form>
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
                                                <div class="small text-muted-2">Service</div>
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
                                        <div class="d-flex align-items-center gap-3 py-2">
                                            <i class="bi bi-clock fs-5" style="color:var(--brand-700);width:22px;"></i>
                                            <div>
                                                <div class="small text-muted-2">Time</div>
                                                <div class="fw-semibold book-confirm-time">—</div>
                                            </div>
                                        </div>
                                    </div>

                                    <form method="POST" action="{{ route('booking.store') }}" class="d-flex gap-2">
                                        @csrf
                                        <input type="hidden" name="date" class="book-confirm-date-input">
                                        <input type="hidden" name="time" class="book-confirm-time-input">
                                        <input type="hidden" name="service_id" class="book-confirm-service-input">
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
