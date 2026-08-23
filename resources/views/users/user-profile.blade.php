<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile — Pus-Pus Britanico</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="/css/user_appointments.css">
</head>

<body>

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top mask-custom shadow-sm">
        <div class="container">
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
                            @if (session('user_email'))
                                <div class="dropdown">
                                    <button
                                        class="nav-link navh d-flex align-items-center gap-2 border-0 bg-transparent dropdown-toggle"
                                        type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-person-circle"></i>
                                        <span>{{ session('user_email') }}</span>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                        <li>
                                            <a class="dropdown-item small" href="{{ route('userProfile') }}">
                                                <i class="bi bi-person me-2"></i>User Profile
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
            <h1><i class="fas fa-user me-2" style="opacity:0.8"></i>My Profile</h1>
            <p>View and manage your personal information</p>
        </div>
    </div>

    <!-- SUB-NAV -->
    <div class="subnav">
        <div class="container px-4">
            <div class="subnav-inner">
                <a href="{{ route('userProfile') }}" class="subnav-link active"><i class="fas fa-user"></i> Personal
                    Info</a>
                <a href="{{ route('userAppointment') }}" class="subnav-link"><i class="fas fa-calendar-check"></i>
                    Appointments</a>
                <a href="{{ route('settings') }}" class="subnav-link"><i class="fas fa-lock"></i> Change
                    Password</a>
            </div>
        </div>
    </div>

    <!-- CONTENT -->
    <div class="content-wrap">

        @include('partials.flash-toasts', ['topOffset' => '100px'])

        <form method="POST" action="{{ route('userProfile.update') }}" enctype="multipart/form-data">
            @csrf

            <!-- Photo + locked account info -->
            <div class="section-card">
                <div class="card-hd">
                    <div class="card-hd-left">
                        <div class="card-hd-icon"><i class="fas fa-id-card"></i></div>
                        <div>
                            <h4>Account Information</h4>
                            <p>Your photo and login details</p>
                        </div>
                    </div>
                </div>
                <div class="appt-body align-items-center">
                    <div class="text-center">
                        <img src="{{ $patientInfo->photo_url }}" alt="Profile photo"
                             style="width:110px;height:110px;border-radius:50%;object-fit:cover;border:3px solid #e9f7ea;">
                        <div class="mt-2">
                            <label class="btn-outline" style="cursor:pointer;display:inline-block;">
                                <i class="fas fa-camera"></i> Change Photo
                                <input type="file" name="photo" accept=".jpg,.jpeg,.png" class="d-none">
                            </label>
                        </div>
                        <div class="small text-muted mt-1">JPG or PNG, max 2MB</div>
                    </div>

                    <div class="appt-detail">
                        <div class="mb-2">
                            <label class="ml">Email address</label>
                            <input type="text" class="mi" value="{{ $user->Email }}" disabled>
                            <div class="small text-muted mt-1">Email can't be changed here. Contact the clinic if you need to update it.</div>
                        </div>
                        <div class="mb-2">
                            <label class="ml">Member since</label>
                            <input type="text" class="mi" value="{{ \Carbon\Carbon::parse($user->DateCreated)->format('F j, Y') }}" disabled>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Editable patient information -->
            <div class="section-card">
                <div class="card-hd">
                    <div class="card-hd-left">
                        <div class="card-hd-icon"><i class="fas fa-user-edit"></i></div>
                        <div>
                            <h4>Personal Information</h4>
                            <p>Everything here can be edited</p>
                        </div>
                    </div>
                </div>
                <div class="p-4">
                    <div class="row g-3 mb-2">
                        <div class="col-md-4">
                            <label class="ml">Last name</label>
                            <input class="mi" name="last_name" value="{{ old('last_name', $patientInfo->LastName) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="ml">First name</label>
                            <input class="mi" name="first_name" value="{{ old('first_name', $patientInfo->FirstName) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="ml">Middle name</label>
                            <input class="mi" name="middle_name" value="{{ old('middle_name', $patientInfo->MiddleName) }}">
                        </div>

                        <div class="col-md-6">
                            <label class="ml">Birthdate</label>
                            <input type="date" class="mi" name="birthdate"
                                   value="{{ old('birthdate', optional($patientInfo->DateOfBirth)->format('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="ml">Gender</label>
                            <select class="mi" name="gender" required>
                                <option value="male" {{ old('gender', $patientInfo->Gender) === 'male' ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ old('gender', $patientInfo->Gender) === 'female' ? 'selected' : '' }}>Female</option>
                                <option value="other" {{ old('gender', $patientInfo->Gender) === 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="ml">Religion</label>
                            <input class="mi" name="religion" value="{{ old('religion', $patientInfo->Religion) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="ml">Nationality</label>
                            <input class="mi" name="nationality" value="{{ old('nationality', $patientInfo->Nationality) }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="ml">Occupation</label>
                            <input class="mi" name="occupation" value="{{ old('occupation', $patientInfo->Occupation) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="ml">Cell/Mobile number</label>
                            <input class="mi" name="phone" value="{{ old('phone', $patientInfo->PhoneNumber) }}" required>
                        </div>

                        <div class="col-12">
                            <label class="ml">Home address</label>
                            <input class="mi" name="address" value="{{ old('address', $patientInfo->Address) }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="ml">Parent/Guardian's name</label>
                            <input class="mi" name="guardian_name" value="{{ old('guardian_name', $patientInfo->ParentsName) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="ml">Parent/Guardian's occupation</label>
                            <input class="mi" name="guardian_occupation" value="{{ old('guardian_occupation', $patientInfo->ParentsOccupation) }}">
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-3">
                        <button type="submit" class="btn-prim"><i class="fas fa-save me-1"></i> Save Changes</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- FOOTER -->
    <footer id="contact">
        <div class="container">
            <div class="row mb-5">
                <div class="col-lg-6 col-md-12 mb-4 mb-md-0">
                    <h5 class="text-uppercase fw-bold">PUS-PUS BRITANICO DENTAL CLINIC</h5>
                    <p>
                        Providing quality dental care with compassion and professionalism. Our clinic is dedicated to
                        ensuring every patient receives personalized treatment in a comfortable and welcoming
                        environment. Your smile is our priority.
                    </p>
                </div>
                <div class="col-md-6 col-lg-3 offset-lg-3">
                    <h6 class="text-uppercase fw-bold mb-4">Contact Information</h6>
                    <p><i class="fas fa-map-marker-alt me-3"></i> #50 Mainroad Ave. B21 L31 Phase 1 Pacita Complex 2 San
                        Pedro, Laguna</p>
                    <p><i class="fas fa-phone me-3"></i>(02)84045642</p>
                    <p><i class="fa-solid fa-mobile me-3"></i>+63 968-476-5943</p>
                </div>
            </div>
            <hr style="border-color: rgba(255, 255, 255, 0.934); margin: 30px 0;">
            <div class="text-center">
                <p style="color: rgba(255, 255, 255, 0.8); margin: 0;">&copy; 2026 Pus-Pus Britanico Dental Clinic. All
                    rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
