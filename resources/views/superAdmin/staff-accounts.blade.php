<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Staff Accounts • Dental Clinic</title>
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
                <a href="{{ route('staffAcc') }}" class="active"><i class="bi bi-people-fill"></i> Staff Accounts</a>
                <a href="{{ route('userAcc') }}"><i class="bi bi-people-fill"></i> User Accounts</a>
                <a href="{{ route('dentistSchedule') }}"><i class="bi bi-calendar3"></i> Doctor Schedule</a>
                <a href="{{ route('walkIn') }}"><i class="bi bi-calendar3"></i> Walk-in Appointments</a>
                <a href="{{ route('appointmentApproval') }}"><i class="bi bi-clipboard2-check"></i> Appointment
                    Approval</a>
                <a href="{{ route('appointments') }}"><i class="bi bi-clipboard2-check"></i> Appointments</a>
                <a href="{{ route('patientRecords') }}"><i class="bi bi-folder2-open"></i> Patient Records</a>
                <div class="nav-section">System</div>
                <a href="{{ route('configuration') }}"><i class="bi bi-sliders2"></i> Configuration</a>
                <div class="divider"></div>
                <form method="POST" action="{{ route('logout') }}" class="m-0">
                    @csrf
                    <button type="submit" style="all:unset; cursor:pointer; display:flex; align-items:center; gap:8px; padding: 8px 12px; width: 100%;">
                        <i class="bi bi-box-arrow-right"></i> Log Out
                    </button>
                </form>
            </nav>
            <div class="footer">© PUS-PUS BRITANICO DENTAL CLINIC</div>
        </aside>

        <main>
            <div class="topbar">
                <div class="left">
                    <button class="toggle"><i class="bi bi-list"></i></button>
                </div>
                <div class="right">
                    <div class="user-chip">
                        <div><img class="avatar" src="/images/default.png" alt=""></div>
                        <div class="meta">
                            <div class="name"> Admin</div>
                            <div class="role">Administrator</div>
                        </div>
                        <i class="bi bi-chevron-down ms-1 text-muted-2"></i>
                    </div>
                </div>
            </div>

            <div class="content">
                <div class="page-head">
                    <div>
                        <h2>Staff Accounts</h2>
                        <div class="crumbs">Manage clinic staff and dentist logins.</div>
                    </div>
                    <div>
                        <button class="btn btn-brand" data-bs-toggle="modal" data-bs-target="#archivesModal"><i
                                class="bi bi-archive"></i> Archives</button>
                        <button class="btn btn-brand" data-bs-toggle="modal" data-bs-target="#addModal"><i
                                class="bi bi-people-fill"></i> Add Staff</button>
                    </div>
                </div>

                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
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

                <div class="card-soft p-3 p-md-4">
                    <form method="GET" action="{{ route('staffAcc') }}" class="data-toolbar">
                        <div class="left">
                            <span class="text-muted-2 small">Show</span>
                            <select class="form-select" style="width: 80px;">
                                <option>10</option>
                                <option>25</option>
                                <option>50</option>
                            </select>
                            <span class="text-muted-2 small">entries</span>
                        </div>
                        <div class="right">
                            <div class="input-icon search">
                                <i class="bi bi-search"></i>
                                <input class="form-control" name="search" value="{{ $search }}"
                                    placeholder="Search by name or email..." style="height:40px; padding-left:2.4rem;" />
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table-soft">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($staff as $acc)
                                    @php $si = $acc->staffInfo; @endphp
                                    <tr>
                                        <td><span><img class="avatar-initials" src="{{ $si->photo_url ?? asset('images/default.png') }}" alt=""></span><span
                                                class="fw-semibold">{{ $si->FirstName ?? '' }} {{ $si->LastName ?? '' }}</span></td>
                                        <td>{{ $acc->Email }}</td>
                                        <td>{{ $acc->Position }}</td>
                                        <td><span class="pill pill-success">Active</span></td>
                                        <td class="text-end">
                                            <button class="btn btn-pill btn-pill-edit me-1" data-bs-toggle="modal"
                                                data-bs-target="#editUserModal{{ $acc->StaffID }}"><i class="bi bi-pencil-square"></i>
                                                Edit</button>
                                            <form method="POST" action="{{ route('staffAcc.archive', $acc->StaffID) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-pill btn-pill-archive"><i class="bi bi-archive"></i>
                                                    Archive</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted-2 py-4">No staff accounts yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="pagination-soft">
                        <div>Showing {{ $staff->count() }} of {{ $staff->total() }} entries</div>
                        <div class="pages">
                            <a href="{{ $staff->previousPageUrl() ?? '#' }}"><i class="bi bi-chevron-left"></i></a>
                            @for ($i = 1; $i <= $staff->lastPage(); $i++)
                                <a href="{{ $staff->url($i) }}" class="{{ $staff->currentPage() === $i ? 'active' : '' }}">{{ $i }}</a>
                            @endfor
                            <a href="{{ $staff->nextPageUrl() ?? '#' }}"><i class="bi bi-chevron-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- ===================== ARCHIVES MODAL ===================== -->
    <div class="modal fade" id="archivesModal" tabindex="-1" aria-labelledby="modalWeek3Label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h5 class="modal-title fw-semibold" id="modalWeek3Label">Staff Accounts - Archives</h5>
                        <div class="small text-muted">View and manage archived (frozen) accounts</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-2">
                    <div class="table-responsive">
                        <table class="table-soft">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($archivedStaff as $acc)
                                    @php $si = $acc->staffInfo; @endphp
                                    <tr>
                                        <td><span><img class="avatar-initials" src="{{ $si->photo_url ?? asset('images/default.png') }}" alt=""></span><span
                                                class="fw-semibold">{{ $si->FirstName ?? '' }} {{ $si->LastName ?? '' }}</span></td>
                                        <td>{{ $acc->Email }}</td>
                                        <td>{{ $acc->Position }}</td>
                                        <td><span class="pill pill-muted">Frozen</span></td>
                                        <td class="text-end">
                                            <button class="btn btn-pill btn-pill-edit me-1" data-bs-toggle="modal"
                                                data-bs-target="#editUserModal{{ $acc->StaffID }}"><i class="bi bi-pencil-square"></i>
                                                Edit</button>
                                            <form method="POST" action="{{ route('staffAcc.unarchive', $acc->StaffID) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-pill btn-pill-archive"><i class="bi bi-archive"></i>
                                                    Unarchive</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted-2 py-4">No archived accounts.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ===================== ADD USER MODAL ===================== -->
    <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h5 class="modal-title fw-semibold" id="addModalLabel">Add User</h5>
                        <div class="small text-muted">Create a new account</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('staffAcc.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body pt-2">

                        <div class="d-flex align-items-center gap-3 mb-4">
                            <img class="avatar-initials" src="/images/default.png" alt="" style="width:64px;height:64px;">
                            <div>
                                <label class="btn btn-pill btn-pill-edit" style="cursor:pointer;"><i class="bi bi-camera"></i> Change
                                    Photo
                                    <input type="file" name="photo" accept=".jpg,.jpeg,.png" class="d-none">
                                </label>
                                <div class="small text-muted-2 mt-1">JPG or PNG, max 2MB.</div>
                            </div>
                        </div>

                        <div class="section-label">Personal Information</div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Last name</label>
                                <div class="input-icon"><i class="bi bi-person"></i><input type="text" name="last_name" class="form-control"
                                        placeholder="Last name" required /></div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">First name</label>
                                <div class="input-icon"><i class="bi bi-person"></i><input type="text" name="first_name" class="form-control"
                                        placeholder="First name" required /></div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Middle name</label>
                                <div class="input-icon"><i class="bi bi-person"></i><input type="text" name="middle_name" class="form-control"
                                        placeholder="Middle name" /></div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Birthdate</label>
                                <div class="input-icon"><i class="bi bi-calendar-event"></i><input type="date" name="birthdate"
                                        class="form-control" required />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Age</label>
                                <div class="input-icon"><i class="bi bi-calendar3"></i><input type="number" min="0" name="age"
                                        class="form-control" placeholder="14" /></div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Gender</label>
                                <div class="input-icon">
                                    <select class="form-select" name="gender" required>
                                        <option value="" disabled selected>Select gender</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Religion</label>
                                <div class="input-icon"><i class="bi bi-book"></i><input class="form-control" name="religion"
                                        placeholder="Catholic" />
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Nationality</label>
                                <div class="input-icon"><i class="bi bi-flag"></i><input class="form-control" name="nationality"
                                        placeholder="Filipino" required />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Role</label>
                                <div class="input-icon">
                                    <select class="form-select" name="role" required>
                                        <option value="Dentist">Dentist</option>
                                        <option value="Staff">Staff</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Home address</label>
                                <div class="input-icon"><i class="bi bi-geo-alt"></i><input class="form-control" name="address"
                                        placeholder="Street, City, Province" required /></div>
                            </div>
                        </div>

                        <div class="section-label mt-2">Contact Details</div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Email address</label>
                                <div class="input-icon"><i class="bi bi-envelope"></i><input type="email" name="email"
                                        class="form-control" placeholder="you@clinic.com" required /></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Cell/Mobile number</label>
                                <div class="input-icon"><i class="bi bi-telephone"></i><input class="form-control" name="phone"
                                        placeholder="+63 9XX XXX XXXX" required /></div>
                            </div>
                        </div>

                        <div class="section-label mt-2">Credentials</div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Password</label>
                                <div class="input-icon"><i class="bi bi-lock"></i><input type="password" name="password"
                                        class="form-control" placeholder="••••••••" required minlength="8" /></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Confirm password</label>
                                <div class="input-icon"><i class="bi bi-shield-lock"></i><input type="password"
                                        name="password_confirmation" class="form-control" placeholder="••••••••" required /></div>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-brand">Create User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ===================== ONE EDIT MODAL PER STAFF MEMBER (active + archived) ===================== --}}
    @foreach ($staff->merge($archivedStaff) as $acc)
        @php $si = $acc->staffInfo; @endphp
        <div class="modal fade" id="editUserModal{{ $acc->StaffID }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header border-0 pb-0">
                        <div>
                            <h5 class="modal-title fw-semibold">Edit User</h5>
                            <div class="small text-muted">Update account details</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST" action="{{ route('staffAcc.update', $acc->StaffID) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body pt-2">

                            <div class="d-flex align-items-center gap-3 mb-4">
                                <img class="avatar-initials" src="{{ $si->photo_url ?? asset('images/default.png') }}" alt="" style="width:64px;height:64px;">
                                <div>
                                    <label class="btn btn-pill btn-pill-edit" style="cursor:pointer;"><i class="bi bi-camera"></i> Change
                                        Photo
                                        <input type="file" name="photo" accept=".jpg,.jpeg,.png" class="d-none">
                                    </label>
                                    <div class="small text-muted-2 mt-1">JPG or PNG, max 2MB.</div>
                                </div>
                            </div>

                            <div class="section-label">Personal Information</div>
                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Last name</label>
                                    <div class="input-icon"><i class="bi bi-person"></i><input type="text" name="last_name" class="form-control"
                                            value="{{ $si->LastName ?? '' }}" required /></div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">First name</label>
                                    <div class="input-icon"><i class="bi bi-person"></i><input type="text" name="first_name" class="form-control"
                                            value="{{ $si->FirstName ?? '' }}" required /></div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Middle name</label>
                                    <div class="input-icon"><i class="bi bi-person"></i><input type="text" name="middle_name" class="form-control"
                                            value="{{ $si->MiddleName ?? '' }}" /></div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Birthdate</label>
                                    <div class="input-icon"><i class="bi bi-calendar-event"></i><input type="date" name="birthdate"
                                            class="form-control" value="{{ optional($si->DateOfBirth ?? null)->format('Y-m-d') }}" required />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Age</label>
                                    <div class="input-icon"><i class="bi bi-calendar3"></i><input type="number" min="0" name="age"
                                            class="form-control" value="{{ $si->Age ?? '' }}" /></div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Gender</label>
                                    <div class="input-icon">
                                        <select class="form-select" name="gender" required>
                                            <option value="male" {{ ($si->Gender ?? '') === 'male' ? 'selected' : '' }}>Male</option>
                                            <option value="female" {{ ($si->Gender ?? '') === 'female' ? 'selected' : '' }}>Female</option>
                                            <option value="other" {{ ($si->Gender ?? '') === 'other' ? 'selected' : '' }}>Other</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Religion</label>
                                    <div class="input-icon"><i class="bi bi-book"></i><input class="form-control" name="religion"
                                            value="{{ $si->Religion ?? '' }}" />
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Nationality</label>
                                    <div class="input-icon"><i class="bi bi-flag"></i><input class="form-control" name="nationality"
                                            value="{{ $si->Nationality ?? '' }}" required />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Role</label>
                                    <div class="input-icon">
                                        <select class="form-select" name="role" required>
                                            <option value="Dentist" {{ $acc->Position === 'Dentist' ? 'selected' : '' }}>Dentist</option>
                                            <option value="Staff" {{ $acc->Position === 'Staff' ? 'selected' : '' }}>Staff</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Home address</label>
                                    <div class="input-icon"><i class="bi bi-geo-alt"></i><input class="form-control" name="address"
                                            value="{{ $si->Address ?? '' }}" required /></div>
                                </div>
                            </div>

                            <div class="section-label mt-2">Contact Details</div>
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Email address</label>
                                    <div class="input-icon"><i class="bi bi-envelope"></i><input type="email" name="email"
                                            class="form-control" value="{{ $acc->Email }}" required /></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Cell/Mobile number</label>
                                    <div class="input-icon"><i class="bi bi-telephone"></i><input class="form-control" name="phone"
                                            value="{{ $si->PhoneNumber ?? '' }}" required /></div>
                                </div>
                            </div>

                            <div class="section-label mt-2">Account Details</div>
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Date created</label>
                                    <div class="input-icon"><i class="bi bi-clock-history"></i><input type="text"
                                            class="form-control" value="{{ \Carbon\Carbon::parse($acc->DateCreated)->format('M j, Y g:i A') }}" disabled></div>
                                </div>
                            </div>

                            <div class="section-label mt-2">Credentials</div>
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">New password <span class="text-muted">(optional)</span></label>
                                    <div class="input-icon"><i class="bi bi-lock"></i><input type="password" name="password"
                                            class="form-control" placeholder="Leave blank to keep current" minlength="8" /></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Confirm new password</label>
                                    <div class="input-icon"><i class="bi bi-shield-lock"></i><input type="password"
                                            name="password_confirmation" class="form-control" placeholder="••••••••" /></div>
                                </div>
                            </div>

                        </div>
                        <div class="modal-footer border-0 pt-0">
                            <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-brand">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>