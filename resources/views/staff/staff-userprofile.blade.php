@php $profileLabel = $staff->IsSuperAdmin ? 'Super Admin Profile' : 'My Profile'; @endphp
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
  <link rel="icon" type="image/png" href="/images/puspus_logo.png">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ $profileLabel }} • Dental Clinic</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/mobile.css') }}">
</head>

<body>
    @php
        $si = $staff->staffInfo;
        $verified = (bool) $staff->EmailVerifiedAt;
        $showVerifyForm = session('show_staff_verify', false);
        $isSuperAdmin = (bool) $staff->IsSuperAdmin;
        $profileFailed = $errors->any() && old('form_source') === 'edit_staff_profile';
        $pv = fn ($field, $default = '') => $profileFailed ? old($field, $default) : $default;
        $pErr = fn ($field) => $profileFailed && $errors->has($field) ? 'has-error' : '';
        $pMsg = fn ($field) => $profileFailed && $errors->has($field) ? $errors->first($field) : null;
    @endphp
    <div class="app">
        <aside class="sidebar offcanvas position-sticky" tabindex="-1" id="sidebarOffcanvas">
            <div class="brand">
                <div><img class="logo" src="/images/puspus_logo.png" alt=""></div>
                <div>
                    <div class="name">PUS-PUS BRITANICO</div>
                    <div class="sub">Dental Clinic</div>
                </div>
            </div>
            @include('partials.admin-sidebar-nav')
            @include('partials.admin-profile-badge')
        </aside>

        <main>
            <div class="topbar">
                <div class="left">
                    <button class="toggle d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas"
                        aria-controls="sidebarOffcanvas">
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
                        <h2>{{ $profileLabel }}</h2>
                        <div class="crumbs">View your information and manage your account security.</div>
                    </div>
                </div>

                @include('partials.flash-toasts')

                <div class="row g-3">
                    <div class="col-lg-3">
                        <div class="card-soft p-2">
                            <div class="nav flex-column nav-pills" role="tablist" aria-orientation="vertical">
                                <button class="nav-link text-start {{ $activeTab === 'profile' ? 'active' : '' }}"
                                    data-profile-tab="profile" type="button"><i class="bi bi-person me-2"></i>{{ $profileLabel }}</button>
                                <button class="nav-link text-start {{ $activeTab === 'security' ? 'active' : '' }}"
                                    data-profile-tab="security" type="button"><i class="bi bi-shield-lock me-2"></i>Security</button>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-9">

                        {{-- ===================== USER PROFILE ===================== --}}
                        <div class="settings-pane" data-profile-pane="profile" @if ($activeTab !== 'profile') hidden @endif>
                            <div class="card-soft p-3 p-md-4">
                                <div class="d-flex align-items-center gap-3 mb-4">
                                    <img class="avatar-initials" src="{{ $si->photo_url ?? asset('images/default.png') }}" alt="" style="width:72px;height:72px;">
                                    <div>
                                        <h5 class="fw-semibold mb-1">{{ $si->FirstName ?? '' }} {{ $si->LastName ?? '' }}{{ $si ? '' : ($isSuperAdmin ? 'Super Admin' : 'Administrator') }}</h5>
                                        <span class="pill pill-info">{{ $isSuperAdmin ? 'Super Admin' : ucfirst($staff->AccountRole) }}</span>
                                        @if ($staff->Position)
                                            <span class="pill pill-success">{{ $staff->Position }}</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="section-label">Account Information</div>
                                <div class="row g-3 {{ $si ? 'mb-3' : '' }}">
                                    <div class="col-md-6">
                                        <label class="form-label">Email address</label>
                                        <div class="input-icon"><i class="bi bi-envelope"></i><input type="text" class="form-control" value="{{ $staff->Email }}" disabled></div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Email verification</label>
                                        <div class="input-icon">
                                            @if ($verified)
                                                <i class="bi bi-patch-check-fill"></i><input type="text" class="form-control" value="Verified on {{ $staff->EmailVerifiedAt->format('M j, Y g:i A') }}" disabled>
                                            @else
                                                <i class="bi bi-exclamation-triangle"></i><input type="text" class="form-control" value="Not verified yet" disabled>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Date created</label>
                                        <div class="input-icon"><i class="bi bi-clock-history"></i><input type="text" class="form-control" value="{{ \Carbon\Carbon::parse($staff->DateCreated)->format('M j, Y g:i A') }}" disabled></div>
                                    </div>
                                </div>

                                @if ($si)
                                    <div class="section-label mt-2">Personal Information</div>
                                    <form method="POST" action="{{ route('staffProfile.updateProfile') }}"
                                          data-edit-toggle data-confirm-title="Save changes?"
                                          data-confirm-message="Save these changes to your profile?">
                                        @csrf
                                        <input type="hidden" name="form_source" value="edit_staff_profile">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label">Last name</label>
                                                <div class="input-icon {{ $pErr('last_name') }}"><i class="bi bi-person"></i><input type="text" name="last_name" class="form-control"
                                                        value="{{ $pv('last_name', $si->LastName ?? '') }}" required {{ $profileFailed ? '' : 'disabled' }} data-editable></div>
                                                @if ($pMsg('last_name')) <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $pMsg('last_name') }}</div> @endif
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">First name</label>
                                                <div class="input-icon {{ $pErr('first_name') }}"><i class="bi bi-person"></i><input type="text" name="first_name" class="form-control"
                                                        value="{{ $pv('first_name', $si->FirstName ?? '') }}" required {{ $profileFailed ? '' : 'disabled' }} data-editable></div>
                                                @if ($pMsg('first_name')) <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $pMsg('first_name') }}</div> @endif
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Middle name</label>
                                                <div class="input-icon {{ $pErr('middle_name') }}"><i class="bi bi-person"></i><input type="text" name="middle_name" class="form-control"
                                                        value="{{ $pv('middle_name', $si->MiddleName ?? '') }}" {{ $profileFailed ? '' : 'disabled' }} data-editable></div>
                                                @if ($pMsg('middle_name')) <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $pMsg('middle_name') }}</div> @endif
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Birthdate</label>
                                                <div class="input-icon {{ $pErr('birthdate') }}"><i class="bi bi-calendar-event"></i><input type="date" name="birthdate" class="form-control"
                                                        value="{{ $pv('birthdate', optional($si->DateOfBirth ?? null)->format('Y-m-d')) }}"
                                                        max="{{ now()->subYears(18)->year }}-12-31" required
                                                        data-age-target="#staffProfileAge" {{ $profileFailed ? '' : 'disabled' }} data-editable></div>
                                                @if ($pMsg('birthdate'))
                                                    <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $pMsg('birthdate') }}</div>
                                                @else
                                                    <div class="small text-muted-2 mt-1">Must be at least 18 years old.</div>
                                                @endif
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Age</label>
                                                <div class="input-icon"><i class="bi bi-calendar3"></i><input type="text" class="form-control" id="staffProfileAge" placeholder="—" disabled></div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Gender</label>
                                                <div class="input-icon {{ $pErr('gender') }}"><i class="bi bi-person-badge"></i>
                                                    <select class="form-select" name="gender" required {{ $profileFailed ? '' : 'disabled' }} data-editable>
                                                        <option value="male" {{ $pv('gender', $si->Gender ?? '') === 'male' ? 'selected' : '' }}>Male</option>
                                                        <option value="female" {{ $pv('gender', $si->Gender ?? '') === 'female' ? 'selected' : '' }}>Female</option>
                                                        <option value="other" {{ $pv('gender', $si->Gender ?? '') === 'other' ? 'selected' : '' }}>Other</option>
                                                    </select>
                                                </div>
                                                @if ($pMsg('gender')) <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $pMsg('gender') }}</div> @endif
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Religion</label>
                                                <div class="input-icon {{ $pErr('religion') }}"><i class="bi bi-book"></i><input type="text" name="religion" class="form-control"
                                                        value="{{ $pv('religion', $si->Religion ?? '') }}" {{ $profileFailed ? '' : 'disabled' }} data-editable></div>
                                                @if ($pMsg('religion')) <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $pMsg('religion') }}</div> @endif
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Nationality</label>
                                                <div class="input-icon {{ $pErr('nationality') }}"><i class="bi bi-flag"></i><input type="text" name="nationality" class="form-control"
                                                        value="{{ $pv('nationality', $si->Nationality ?? '') }}" required {{ $profileFailed ? '' : 'disabled' }} data-editable></div>
                                                @if ($pMsg('nationality')) <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $pMsg('nationality') }}</div> @endif
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Cell/Mobile number</label>
                                                <div class="input-icon {{ $pErr('phone') }}"><i class="bi bi-telephone"></i><input type="text" name="phone" class="form-control"
                                                        value="{{ $pv('phone', $si->PhoneNumber ?? '') }}" required {{ $profileFailed ? '' : 'disabled' }} data-editable></div>
                                                @if ($pMsg('phone')) <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $pMsg('phone') }}</div> @endif
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">Home address</label>
                                                <div class="input-icon {{ $pErr('address') }}"><i class="bi bi-geo-alt"></i><input type="text" name="address" class="form-control"
                                                        value="{{ $pv('address', $si->Address ?? '') }}" required {{ $profileFailed ? '' : 'disabled' }} data-editable></div>
                                                @if ($pMsg('address')) <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $pMsg('address') }}</div> @endif
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-end gap-2 mt-3">
                                            <button type="button" class="btn btn-brand" data-edit-btn="edit" @if ($profileFailed) hidden @endif><i class="bi bi-pencil-square"></i> Edit</button>
                                            <button type="button" class="btn btn-ghost" data-edit-btn="cancel" @unless ($profileFailed) hidden @endunless>Cancel</button>
                                            <button type="submit" class="btn btn-brand" data-edit-btn="save" @unless ($profileFailed) hidden @endunless><i class="bi bi-save"></i> Save Changes</button>
                                        </div>
                                    </form>
                                @endif
                            </div>

                            @unless ($verified)
                                <!-- Email Verification -->
                                <div class="card-soft p-3 p-md-4 mt-3">
                                    <div class="section-label mb-3"><i class="bi bi-shield-exclamation me-1"></i> Verify Your Email</div>
                                    <p class="text-muted-2 small">For your security, verify your email address before you can change your password. We'll send a 6-digit code to <strong>{{ $staff->Email }}</strong>.</p>

                                    @if ($showVerifyForm)
                                        <form method="POST" action="{{ route('staffProfile.verifyEmail') }}" class="row g-2 align-items-end">
                                            @csrf
                                            <div class="col-md-4">
                                                <label class="form-label">Verification code</label>
                                                <input type="text" name="code" class="form-control text-center {{ session('email_verify_error') ? 'has-error' : '' }}" maxlength="6" inputmode="numeric" pattern="[0-9]*" placeholder="••••••" required value="{{ old('code') }}" style="letter-spacing:4px;">
                                                @if (session('email_verify_error'))
                                                    <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ session('email_verify_error') }}</div>
                                                @endif
                                            </div>
                                            <div class="col-auto">
                                                <button type="submit" class="btn btn-brand">Verify</button>
                                            </div>
                                        </form>
                                        <form method="POST" action="{{ route('staffProfile.sendVerification') }}" class="mt-2">
                                            @csrf
                                            <button type="submit" class="btn btn-link btn-sm p-0">Didn't receive the code? Resend</button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('staffProfile.sendVerification') }}">
                                            @csrf
                                            <button type="submit" class="btn btn-brand"><i class="bi bi-envelope-check me-1"></i> Send Verification Code</button>
                                        </form>
                                    @endif
                                </div>
                            @endunless
                        </div>

                        {{-- ===================== SECURITY ===================== --}}
                        <div class="settings-pane" data-profile-pane="security" @if ($activeTab !== 'security') hidden @endif>

                            {{-- Security tips — shown to every admin session (admin + super admin) --}}
                            <div class="card-soft p-3 p-md-4 mb-3"
                                style="background:rgba(59,217,77,.06);border:1px solid rgba(59,217,77,.25);">
                                <div class="section-label mb-3" style="color:var(--success,#10b981);">
                                    <i class="bi bi-lightbulb-fill me-1"></i> Security Tips
                                </div>
                                <ul class="list-unstyled m-0 small text-muted-2">
                                    <li class="d-flex align-items-start gap-2 mb-2">
                                        <i class="bi bi-check-circle-fill mt-1" style="color:var(--success,#10b981);"></i>
                                        <span>Use a mix of uppercase, lowercase, numbers and symbols</span>
                                    </li>
                                    <li class="d-flex align-items-start gap-2 mb-2">
                                        <i class="bi bi-check-circle-fill mt-1" style="color:var(--success,#10b981);"></i>
                                        <span>Never reuse a password from another site</span>
                                    </li>
                                    <li class="d-flex align-items-start gap-2 mb-2">
                                        <i class="bi bi-check-circle-fill mt-1" style="color:var(--success,#10b981);"></i>
                                        <span>Your password should be at least 8 characters long</span>
                                    </li>
                                    <li class="d-flex align-items-start gap-2">
                                        <i class="bi bi-check-circle-fill mt-1" style="color:var(--success,#10b981);"></i>
                                        <span>Avoid using your name or birthday in your password</span>
                                    </li>
                                </ul>
                            </div>

                            <div class="card-soft p-3 p-md-4">
                                <div class="section-label mb-3"><i class="bi bi-shield-lock me-1"></i> Change Password</div>

                                @if (!$verified)
                                    <p class="text-muted-2 small mb-0">Verify your email on the {{ $profileLabel }} tab to unlock password changes.</p>
                                @else
                                    <form method="POST" action="{{ route('staffProfile.password.update') }}">
                                        @csrf
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label">Current password</label>
                                                <div class="pw-field">
                                                    <input type="checkbox" class="pw-toggle-checkbox" id="pwSpCur">
                                                    <div class="input-icon {{ session('password_error') ? 'has-error' : '' }}"><i class="bi bi-lock"></i><input type="text" name="current_password" class="form-control pw-mask" placeholder="••••••••" required value="{{ old('current_password') }}" autocomplete="current-password"></div>
                                                    <label for="pwSpCur" class="pw-eye-btn" aria-label="Show or hide password"><i class="bi bi-eye"></i><i class="bi bi-eye-slash"></i></label>
                                                </div>
                                                @if (session('password_error'))
                                                    <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ session('password_error') }}</div>
                                                @endif
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">New password</label>
                                                <div class="pw-field">
                                                    <input type="checkbox" class="pw-toggle-checkbox" id="pwSpNew">
                                                    <div class="input-icon @error('password') has-error @enderror"><i class="bi bi-key"></i><input type="text" name="password" class="form-control pw-mask" placeholder="••••••••" minlength="8" required value="{{ old('password') }}" autocomplete="new-password"></div>
                                                    <label for="pwSpNew" class="pw-eye-btn" aria-label="Show or hide password"><i class="bi bi-eye"></i><i class="bi bi-eye-slash"></i></label>
                                                </div>
                                                @error('password') <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div> @enderror
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Confirm new password</label>
                                                <div class="pw-field">
                                                    <input type="checkbox" class="pw-toggle-checkbox" id="pwSpConfirm">
                                                    <div class="input-icon @error('password') has-error @enderror"><i class="bi bi-shield-lock"></i><input type="text" name="password_confirmation" class="form-control pw-mask" placeholder="••••••••" required value="{{ old('password_confirmation') }}" autocomplete="new-password"></div>
                                                    <label for="pwSpConfirm" class="pw-eye-btn" aria-label="Show or hide password"><i class="bi bi-eye"></i><i class="bi bi-eye-slash"></i></label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-end mt-3">
                                            <button type="submit" class="btn btn-brand"><i class="bi bi-save me-1"></i> Update Password</button>
                                        </div>
                                    </form>
                                @endif
                            </div>

                            @if ($isSuperAdmin && $bootstrapConfigured)
                                @php $showReleaseVerify = session('show_super_admin_release', false); @endphp
                                {{-- Release the super admin account back to the .env setup login --}}
                                <div class="card-soft p-3 p-md-4 mt-3" style="border:1px solid rgba(229,86,75,.35);">
                                    <div class="section-label mb-3" style="color:var(--danger);"><i class="bi bi-exclamation-octagon me-1"></i> Release super admin account</div>
                                    <p class="text-muted-2 small">
                                        This removes <strong>{{ $staff->Email }}</strong> as the super admin and restores the
                                        temporary setup login (<code>{{ $bootstrapEmail }}</code>). We'll email a 6-digit code
                                        to <strong>{{ $staff->Email }}</strong> to confirm — you'll be signed out.
                                    </p>

                                    @if ($showReleaseVerify)
                                        <form method="POST" action="{{ route('superAdminRelease.verify') }}" class="row g-2 align-items-end">
                                            @csrf
                                            <div class="col-sm-4">
                                                <label class="form-label">Verification code</label>
                                                <div class="input-icon {{ session('release_code_error') ? 'has-error' : '' }}"><i class="bi bi-hash"></i><input type="text" name="code" class="form-control text-center" maxlength="6" inputmode="numeric" pattern="[0-9]*" placeholder="••••••" required autocomplete="one-time-code" value="{{ old('code') }}" style="letter-spacing:4px;"></div>
                                                @if (session('release_code_error'))
                                                    <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ session('release_code_error') }}</div>
                                                @endif
                                            </div>
                                            <div class="col-auto">
                                                <button type="submit" class="btn" style="background:var(--danger);border-color:var(--danger);color:#fff;"><i class="bi bi-box-arrow-left me-1"></i> Verify &amp; release account</button>
                                            </div>
                                        </form>
                                        <form method="POST" action="{{ route('superAdminRelease.sendCode') }}" class="mt-2">
                                            @csrf
                                            <button type="submit" class="btn btn-link btn-sm p-0">Didn't receive the code? Resend</button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('superAdminRelease.sendCode') }}">
                                            @csrf
                                            <button type="submit" class="btn" style="background:var(--danger);border-color:var(--danger);color:#fff;"><i class="bi bi-envelope-check me-1"></i> Send verification code</button>
                                        </form>
                                    @endif
                                </div>
                            @endif
                        </div>

                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @include('partials.confirm-action-modal')
    <script src="{{ asset('js/field-restrictions.js') }}"></script>
    <script src="{{ asset('js/birthdate-age.js') }}"></script>
    <script src="{{ asset('js/profile-edit-toggle.js') }}"></script>
    <script>
        document.querySelectorAll('[data-profile-tab]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var tab = btn.dataset.profileTab;

                document.querySelectorAll('[data-profile-tab]').forEach(function (b) {
                    b.classList.toggle('active', b === btn);
                });
                document.querySelectorAll('[data-profile-pane]').forEach(function (pane) {
                    pane.hidden = pane.dataset.profilePane !== tab;
                });

                var url = new URL(window.location.href);
                url.searchParams.set('tab', tab);
                window.history.replaceState({}, '', url);
            });
        });
    </script>
</body>

</html>
