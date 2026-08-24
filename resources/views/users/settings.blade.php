<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings — Pus-Pus Britanico</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="/css/settings.css">
</head>

<body>
    @php
        $showResetModal = session('show_settings_reset_modal', false);
    @endphp

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top mask-custom shadow-sm">
        <div class="container-fluid px-3 px-lg-5">
            <a class="navbar-brand d-flex align-items-center" href="#home">
                <img class="logo" src="/images/puspus_logo.png" alt="Pus-Pus Britanico logo">
                <span class="navt ms-1" style="color:#0f7a2d;">PUS-PUS</span>
                <span class="navt ms-2" style="color:#144d25;">BRITANICO</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarSupportedContent">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav mx-auto text-center">
                    <li class="nav-item"><a class="nav-link navh" href="{{ route('landingPage') }}#home">Home</a></li>
                    <li class="nav-item"><a class="nav-link navh"
                            href="{{ route('landingPage') }}#services">Services</a></li>
                    <li class="nav-item"><a class="nav-link navh" href="{{ route('landingPage') }}#how">How It Works</a>
                    </li>
                    <li class="nav-item"><a class="nav-link navh" href="{{ route('landingPage') }}#about">About</a></li>
                    <li class="nav-item"><a class="nav-link navh"
                            href="{{ route('landingPage') }}#appointment">Appointment</a></li>
                    <li class="nav-item"><a class="nav-link navh" href="{{ route('landingPage') }}#contact">Contact</a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-lg-3">
                    <li class="nav-item">
                        <div class="d-flex justify-content-between">
                            @include('partials.user-notif-dropdown')
                            @if (session('user_email'))
                                <div class="dropdown d-flex align-items-center">
                                    <a href="{{ route('settings', ['tab' => 'profile']) }}"
                                        class="nav-link navh d-flex align-items-center gap-2" style="padding-right:8px;">
                                        <i class="bi bi-person-circle"></i>
                                        <span>{{ session('user_email') }}</span>
                                    </a>
                                    <button class="nav-link navh border-0 bg-transparent dropdown-toggle"
                                        type="button" data-bs-toggle="dropdown" aria-expanded="false"
                                        aria-label="Account menu" style="padding-left:6px;padding-right:10px;margin-left:-4px;"></button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                        <li>
                                            <a class="dropdown-item small" href="{{ route('userAppointment') }}">
                                                <i class="bi bi-calendar-check me-2"></i>User Appointments
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item small" href="{{ route('settings') }}">
                                                <i class="bi bi-gear me-2"></i>Settings
                                            </a>
                                        </li>
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        <li>
                                            <form method="POST" action="{{ route('logout') }}" class="m-0">
                                                @csrf
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="bi bi-box-arrow-right me-1"></i> Log Out
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            @else
                                <a href="{{ route('login') }}" class="nav-link navh signin-btn">Sign In</a>
                                <a href="{{ route('signup') }}" class="nav-link navh signup-btn">Sign Up</a>
                            @endif
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- PAGE HERO -->
    <div class="page-hero">
        <div class="container px-4">
            <h1>USER PROFILE</h1>
            <p class="page-hero-sub">Settings</p>
        </div>
    </div>

    <!-- SUB-NAV -->
    <div class="subnav">
        <div class="container px-4">
            <div class="subnav-inner">
                <a href="{{ route('userAppointment') }}" class="subnav-link"><i class="fas fa-calendar-check"></i>
                    Appointments</a>
                <a href="{{ route('settings') }}" class="subnav-link active"><i class="fas fa-gear"></i>
                    Settings</a>
            </div>
        </div>
    </div>

    <!-- CONTENT -->
    <div class="content-wrap">

        @include('partials.flash-toasts', ['topOffset' => '100px'])

        <div class="settings-layout">
            <!-- TABS -->
            <aside class="settings-tabs-card">
                <div class="settings-tabs-label">Settings</div>
                <nav class="settings-tabs">
                    <button type="button" class="settings-tab-btn {{ $activeTab === 'profile' ? 'active' : '' }}"
                        data-tab="profile">
                        <i class="fas fa-user"></i> User Information
                    </button>
                    <button type="button" class="settings-tab-btn {{ $activeTab === 'security' ? 'active' : '' }}"
                        data-tab="security">
                        <i class="fas fa-shield-halved"></i> Security
                    </button>
                </nav>
            </aside>

            <div class="settings-content">

                {{-- ===================== USER INFORMATION ===================== --}}
                <div class="settings-pane" id="pane-profile" @if($activeTab !== 'profile') hidden @endif>
                    <form method="POST" action="{{ route('userProfile.update') }}" enctype="multipart/form-data">
                        @csrf

                        <!-- Photo + locked account info -->
                        <div class="section-card">
                            <div class="card-hd">
                                <div class="card-hd-icon"><i class="fas fa-id-card"></i></div>
                                <div>
                                    <h4>Account Information</h4>
                                    <p>Your photo and login details</p>
                                </div>
                            </div>
                            <div class="card-bd d-flex gap-4 align-items-center flex-wrap">
                                <div class="text-center">
                                    <img src="{{ $patientInfo->photo_url }}" alt="Profile photo"
                                         style="width:96px;height:96px;border-radius:50%;object-fit:cover;border:3px solid #e9f7ea;">
                                    <div class="mt-2">
                                        <label class="btn-edit" style="cursor:pointer;">
                                            <i class="fas fa-camera"></i> Change Photo
                                            <input type="file" name="photo" accept=".jpg,.jpeg,.png" class="d-none">
                                        </label>
                                    </div>
                                    <div class="small text-muted mt-1">JPG or PNG, max 2MB</div>
                                </div>

                                <div class="flex-grow-1" style="min-width:220px">
                                    <div class="mb-3">
                                        <label class="fl">Email address</label>
                                        <input type="text" class="fc fc-plain" value="{{ $user->Email }}" disabled>
                                        <div class="small text-muted mt-1">Email can't be changed here. Contact the clinic if you need to update it.</div>
                                    </div>
                                    <div>
                                        <label class="fl">Member since</label>
                                        <input type="text" class="fc fc-plain" value="{{ \Carbon\Carbon::parse($user->DateCreated)->format('F j, Y') }}" disabled>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Editable patient information -->
                        <div class="section-card">
                            <div class="card-hd">
                                <div class="card-hd-icon"><i class="fas fa-user-edit"></i></div>
                                <div>
                                    <h4>Personal Information</h4>
                                    <p>Everything here can be edited</p>
                                </div>
                            </div>
                            <div class="card-bd">
                                <div class="row g-3 mb-2">
                                    <div class="col-md-4">
                                        <label class="fl">Last name</label>
                                        <input class="fc fc-plain" name="last_name" value="{{ old('last_name', $patientInfo->LastName) }}" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="fl">First name</label>
                                        <input class="fc fc-plain" name="first_name" value="{{ old('first_name', $patientInfo->FirstName) }}" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="fl">Middle name</label>
                                        <input class="fc fc-plain" name="middle_name" value="{{ old('middle_name', $patientInfo->MiddleName) }}">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="fl">Birthdate</label>
                                        <input type="date" class="fc fc-plain" name="birthdate"
                                               value="{{ old('birthdate', optional($patientInfo->DateOfBirth)->format('Y-m-d')) }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="fl">Gender</label>
                                        <select class="fc fc-plain" name="gender" required>
                                            <option value="male" {{ old('gender', $patientInfo->Gender) === 'male' ? 'selected' : '' }}>Male</option>
                                            <option value="female" {{ old('gender', $patientInfo->Gender) === 'female' ? 'selected' : '' }}>Female</option>
                                            <option value="other" {{ old('gender', $patientInfo->Gender) === 'other' ? 'selected' : '' }}>Other</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="fl">Religion</label>
                                        <input class="fc fc-plain" name="religion" value="{{ old('religion', $patientInfo->Religion) }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="fl">Nationality</label>
                                        <input class="fc fc-plain" name="nationality" value="{{ old('nationality', $patientInfo->Nationality) }}" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="fl">Occupation</label>
                                        <input class="fc fc-plain" name="occupation" value="{{ old('occupation', $patientInfo->Occupation) }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="fl">Cell/Mobile number</label>
                                        <input class="fc fc-plain" name="phone" value="{{ old('phone', $patientInfo->PhoneNumber) }}" required>
                                    </div>

                                    <div class="col-12">
                                        <label class="fl">Home address</label>
                                        <input class="fc fc-plain" name="address" value="{{ old('address', $patientInfo->Address) }}" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="fl">Parent/Guardian's name</label>
                                        <input class="fc fc-plain" name="guardian_name" value="{{ old('guardian_name', $patientInfo->ParentsName) }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="fl">Parent/Guardian's occupation</label>
                                        <input class="fc fc-plain" name="guardian_occupation" value="{{ old('guardian_occupation', $patientInfo->ParentsOccupation) }}">
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end mt-3">
                                    <button type="submit" class="btn-prim" style="width:auto;padding-left:26px;padding-right:26px;"><i class="fas fa-save"></i> Save Changes</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                {{-- ===================== SECURITY ===================== --}}
                <div class="settings-pane" id="pane-security" @if($activeTab !== 'security') hidden @endif>

                    <!-- Tips -->
                    <div class="tip-box">
                        <h5><i class="fas fa-lightbulb" style="color:#3bd94d"></i> Security Tips</h5>
                        <ul>
                            <li><i class="fas fa-check-circle"></i> Use a mix of uppercase, lowercase, numbers and symbols</li>
                            <li><i class="fas fa-check-circle"></i> Never reuse a password from another site</li>
                            <li><i class="fas fa-check-circle"></i> Your password should be at least 8 characters long</li>
                            <li><i class="fas fa-check-circle"></i> Avoid using your name or birthday in your password</li>
                        </ul>
                    </div>

                    <!-- Change Password -->
                    <form method="POST" action="{{ route('settings.password.update') }}">
                        @csrf
                        <div class="section-card">
                            <div class="card-hd">
                                <div class="card-hd-icon"><i class="fas fa-key"></i></div>
                                <div>
                                    <h4>Update Password</h4>
                                    <p>Enter your current password then set a new one</p>
                                </div>
                            </div>
                            <div class="card-bd">

                                <div class="mb-3">
                                    <label class="fl">Current Password</label>
                                    <div class="pw-field">
                                        <input type="checkbox" class="pw-toggle-checkbox" id="pwCheckCur">
                                        <div class="fw">
                                            <input type="text" class="fc pw-mask" name="current_password"
                                                placeholder="Enter your current password" required>
                                            <label for="pwCheckCur" class="eye-btn">
                                                <i class="fas fa-eye"></i><i class="fas fa-eye-slash"></i>
                                            </label>
                                        </div>
                                    </div>
                                    <p class="field-hint"><i class="fas fa-info-circle"></i> This is the password you use to log
                                        in.</p>
                                </div>

                                <div class="divider"></div>

                                <div class="mb-3">
                                    <label class="fl">New Password</label>
                                    <div class="pw-field">
                                        <input type="checkbox" class="pw-toggle-checkbox" id="pwCheckNew">
                                        <div class="fw">
                                            <input type="text" class="fc pw-mask" name="password"
                                                placeholder="Create a strong new password" required minlength="8">
                                            <label for="pwCheckNew" class="eye-btn">
                                                <i class="fas fa-eye"></i><i class="fas fa-eye-slash"></i>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="fl">Confirm New Password</label>
                                    <div class="pw-field">
                                        <input type="checkbox" class="pw-toggle-checkbox" id="pwCheckConfirm">
                                        <div class="fw">
                                            <input type="text" class="fc pw-mask" name="password_confirmation"
                                                placeholder="Re-enter your new password" required>
                                            <label for="pwCheckConfirm" class="eye-btn">
                                                <i class="fas fa-eye"></i><i class="fas fa-eye-slash"></i>
                                            </label>
                                        </div>
                                    </div>
                                    <p class="field-hint"><i class="fas fa-lock"></i> Make sure both passwords match exactly.</p>
                                </div>

                                <div class="divider"></div>

                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <button type="submit" class="btn-prim"><i class="fas fa-shield-alt"></i> Update
                                            Password</button>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </form>

                    <!-- Forgot / Reset -->
                    <div class="section-card" style="border-color:rgba(239,68,68,0.14)">
                        <div class="card-hd" style="border-color:rgba(239,68,68,0.1)">
                            <div class="card-hd-icon" style="background:rgba(239,68,68,0.08);color:#ef4444"><i
                                    class="fas fa-question-circle"></i></div>
                            <div>
                                <h4 style="color:#b91c1c">Forgot Your Password?</h4>
                                <p>We can send a reset code to your registered email</p>
                            </div>
                        </div>
                        <div class="card-bd">
                            <p style="font-size:14px;margin-bottom:16px">If you've forgotten your current password, you can
                                request a reset code sent to
                                <strong style="color:#0f7a23">{{ session('user_email') }}</strong>.
                            </p>
                            <form method="POST" action="{{ route('settings.reset.send') }}">
                                @csrf
                                <button type="submit" class="btn-outline-sm">
                                    <i class="fas fa-envelope"></i> Send Reset Code
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>

    <!-- FOOTER -->
    <footer id="contact">
        <div class="container">
            <div class="row mb-5">
                <div class="col-lg-6 col-md-12 mb-4 mb-md-0">
                    <h5 class="text-uppercase fw-bold">PUS-PUS BRITANICO DENTAL CLINIC</h5>
                    <p>
                        Providing quality dental care with compassion and professionalism. Our clinic is dedicated to
                        ensuring every
                        patient receives personalized treatment in a comfortable and welcoming environment. Your smile
                        is our
                        priority.
                    </p>
                </div>
                <div class="col-md-6 col-lg-3 offset-lg-3">
                    <h6 class="text-uppercase fw-bold mb-4">Contact Information</h6>
                    <p><i class="fas fa-map-marker-alt me-3"></i> #50 Mainroad Ave. B21 L31 Phase 1 Pacita Complex 2 San
                        Pedro,
                        Laguna</p>
                    <p><i class="fas fa-phone me-3"></i>(02)84045642</p>
                    <p><i class="fa-solid fa-mobile me-3"></i>+63 968-476-5943</p>
                </div>
            </div>
            <hr style="border-color: rgba(255, 255, 255, 0.934); margin: 30px 0;">
            <div class="text-center">
                <p style="color: rgba(255, 255, 255, 0.8); margin: 0;">&copy; 2026 Pus-Pus Britanico Dental Clinic. All
                    rights
                    reserved.
                </p>
            </div>
        </div>
    </footer>

    @include('partials.user-notif-modal')

    {{-- ===================== FORGOT PASSWORD MODAL (same pattern as login) ===================== --}}
    @if ($showResetModal)
        <div class="modal-backdrop fade show"></div>
    @endif

    <div class="modal fade {{ $showResetModal ? 'show' : '' }}" id="settingsResetModal" tabindex="-1"
         aria-hidden="{{ $showResetModal ? 'false' : 'true' }}" style="{{ $showResetModal ? 'display:block;' : '' }}">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0" style="display:flex; align-items:center; justify-content:space-between;">
                    <h5 class="modal-title fw-semibold">Enter your code</h5>
                    <form method="POST" action="{{ route('settings.reset.cancel') }}" class="m-0" style="margin-left: auto;">
                        @csrf
                        <button type="submit" class="btn-close"
                          style="background-color: transparent; border: 0; padding: 0.25em; margin: 0; box-shadow: none;"
                          aria-label="Close"></button>
                    </form>
                </div>
                <div class="modal-body pt-2">
                    @if ($errors->any())
                        <div class="alert alert-danger py-2 small">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <p class="small text-muted mb-3">
                        We sent a 6-digit code to <strong>{{ session('user_email') }}</strong>.
                    </p>

                    @if (session('settings_reset_sent'))
                        <div class="alert alert-success py-2 small">Code sent! Check your inbox (and spam folder).</div>
                    @endif
                    @if (session('settings_reset_resent'))
                        <div class="alert alert-success py-2 small">A new code has been sent.</div>
                    @endif
                    @if (session('settings_reset_expired'))
                        <div class="alert alert-warning py-2 small">Your session expired. Please click "Send Reset Code" again.</div>
                    @endif
                    @if (session('settings_reset_error'))
                        <div class="alert alert-danger py-2 small">{{ session('settings_reset_error') }}</div>
                    @endif

                    <form method="POST" action="{{ route('settings.reset.verify') }}" class="mb-2">
                        @csrf
                        <label class="fl">Verification code</label>
                        <input type="text" name="code" class="fc text-center mb-3" maxlength="6"
                               inputmode="numeric" pattern="[0-9]*" placeholder="••••••" required
                               style="letter-spacing: 6px; font-size: 1.25rem;">

                        <label class="fl">New password</label>
                        <div class="pw-field mb-3">
                            <input type="checkbox" class="pw-toggle-checkbox" id="pwCheckModalNew">
                            <div class="fw">
                                <input type="text" name="password" class="fc pw-mask" placeholder="••••••••" required minlength="8">
                                <label for="pwCheckModalNew" class="eye-btn">
                                    <i class="fas fa-eye"></i><i class="fas fa-eye-slash"></i>
                                </label>
                            </div>
                        </div>

                        <label class="fl">Confirm new password</label>
                        <div class="pw-field mb-3">
                            <input type="checkbox" class="pw-toggle-checkbox" id="pwCheckModalConfirm">
                            <div class="fw">
                                <input type="text" name="password_confirmation" class="fc pw-mask" placeholder="••••••••" required>
                                <label for="pwCheckModalConfirm" class="eye-btn">
                                    <i class="fas fa-eye"></i><i class="fas fa-eye-slash"></i>
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="btn-prim w-100"><i class="fas fa-shield-alt"></i> Reset Password</button>
                    </form>

                    <form method="POST" action="{{ route('settings.reset.resend') }}" class="text-center">
                        @csrf
                        <span class="small text-muted">Didn't receive the code?</span>
                        <button type="submit" class="btn-link btn-sm p-0 ms-1" style="background:none;border:none;color:#0f7a23;">Resend</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('.settings-tab-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var tab = btn.dataset.tab;

                document.querySelectorAll('.settings-tab-btn').forEach(function (b) {
                    b.classList.toggle('active', b === btn);
                });
                document.querySelectorAll('.settings-pane').forEach(function (pane) {
                    pane.hidden = pane.id !== 'pane-' + tab;
                });

                var url = new URL(window.location.href);
                url.searchParams.set('tab', tab);
                window.history.replaceState({}, '', url);
            });
        });
    </script>
</body>

</html>
