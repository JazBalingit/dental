<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>User Accounts • Dental Clinic</title>
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
                <a href="{{ route('userAcc') }}" class="active"><i class="bi bi-people-fill"></i> User Accounts</a>
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
                    @include('partials.admin-notif-dropdown')
                    <div class="dropdown">
                        <button class="user-chip" type="button" data-bs-toggle="dropdown" aria-expanded="false"
                            style="all:unset; cursor:pointer; display:flex; align-items:center; gap:.6rem; padding:.35rem .8rem .35rem .35rem; border-radius:999px; background:var(--brand-50); border:1px solid var(--brand-100); font-family:inherit;">
                            <div><img class="avatar" src="/images/default.png" alt=""></div>
                            <div class="meta">
                                <div class="name">{{ session('user_email', 'Admin') }}</div>
                                <div class="role">{{ session('account_type') === 'staff' ? 'Staff' : 'Administrator' }}</div>
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
                        <h2>User Accounts</h2>
                        <div class="crumbs">View, edit, and manage patient accounts.</div>
                    </div>
                </div>

                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <div class="card-soft p-3 p-md-4">
                    <form method="GET" action="{{ route('userAcc') }}" class="data-toolbar">
                        <div class="left">
                            <ul class="nav nav-pills" id="userTabs" role="tablist">
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
                                            <th>Date Created</th>
                                            <th>Status</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($users as $acc)
                                            @php $pi = $acc->patientInfo; @endphp
                                            <tr>
                                                <td><span><img class="avatar-initials" src="{{ $pi->photo_url ?? asset('images/default.png') }}" alt=""></span><span
                                                        class="fw-semibold">{{ $pi->FirstName ?? '' }} {{ $pi->LastName ?? '' }}</span></td>
                                                <td>{{ $acc->Email }}</td>
                                                <td>{{ $pi->PhoneNumber ?? '—' }}</td>
                                                <td>{{ \Carbon\Carbon::parse($acc->DateCreated)->format('M j, Y') }}</td>
                                                <td><span class="pill pill-success">Active</span></td>
                                                <td class="text-end">
                                                    <button class="btn btn-pill btn-pill-edit me-1" data-bs-toggle="modal"
                                                        data-bs-target="#editUserModal{{ $acc->UserID }}"><i class="bi bi-pencil-square"></i>
                                                        Edit</button>
                                                    <form method="POST" action="{{ route('userAcc.archive', $acc->UserID) }}" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-pill btn-pill-archive"><i class="bi bi-archive"></i>
                                                            Archive</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted-2 py-4">No user accounts yet.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="pagination-soft">
                                <div>Showing {{ $users->count() }} of {{ $users->total() }} entries</div>
                                <div class="pages">
                                    <a href="{{ $users->previousPageUrl() ?? '#' }}"><i class="bi bi-chevron-left"></i></a>
                                    @for ($i = 1; $i <= $users->lastPage(); $i++)
                                        <a href="{{ $users->url($i) }}" class="{{ $users->currentPage() === $i ? 'active' : '' }}">{{ $i }}</a>
                                    @endfor
                                    <a href="{{ $users->nextPageUrl() ?? '#' }}"><i class="bi bi-chevron-right"></i></a>
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
                                            <th>Date Created</th>
                                            <th>Status</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($archivedUsers as $acc)
                                            @php $pi = $acc->patientInfo; @endphp
                                            <tr>
                                                <td><span><img class="avatar-initials" src="{{ $pi->photo_url ?? asset('images/default.png') }}" alt=""></span><span
                                                        class="fw-semibold">{{ $pi->FirstName ?? '' }} {{ $pi->LastName ?? '' }}</span></td>
                                                <td>{{ $acc->Email }}</td>
                                                <td>{{ $pi->PhoneNumber ?? '—' }}</td>
                                                <td>{{ \Carbon\Carbon::parse($acc->DateCreated)->format('M j, Y') }}</td>
                                                <td><span class="pill pill-muted">Frozen</span></td>
                                                <td class="text-end">
                                                    <button class="btn btn-pill btn-pill-edit me-1" data-bs-toggle="modal"
                                                        data-bs-target="#editUserModal{{ $acc->UserID }}"><i class="bi bi-pencil-square"></i>
                                                        Edit</button>
                                                    <form method="POST" action="{{ route('userAcc.unarchive', $acc->UserID) }}" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-pill btn-pill-archive"><i class="bi bi-archive"></i>
                                                            Unarchive</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted-2 py-4">No archived accounts.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="pagination-soft">
                                <div>Showing {{ $archivedUsers->count() }} of {{ $archivedUsers->total() }} entries</div>
                                <div class="pages">
                                    <a href="{{ $archivedUsers->previousPageUrl() ?? '#' }}"><i class="bi bi-chevron-left"></i></a>
                                    @for ($i = 1; $i <= $archivedUsers->lastPage(); $i++)
                                        <a href="{{ $archivedUsers->url($i) }}" class="{{ $archivedUsers->currentPage() === $i ? 'active' : '' }}">{{ $i }}</a>
                                    @endfor
                                    <a href="{{ $archivedUsers->nextPageUrl() ?? '#' }}"><i class="bi bi-chevron-right"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                    document.querySelectorAll('#userTabs button').forEach(function (btn) {
                        btn.addEventListener('shown.bs.tab', function () {
                            document.getElementById('activeTabField').value = btn.id === 'archived-tab-btn' ? 'archived' : 'active';
                        });
                    });
                </script>
            </div>
        </main>
    </div>

    {{-- ===================== ONE EDIT MODAL PER USER (active + archived) ===================== --}}
    @foreach ($users->merge($archivedUsers) as $acc)
        @php
            $pi = $acc->patientInfo;
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
                    <form method="POST" action="{{ route('userAcc.update', $acc->UserID) }}" enctype="multipart/form-data">
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
                                <img class="avatar-initials" src="{{ $pi->photo_url ?? asset('images/default.png') }}" alt="" style="width:64px;height:64px;">
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
                                            value="{{ $ev('last_name', $pi->LastName ?? '') }}" required /></div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">First name</label>
                                    <div class="input-icon"><i class="bi bi-person"></i><input type="text" name="first_name" class="form-control"
                                            value="{{ $ev('first_name', $pi->FirstName ?? '') }}" required /></div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Middle name</label>
                                    <div class="input-icon"><i class="bi bi-person"></i><input type="text" name="middle_name" class="form-control"
                                            value="{{ $ev('middle_name', $pi->MiddleName ?? '') }}" /></div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Birthdate</label>
                                    <div class="input-icon"><i class="bi bi-calendar-event"></i><input type="date" name="birthdate"
                                            class="form-control" value="{{ $ev('birthdate', optional($pi->DateOfBirth ?? null)->format('Y-m-d')) }}" required />
                                    </div>
                                    <div class="small text-muted-2 mt-1">Age is calculated automatically from the birthdate.</div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Gender</label>
                                    <div class="input-icon">
                                        <select class="form-select" name="gender" required>
                                            <option value="male" {{ $ev('gender', $pi->Gender ?? '') === 'male' ? 'selected' : '' }}>Male</option>
                                            <option value="female" {{ $ev('gender', $pi->Gender ?? '') === 'female' ? 'selected' : '' }}>Female</option>
                                            <option value="other" {{ $ev('gender', $pi->Gender ?? '') === 'other' ? 'selected' : '' }}>Other</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Religion</label>
                                    <div class="input-icon"><i class="bi bi-book"></i><input class="form-control" name="religion"
                                            value="{{ $ev('religion', $pi->Religion ?? '') }}" />
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Nationality</label>
                                    <div class="input-icon"><i class="bi bi-flag"></i><input class="form-control" name="nationality"
                                            value="{{ $ev('nationality', $pi->Nationality ?? '') }}" required />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Occupation</label>
                                    <div class="input-icon"><i class="bi bi-briefcase"></i><input class="form-control" name="occupation"
                                            value="{{ $ev('occupation', $pi->Occupation ?? '') }}" />
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Home address</label>
                                    <div class="input-icon"><i class="bi bi-geo-alt"></i><input class="form-control" name="address"
                                            value="{{ $ev('address', $pi->Address ?? '') }}" required /></div>
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
                                            value="{{ $ev('phone', $pi->PhoneNumber ?? '') }}" required /></div>
                                </div>
                            </div>

                            <div class="section-label mt-2">Parent / Guardian (for minors)</div>
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Guardian's name</label>
                                    <div class="input-icon"><i class="bi bi-person-heart"></i><input class="form-control" name="guardian_name"
                                            value="{{ $ev('guardian_name', $pi->ParentsName ?? '') }}" />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Guardian's occupation</label>
                                    <div class="input-icon"><i class="bi bi-briefcase"></i><input class="form-control" name="guardian_occupation"
                                            value="{{ $ev('guardian_occupation', $pi->ParentsOccupation ?? '') }}" />
                                    </div>
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

    @include('partials.admin-notif-modal')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
