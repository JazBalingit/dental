<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>My Profile • Dental Clinic</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
</head>

<body>
    @php
        $si = $staff->staffInfo;
        $verified = (bool) $staff->EmailVerifiedAt;
        $showVerifyForm = session('show_staff_verify', false);
        $isSuperAdmin = (bool) session('is_super_admin');
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
            <nav class="nav">
                <div class="nav-section">Main</div>
                <a href="{{ route('dashboard') }}"><i class="bi bi-grid-1x2-fill"></i> Dashboard</a>
                <a href="{{ route('staffAcc') }}"><i class="bi bi-people-fill"></i> Staff Accounts</a>
                <a href="{{ route('userAcc') }}"><i class="bi bi-people-fill"></i> User Accounts</a>
                <a href="{{ route('dentistSchedule') }}"><i class="bi bi-calendar3"></i> Doctor Schedule</a>
                <a href="{{ route('walkIn') }}"><i class="bi bi-calendar3"></i> Walk-in Appointments</a>
                <a href="{{ route('appointmentApproval') }}"><i class="bi bi-clipboard2-check"></i> Appointment
                    Approval</a>
                <a href="{{ route('appointments') }}"><i class="bi bi-clipboard2-check"></i> Appointments</a>
                <a href="{{ route('patientRecords') }}"><i class="bi bi-folder2-open"></i> Patient Records</a>
                <div class="nav-section">System</div>
                <a href="{{ route('configuration') }}"><i class="bi bi-sliders2"></i> Settings</a>
            </nav>
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
                        <h2>My Profile</h2>
                        <div class="crumbs">View your information and manage your account security.</div>
                    </div>
                </div>

                @include('partials.flash-toasts')

                <div class="row g-3">
                    <div class="col-lg-3">
                        <div class="card-soft p-2">
                            <div class="nav flex-column nav-pills" role="tablist" aria-orientation="vertical">
                                <button class="nav-link text-start {{ $activeTab === 'profile' ? 'active' : '' }}"
                                    data-profile-tab="profile" type="button"><i class="bi bi-person me-2"></i>User
                                    Profile</button>
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
                                        <span class="pill pill-info">{{ ucfirst($staff->AccountRole) }}</span>
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
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label">Last name</label>
                                            <div class="input-icon"><i class="bi bi-person"></i><input type="text" class="form-control" value="{{ $si->LastName ?? '' }}" disabled></div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">First name</label>
                                            <div class="input-icon"><i class="bi bi-person"></i><input type="text" class="form-control" value="{{ $si->FirstName ?? '' }}" disabled></div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Middle name</label>
                                            <div class="input-icon"><i class="bi bi-person"></i><input type="text" class="form-control" value="{{ $si->MiddleName ?? '' }}" disabled></div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Birthdate</label>
                                            <div class="input-icon"><i class="bi bi-calendar-event"></i><input type="text" class="form-control" value="{{ optional($si->DateOfBirth ?? null)->format('M j, Y') }}" disabled></div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Age</label>
                                            <div class="input-icon"><i class="bi bi-calendar3"></i><input type="text" class="form-control" value="{{ optional($si->DateOfBirth ?? null)->age ?? '' }}" disabled></div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Gender</label>
                                            <div class="input-icon"><i class="bi bi-person-badge"></i><input type="text" class="form-control" value="{{ ucfirst($si->Gender ?? '') }}" disabled></div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Religion</label>
                                            <div class="input-icon"><i class="bi bi-book"></i><input type="text" class="form-control" value="{{ $si->Religion ?? '' }}" disabled></div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Nationality</label>
                                            <div class="input-icon"><i class="bi bi-flag"></i><input type="text" class="form-control" value="{{ $si->Nationality ?? '' }}" disabled></div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Cell/Mobile number</label>
                                            <div class="input-icon"><i class="bi bi-telephone"></i><input type="text" class="form-control" value="{{ $si->PhoneNumber ?? '' }}" disabled></div>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Home address</label>
                                            <div class="input-icon"><i class="bi bi-geo-alt"></i><input type="text" class="form-control" value="{{ $si->Address ?? '' }}" disabled></div>
                                        </div>
                                    </div>
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
                                                <input type="text" name="code" class="form-control text-center" maxlength="6" inputmode="numeric" pattern="[0-9]*" placeholder="••••••" required style="letter-spacing:4px;">
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
                            <div class="card-soft p-3 p-md-4">
                                <div class="section-label mb-3"><i class="bi bi-shield-lock me-1"></i> Change Password</div>

                                @if ($isSuperAdmin)
                                    <p class="text-muted-2 small mb-0">Your login is managed by server configuration, not a stored password — there's nothing to change here.</p>
                                @elseif (!$verified)
                                    <p class="text-muted-2 small mb-0">Verify your email on the User Profile tab to unlock password changes.</p>
                                @else
                                    <form method="POST" action="{{ route('staffProfile.password.update') }}">
                                        @csrf
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label">Current password</label>
                                                <div class="input-icon"><i class="bi bi-lock"></i><input type="password" name="current_password" class="form-control" required></div>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">New password</label>
                                                <div class="input-icon"><i class="bi bi-key"></i><input type="password" name="password" class="form-control" minlength="8" required></div>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Confirm new password</label>
                                                <div class="input-icon"><i class="bi bi-shield-lock"></i><input type="password" name="password_confirmation" class="form-control" required></div>
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-end mt-3">
                                            <button type="submit" class="btn btn-brand"><i class="bi bi-save me-1"></i> Update Password</button>
                                        </div>
                                    </form>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
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
