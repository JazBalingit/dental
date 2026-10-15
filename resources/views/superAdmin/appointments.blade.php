<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
  <link rel="icon" type="image/png" href="/images/puspus_logo.png">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Appointments • Dental Clinic</title>
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
        /* "Are you sure?" popup stacks above the open Cancel window. */
        #appConfirmModal { z-index: 1070; }
        .modal-backdrop.show + .modal-backdrop.show { z-index: 1065; }
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
            @include('partials.admin-sidebar-nav', ['active' => 'appointments'])
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
                        <h2>Appointments</h2>
                        <div class="crumbs">Manage booked visits — view details, mark them done, cancel, or record a no-show.</div>
                    </div>
                </div>

                @include('partials.flash-toasts')

                <!-- mini stats -->
                <div class="row g-3 mb-3">
                    <div class="col-6 col-lg-4">
                        <div class="stat-card">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="label">Total</div>
                                    <div class="value">{{ $stats['total'] }}</div>
                                </div>
                                <div class="icon"><i class="bi bi-calendar-day"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-4">
                        <div class="stat-card alt-1">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="label">Completed</div>
                                    <div class="value">{{ $stats['completed'] }}</div>
                                </div>
                                <div class="icon"><i class="bi bi-check2-circle"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-4">
                        <div class="stat-card alt-4">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="label">Approved</div>
                                    <div class="value">{{ $stats['approved'] }}</div>
                                </div>
                                <div class="icon">
                                    <i class="bi bi-hourglass"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-4">
                        <div class="stat-card alt-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="label">Pending</div>
                                    <div class="value">{{ $stats['pending'] }}</div>
                                </div>
                                <div class="icon"><i class="bi bi-clock-history"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-4">
                        <div class="stat-card alt-2">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="label">Declined</div>
                                    <div class="value">{{ $stats['declined'] }}</div>
                                </div>
                                <div class="icon"><i class="bi bi-slash-circle"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-4">
                        <div class="stat-card alt-2">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="label">Cancelled</div>
                                    <div class="value">{{ $stats['cancelled'] }}</div>
                                    <div class="small text-muted-2">{{ $stats['noShow'] ?? 0 }} no-show</div>
                                </div>
                                <div class="icon"><i class="bi bi-x-circle"></i></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-soft p-3 p-md-4">
                    <form method="GET" action="{{ route('appointments') }}" class="data-toolbar">
                        <div class="left">
                            <span class="text-muted-2 small">Filter</span>
                            <select class="form-select" name="status" style="min-width:140px;" onchange="this.form.submit()">
                                <option value="" {{ !$status ? 'selected' : '' }}>All Status</option>
                                <option value="Pending" {{ $status === 'Pending' ? 'selected' : '' }}>Pending</option>
                                <option value="Approved" {{ $status === 'Approved' ? 'selected' : '' }}>Approved</option>
                                <option value="Completed" {{ $status === 'Completed' ? 'selected' : '' }}>Completed</option>
                                <option value="Declined" {{ $status === 'Declined' ? 'selected' : '' }}>Declined</option>
                                <option value="Cancelled" {{ $status === 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                                <option value="No Show" {{ $status === 'No Show' ? 'selected' : '' }}>No Show</option>
                            </select>
                        </div>
                        <div class="right">
                            <div class="input-icon search">
                                <i class="bi bi-search"></i>
                                <input class="form-control" name="search" value="{{ $search }}" placeholder="Search patient or treatment..."
                                    style="height:40px; padding-left:2.4rem;" />
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table-soft">
                            <thead>
                                <tr>
                                    <th>Date &amp; Time</th>
                                    <th>Patient ID</th>
                                    <th>Patient</th>
                                    <th>Dentist</th>
                                    <th>Treatment</th>
                                    <th>Approved At</th>
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
                                        $timeLabel = \Carbon\Carbon::createFromFormat('H:i', $appt->AppointmentTime)->format('g:i A');
                                        $startsAt = \Carbon\Carbon::parse($appt->AppointmentDate->format('Y-m-d') . ' ' . $appt->AppointmentTime);
                                        $hasStarted = now()->gte($startsAt);
                                    @endphp
                                    <tr>
                                        <td><span class="fw-semibold">{{ $appt->AppointmentDate->format('M j, Y') }} &bull; {{ $timeLabel }}</span></td>
                                        <td><span style="font-size:12px; color:#9ca3af; font-weight:500;">{{ $p ? 'PT-' . str_pad($p->PatientID, 4, '0', STR_PAD_LEFT) : '—' }}</span></td>
                                        <td><span><img class="avatar-initials" src="{{ $p->photo_url ?? asset('images/default.png') }}" alt=""></span>{{ $p->FirstName ?? '' }} {{ $p->LastName ?? '' }}
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
                                        <td>{{ $appt->ApprovedAt ? $appt->ApprovedAt->format('M j, Y g:i A') : '—' }}</td>
                                        <td><span class="pill {{ $pillClass }}">{{ $appt->Status }}</span>@include('partials.reschedule-badge', ['appt' => $appt])</td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-pill btn-pill-edit me-1" data-bs-toggle="modal"
                                                data-bs-target="#patientInfoModal{{ $appt->AppointmentID }}"><i class="bi bi-eye"></i> View</button>
                                            @if ($appt->can_reschedule)
                                                <a href="{{ route('appointments.reschedule', ['id' => $appt->AppointmentID, 'from' => 'appointments']) }}" class="btn btn-pill btn-pill-edit me-1"
                                                    title="Propose a new date/time to the patient"><i class="bi bi-calendar2-week"></i> Reschedule</a>
                                            @endif
                                            @if ($appt->Status === 'Approved')
                                                @if ($hasStarted)
                                                    <form method="POST" action="{{ route('appointments.complete', $appt->AppointmentID) }}" class="d-inline"
                                                        data-confirm-title="Mark this appointment as completed?"
                                                        data-confirm-message="{{ trim(($appt->patientInfo->FirstName ?? '') . ' ' . ($appt->patientInfo->LastName ?? '')) ?: 'The patient' }}'s visit on {{ $appt->AppointmentDate->format('F j, Y') }} ({{ $appt->time_range_label }}) will be marked as done and added to their dental records. This can't be undone."
                                                        data-confirm-label="Yes, mark as done" data-confirm-class="btn-pill-done">
                                                        @csrf
                                                        <button type="submit" class="btn btn-pill btn-pill-done me-1"><i class="bi bi-check2"></i> Done</button>
                                                    </form>
                                                @else
                                                    <button type="button" class="btn btn-pill btn-pill-done me-1" disabled
                                                        style="opacity:.5; cursor:not-allowed;"
                                                        title="Available once the appointment starts — {{ $startsAt->format('M j, Y') }} at {{ $startsAt->format('g:i A') }}">
                                                        <i class="bi bi-lock"></i> Done
                                                    </button>
                                                @endif
                                            @endif
                                            @if (in_array($appt->Status, ['Pending', 'Approved']))
                                                <button type="button" class="btn btn-pill btn-pill-cancel" data-bs-toggle="modal"
                                                    data-bs-target="#cancelledReasonModal{{ $appt->AppointmentID }}"><i class="bi bi-x"></i> Cancel</button>
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
    @include('partials.admin-notif-modal')

    {{-- ===================== PER-ROW CANCEL REASON MODALS ===================== --}}
    @foreach ($appointments as $appt)
        @include('partials.appointment-view-modal', ['appt' => $appt])

        @if (in_array($appt->Status, ['Pending', 'Approved']))
            <div class="modal fade" id="cancelledReasonModal{{ $appt->AppointmentID }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <div>
                                <h5 class="modal-title fw-semibold">Cancel Appointment</h5>
                                <div class="small text-muted">Choose what happened — the patient is notified either way</div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form method="POST" action="{{ route('appointments.cancel', $appt->AppointmentID) }}" class="cancel-outcome-form"
                            data-patient="{{ trim(($appt->patientInfo->FirstName ?? '') . ' ' . ($appt->patientInfo->LastName ?? '')) ?: 'the patient' }}"
                            data-when="{{ $appt->AppointmentDate->format('F j, Y') }} ({{ $appt->time_range_label }})"
                            data-confirm-title="Cancel this appointment?"
                            data-confirm-message="{{ trim(($appt->patientInfo->FirstName ?? '') . ' ' . ($appt->patientInfo->LastName ?? '')) ?: 'The patient' }}'s appointment on {{ $appt->AppointmentDate->format('F j, Y') }} ({{ $appt->time_range_label }}) will be cancelled, the time slot freed, and the patient notified. This can't be undone."
                            data-confirm-label="Yes, cancel it" data-confirm-class="btn-pill-cancel">
                            @csrf
                            @php
                                $apptStarted = now()->gte(\Carbon\Carbon::parse($appt->AppointmentDate->format('Y-m-d') . ' ' . $appt->AppointmentTime));
                            @endphp
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">What happened?</label>
                                    <select class="form-select cancel-outcome-select" name="outcome" required>
                                        @foreach (\App\Http\Controllers\AppointmentsController::CANCEL_OUTCOMES as $key => $outcome)
                                            <option value="{{ $key }}" data-reason="{{ $outcome['reason'] }}" data-default="{{ $outcome['default'] }}"
                                                @if ($key === 'no_show' && !$apptStarted) disabled @endif>
                                                {{ $outcome['label'] }}{{ $key === 'no_show' && !$apptStarted ? ' (after the appointment time)' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="small text-muted-2 mt-1 cancel-outcome-hint">The patient is notified with your message below.</div>
                                </div>
                                <div class="cancel-outcome-reason">
                                    <label class="form-label">Message to patient <span class="cancel-outcome-optional text-muted-2" hidden>(optional)</span></label>
                                    <textarea class="form-control" name="reason" rows="4" required maxlength="500"
                                        placeholder="e.g. Requested time slot is no longer available. Please choose another schedule."></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-pill btn-pill-cancel cancel-outcome-submit"><i class="bi bi-send"></i> Send &amp; Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    @endforeach

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
        // Cancel modal: the "What happened?" choice decides whether a message
        // is required, optional, or not asked for at all (No Show).
        document.addEventListener('change', function (e) {
            if (!e.target.matches('.cancel-outcome-select')) return;
            var form = e.target.closest('form');
            var opt = e.target.selectedOptions[0];
            var mode = opt.dataset.reason;
            var wrap = form.querySelector('.cancel-outcome-reason');
            var area = wrap.querySelector('textarea');
            wrap.hidden = mode === 'none';
            area.required = mode === 'required';
            area.disabled = mode === 'none';
            form.querySelector('.cancel-outcome-optional').hidden = mode !== 'optional';
            area.placeholder = mode === 'optional' && opt.dataset.default
                ? 'Leave blank to send: "' + opt.dataset.default + '"'
                : 'e.g. Requested time slot is no longer available. Please choose another schedule.';
            form.querySelector('.cancel-outcome-hint').textContent = mode === 'none'
                ? 'No reason needed — the appointment is marked "No Show" and the patient is told they missed it.'
                : 'The patient is notified with your message below.';
            form.querySelector('.cancel-outcome-submit').innerHTML = mode === 'none'
                ? '<i class="bi bi-person-x"></i> Mark as No Show'
                : '<i class="bi bi-send"></i> Send &amp; Cancel';

            // The "are you sure?" step after this window matches the chosen outcome.
            var who = form.dataset.patient, when = form.dataset.when;
            if (mode === 'none') {
                form.dataset.confirmTitle = 'Mark as No Show?';
                form.dataset.confirmMessage = who + "'s appointment on " + when + ' will be marked as a no-show, the time slot freed, and the patient told they missed it. This can\'t be undone.';
                form.dataset.confirmLabel = 'Yes, mark as No Show';
            } else {
                form.dataset.confirmTitle = 'Cancel this appointment?';
                form.dataset.confirmMessage = who + "'s appointment on " + when + ' will be cancelled, the time slot freed, and the patient notified. This can\'t be undone.';
                form.dataset.confirmLabel = 'Yes, cancel it';
            }
        });
    </script>
    @include('partials.confirm-action-modal')
</body>

</html>
