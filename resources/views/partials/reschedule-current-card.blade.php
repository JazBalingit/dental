{{--
    "Moving this appointment" summary at the top of both reschedule pages.
    Expects: $appointment (with patientInfo, dentist, services, pendingReschedule).
    Optional: $audience = 'staff' | 'patient'.
--}}
@php
    $audience = $audience ?? 'staff';
    $p = $appointment->patientInfo;
    $open = $appointment->pendingReschedule;
@endphp
<div class="rs-card">
    <div class="rs-card-icon"><i class="bi bi-arrow-left-right"></i></div>
    <div class="rs-card-main">
        <div class="rs-card-title">
            {{ $audience === 'staff' ? 'Rescheduling ' . trim(($p->FirstName ?? '') . ' ' . ($p->LastName ?? '')) . "'s appointment" : 'Rescheduling your appointment' }}
            @if ($p)<span class="rs-card-id">PT-{{ str_pad($p->PatientID, 4, '0', STR_PAD_LEFT) }}</span>@endif
        </div>
        <div class="rs-card-grid">
            <div><span>Current date</span><b>{{ $appointment->AppointmentDate->format('F j, Y') }}</b></div>
            <div><span>Current time</span><b>{{ $appointment->time_range_label }}</b></div>
            <div><span>Dentist</span><b>{{ $appointment->dentist_name }}</b></div>
            <div><span>Service(s)</span><b>{{ $appointment->TypeOfAppointment ?: ($appointment->service->ServiceName ?? '—') }}</b></div>
        </div>
        <div class="rs-card-steps">
            @if ($audience === 'staff')
                <i class="bi bi-info-circle me-1"></i> Pick a new date and time below. The patient is notified and can
                <strong>accept</strong> it, <strong>choose another time</strong>, or <strong>keep the original</strong>. Until they answer, both the
                original and the proposed time stay reserved; an unanswered proposal expires after {{ \App\Models\RescheduleRequest::RESPONSE_HOURS }} hours
                (or when the original visit starts).
            @else
                <i class="bi bi-info-circle me-1"></i> Pick a new date and time below. Your original time stays reserved until you confirm;
                after that your appointment moves and goes back to <strong>Pending</strong> for the clinic to approve.
            @endif
        </div>
        @if ($open)
            <div class="rs-card-open">
                <i class="bi bi-hourglass-split me-1"></i>
                A proposed time is already waiting for the patient's answer: <strong>{{ $open->when_label }}</strong>.
                {{ $audience === 'staff' ? 'Proposing a new time replaces it.' : 'Picking your own time replaces it.' }}
            </div>
        @endif
    </div>
</div>

<style>
    .rs-card { display: flex; gap: 1rem; align-items: flex-start; background: #fff; border: 1px solid #e3efe6; border-left: 4px solid var(--brand-700, #167d1d); border-radius: 14px; padding: 1rem 1.1rem; margin-bottom: 1rem; }
    .rs-card-icon { flex: none; width: 40px; height: 40px; border-radius: 12px; background: #eaf8ec; color: var(--brand-700, #167d1d); display: flex; align-items: center; justify-content: center; font-size: 1.1rem; }
    .rs-card-main { flex: 1; min-width: 0; }
    .rs-card-title { font-weight: 700; color: #0f172a; display: flex; flex-wrap: wrap; align-items: center; gap: .5rem; }
    .rs-card-id { font-size: .75rem; font-weight: 600; color: #64748b; }
    .rs-card-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: .5rem .9rem; margin: .6rem 0; }
    .rs-card-grid span { display: block; font-size: .66rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: #64748b; }
    .rs-card-grid b { font-size: .9rem; color: #0f172a; overflow-wrap: anywhere; }
    .rs-card-steps { font-size: .8rem; color: #475569; background: #f8fafc; border-radius: .55rem; padding: .5rem .7rem; }
    .rs-card-open { margin-top: .5rem; font-size: .8rem; color: #8a6100; background: #fff8e6; border-radius: .55rem; padding: .45rem .7rem; }
    @media (max-width: 767.98px) { .rs-card-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
</style>
