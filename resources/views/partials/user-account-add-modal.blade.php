{{-- User Accounts → Add User. Same fields and rules as patient self sign-up
     (login_signup/signup); laid out like Staff Accounts → Add User. The
     account starts unverified — the patient confirms the email with a code
     on first login (EnsurePatientIsVerified). Shared by the admin and super
     admin User Accounts pages. --}}
@php $addFailed = $errors->any() && old('form_source') === 'add'; @endphp
<div class="modal fade {{ $addFailed ? 'show' : '' }}" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel"
    aria-hidden="{{ $addFailed ? 'false' : 'true' }}" style="{{ $addFailed ? 'display:block;' : '' }}">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title fw-semibold" id="addUserModalLabel">Add User</h5>
                    <div class="small text-muted">Create a patient account. The patient verifies their email on first login.</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('userAcc.store') }}">
                @csrf
                <input type="hidden" name="form_source" value="add">
                <div class="modal-body">

                    @if ($addFailed)
                        <div class="alert alert-danger">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="section-label">Personal Information</div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Last name</label>
                            <div class="input-icon {{ $addFailed && $errors->has('last_name') ? 'has-error' : '' }}"><i class="bi bi-person"></i><input type="text" name="last_name" class="form-control"
                                    value="{{ $addFailed ? old('last_name') : '' }}" placeholder="Last name" required /></div>
                            @if ($addFailed && $errors->has('last_name')) <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $errors->first('last_name') }}</div> @endif
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">First name</label>
                            <div class="input-icon {{ $addFailed && $errors->has('first_name') ? 'has-error' : '' }}"><i class="bi bi-person"></i><input type="text" name="first_name" class="form-control"
                                    value="{{ $addFailed ? old('first_name') : '' }}" placeholder="First name" required /></div>
                            @if ($addFailed && $errors->has('first_name')) <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $errors->first('first_name') }}</div> @endif
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Middle name</label>
                            <div class="input-icon {{ $addFailed && $errors->has('middle_name') ? 'has-error' : '' }}"><i class="bi bi-person"></i><input type="text" name="middle_name" class="form-control"
                                    value="{{ $addFailed ? old('middle_name') : '' }}" placeholder="Middle name" /></div>
                            @if ($addFailed && $errors->has('middle_name')) <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $errors->first('middle_name') }}</div> @endif
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Birthdate</label>
                            <div class="input-icon {{ $addFailed && $errors->has('birthdate') ? 'has-error' : '' }}"><i class="bi bi-calendar-event"></i><input type="date" name="birthdate"
                                    class="form-control" value="{{ $addFailed ? old('birthdate') : '' }}" max="{{ \App\Services\BookingService::adultCutoff() }}" required
                                    data-age-target="#userAgeNew" />
                            </div>
                            @if ($addFailed && $errors->has('birthdate'))
                                <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $errors->first('birthdate') }}</div>
                            @else
                                <div class="small text-muted-2 mt-1">Must be 18 or older. Minors are booked from a parent's account with <strong>Book for Someone Else</strong>.</div>
                            @endif
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Age</label>
                            <div class="input-icon"><i class="bi bi-person-vcard"></i><input type="text" class="form-control" id="userAgeNew" placeholder="—" disabled></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Gender</label>
                            <div class="input-icon {{ $addFailed && $errors->has('gender') ? 'has-error' : '' }}">
                                <select class="form-select" name="gender" required>
                                    <option value="" disabled {{ $addFailed && old('gender') ? '' : 'selected' }}>Select gender</option>
                                    <option value="male" {{ $addFailed && old('gender') === 'male' ? 'selected' : '' }}>Male</option>
                                    <option value="female" {{ $addFailed && old('gender') === 'female' ? 'selected' : '' }}>Female</option>
                                    <option value="other" {{ $addFailed && old('gender') === 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>
                            @if ($addFailed && $errors->has('gender')) <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $errors->first('gender') }}</div> @endif
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Religion</label>
                            <div class="input-icon {{ $addFailed && $errors->has('religion') ? 'has-error' : '' }}"><i class="bi bi-book"></i><input class="form-control" name="religion"
                                    value="{{ $addFailed ? old('religion') : '' }}" placeholder="Catholic" /></div>
                            @if ($addFailed && $errors->has('religion')) <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $errors->first('religion') }}</div> @endif
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Nationality</label>
                            <div class="input-icon {{ $addFailed && $errors->has('nationality') ? 'has-error' : '' }}"><i class="bi bi-flag"></i><input class="form-control" name="nationality"
                                    value="{{ $addFailed ? old('nationality') : '' }}" placeholder="Filipino" required /></div>
                            @if ($addFailed && $errors->has('nationality')) <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $errors->first('nationality') }}</div> @endif
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Occupation <span class="text-muted">(optional)</span></label>
                            <div class="input-icon {{ $addFailed && $errors->has('occupation') ? 'has-error' : '' }}"><i class="bi bi-briefcase"></i><input class="form-control" name="occupation"
                                    value="{{ $addFailed ? old('occupation') : '' }}" placeholder="Occupation" /></div>
                            @if ($addFailed && $errors->has('occupation')) <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $errors->first('occupation') }}</div> @endif
                        </div>
                    </div>

                    <div class="section-label mt-2">Home Address</div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Street / House No.</label>
                            <div class="input-icon"><i class="bi bi-signpost-2"></i><input class="form-control addr-part" name="addr_street"
                                    value="{{ $addFailed ? old('addr_street') : '' }}" placeholder="123 Sample St." /></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Barangay</label>
                            <div class="input-icon"><i class="bi bi-geo"></i><input class="form-control addr-part" name="addr_barangay"
                                    value="{{ $addFailed ? old('addr_barangay') : '' }}" placeholder="Barangay" /></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">City / Municipality</label>
                            <div class="input-icon"><i class="bi bi-buildings"></i><input class="form-control addr-part" name="addr_city"
                                    value="{{ $addFailed ? old('addr_city') : '' }}" placeholder="City / Municipality" required /></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Province</label>
                            <div class="input-icon"><i class="bi bi-map"></i><input class="form-control addr-part" name="addr_province"
                                    value="{{ $addFailed ? old('addr_province') : '' }}" placeholder="Province" required /></div>
                        </div>
                        <input type="hidden" name="address" value="{{ $addFailed ? old('address') : '' }}">
                        @if ($addFailed && $errors->has('address')) <div class="col-12"><div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $errors->first('address') }}</div></div> @endif
                    </div>

                    <div class="section-label mt-2">Contact Details</div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Email address</label>
                            <div class="input-icon {{ $addFailed && $errors->has('email') ? 'has-error' : '' }}"><i class="bi bi-envelope"></i><input type="email" name="email"
                                    class="form-control" value="{{ $addFailed ? old('email') : '' }}" placeholder="you@clinic.com" required /></div>
                            @if ($addFailed && $errors->has('email'))
                                <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $errors->first('email') }}</div>
                            @else
                                <div class="small text-muted-2 mt-1">The patient will be asked to verify this email on first login.</div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Cell/Mobile number</label>
                            <div class="input-icon {{ $addFailed && $errors->has('phone') ? 'has-error' : '' }}"><i class="bi bi-telephone"></i><input type="tel" name="phone" class="form-control"
                                    value="{{ $addFailed ? old('phone') : '' }}" placeholder="09XXXXXXXXX" inputmode="numeric" pattern="[0-9]{11}"
                                    title="Enter an 11-digit mobile number" required /></div>
                            @if ($addFailed && $errors->has('phone')) <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $errors->first('phone') }}</div> @endif
                        </div>
                    </div>

                    <div class="section-label mt-2">Credentials</div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Password</label>
                            <div class="pw-field">
                                <input type="checkbox" class="pw-toggle-checkbox" id="pwUserAddNew">
                                <div class="input-icon {{ $addFailed && $errors->has('password') ? 'has-error' : '' }}"><i class="bi bi-lock"></i><input type="text" name="password"
                                        class="form-control pw-mask" placeholder="••••••••" required minlength="8"
                                        value="{{ $addFailed ? old('password') : '' }}" autocomplete="new-password" /></div>
                                <label for="pwUserAddNew" class="pw-eye-btn" aria-label="Show or hide password"><i class="bi bi-eye"></i><i class="bi bi-eye-slash"></i></label>
                            </div>
                            @if ($addFailed && $errors->has('password')) <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $errors->first('password') }}</div> @endif
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirm password</label>
                            <div class="pw-field">
                                <input type="checkbox" class="pw-toggle-checkbox" id="pwUserAddConfirm">
                                <div class="input-icon {{ $addFailed && $errors->has('password') ? 'has-error' : '' }}"><i class="bi bi-shield-lock"></i><input type="text"
                                        name="password_confirmation" class="form-control pw-mask" placeholder="••••••••" required
                                        value="{{ $addFailed ? old('password_confirmation') : '' }}" autocomplete="new-password" /></div>
                                <label for="pwUserAddConfirm" class="pw-eye-btn" aria-label="Show or hide password"><i class="bi bi-eye"></i><i class="bi bi-eye-slash"></i></label>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-brand">Create User</button>
                </div>
            </form>
        </div>
    </div>
</div>
