{{--
    "Edit patient" modal for the Patient Records list (admin + super admin).
    One per patient; a failed save re-renders the page with this modal
    server-opened (same pattern as the Edit User modal) so the errors show
    in context.

    Expected: $patient (PatientInfo with userAccount)
--}}
@php
    $fullName = trim($patient->FirstName . ' ' . $patient->LastName);
    $hasAccount = (bool) $patient->userAccount;
    $source = 'editPatient_' . $patient->PatientID;
    $failed = $errors->any() && old('form_source') === $source;
    $val = fn (string $key, $default = '') => $failed ? old($key, $default) : $default;
    $err = fn (string $key) => $failed && $errors->has($key) ? 'is-invalid' : '';
@endphp
@if ($failed)
    <div class="modal-backdrop fade show"></div>
@endif
<div class="modal fade {{ $failed ? 'show' : '' }}" id="editPatientModal{{ $patient->PatientID }}" tabindex="-1"
    aria-hidden="{{ $failed ? 'false' : 'true' }}" style="{{ $failed ? 'display:block;' : '' }}">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title fw-semibold">Edit Patient</h5>
                    <div class="small text-muted">Update this patient's personal information</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('patientRecords.patient.update', $patient->PatientID) }}">
                @csrf
                <input type="hidden" name="form_source" value="{{ $source }}">
                <div class="modal-body">
                    @if ($failed)
                        <div class="alert alert-danger">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="d-flex align-items-center gap-3 mb-4">
                        <img class="avatar-initials" src="{{ $patient->photo_url }}" alt="" style="width:64px;height:64px;">
                        <div>
                            <div class="fw-semibold">{{ $fullName }}</div>
                            <div class="small text-muted-2">PT-{{ str_pad($patient->PatientID, 4, '0', STR_PAD_LEFT) }}</div>
                        </div>
                    </div>

                    <div class="section-label">Personal Information</div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Last name</label>
                            <input type="text" name="last_name" class="form-control {{ $err('last_name') }}" value="{{ $val('last_name', $patient->LastName) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">First name</label>
                            <input type="text" name="first_name" class="form-control {{ $err('first_name') }}" value="{{ $val('first_name', $patient->FirstName) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Middle name</label>
                            <input type="text" name="middle_name" class="form-control {{ $err('middle_name') }}" value="{{ $val('middle_name', $patient->MiddleName) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Birthdate</label>
                            <input type="date" name="birthdate" class="form-control {{ $err('birthdate') }}" max="{{ now()->toDateString() }}"
                                value="{{ $val('birthdate', optional($patient->DateOfBirth)->format('Y-m-d')) }}" required>
                            <div class="small text-muted-2 mt-1">Age is calculated automatically from the birthdate.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Gender</label>
                            <select name="gender" class="form-select {{ $err('gender') }}" required>
                                @foreach (['male' => 'Male', 'female' => 'Female', 'other' => 'Other'] as $key => $label)
                                    <option value="{{ $key }}" @selected($val('gender', strtolower($patient->Gender ?? '')) === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nationality</label>
                            <input type="text" name="nationality" class="form-control {{ $err('nationality') }}" value="{{ $val('nationality', $patient->Nationality) }}" required>
                        </div>
                    </div>

                    <div class="section-label">Contact Information</div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Mobile number</label>
                            <input type="text" name="phone" class="form-control {{ $err('phone') }}" inputmode="numeric" maxlength="11"
                                value="{{ $val('phone', $patient->PhoneNumber) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            @if ($hasAccount)
                                <input type="email" class="form-control" value="{{ $patient->userAccount->Email }}" disabled>
                                <div class="small text-muted-2 mt-1">Managed under User Accounts — it's this patient's login email.</div>
                            @else
                                <input type="email" name="email" class="form-control {{ $err('email') }}" value="{{ $val('email', $patient->Email) }}">
                            @endif
                        </div>
                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <input type="text" name="address" class="form-control {{ $err('address') }}" value="{{ $val('address', $patient->Address) }}" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-pill btn-pill-cancel" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-brand"><i class="bi bi-floppy me-1"></i> Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
