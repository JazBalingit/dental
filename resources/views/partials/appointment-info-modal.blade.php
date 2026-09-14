{{--
    "Appointment Information" modal — dental chart + dentist's notes for one
    appointment. Shared by the Current Appointments and Appointment History
    pages (each just loops its own set of appointments through this).

    Expected: $appointment
--}}
@php
    $rec = $appointment->patientRecord;
    $statusLabel = $appointment->Status === 'Approved' ? 'Booked' : $appointment->Status;
    $badgeClass = $appointment->Status === 'Completed'
        ? 'badge-completed'
        : ($appointment->Status === 'Declined' ? 'badge-cancelled' : 'badge-scheduled');
@endphp
<div class="modal fade" id="apptModal{{ $appointment->AppointmentID }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-circle-info me-2" style="color:#0f7a33"></i>Appointment Information</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">

                <div class="appt-info-grid">
                    <div class="appt-info-cell"><span class="appt-info-lbl">Service</span><span class="appt-info-val">{{ $appointment->TypeOfAppointment ?: ($appointment->service->ServiceName ?? '—') }}</span></div>
                    <div class="appt-info-cell"><span class="appt-info-lbl">Dentist</span><span class="appt-info-val">{{ $appointment->dentist_name }}</span></div>
                    <div class="appt-info-cell"><span class="appt-info-lbl">Date</span><span class="appt-info-val">{{ $appointment->AppointmentDate->format('F j, Y') }}</span></div>
                    <div class="appt-info-cell"><span class="appt-info-lbl">Time</span><span class="appt-info-val">{{ \Carbon\Carbon::createFromFormat('H:i', $appointment->AppointmentTime)->format('g:i A') }}</span></div>
                    <div class="appt-info-cell"><span class="appt-info-lbl">Duration</span><span class="appt-info-val">{{ $appointment->duration_label }}</span></div>
                    <div class="appt-info-cell"><span class="appt-info-lbl">Status</span><span class="appt-info-val"><span class="badge-pill {{ $badgeClass }}">{{ $statusLabel }}</span></span></div>
                    @if ($appointment->ApprovedAt)
                        <div class="appt-info-cell"><span class="appt-info-lbl">Approved On</span><span class="appt-info-val">{{ $appointment->ApprovedAt->format('F j, Y g:i A') }}</span></div>
                    @endif
                    @if ($appointment->Status === 'Declined' && $appointment->DeclineReason)
                        <div class="appt-info-cell" style="grid-column:1/-1"><span class="appt-info-lbl">Reason</span><span class="appt-info-val">{{ $appointment->DeclineReason }}</span></div>
                    @endif
                </div>

                <div class="appt-info-divider"></div>

                @if ($rec)
                    @include('partials.odontogram', ['record' => $rec, 'readonly' => true])

                    <div class="odontogram-heading"><i class="bi bi-journal-text"></i> Dentist's Notes</div>
                    <div class="odontogram-detail-readvalue">{{ $rec->Notes ?: 'No notes were recorded for this visit.' }}</div>
                @else
                    <div class="appt-info-empty">
                        <i class="fas fa-tooth"></i>
                        <p>Your dental chart and dentist's notes will appear here once this appointment is completed.</p>
                    </div>
                @endif

            </div>
            <div class="modal-footer gap-2">
                <button class="btn-sec" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
