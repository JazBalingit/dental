<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
  <link rel="icon" type="image/png" href="/images/puspus_logo.png">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Set Up Super Admin Account • Dental Clinic</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <style>
        /* Inert sidebar item — the setup session has no navigation. */
        .sidebar .nav .nav-locked {
            display: flex;
            align-items: center;
            gap: .75rem;
            padding: .65rem .85rem;
            border-radius: 10px;
            font-size: .92rem;
            font-weight: 500;
            color: #fff;
            background: linear-gradient(135deg, var(--brand-700), var(--brand-600));
            box-shadow: 0 8px 18px -8px rgba(46, 133, 50, .7);
        }

        .sidebar .nav .nav-locked i {
            font-size: 1.05rem;
            width: 20px;
            text-align: center;
        }
    </style>
    <link rel="stylesheet" href="{{ asset('css/modals.css') }}">
    <link rel="stylesheet" href="{{ asset('css/mobile.css') }}">
</head>

<body>
    <div class="app">
        <aside class="sidebar">
            <div class="brand">
                <div><img class="logo" src="/images/puspus_logo.png" alt=""></div>
                <div>
                    <div class="name">PUS-PUS BRITANICO</div>
                    <div class="sub">Dental Clinic</div>
                </div>
            </div>

            <nav class="nav">
                <div class="nav-section">Setup</div>
                <span class="nav-locked active"><i class="bi bi-shield-lock"></i> Set up your account</span>
            </nav>

            <div class="sidebar-footer">
                <form method="POST" action="{{ route('logout') }}" class="m-0">
                    @csrf
                    <button type="submit" class="sidebar-profile-badge" style="justify-content:flex-start;gap:.65rem;">
                        <i class="bi bi-box-arrow-right" style="font-size:1.05rem;"></i>
                        <span class="meta"><span class="name" style="color:#fff;">Log Out</span></span>
                    </button>
                </form>
            </div>
        </aside>

        <main>
            <div class="topbar">
                <div class="left">
                    <p class="page-title mb-0">Super Admin Setup</p>
                </div>
                <div class="right"></div>
            </div>

            <div class="content">
                <div class="page-head">
                    <div>
                        <h2>Set up your super admin account</h2>
                        <div class="crumbs">Finish setting up the permanent super administrator login.</div>
                    </div>
                </div>

                @include('partials.flash-toasts')

                <div class="row g-3" style="max-width:720px;">
                    <div class="col-12">

                        {{-- ===================== BOOTSTRAP ACCOUNT ===================== --}}
                        <div class="card-soft p-3 p-md-4">
                            <div class="section-label mb-3"><i class="bi bi-key me-1"></i> Setup account</div>
                            <p class="text-muted-2 small">
                                You're signed in with the temporary setup credentials. Finish the setup below to create
                                your permanent super admin login — after that, <strong>these credentials stop
                                    working</strong>.
                            </p>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Email address</label>
                                    <div class="input-icon"><i class="bi bi-envelope"></i><input type="text"
                                            class="form-control" value="{{ $bootstrapEmail }}" disabled></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Current password</label>
                                    <div class="input-icon"><i class="bi bi-key"></i><input type="text"
                                            class="form-control" value="{{ $bootstrapPassword }}" disabled></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Account role</label>
                                    <div class="input-icon"><i class="bi bi-shield-lock"></i><input type="text"
                                            class="form-control" value="{{ $roleLabel }}" disabled></div>
                                </div>
                            </div>
                        </div>

                        @unless ($showVerify)
                            {{-- ===================== STEP 1 — DETAILS ===================== --}}
                            <div class="card-soft p-3 p-md-4 mt-3">
                                <div class="section-label mb-3"><i class="bi bi-shield-check me-1"></i> Create your super
                                    admin login</div>
                                <p class="text-muted-2 small">
                                    Pick the email and password you'll use from now on. The email must not already belong
                                    to another account — we'll send a 6-digit code there to confirm it's yours. The
                                    password needs at least 8 characters with upper &amp; lower case, a number, and a
                                    symbol.
                                </p>

                                <form method="POST" action="{{ route('superAdminSetup.sendCode') }}">
                                    @csrf
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label class="form-label">Email address</label>
                                            <div class="input-icon @error('new_email') has-error @enderror"><i class="bi bi-envelope"></i><input type="email"
                                                    name="new_email" class="form-control"
                                                    value="{{ old('new_email', $pendingEmail) }}"
                                                    placeholder="you@example.com" required></div>
                                            @error('new_email') <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Password</label>
                                            <div class="pw-field">
                                                <input type="checkbox" class="pw-toggle-checkbox" id="pwSaNew">
                                                <div class="input-icon @error('password') has-error @enderror"><i class="bi bi-lock"></i><input type="text"
                                                        name="password" class="form-control pw-mask" placeholder="••••••••" required
                                                        minlength="8" value="{{ old('password') }}"
                                                        autocomplete="new-password"></div>
                                                <label for="pwSaNew" class="pw-eye-btn"
                                                    aria-label="Show or hide password"><i class="bi bi-eye"></i><i
                                                        class="bi bi-eye-slash"></i></label>
                                            </div>
                                            @error('password') <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Confirm password</label>
                                            <div class="pw-field">
                                                <input type="checkbox" class="pw-toggle-checkbox" id="pwSaConfirm">
                                                <div class="input-icon @error('password') has-error @enderror"><i class="bi bi-shield-lock"></i><input
                                                        type="text" name="password_confirmation"
                                                        class="form-control pw-mask" placeholder="••••••••" required
                                                        value="{{ old('password_confirmation') }}"
                                                        autocomplete="new-password"></div>
                                                <label for="pwSaConfirm" class="pw-eye-btn"
                                                    aria-label="Show or hide password"><i class="bi bi-eye"></i><i
                                                        class="bi bi-eye-slash"></i></label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-end mt-3">
                                        <button type="submit" class="btn btn-brand"><i
                                                class="bi bi-envelope-check me-1"></i>
                                            Send verification code</button>
                                    </div>
                                </form>
                            </div>
                        @else
                            {{-- ===================== STEP 2 — VERIFY EMAIL ===================== --}}
                            <div class="card-soft p-3 p-md-4 mt-3">
                                <div class="section-label mb-3"><i class="bi bi-envelope-check me-1"></i> Verify your
                                    email</div>
                                <p class="text-muted-2 small">
                                    We sent a 6-digit code to <strong>{{ $pendingEmail }}</strong>. Enter it below to
                                    finish setting up your super admin account. The code is valid for 5 minutes.
                                </p>

                                <form method="POST" action="{{ route('superAdminSetup.verify') }}">
                                    @csrf
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Verification code</label>
                                            <div class="input-icon {{ session('setup_code_error') ? 'has-error' : '' }}"><i class="bi bi-hash"></i><input type="text"
                                                    name="code" class="form-control text-center" maxlength="6"
                                                    inputmode="numeric" pattern="[0-9]*" placeholder="••••••" required
                                                    autocomplete="one-time-code" value="{{ old('code') }}"
                                                    style="letter-spacing:6px;font-size:1.15rem;"></div>
                                            @if (session('setup_code_error'))
                                                <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ session('setup_code_error') }}</div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-end mt-3">
                                        <button type="submit" class="btn btn-brand"><i
                                                class="bi bi-check2-circle me-1"></i>
                                            Verify &amp; activate account</button>
                                    </div>
                                </form>

                                <hr class="my-3">
                                <div class="d-flex flex-wrap gap-3 align-items-center">
                                    <form method="POST" action="{{ route('superAdminSetup.resendCode') }}" class="m-0">
                                        @csrf
                                        <span class="small text-muted-2">Didn't get the code?</span>
                                        <button type="submit" class="btn btn-link btn-sm p-0 ms-1" style="color:#0f7a23;"
                                            data-resend-cooldown="{{ $verifyRetrySeconds }}">Resend</button>
                                    </form>
                                    <a href="{{ route('superAdminSetup', ['edit' => 1]) }}"
                                        class="small text-muted-2">Use a different email</a>
                                </div>
                            </div>
                        @endunless

                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/otp-resend-cooldown.js') }}"></script>
</body>

</html>
