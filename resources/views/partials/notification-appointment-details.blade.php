{{--
  Appointment details shown inside the notification-details modal (an HTML
  fragment fetched by public/js/notification-details.js).

  Expects: $appointment (with patientInfo, service(s), dentist), $isAdminSide.
--}}
@php
    $a = $appointment;
    $p = $a->patientInfo;
    $services = $a->TypeOfAppointment
        ?: ($a->services->pluck('ServiceName')->implode(', ') ?: ($a->service->ServiceName ?? '—'));

    [$statusLabel, $statusTone, $note] = match ($a->Status) {
        'Pending' => ['Pending approval', 'warning', $isAdminSide
            ? 'This appointment is awaiting approval on the Appointment Approval page.'
            : 'Your appointment request has been received and is awaiting approval. You will be notified once it has been reviewed.'],
        'Approved' => ['Approved', 'info', $isAdminSide
            ? 'This appointment has been approved and is scheduled.'
            : 'Your appointment has been approved. Kindly arrive at the clinic a few minutes before your scheduled time.'],
        'Completed' => ['Completed', 'success', $isAdminSide
            ? 'This appointment has been completed.'
            : 'Your appointment has been completed. Thank you for choosing Pus-Pus Britanico Dental Clinic.'],
        default => [$a->Status, 'muted', null],
    };
@endphp

<div class="nd-hero">
    <div class="nd-hero-date">
        <span class="nd-hero-month">{{ $a->AppointmentDate->format('M') }}</span>
        <span class="nd-hero-day">{{ $a->AppointmentDate->format('j') }}</span>
    </div>
    <div class="nd-hero-info">
        <div class="nd-hero-when">{{ $a->AppointmentDate->format('l, F j, Y') }}</div>
        <div class="nd-hero-time"><i class="bi bi-clock"></i> {{ $a->time_range_label }}</div>
    </div>
    <span class="nd-status nd-status-{{ $statusTone }}">{{ $statusLabel }}</span>
</div>

<dl class="nd-grid">
    @if ($isAdminSide && $p)
        <div class="nd-cell nd-wide"><dt>Patient</dt><dd>{{ trim($p->FirstName . ' ' . $p->LastName) }}</dd></div>
    @endif
    <div class="nd-cell nd-wide"><dt>Service</dt><dd>{{ $services }}</dd></div>
    <div class="nd-cell"><dt>Dentist</dt><dd>{{ $a->dentist_name }}</dd></div>
    <div class="nd-cell"><dt>Duration</dt><dd>{{ $a->duration_label }}</dd></div>
    <div class="nd-cell"><dt>Booked on</dt><dd>{{ $a->created_at?->format('F j, Y') }}</dd></div>
    @if ($a->ApprovedAt)
        <div class="nd-cell"><dt>Approved on</dt><dd>{{ $a->ApprovedAt->format('F j, Y') }}</dd></div>
    @endif
</dl>

@if ($note)
    <p class="nd-note">{{ $note }}</p>
@endif
