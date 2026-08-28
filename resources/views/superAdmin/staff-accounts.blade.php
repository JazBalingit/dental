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
                <a href="{{ route('staffAcc') }}" class="active"><i class="bi bi-people-fill"></i> Staff Accounts</a>
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
                        <h2>Staff Accounts</h2>
                        <div class="crumbs">Manage clinic staff and dentist logins.</div>
                    </div>
                    <div>
                        <button class="btn btn-brand" data-bs-toggle="modal" data-bs-target="#addModal"><i
                                class="bi bi-people-fill"></i> Add Staff</button>
                    </div>
                </div>

                @include('partials.flash-toasts')

                <div class="card-soft p-3 p-md-4">
                    <form method="GET" action="{{ route('staffAcc') }}" class="data-toolbar">
                        <div class="left">
                            <ul class="nav nav-pills" id="staffTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link {{ $tab !== 'archived' ? 'active' : '' }}" id="active-tab-btn"
                                        data-bs-toggle="pill" data-bs-target="#activePane" type="button" role="tab">
                                        Active
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link {{ $tab === 'archived' ? 'active' : '' }}" id="archived-tab-btn"
                                        data-bs-toggle="pill" data-bs-target="#archivedPane" type="button" role="tab">
                                        Archived
                                    </button>
                                </li>
                            </ul>
                        </div>
                        <div class="right">
                            <input type="hidden" name="tab" id="activeTabField" value="{{ $tab }}">
                            <div class="input-icon search">
                                <i class="bi bi-search"></i>
                                <input class="form-control" name="search" value="{{ $search }}"
                                    placeholder="Search by name or email..." style="height:40px; padding-left:2.4rem;" />
                            </div>
                        </div>
                    </form>

                    <div class="tab-content mt-3">
                        <div class="tab-pane fade {{ $tab !== 'archived' ? 'show active' : '' }}" id="activePane" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table-soft">
                                    <thead>
                                        <tr>
                                            <th>User</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>Role</th>
                                            <th>Date Created</th>
                                            <th>Status</th>
                                            <th>Verification</th>
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
                                                <td>{{ $si->PhoneNumber ?? '—' }}</td>
                                                <td>{{ $acc->Position }} <span class="pill pill-info">{{ ucfirst($acc->AccountRole) }}</span></td>
                                                <td>{{ \Carbon\Carbon::parse($acc->DateCreated)->format('M j, Y') }}</td>
                                                <td><span class="pill pill-success">Active</span></td>
                                                <td>
                                                    @if ($acc->EmailVerifiedAt)
                                                        <span class="pill pill-success"><i class="bi bi-patch-check-fill"></i> Verified</span>
                                                    @else
                                                        <span class="pill pill-warning"><i class="bi bi-exclamation-triangle"></i> Unverified</span>
                                                    @endif
                                                </td>
                                                <td class="text-end">
                                                    <button class="btn btn-pill btn-pill-edit me-1" data-bs-toggle="modal"
                                                        data-bs-target="#editUserModal{{ $acc->UserID }}"><i class="bi bi-pencil-square"></i>
                                                        Edit</button>
                                                    <form method="POST" action="{{ route('staffAcc.archive', $acc->UserID) }}" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-pill btn-pill-archive"><i class="bi bi-archive"></i>
                                                            Archive</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center text-muted-2 py-4">No staff accounts yet.</td>
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

                        <div class="tab-pane fade {{ $tab === 'archived' ? 'show active' : '' }}" id="archivedPane" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table-soft">
                                    <thead>
                                        <tr>
                                            <th>User</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>Role</th>
                                            <th>Date Created</th>
                                            <th>Status</th>
                                            <th>Verification</th>
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
                                                <td>{{ $si->PhoneNumber ?? '—' }}</td>
                                                <td>{{ $acc->Position }} <span class="pill pill-info">{{ ucfirst($acc->AccountRole) }}</span></td>
                                                <td>{{ \Carbon\Carbon::parse($acc->DateCreated)->format('M j, Y') }}</td>
                                                <td><span class="pill pill-muted">Frozen</span></td>
                                                <td>
                                                    @if ($acc->EmailVerifiedAt)
                                                        <span class="pill pill-success"><i class="bi bi-patch-check-fill"></i> Verified</span>
                                                    @else
                                                        <span class="pill pill-warning"><i class="bi bi-exclamation-triangle"></i> Unverified</span>
                                                    @endif
                                                </td>
                                                <td class="text-end">
                                                    <button class="btn btn-pill btn-pill-edit me-1" data-bs-toggle="modal"
                                                        data-bs-target="#editUserModal{{ $acc->UserID }}"><i class="bi bi-pencil-square"></i>
                                                        Edit</button>
                                                    <form method="POST" action="{{ route('staffAcc.unarchive', $acc->UserID) }}" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-pill btn-pill-archive"><i class="bi bi-archive"></i>
                                                            Unarchive</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center text-muted-2 py-4">No archived accounts.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="pagination-soft">
                                <div>Showing {{ $archivedStaff->count() }} of {{ $archivedStaff->total() }} entries</div>
                                <div class="pages">
                                    <a href="{{ $archivedStaff->previousPageUrl() ?? '#' }}"><i class="bi bi-chevron-left"></i></a>
                                    @for ($i = 1; $i <= $archivedStaff->lastPage(); $i++)
                                        <a href="{{ $archivedStaff->url($i) }}" class="{{ $archivedStaff->currentPage() === $i ? 'active' : '' }}">{{ $i }}</a>
                                    @endfor
                                    <a href="{{ $archivedStaff->nextPageUrl() ?? '#' }}"><i class="bi bi-chevron-right"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                    document.querySelectorAll('#staffTabs button').forEach(function (btn) {
                        btn.addEventListener('shown.bs.tab', function () {
                            document.getElementById('activeTabField').value = btn.id === 'archived-tab-btn' ? 'archived' : 'active';
                        });
                    });
                </script>
            </div>
        </main>
    </div>

    <!-- ===================== ADD USER MODAL ===================== -->
    @php $addFailed = $errors->any() && old('form_source') === 'add'; @endphp
    @if ($addFailed)
        <div class="modal-backdrop fade show"></div>
    @endif
    <div class="modal fade {{ $addFailed ? 'show' : '' }}" id="addModal" tabindex="-1" aria-labelledby="addModalLabel"
        aria-hidden="{{ $addFailed ? 'false' : 'true' }}" style="{{ $addFailed ? 'display:block;' : '' }}">
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
                    <input type="hidden" name="form_source" value="add">
                    <div class="modal-body pt-2">

                        @if ($addFailed)
                            <div class="alert alert-danger">
                                <ul class="mb-0 ps-3">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

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
                                        value="{{ old('last_name') }}" placeholder="Last name" required /></div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">First name</label>
                                <div class="input-icon"><i class="bi bi-person"></i><input type="text" name="first_name" class="form-control"
                                        value="{{ old('first_name') }}" placeholder="First name" required /></div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Middle name</label>
                                <div class="input-icon"><i class="bi bi-person"></i><input type="text" name="middle_name" class="form-control"
                                        value="{{ old('middle_name') }}" placeholder="Middle name" /></div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Birthdate</label>
                                <div class="input-icon"><i class="bi bi-calendar-event"></i><input type="date" name="birthdate"
                                        class="form-control" value="{{ old('birthdate') }}" required />
                                </div>
                                <div class="small text-muted-2 mt-1">Age is calculated automatically from the birthdate.</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Gender</label>
                                <div class="input-icon">
                                    <select class="form-select" name="gender" required>
                                        <option value="" disabled {{ old('gender') ? '' : 'selected' }}>Select gender</option>
                                        <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>Male</option>
                                        <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Female</option>
                                        <option value="other" {{ old('gender') === 'other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Religion</label>
                                <div class="input-icon"><i class="bi bi-book"></i><input class="form-control" name="religion"
                                        value="{{ old('religion') }}" placeholder="Catholic" />
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Nationality</label>
                                <div class="input-icon"><i class="bi bi-flag"></i><input class="form-control" name="nationality"
                                        value="{{ old('nationality') }}" placeholder="Filipino" required />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Role</label>
                                <div class="input-icon">
                                    <select class="form-select" name="role" required>
                                        <option value="Dentist" {{ old('role') === 'Dentist' ? 'selected' : '' }}>Dentist</option>
                                        <option value="Staff" {{ old('role', 'Staff') === 'Staff' ? 'selected' : '' }}>Staff</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Home address</label>
                                <div class="input-icon"><i class="bi bi-geo-alt"></i><input class="form-control" name="address"
                                        value="{{ old('address') }}" placeholder="Street, City, Province" required /></div>
                            </div>
                        </div>

                        <div class="section-label mt-2">Contact Details</div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Email address</label>
                                <div class="input-icon"><i class="bi bi-envelope"></i><input type="email" name="email"
                                        class="form-control" value="{{ old('email') }}" placeholder="you@clinic.com" required /></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Cell/Mobile number</label>
                                <div class="input-icon"><i class="bi bi-telephone"></i><input class="form-control" name="phone"
                                        value="{{ old('phone') }}" placeholder="+63 9XX XXX XXXX" required /></div>
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
        @php
            $si = $acc->staffInfo;
            $editFailed = $errors->any() && old('form_source') === 'edit_' . $acc->UserID;
            $ev = fn ($field, $default = '') => $editFailed ? old($field) : $default;
        @endphp
        @if ($editFailed)
            <div class="modal-backdrop fade show"></div>
        @endif
        <div class="modal fade {{ $editFailed ? 'show' : '' }}" id="editUserModal{{ $acc->UserID }}" tabindex="-1"
            aria-hidden="{{ $editFailed ? 'false' : 'true' }}" style="{{ $editFailed ? 'display:block;' : '' }}">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header border-0 pb-0">
                        <div>
                            <h5 class="modal-title fw-semibold">Edit User</h5>
                            <div class="small text-muted">Update account details</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST" action="{{ route('staffAcc.update', $acc->UserID) }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="form_source" value="edit_{{ $acc->UserID }}">
                        <div class="modal-body pt-2">

                            @if ($editFailed)
                                <div class="alert alert-danger">
                                    <ul class="mb-0 ps-3">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

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
                                            value="{{ $ev('last_name', $si->LastName ?? '') }}" required /></div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">First name</label>
                                    <div class="input-icon"><i class="bi bi-person"></i><input type="text" name="first_name" class="form-control"
                                            value="{{ $ev('first_name', $si->FirstName ?? '') }}" required /></div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Middle name</label>
                                    <div class="input-icon"><i class="bi bi-person"></i><input type="text" name="middle_name" class="form-control"
                                            value="{{ $ev('middle_name', $si->MiddleName ?? '') }}" /></div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Birthdate</label>
                                    <div class="input-icon"><i class="bi bi-calendar-event"></i><input type="date" name="birthdate"
                                            class="form-control" value="{{ $ev('birthdate', optional($si->DateOfBirth ?? null)->format('Y-m-d')) }}" required />
                                    </div>
                                    <div class="small text-muted-2 mt-1">Age is calculated automatically from the birthdate.</div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Gender</label>
                                    <div class="input-icon">
                                        <select class="form-select" name="gender" required>
                                            <option value="male" {{ $ev('gender', $si->Gender ?? '') === 'male' ? 'selected' : '' }}>Male</option>
                                            <option value="female" {{ $ev('gender', $si->Gender ?? '') === 'female' ? 'selected' : '' }}>Female</option>
                                            <option value="other" {{ $ev('gender', $si->Gender ?? '') === 'other' ? 'selected' : '' }}>Other</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Religion</label>
                                    <div class="input-icon"><i class="bi bi-book"></i><input class="form-control" name="religion"
                                            value="{{ $ev('religion', $si->Religion ?? '') }}" />
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Nationality</label>
                                    <div class="input-icon"><i class="bi bi-flag"></i><input class="form-control" name="nationality"
                                            value="{{ $ev('nationality', $si->Nationality ?? '') }}" required />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Role</label>
                                    <div class="input-icon">
                                        <select class="form-select" name="role" required>
                                            <option value="Dentist" {{ $ev('role', $acc->Position) === 'Dentist' ? 'selected' : '' }}>Dentist</option>
                                            <option value="Staff" {{ $ev('role', $acc->Position) === 'Staff' ? 'selected' : '' }}>Staff</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Home address</label>
                                    <div class="input-icon"><i class="bi bi-geo-alt"></i><input class="form-control" name="address"
                                            value="{{ $ev('address', $si->Address ?? '') }}" required /></div>
                                </div>
                            </div>

                            <div class="section-label mt-2">Contact Details</div>
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Email address</label>
                                    <div class="input-icon"><i class="bi bi-envelope"></i><input type="email" name="email"
                                            class="form-control" value="{{ $ev('email', $acc->Email) }}" required /></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Cell/Mobile number</label>
                                    <div class="input-icon"><i class="bi bi-telephone"></i><input class="form-control" name="phone"
                                            value="{{ $ev('phone', $si->PhoneNumber ?? '') }}" required /></div>
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
                                <div class="col-12">
                                    <button type="button" class="btn btn-pill btn-pill-edit" data-bs-dismiss="modal"
                                        data-bs-toggle="modal" data-bs-target="#changePasswordModal{{ $acc->UserID }}">
                                        <i class="bi bi-shield-lock"></i> Change Password
                                    </button>
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

    {{-- ===================== CHANGE PASSWORD MODAL PER STAFF MEMBER ===================== --}}
    {{-- Purely client-side (opened only by the button above) so Bootstrap
    fully owns the show/hide/backdrop lifecycle — unlike the other modals on
    this page, this one is never server-rendered as already-open, so there's
    no stale backdrop left behind for the close button to fight with. Any
    validation or "current password is incorrect" error surfaces via the
    page's usual flash-toast instead of an inline alert here. --}}
    @foreach ($staff->merge($archivedStaff) as $acc)
        <div class="modal fade" id="changePasswordModal{{ $acc->UserID }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header border-0 pb-0">
                        <div>
                            <h5 class="modal-title fw-semibold">Change Password</h5>
                            <div class="small text-muted">{{ trim(($acc->staffInfo->FirstName ?? '') . ' ' . ($acc->staffInfo->LastName ?? '')) ?: $acc->Email }}</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST" action="{{ route('staffAcc.password.update', $acc->UserID) }}">
                        <div class="modal-body pt-2">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Current password</label>
                                <div class="input-icon"><i class="bi bi-lock"></i><input type="password"
                                        name="current_password" class="form-control" required></div>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">New password</label>
                                    <div class="input-icon"><i class="bi bi-key"></i><input type="password" name="password"
                                            class="form-control" minlength="8" required></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Confirm new password</label>
                                    <div class="input-icon"><i class="bi bi-shield-lock"></i><input type="password"
                                            name="password_confirmation" class="form-control" required></div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-0 pt-0">
                            <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-brand">Update Password</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach

    @include('partials.admin-notif-modal')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>