<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
  <link rel="icon" type="image/png" href="/images/puspus_logo.png">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Appointment Requests • Dental Clinic</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/medical-history.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modals.css') }}">
    <link rel="stylesheet" href="{{ asset('css/mobile.css') }}">
<style>
        /* Approve confirmation: the dentist's day at a glance. */
        .ap-day { border: 1px solid #e2e8f0; border-radius: .75rem; padding: .6rem .7rem; background: #fcfdfc; }
        .ap-day-title { font-size: .74rem; font-weight: 700; color: #334155; }
        .ap-day-legend { display: flex; gap: .55rem; font-size: .66rem; color: #64748b; }
        .ap-day-legend i { display: inline-block; width: 8px; height: 8px; border-radius: 2px; margin-right: .2rem; vertical-align: middle; }
        .ap-day-legend .is-this { background: #167d1d; } .ap-day-legend .is-free { background: #bbf7d0; } .ap-day-legend .is-busy { background: #fecaca; }
        .ap-day-group { font-size: .64rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: #94a3b8; margin: .35rem 0 .2rem; }
        .ap-day-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(66px, 1fr)); gap: .25rem; }
        .ap-chip { display: flex; flex-direction: column; align-items: center; border-radius: .4rem; padding: .2rem .1rem; font-size: .72rem; font-weight: 700; line-height: 1.15; border: 1px solid transparent; cursor: default; }
        .ap-chip small { font-size: .58rem; font-weight: 600; opacity: .85; }
        .ap-chip.is-free { background: #f0fdf4; border-color: #bbf7d0; color: #15803d; }
        .ap-chip.is-booked { background: #eef3ff; border-color: #c7d6ff; color: #1d4ed8; }
        .ap-chip.is-pending { background: #fff8e6; border-color: #f5d784; color: #8a6100; }
        .ap-chip.is-unavailable { background: #fef2f2; border-color: #fecaca; color: #b91c1c; }
        .ap-chip.is-passed { background: #f1f5f9; border-color: #e2e8f0; color: #94a3b8; }
        .ap-chip.is-this, .ap-chip.is-selected { background: #167d1d; border-color: #167d1d; color: #fff; }
        .ap-chip.is-this.is-released { background: #f0fdf4; border-color: #bbf7d0; color: #15803d; }
        .ap-chip.is-this.is-released small::after { content: ' (freed)'; }
    </style>
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
            @include('partials.admin-sidebar-nav', ['active' => 'appointmentApproval'])
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
                        <h2>Appointment Requests</h2>
                        <div class="crumbs">New booking requests waiting for your decision — review, then approve or decline.</div>
                    </div>
                </div>

                @include('partials.flash-toasts')

                <!-- mini stats -->
                <div class="row g-3 mb-3">
                    <div class="col-6 col-md-3">
                        <div class="stat-card">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="label">Today</div>
                                    <div class="value">{{ $stats['today'] }}</div>
                                </div>
                                <div class="icon"><i class="bi bi-calendar-day"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stat-card alt-1">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="label">Approved</div>
                                    <div class="value">{{ $stats['approved'] }}</div>
                                </div>
                                <div class="icon"><i class="bi bi-check2-circle"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stat-card alt-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="label">Pending</div>
                                    <div class="value">{{ $stats['pending'] }}</div>
                                </div>
                                <div class="icon"><i class="bi bi-hourglass"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stat-card alt-2">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="label">Declined</div>
                                    <div class="value">{{ $stats['declined'] }}</div>
                                </div>
                                <div class="icon"><i class="bi bi-x-circle"></i></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-soft p-3 p-md-4">
                    <form method="GET" action="{{ route('appointmentApproval') }}" class="data-toolbar">
                        <div class="left d-flex align-items-center gap-2">
                            <span class="text-muted-2 small">Filter</span>
                            <select class="form-select" name="status" style="min-width:140px;">
                                <option value="" {{ !$status ? 'selected' : '' }}>All Status</option>
                                <option value="Pending" {{ $status === 'Pending' ? 'selected' : '' }}>Pending</option>
                                <option value="Approved" {{ $status === 'Approved' ? 'selected' : '' }}>Booked</option>
                                <option value="Declined" {{ $status === 'Declined' ? 'selected' : '' }}>Declined</option>
                                <option value="Completed" {{ $status === 'Completed' ? 'selected' : '' }}>Completed</option>
                                <option value="Cancelled" {{ $status === 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                                <option value="No Show" {{ $status === 'No Show' ? 'selected' : '' }}>No Show</option>
                            </select>
                        </div>
                        <div class="right d-flex gap-2">
                            <div class="input-icon search">
                                <i class="bi bi-search"></i>
                                <input class="form-control" name="search" value="{{ $search }}"
                                    placeholder="Search patient or service..."
                                    style="height:40px; padding-left:2.4rem;" />
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table-soft">
                            <thead>
                                <tr>
                                    <th>Patient ID</th>
                                    <th>Patient</th>
                                    <th>Dentist</th>
                                    <th>Service</th>
                                    <th>Date &amp; Time</th>
                                    <th>Booked At</th>
                                    <th>Status</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($appointments as $appt)
                                    @php
                                        $p = $appt->patientInfo;
                                        $pillClass = match ($appt->Status) {
                                            'Approved' => 'pill-info',
                                            'Completed' => 'pill-success',
                                            'Declined', 'Cancelled' => 'pill-danger',
                                            'No Show' => 'pill-muted',
                                            default => 'pill-warning',
                                        };
                                    @endphp
                                    <tr>
                                        <td><span style="font-size:12px; color:#9ca3af; font-weight:500;">{{ $p ? 'PT-' . str_pad($p->PatientID, 4, '0', STR_PAD_LEFT) : '—' }}</span></td>
                                        <td><span><img class="avatar-initials"
                                                    src="{{ $p->photo_url ?? asset('images/default.png') }}"
                                                    alt=""></span>{{ $p->FirstName }} {{ $p->LastName }}
                                            @if (in_array($appt->Source, ['Walk-in', 'Follow-up'], true))
                                                <span class="pill pill-muted">{{ $appt->Source }}</span>
                                            @endif
                                            @if ($appt->is_guest)
                                                <span class="pill pill-muted" title="Booked online without an account">Guest</span>
                                            @endif
                                            @php $mhAlerts = ($appt->medicalHistory ?? $appt->patientInfo?->latestMedicalHistory)?->alerts ?? []; @endphp
                                            @if ($mhAlerts)
                                                <span class="mh-alert is-danger" title="{{ collect($mhAlerts)->pluck('text')->implode(' · ') }}">Medical</span>
                                            @endif
                                            @if ($appt->BookedForOther)
                                                <div class="small text-muted-2" style="font-weight:500;"><i class="bi bi-person-heart"></i> Booked by {{ $appt->booked_by_label }}{{ $appt->patientInfo?->Relationship ? ' · patient is their ' . strtolower($appt->patientInfo->Relationship) : '' }}</div>
                                            @endif
                                        </td>
                                        <td>{{ $appt->dentist_name }}</td>
                                        <td>{{ $appt->TypeOfAppointment ?: ($appt->service->ServiceName ?? '—') }}</td>
                                        <td>{{ $appt->AppointmentDate->format('F j, Y') }} &bull; {{ $appt->time_range_label }}</td>
                                        <td>{{ $appt->created_at->format('M j, Y g:i A') }}</td>
                                        <td><span class="pill {{ $pillClass }}">{{ $appt->Status === 'Approved' ? 'Booked' : $appt->Status }}</span>@include('partials.reschedule-badge', ['appt' => $appt])</td>
                                        <td class="text-end">
                                            <button class="btn btn-pill btn-pill-edit me-1" data-bs-toggle="modal"
                                                data-bs-target="#patientInfoModal{{ $appt->AppointmentID }}"><i
                                                    class="bi bi-eye"></i> View</button>
                                            @if ($appt->can_reschedule)
                                                <a href="{{ route('appointments.reschedule', ['id' => $appt->AppointmentID, 'from' => 'requests']) }}" class="btn btn-pill btn-pill-edit me-1"
                                                    title="Propose a new date/time to the patient"><i class="bi bi-calendar2-week"></i> Reschedule</a>
                                            @endif
                                            @if ($appt->Status === 'Pending')
                                                <button class="btn btn-pill btn-pill-done" data-bs-toggle="modal"
                                                    data-bs-target="#reviewAppointmentModal{{ $appt->AppointmentID }}"><i
                                                        class="bi bi-clipboard2-check"></i> Review</button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted-2 py-4">No appointments found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="pagination-soft">
                        <div>Showing {{ $appointments->count() }} of {{ $appointments->total() }} appointments</div>
                        <div class="pages">
                            @include('partials.pagination-pages', ['paginator' => $appointments])
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    {{-- ===================== PER-ROW MODALS ===================== --}}
    @foreach ($appointments as $appt)
        @php
            $p = $appt->patientInfo;
        @endphp

        @include('partials.appointment-view-modal', ['appt' => $appt])

        @if ($appt->Status === 'Pending')
            <!-- REVIEW APPOINTMENT -->
            <div class="modal fade" id="reviewAppointmentModal{{ $appt->AppointmentID }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable modal-fullscreen-sm-down">
                    <div class="modal-content">
                        <div class="modal-header">
                            <div>
                                <h5 class="modal-title fw-semibold">Review Appointment</h5>
                                <div class="small text-muted">{{ $p->FirstName }} {{ $p->LastName }}'s appointment request</div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form method="POST" action="{{ route('appointmentApproval.approve', $appt->AppointmentID) }}">
                            @csrf
                            <div class="modal-body">
                                {{-- Landscape: request details (left) | patient's note + photos (right). --}}
                                <div class="row g-4">
                                <div class="col-lg-5">
                                <div class="section-label">Appointment Details</div>
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Dentist</label>
                                        <div class="input-icon"><i class="bi bi-person-badge"></i><input type="text"
                                                class="form-control" value="{{ $appt->dentist_name }}" disabled /></div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Service</label>
                                        <div class="input-icon"><i class="bi bi-heart-pulse"></i><input type="text"
                                                class="form-control" value="{{ $appt->TypeOfAppointment ?: ($appt->service->ServiceName ?? '') }}" disabled />
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Date</label>
                                        <div class="input-icon"><i class="bi bi-calendar-event"></i><input type="text"
                                                class="form-control" value="{{ $appt->AppointmentDate->format('F j, Y') }}"
                                                disabled /></div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Time</label>
                                        <div class="input-icon"><i class="bi bi-clock"></i><input type="text"
                                                class="form-control" value="{{ $appt->time_range_label }}" disabled /></div>
                                    </div>
                                </div>
                                </div>
                                <div class="col-lg-7">
                                    @include('partials.appointment-photos', ['appointment' => $appt, 'audience' => 'staff'])
                                </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-pill btn-pill-cancel" data-bs-toggle="modal"
                                    data-bs-target="#declineReasonModal{{ $appt->AppointmentID }}">
                                    <i class="bi bi-x"></i> Decline
                                </button>
                                <button type="button" class="btn btn-pill btn-pill-done" data-swap-modal="#approveConfirmModal{{ $appt->AppointmentID }}"><i class="bi bi-check2"></i>
                                    Approve</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- CONFIRM APPROVAL (optional duration adjustment) -->
            @php $opts = $durationOptions[$appt->AppointmentID] ?? []; @endphp
            <div class="modal fade" id="approveConfirmModal{{ $appt->AppointmentID }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <div>
                                <h5 class="modal-title fw-semibold">Approve appointment?</h5>
                                <div class="small text-muted">{{ $p->FirstName }} {{ $p->LastName }} · {{ $appt->AppointmentDate->format('F j, Y') }}</div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form method="POST" action="{{ route('appointmentApproval.approve', $appt->AppointmentID) }}" class="approve-confirm-form">
                            @csrf
                            <div class="modal-body">
                                <p class="text-muted-2 small mb-3">
                                    The patient will be notified that their appointment is approved. You can confirm it as booked, or adjust the
                                    visit length first — for example if their photos or note show it needs more (or less) time.
                                </p>

                                <div class="rounded-3 p-3 mb-3" style="background:#f8fafc;border:1px solid #e2e8f0;">
                                    <div class="d-flex justify-content-between small"><span class="text-muted-2">Service(s)</span><span class="fw-semibold text-end">{{ $appt->TypeOfAppointment ?: ($appt->service->ServiceName ?? '—') }}</span></div>
                                    <div class="d-flex justify-content-between small mt-1"><span class="text-muted-2">Dentist</span><span class="fw-semibold">{{ $appt->dentist_name }}</span></div>
                                    <div class="d-flex justify-content-between small mt-1"><span class="text-muted-2">Time</span>
                                        <span class="fw-semibold approve-time-label" data-start="{{ \Carbon\Carbon::createFromFormat('H:i', $appt->AppointmentTime)->format('g:i A') }}">{{ $appt->time_range_label }}</span></div>
                                </div>

                                <label class="form-label" for="approveDuration{{ $appt->AppointmentID }}">Duration <span class="text-muted-2 fw-normal">(optional)</span></label>
                                <select class="form-select approve-duration-select" id="approveDuration{{ $appt->AppointmentID }}" name="duration_slots">
                                    @foreach ($opts as $slots => $o)
                                        <option value="{{ $slots }}" data-end="{{ $o['end'] }}" {{ $o['current'] ? 'selected' : '' }} {{ $o['available'] ? '' : 'disabled' }}>
                                            {{ $o['label'] }} — until {{ $o['end'] }}{{ $o['current'] ? '  (as booked)' : '' }}{{ !$o['available'] && $o['reason'] ? '  · not allowed: ' . $o['reason'] : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="small text-muted-2 mt-1 approve-duration-hint">
                                    Leave it as booked, or pick a longer/shorter visit. Longer options are only available while the dentist's next hours are free.
                                </div>
                                @php
                                    $dayStates = $dayOverviews[$appt->AppointmentID] ?? [];
                                    $firstBlocked = collect($opts)->first(fn ($o) => !$o['available']);
                                    $longest = collect($opts)->filter(fn ($o) => $o['available'])->last();
                                    $apStartIndex = array_search($appt->AppointmentTime, array_keys($dayStates), true);
                                    $apGroups = [
                                        'Morning' => array_filter($dayStates, fn ($t) => (int) substr($t, 0, 2) < 12, ARRAY_FILTER_USE_KEY),
                                        'Afternoon' => array_filter($dayStates, fn ($t) => (int) substr($t, 0, 2) >= 12, ARRAY_FILTER_USE_KEY),
                                    ];
                                    $apKindText = ['this' => 'This visit', 'free' => 'Free', 'booked' => 'Booked', 'pending' => 'Pending', 'unavailable' => 'Unavailable', 'passed' => 'Passed'];
                                @endphp
                                @if ($firstBlocked && $longest)
                                    <div class="small mt-2 p-2 rounded-3" style="background:#fff7ed;color:#9a3412;">
                                        <i class="bi bi-exclamation-triangle me-1"></i>
                                        Can extend until <strong>{{ $longest['end'] }}</strong> at most — {{ $firstBlocked['reason'] }}.
                                    </div>
                                @endif

                                @if (!empty($dayStates))
                                    <div class="ap-day mt-3" data-start-index="{{ $apStartIndex === false ? -1 : $apStartIndex }}">
                                        <div class="d-flex justify-content-between align-items-baseline mb-1">
                                            <span class="ap-day-title">{{ $appt->dentist_name }}'s day · {{ $appt->AppointmentDate->format('M j') }}</span>
                                            <span class="ap-day-legend">
                                                <span><i class="is-this"></i>This visit</span><span><i class="is-free"></i>Free</span><span><i class="is-busy"></i>Taken</span>
                                            </span>
                                        </div>
                                        @foreach ($apGroups as $groupLabel => $groupTimes)
                                            @continue(empty($groupTimes))
                                            <div class="ap-day-group">{{ $groupLabel }}</div>
                                            <div class="ap-day-grid">
                                                @foreach ($groupTimes as $time => $s)
                                                    <span class="ap-chip is-{{ $s['kind'] }}" data-time="{{ $time }}"
                                                        title="{{ $s['label'] }} — {{ $apKindText[$s['kind']] }}{{ $s['who'] ? ': ' . $s['who'] : '' }}">
                                                        {{ $s['label'] }}<small>{{ $apKindText[$s['kind']] }}</small>
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-ghost" data-swap-modal="#reviewAppointmentModal{{ $appt->AppointmentID }}">
                                    <i class="bi bi-arrow-left"></i> Back</button>
                                <button type="submit" class="btn btn-pill btn-pill-done"><i class="bi bi-check2"></i> Confirm Approval</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- DECLINE REASON -->
            <div class="modal fade" id="declineReasonModal{{ $appt->AppointmentID }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <div>
                                <h5 class="modal-title fw-semibold">Decline Appointment</h5>
                                <div class="small text-muted">Let the patient know why this was declined</div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form method="POST" action="{{ route('appointmentApproval.decline', $appt->AppointmentID) }}">
                            @csrf
                            <div class="modal-body">
                                <div class="section-label">Reason for Decline</div>
                                <div class="mb-3">
                                    <label class="form-label">Message to patient</label>
                                    <textarea class="form-control" name="reason" rows="4" required
                                        placeholder="e.g. Requested time slot is no longer available. Please choose another schedule."></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-pill btn-pill-cancel"><i class="bi bi-send"></i> Send &
                                    Decline</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    @endforeach

    @include('partials.admin-notif-modal')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
        // Swap one modal for another (close this one first, then open the
        // target) instead of stacking them — Review ⇄ Approve confirmation.
        document.addEventListener('click', function (e) {
            var btn = e.target.closest('[data-swap-modal]');
            if (!btn) return;
            var from = btn.closest('.modal');
            var to = document.querySelector(btn.dataset.swapModal);
            if (!to) return;
            if (from) {
                from.addEventListener('hidden.bs.modal', function () { bootstrap.Modal.getOrCreateInstance(to).show(); }, { once: true });
                bootstrap.Modal.getOrCreateInstance(from).hide();
            } else {
                bootstrap.Modal.getOrCreateInstance(to).show();
            }
        });

        // Approve confirmation: show the new end time as the duration changes.
        document.addEventListener('change', function (e) {
            if (!e.target.matches('.approve-duration-select')) return;
            var form = e.target.closest('form');
            var label = form.querySelector('.approve-time-label');
            var opt = e.target.selectedOptions[0];
            label.textContent = label.dataset.start + ' - ' + opt.dataset.end;
            label.style.color = opt.defaultSelected ? '' : '#167d1d';

            // Day grid: highlight the hours this visit would now cover.
            var day = form.querySelector('.ap-day');
            if (day) {
                var startIndex = parseInt(day.dataset.startIndex, 10);
                var slots = parseInt(e.target.value, 10);
                day.querySelectorAll('.ap-chip').forEach(function (chip, i) {
                    var covered = startIndex >= 0 && i >= startIndex && i < startIndex + slots;
                    chip.classList.toggle('is-selected', covered && !chip.classList.contains('is-this'));
                    chip.classList.toggle('is-released', chip.classList.contains('is-this') && !covered);
                });
            }
        });
    </script>
    @include('partials.confirm-action-modal')
</body>

</html>
