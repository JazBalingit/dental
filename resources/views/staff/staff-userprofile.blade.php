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
    <link rel="stylesheet" href="/css/styles.css">
</head>

<body>
    @php
        $si = $staff->staffInfo;
        $verified = (bool) $staff->EmailVerifiedAt;
        $showVerifyForm = session('show_staff_verify', false);
    @endphp
    <div class="app">
        <aside class="sidebar">
            <div class="brand">
                <div><img class="logo" src="/images/adams_logo2.png" alt=""></div>
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
                <a href="{{ route('configuration') }}"><i class="bi bi-sliders2"></i> Configuration</a>
            </nav>
            <div class="footer">© PUS-PUS BRITANICO DENTAL CLINIC</div>
        </aside>

        <main>
            <div class="topbar">
                <div class="left">
                    <button class="toggle"><i class="bi bi-list"></i></button>
                </div>
                <div class="right">
                    <div class="dropdown">
                        <button class="user-chip" type="button" data-bs-toggle="dropdown" aria-expanded="false"
                            style="all:unset; cursor:pointer; display:flex; align-items:center; gap:.6rem; padding:.35rem .8rem .35rem .35rem; border-radius:999px; background:var(--brand-50); border:1px solid var(--brand-100); font-family:inherit;">
                            <div><img class="avatar" src="{{ $si->photo_url ?? asset('images/default.png') }}" alt=""></div>
                            <div class="meta">
                                <div class="name">{{ $si->FirstName ?? 'Staff' }}</div>
                                <div class="role">{{ $staff->Position }}</div>
                            </div>
                            <i class="bi bi-chevron-down ms-1 text-muted-2"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                            <li><a class="dropdown-item small" href="{{ route('staffProfile') }}"><i class="bi bi-person me-2"></i>My Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}" class="m-0">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger small"><i class="bi bi-box-arrow-right me-1"></i> Log Out</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="content">
                <div class="page-head">
                    <div>
                        <h2>My Profile</h2>
                        <div class="crumbs">View your information and manage your account security.</div>
                    </div>
                </div>

                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                @if (session('verify_sent'))
                    <div class="alert alert-success">Verification code sent! Check your inbox (and spam folder).</div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Account Information -->
                <div class="card-soft p-3 p-md-4 mb-3">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <img class="avatar-initials" src="{{ $si->photo_url ?? asset('images/default.png') }}" alt="" style="width:72px;height:72px;">
                        <div>
                            <h5 class="fw-semibold mb-1">{{ $si->FirstName ?? '' }} {{ $si->LastName ?? '' }}</h5>
                            <span class="pill pill-info">{{ ucfirst($staff->AccountRole) }}</span>
                            <span class="pill pill-success">{{ $staff->Position }}</span>
                        </div>
                    </div>

                    <div class="section-label">Account Information</div>
                    <div class="row g-3 mb-3">
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
                            <div class="input-icon"><i class="bi bi-calendar3"></i><input type="text" class="form-control" value="{{ $si->Age ?? '' }}" disabled></div>
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
                </div>

                @unless ($verified)
                    <!-- Email Verification -->
                    <div class="card-soft p-3 p-md-4 mb-3">
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

                <!-- Change Password -->
                <div class="card-soft p-3 p-md-4">
                    <div class="section-label mb-3"><i class="bi bi-shield-lock me-1"></i> Change Password</div>

                    @if (!$verified)
                        <p class="text-muted-2 small mb-0">Verify your email above to unlock password changes.</p>
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
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
