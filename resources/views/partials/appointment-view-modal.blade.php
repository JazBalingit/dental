{{--
  "View" modal for one appointment: the appointment summary plus the patient's
  account details (read-only). Shared by Appointments and Appointment Requests
  (admin + super admin).

  Expects: $appt (Appointment with patientInfo loaded).
  Opened with data-bs-target="#patientInfoModal{{ $appt->AppointmentID }}".
--}}
@php
    $p = $appt->patientInfo;
@endphp
<div class="modal fade" id="patientInfoModal{{ $appt->AppointmentID }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title fw-semibold">Appointment Details</h5>
                    <div class="small text-muted">{{ $p->FirstName }} {{ $p->LastName }}'s appointment and account details</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <img class="avatar-initials" src="{{ $p->photo_url ?? asset('images/default.png') }}" alt=""
                        style="width:64px;height:64px;">
                    <div>
                        <div class="fw-semibold">{{ $p->FirstName }} {{ $p->LastName }}
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
                        </div>
                        <div class="small text-muted-2">Patient ID:
                            PT-{{ str_pad($p->PatientID, 4, '0', STR_PAD_LEFT) }}</div>
                    </div>
                </div>

                {{-- Stacked: appointment details on top, personal + contact details below. --}}
                <div class="row g-3">
                <div class="col-12">
                @if ($appt->BookedForOther || $appt->is_guest)
                    {{-- Who to contact about this booking — a parent who booked for
                         their child, or a guest with no account to look up. --}}
                    <div class="section-label">{{ $appt->BookedForOther ? 'Booked By' : 'Booked Without an Account' }}</div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Name</label>
                            <div class="input-icon"><i class="bi bi-person-heart"></i><input class="form-control"
                                    value="{{ $appt->BookedByName ?: ($appt->booked_by_label ?? '—') }}" disabled /></div>
                            @if ($appt->BookedForOther && $appt->patientInfo?->Relationship)
                                <div class="small text-muted-2 mt-1">The patient is their {{ strtolower($appt->patientInfo->Relationship) }}.</div>
                            @endif
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Email</label>
                            <div class="input-icon"><i class="bi bi-envelope"></i><input class="form-control"
                                    value="{{ $appt->BookedByEmail ?: ($appt->bookedBy?->Email ?? '—') }}" disabled /></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Mobile number</label>
                            <div class="input-icon"><i class="bi bi-telephone"></i><input class="form-control"
                                    value="{{ $appt->BookedByPhone ?: ($appt->bookedBy?->patientInfo?->PhoneNumber ?? '—') }}" disabled /></div>
                        </div>
                    </div>
                @endif
                {{-- What the patient declared for this visit (or the current history for older bookings). --}}
                <div class="section-label"><i class="bi bi-heart-pulse me-1" style="color:#b91c1c;"></i> Medical History</div>
                <div class="mb-3">
                    @include('partials.medical-history-view', ['mhv' => $appt->medicalHistory ?? $appt->patientInfo?->latestMedicalHistory, 'mhvCompact' => true])
                    {{-- Only once the patient has a completed visit — before that they
                         aren't in Patient Records yet. --}}
                    @if ($appt->patientInfo && $appt->patientInfo->records()->exists())
                        <a href="{{ route('patientRecords.history', $appt->patientInfo->PatientID) }}" class="btn btn-pill btn-pill-edit mt-2 text-decoration-none">
                            <i class="bi bi-pencil-square"></i> Update in Patient Records
                        </a>
                    @endif
                </div>

                <div class="section-label">Appointment Details</div>
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Date</label>
                        <div class="input-icon"><i class="bi bi-calendar-event"></i><input class="form-control"
                                value="{{ $appt->AppointmentDate->format('F j, Y') }}" disabled /></div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Time</label>
                        <div class="input-icon"><i class="bi bi-clock"></i><input class="form-control"
                                value="{{ $appt->time_range_label }}" disabled /></div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Dentist</label>
                        <div class="input-icon"><i class="bi bi-person-badge"></i><input class="form-control"
                                value="{{ $appt->dentist_name }}" disabled /></div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Treatment</label>
                        <div class="input-icon"><i class="bi bi-clipboard2-pulse"></i><input class="form-control"
                                value="{{ $appt->TypeOfAppointment ?: ($appt->service->ServiceName ?? '—') }}" disabled /></div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <div class="input-icon"><i class="bi bi-info-circle"></i><input class="form-control"
                                value="{{ $appt->Status === 'Approved' ? 'Booked' : $appt->Status }}" disabled /></div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Booked at</label>
                        <div class="input-icon"><i class="bi bi-clock-history"></i><input class="form-control"
                                value="{{ $appt->created_at?->format('M j, Y g:i A') }}" disabled /></div>
                    </div>
                </div>

                </div>
                <div class="col-12">
                <div class="section-label">Personal Information</div>
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Last name</label>
                        <div class="input-icon"><i class="bi bi-person"></i><input type="text" class="form-control"
                                value="{{ $p->LastName }}" disabled /></div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">First name</label>
                        <div class="input-icon"><i class="bi bi-person"></i><input type="text" class="form-control"
                                value="{{ $p->FirstName }}" disabled /></div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Middle name</label>
                        <div class="input-icon"><i class="bi bi-person"></i><input type="text" class="form-control"
                                value="{{ $p->MiddleName }}" disabled /></div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Birthdate</label>
                        <div class="input-icon"><i class="bi bi-calendar-event"></i><input type="date"
                                class="form-control" value="{{ optional($p->DateOfBirth)->format('Y-m-d') }}"
                                disabled /></div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Age</label>
                        <div class="input-icon"><i class="bi bi-calendar3"></i><input type="text"
                                class="form-control" value="{{ optional($p->DateOfBirth)->age }}" disabled /></div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Gender</label>
                        <div class="input-icon"><i class="bi bi-person-badge"></i><input type="text"
                                class="form-control" value="{{ ucfirst($p->Gender) }}" disabled /></div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Religion</label>
                        <div class="input-icon"><i class="bi bi-book"></i><input class="form-control"
                                value="{{ $p->Religion }}" disabled /></div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Nationality</label>
                        <div class="input-icon"><i class="bi bi-flag"></i><input class="form-control"
                                value="{{ $p->Nationality }}" disabled /></div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Occupation</label>
                        <div class="input-icon"><i class="bi bi-briefcase"></i><input class="form-control"
                                value="{{ $p->Occupation }}" disabled /></div>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Home address</label>
                        <div class="input-icon"><i class="bi bi-geo-alt"></i><input class="form-control"
                                value="{{ $p->Address }}" disabled /></div>
                    </div>
                </div>

                <div class="section-label mt-2">Contact Details</div>
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Email address</label>
                        <div class="input-icon"><i class="bi bi-envelope"></i><input type="email"
                                class="form-control" value="{{ $p->userAccount?->Email ?? $p->Email ?? '' }}" disabled /></div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Cell/Mobile number</label>
                        <div class="input-icon"><i class="bi bi-telephone"></i><input class="form-control"
                                value="{{ $p->PhoneNumber }}" disabled /></div>
                    </div>
                </div>
                </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
