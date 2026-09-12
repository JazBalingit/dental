<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings — Patient Portal — Pus-Pus Britanico</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <link rel="stylesheet" href="/css/settings.css">
    <style>
        .cfg-table { width: 100%; border-collapse: collapse; font-size: 14px; }
        .cfg-table th, .cfg-table td { padding: 12px 14px; text-align: left; border-bottom: 1px solid #eef2ee; vertical-align: top; }
        .cfg-table th { font-size: 12px; text-transform: uppercase; letter-spacing: .04em; color: #6b7280; background: #f8faf8; }
        .cfg-table tr:last-child td { border-bottom: 0; }
        .cfg-badge { display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; white-space: nowrap; }
        .cfg-badge.ok { background: rgba(59,217,101,.12); color: #0f7a33; }
        .cfg-badge.fail { background: rgba(239,68,68,.12); color: #b91c1c; }
        .cfg-badge.neutral { background: rgba(59,130,246,.12); color: #1d4ed8; }
        .cfg-toolbar { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 16px; }
        .cfg-toolbar input, .cfg-toolbar select { height: 40px; border: 1px solid #d7e0d7; border-radius: 10px; padding: 0 12px; }
        .cfg-toolbar input { min-width: 220px; }
        .cfg-pager { display: flex; gap: 6px; justify-content: flex-end; margin-top: 14px; flex-wrap: wrap; }
        .cfg-pager a { padding: 6px 11px; border: 1px solid #d7e0d7; border-radius: 8px; color: #0f7a23; text-decoration: none; font-size: 13px; }
        .cfg-pager a.active { background: #0f7a23; color: #fff; border-color: #0f7a23; }
        .cfg-empty { text-align: center; color: #6b7280; padding: 40px 0; }
        .cfg-when { white-space: nowrap; color: #4b5563; }
    </style>
    <link rel="stylesheet" href="{{ asset('css/mobile.css') }}">
</head>

<body>
    @php
        $showResetModal = session('show_settings_reset_modal', false);
    @endphp
    <div class="app">
        <aside class="sidebar offcanvas position-sticky" tabindex="-1" id="sidebarOffcanvas">
            <div class="brand">
                <div><img class="logo" src="/images/puspus_logo.png" alt=""></div>
                <div>
                    <div class="name">PUS-PUS BRITANICO</div>
                    <div class="sub">PATIENT PORTAL</div>
                </div>
            </div>
            @include('partials.patient-sidebar-nav', ['active' => 'settings'])
            @include('partials.patient-profile-badge')
        </aside>

        <main>
            <div class="topbar">
                <div class="left">
                    <button class="toggle d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas" aria-controls="sidebarOffcanvas">
                        <i class="bi bi-list"></i>
                    </button>
                </div>
                <div class="right">
                    @include('partials.user-notif-dropdown')
                </div>
            </div>

            <div class="content">
                @include('partials.flash-toasts')
                <div class="page-head">
                    <div>
                        <h2>Settings</h2>
                        <div class="crumbs">Manage your profile, password, and account activity.</div>
                    </div>
                </div>

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
                    <button type="button" class="settings-tab-btn {{ $activeTab === 'configuration' ? 'active' : '' }}"
                        data-tab="configuration">
                        <i class="fas fa-list-check"></i> Configuration
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
                                                placeholder="Enter your current password" required
                                                value="{{ old('current_password') }}">
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
                                                placeholder="Create a strong new password" required minlength="8"
                                                value="{{ old('password') }}">
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
                                                placeholder="Re-enter your new password" required
                                                value="{{ old('password_confirmation') }}">
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

                {{-- ===================== CONFIGURATION (ACTIVITY LOG) ===================== --}}
                <div class="settings-pane" id="pane-configuration" @if($activeTab !== 'configuration') hidden @endif>
                    <div class="section-card">
                        <div class="card-hd">
                            <div class="card-hd-icon"><i class="fas fa-list-check"></i></div>
                            <div>
                                <h4>Your Activity Log</h4>
                                <p>A record of your sign-ins and sign-outs, failed login attempts on your account,
                                    password changes, appointments you booked / cancelled / rescheduled, and profile
                                    edits.</p>
                            </div>
                        </div>
                        <div class="card-bd">
                            <form method="GET" action="{{ route('settings') }}" class="cfg-toolbar">
                                <input type="hidden" name="tab" value="configuration">
                                <select name="type" onchange="this.form.submit()">
                                    <option value="">All activity</option>
                                    @foreach ($activityTypes as $t)
                                        <option value="{{ $t }}" {{ $activityType === $t ? 'selected' : '' }}>{{ $t }}
                                        </option>
                                    @endforeach
                                </select>
                                <input type="text" name="search" value="{{ $activitySearch }}"
                                    placeholder="Search details...">
                                <button type="submit" class="btn-outline-sm"><i class="fas fa-search"></i> Search</button>
                                @if ($activitySearch || $activityType)
                                    <a href="{{ route('settings', ['tab' => 'configuration']) }}" class="btn-outline-sm"><i
                                            class="fas fa-times"></i> Clear</a>
                                @endif
                            </form>

                            <div class="table-responsive">
                                <table class="cfg-table">
                                    <thead>
                                        <tr>
                                            <th>Activity</th>
                                            <th>Details</th>
                                            <th>When</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($logs as $log)
                                            @php
                                                $isFail = str_starts_with($log->ActivityType, 'Failed');
                                                $isSession = in_array($log->ActivityType, ['Login', 'Logout']);
                                                $cls = $isFail ? 'fail' : ($isSession ? 'neutral' : 'ok');
                                                $when = $log->LoggedInTime ?? $log->created_at;
                                            @endphp
                                            <tr>
                                                <td><span class="cfg-badge {{ $cls }}">{{ $log->ActivityType }}</span>
                                                </td>
                                                <td>
                                                    {{ $log->Description ?: '—' }}
                                                    @if ($log->ActivityType === 'Login' && $log->LoggedOutTime)
                                                        <div class="small text-muted">Signed out
                                                            {{ $log->LoggedOutTime->format('M j, Y g:i A') }}</div>
                                                    @endif
                                                </td>
                                                <td class="cfg-when">
                                                    {{ optional($when)->format('M j, Y g:i A') ?? '—' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="cfg-empty">No activity recorded yet.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @if ($logs->hasPages())
                                <div class="cfg-pager">
                                    @if ($logs->previousPageUrl())
                                        <a href="{{ $logs->previousPageUrl() }}"><i class="fas fa-chevron-left"></i></a>
                                    @endif
                                    @for ($i = 1; $i <= $logs->lastPage(); $i++)
                                        <a href="{{ $logs->url($i) }}"
                                            class="{{ $logs->currentPage() === $i ? 'active' : '' }}">{{ $i }}</a>
                                    @endfor
                                    @if ($logs->nextPageUrl())
                                        <a href="{{ $logs->nextPageUrl() }}"><i class="fas fa-chevron-right"></i></a>
                                    @endif
                                </div>
                            @endif

                            <p class="field-hint mt-3"><i class="fas fa-info-circle"></i> Showing {{ $logs->count() }} of
                                {{ $logs->total() }} entries.</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>

            </div>
        </main>
    </div>

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
                               value="{{ old('code') }}" style="letter-spacing: 6px; font-size: 1.25rem;">

                        <label class="fl">New password</label>
                        <div class="pw-field mb-3">
                            <input type="checkbox" class="pw-toggle-checkbox" id="pwCheckModalNew">
                            <div class="fw">
                                <input type="text" name="password" class="fc pw-mask" placeholder="••••••••" required minlength="8" value="{{ old('password') }}">
                                <label for="pwCheckModalNew" class="eye-btn">
                                    <i class="fas fa-eye"></i><i class="fas fa-eye-slash"></i>
                                </label>
                            </div>
                        </div>

                        <label class="fl">Confirm new password</label>
                        <div class="pw-field mb-3">
                            <input type="checkbox" class="pw-toggle-checkbox" id="pwCheckModalConfirm">
                            <div class="fw">
                                <input type="text" name="password_confirmation" class="fc pw-mask" placeholder="••••••••" required value="{{ old('password_confirmation') }}">
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
