<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
  <link rel="icon" type="image/png" href="/images/puspus_logo.png">
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
    <link rel="stylesheet" href="{{ asset('css/mobile.css') }}">
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
            @include('partials.admin-sidebar-nav', ['active' => 'staffAcc'])
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
                                                    <button type="button" class="btn btn-pill btn-pill-archive" data-bs-toggle="modal"
                                                        data-bs-target="#confirmActionModal"
                                                        data-action-url="{{ route('staffAcc.archive', $acc->UserID) }}"
                                                        data-title="Archive Staff Account"
                                                        data-message="Archive {{ trim(($si->FirstName ?? '') . ' ' . ($si->LastName ?? '')) ?: $acc->Email }}? They won't be able to log in until you unarchive their account."
                                                        data-confirm-label="Archive" data-confirm-class="btn-pill-archive">
                                                        <i class="bi bi-archive"></i> Archive</button>
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
                                    @include('partials.pagination-pages', ['paginator' => $staff])
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
                                                    <button type="button" class="btn btn-pill btn-pill-archive" data-bs-toggle="modal"
                                                        data-bs-target="#confirmActionModal"
                                                        data-action-url="{{ route('staffAcc.unarchive', $acc->UserID) }}"
                                                        data-title="Unarchive Staff Account"
                                                        data-message="Restore {{ trim(($si->FirstName ?? '') . ' ' . ($si->LastName ?? '')) ?: $acc->Email }}? They'll be able to log in again."
                                                        data-confirm-label="Unarchive" data-confirm-class="btn-pill-archive">
                                                        <i class="bi bi-archive"></i> Unarchive</button>
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
                                    @include('partials.pagination-pages', ['paginator' => $archivedStaff])
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
    <div class="modal fade {{ $addFailed ? 'show' : '' }}" id="addModal" tabindex="-1" aria-labelledby="addModalLabel"
        aria-hidden="{{ $addFailed ? 'false' : 'true' }}" style="{{ $addFailed ? 'display:block;' : '' }}">
        <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
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
                                <div class="small text-muted-2 mt-1">JPG or PNG, max 5MB.</div>
                            </div>
                        </div>

                        <div class="section-label">Personal Information</div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Last name</label>
                                <div class="input-icon @error('last_name') has-error @enderror"><i class="bi bi-person"></i><input type="text" name="last_name" class="form-control"
                                        value="{{ old('last_name') }}" placeholder="Last name" required /></div>
                                @error('last_name') <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">First name</label>
                                <div class="input-icon @error('first_name') has-error @enderror"><i class="bi bi-person"></i><input type="text" name="first_name" class="form-control"
                                        value="{{ old('first_name') }}" placeholder="First name" required /></div>
                                @error('first_name') <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Middle name</label>
                                <div class="input-icon @error('middle_name') has-error @enderror"><i class="bi bi-person"></i><input type="text" name="middle_name" class="form-control"
                                        value="{{ old('middle_name') }}" placeholder="Middle name" /></div>
                                @error('middle_name') <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Birthdate</label>
                                <div class="input-icon @error('birthdate') has-error @enderror"><i class="bi bi-calendar-event"></i><input type="date" name="birthdate"
                                        class="form-control" value="{{ old('birthdate') }}" max="{{ now()->subYears(18)->year }}-12-31" required
                                        data-age-target="#staffAgeNew" />
                                </div>
                                @error('birthdate')
                                    <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div>
                                @else
                                    <div class="small text-muted-2 mt-1">Must be at least 18 years old.</div>
                                @enderror
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Age</label>
                                <div class="input-icon"><i class="bi bi-person-vcard"></i><input type="text" class="form-control" id="staffAgeNew" placeholder="—" disabled></div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Gender</label>
                                <div class="input-icon @error('gender') has-error @enderror">
                                    <select class="form-select" name="gender" required>
                                        <option value="" disabled {{ old('gender') ? '' : 'selected' }}>Select gender</option>
                                        <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>Male</option>
                                        <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Female</option>
                                        <option value="other" {{ old('gender') === 'other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                </div>
                                @error('gender') <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Religion</label>
                                <div class="input-icon @error('religion') has-error @enderror"><i class="bi bi-book"></i><input class="form-control" name="religion"
                                        value="{{ old('religion') }}" placeholder="Catholic" />
                                </div>
                                @error('religion') <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Nationality</label>
                                <div class="input-icon @error('nationality') has-error @enderror"><i class="bi bi-flag"></i><input class="form-control" name="nationality"
                                        value="{{ old('nationality') }}" placeholder="Filipino" required />
                                </div>
                                @error('nationality') <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div> @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">Role</label>
                                <div class="input-icon @error('role') has-error @enderror">
                                    <select class="form-select" name="role" required>
                                        <option value="Dentist" {{ old('role') === 'Dentist' ? 'selected' : '' }}>Dentist</option>
                                        <option value="Staff" {{ old('role', 'Staff') === 'Staff' ? 'selected' : '' }}>Staff</option>
                                    </select>
                                </div>
                                @error('role') <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="section-label mt-2">Home Address</div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Street / House No.</label>
                                <div class="input-icon"><i class="bi bi-signpost-2"></i><input class="form-control addr-part" name="addr_street"
                                        value="{{ old('addr_street') }}" placeholder="123 Sample St." /></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Barangay</label>
                                <div class="input-icon"><i class="bi bi-geo"></i><input class="form-control addr-part" name="addr_barangay"
                                        value="{{ old('addr_barangay') }}" placeholder="Barangay" /></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">City / Municipality</label>
                                <div class="input-icon"><i class="bi bi-buildings"></i><input class="form-control addr-part" name="addr_city"
                                        value="{{ old('addr_city') }}" placeholder="City / Municipality" required /></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Province</label>
                                <div class="input-icon"><i class="bi bi-map"></i><input class="form-control addr-part" name="addr_province"
                                        value="{{ old('addr_province') }}" placeholder="Province" required /></div>
                            </div>
                            <input type="hidden" name="address" class="@error('address') has-error @enderror" value="{{ old('address') }}">
                            @error('address') <div class="col-12"><div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div></div> @enderror
                        </div>

                        <div class="section-label mt-2">Contact Details</div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Email address</label>
                                <div class="input-icon @error('email') has-error @enderror"><i class="bi bi-envelope"></i><input type="email" name="email"
                                        class="form-control" value="{{ old('email') }}" placeholder="you@clinic.com" required /></div>
                                @error('email') <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Cell/Mobile number</label>
                                <div class="input-icon @error('phone') has-error @enderror"><i class="bi bi-telephone"></i><input class="form-control" name="phone"
                                        value="{{ old('phone') }}" placeholder="+63 9XX XXX XXXX" required /></div>
                                @error('phone') <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="section-label mt-2">Credentials</div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Password</label>
                                <div class="pw-field">
                                    <input type="checkbox" class="pw-toggle-checkbox" id="pwAddNew">
                                    <div class="input-icon @error('password') has-error @enderror"><i class="bi bi-lock"></i><input type="text" name="password"
                                            class="form-control pw-mask" placeholder="••••••••" required minlength="8"
                                            value="{{ $addFailed ? old('password') : '' }}" autocomplete="new-password" /></div>
                                    <label for="pwAddNew" class="pw-eye-btn" aria-label="Show or hide password"><i class="bi bi-eye"></i><i class="bi bi-eye-slash"></i></label>
                                </div>
                                @error('password') <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Confirm password</label>
                                <div class="pw-field">
                                    <input type="checkbox" class="pw-toggle-checkbox" id="pwAddConfirm">
                                    <div class="input-icon @error('password') has-error @enderror"><i class="bi bi-shield-lock"></i><input type="text"
                                            name="password_confirmation" class="form-control pw-mask" placeholder="••••••••" required
                                            value="{{ $addFailed ? old('password_confirmation') : '' }}" autocomplete="new-password" /></div>
                                    <label for="pwAddConfirm" class="pw-eye-btn" aria-label="Show or hide password"><i class="bi bi-eye"></i><i class="bi bi-eye-slash"></i></label>
                                </div>
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
            $eErr = fn ($field) => $editFailed && $errors->has($field) ? 'has-error' : '';
            $eMsg = fn ($field) => $editFailed && $errors->has($field) ? $errors->first($field) : null;
            $addrParts = array_pad(array_map('trim', explode(',', $si->Address ?? '', 4)), 4, '');
        @endphp
        <div class="modal fade {{ $editFailed ? 'show' : '' }}" id="editUserModal{{ $acc->UserID }}" tabindex="-1"
            aria-hidden="{{ $editFailed ? 'false' : 'true' }}" style="{{ $editFailed ? 'display:block;' : '' }}">
            <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
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
                                    <div class="small text-muted-2 mt-1">JPG or PNG, max 5MB.</div>
                                </div>
                            </div>

                            <div class="section-label">Personal Information</div>
                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Last name</label>
                                    <div class="input-icon {{ $eErr('last_name') }}"><i class="bi bi-person"></i><input type="text" name="last_name" class="form-control"
                                            value="{{ $ev('last_name', $si->LastName ?? '') }}" required /></div>
                                    @if ($eMsg('last_name')) <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $eMsg('last_name') }}</div> @endif
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">First name</label>
                                    <div class="input-icon {{ $eErr('first_name') }}"><i class="bi bi-person"></i><input type="text" name="first_name" class="form-control"
                                            value="{{ $ev('first_name', $si->FirstName ?? '') }}" required /></div>
                                    @if ($eMsg('first_name')) <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $eMsg('first_name') }}</div> @endif
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Middle name</label>
                                    <div class="input-icon {{ $eErr('middle_name') }}"><i class="bi bi-person"></i><input type="text" name="middle_name" class="form-control"
                                            value="{{ $ev('middle_name', $si->MiddleName ?? '') }}" /></div>
                                    @if ($eMsg('middle_name')) <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $eMsg('middle_name') }}</div> @endif
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Birthdate</label>
                                    <div class="input-icon {{ $eErr('birthdate') }}"><i class="bi bi-calendar-event"></i><input type="date" name="birthdate"
                                            class="form-control" value="{{ $ev('birthdate', optional($si->DateOfBirth ?? null)->format('Y-m-d')) }}" max="{{ now()->subYears(18)->year }}-12-31" required
                                            data-age-target="#staffAge{{ $acc->UserID }}" />
                                    </div>
                                    @if ($eMsg('birthdate'))
                                        <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $eMsg('birthdate') }}</div>
                                    @else
                                        <div class="small text-muted-2 mt-1">Must be at least 18 years old.</div>
                                    @endif
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Age</label>
                                    <div class="input-icon"><i class="bi bi-person-vcard"></i><input type="text" class="form-control" id="staffAge{{ $acc->UserID }}" placeholder="—" disabled></div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Gender</label>
                                    <div class="input-icon {{ $eErr('gender') }}">
                                        <select class="form-select" name="gender" required>
                                            <option value="male" {{ $ev('gender', $si->Gender ?? '') === 'male' ? 'selected' : '' }}>Male</option>
                                            <option value="female" {{ $ev('gender', $si->Gender ?? '') === 'female' ? 'selected' : '' }}>Female</option>
                                            <option value="other" {{ $ev('gender', $si->Gender ?? '') === 'other' ? 'selected' : '' }}>Other</option>
                                        </select>
                                    </div>
                                    @if ($eMsg('gender')) <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $eMsg('gender') }}</div> @endif
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Religion</label>
                                    <div class="input-icon {{ $eErr('religion') }}"><i class="bi bi-book"></i><input class="form-control" name="religion"
                                            value="{{ $ev('religion', $si->Religion ?? '') }}" />
                                    </div>
                                    @if ($eMsg('religion')) <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $eMsg('religion') }}</div> @endif
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Nationality</label>
                                    <div class="input-icon {{ $eErr('nationality') }}"><i class="bi bi-flag"></i><input class="form-control" name="nationality"
                                            value="{{ $ev('nationality', $si->Nationality ?? '') }}" required />
                                    </div>
                                    @if ($eMsg('nationality')) <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $eMsg('nationality') }}</div> @endif
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Role</label>
                                    <div class="input-icon {{ $eErr('role') }}">
                                        <select class="form-select" name="role" required>
                                            <option value="Dentist" {{ $ev('role', $acc->Position) === 'Dentist' ? 'selected' : '' }}>Dentist</option>
                                            <option value="Staff" {{ $ev('role', $acc->Position) === 'Staff' ? 'selected' : '' }}>Staff</option>
                                        </select>
                                    </div>
                                    @if ($eMsg('role')) <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $eMsg('role') }}</div> @endif
                                </div>
                            </div>

                            <div class="section-label mt-2">Home Address</div>
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Street / House No.</label>
                                    <div class="input-icon"><i class="bi bi-signpost-2"></i><input class="form-control addr-part" name="addr_street"
                                            value="{{ $editFailed ? old('addr_street') : $addrParts[0] }}" placeholder="123 Sample St." /></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Barangay</label>
                                    <div class="input-icon"><i class="bi bi-geo"></i><input class="form-control addr-part" name="addr_barangay"
                                            value="{{ $editFailed ? old('addr_barangay') : $addrParts[1] }}" placeholder="Barangay" /></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">City / Municipality</label>
                                    <div class="input-icon"><i class="bi bi-buildings"></i><input class="form-control addr-part" name="addr_city"
                                            value="{{ $editFailed ? old('addr_city') : $addrParts[2] }}" placeholder="City / Municipality" required /></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Province</label>
                                    <div class="input-icon"><i class="bi bi-map"></i><input class="form-control addr-part" name="addr_province"
                                            value="{{ $editFailed ? old('addr_province') : $addrParts[3] }}" placeholder="Province" required /></div>
                                </div>
                                <input type="hidden" name="address" class="{{ $eErr('address') }}" value="{{ $ev('address', $si->Address ?? '') }}">
                                @if ($eMsg('address')) <div class="col-12"><div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $eMsg('address') }}</div></div> @endif
                            </div>

                            <div class="section-label mt-2">Contact Details</div>
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Email address</label>
                                    <div class="input-icon {{ $eErr('email') }}"><i class="bi bi-envelope"></i><input type="email" name="email"
                                            class="form-control" value="{{ $ev('email', $acc->Email) }}" required /></div>
                                    @if ($eMsg('email')) <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $eMsg('email') }}</div> @endif
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Cell/Mobile number</label>
                                    <div class="input-icon {{ $eErr('phone') }}"><i class="bi bi-telephone"></i><input class="form-control" name="phone"
                                            value="{{ $ev('phone', $si->PhoneNumber ?? '') }}" required /></div>
                                    @if ($eMsg('phone')) <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $eMsg('phone') }}</div> @endif
                                </div>
                            </div>

                            <div class="section-label mt-2">Account Details</div>
                            <div class="row g-3 mb-3">
                                <div class="col-12">
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
    {{-- Never server-rendered as already-open (no modal-backdrop div here) —
    the shared reopen script in partials/flash-toasts (tracked by modal id in
    sessionStorage) re-opens the exact modal that was submitted when a
    validation error or "current password is incorrect" comes back. The
    form_source hidden field lets THIS specific staff row's fields (and only
    this row's, across the whole @foreach) show the has-error/field-error
    styling — see $pwdFailed above. --}}
    @foreach ($staff->merge($archivedStaff) as $acc)
        @php
            $pwdFailed = ($errors->any() || session('password_error')) && old('form_source') === 'pwd_' . $acc->UserID;
        @endphp
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
                            <input type="hidden" name="form_source" value="pwd_{{ $acc->UserID }}">
                            <div class="mb-3">
                                <label class="form-label">Current password</label>
                                <div class="pw-field">
                                    <input type="checkbox" class="pw-toggle-checkbox" id="pwChgCur{{ $acc->UserID }}">
                                    <div class="input-icon {{ $pwdFailed && session('password_error') ? 'has-error' : '' }}"><i class="bi bi-lock"></i><input type="text"
                                            name="current_password" class="form-control pw-mask" placeholder="••••••••" required value="{{ $pwdFailed ? old('current_password') : '' }}" autocomplete="current-password"></div>
                                    <label for="pwChgCur{{ $acc->UserID }}" class="pw-eye-btn" aria-label="Show or hide password"><i class="bi bi-eye"></i><i class="bi bi-eye-slash"></i></label>
                                </div>
                                @if ($pwdFailed && session('password_error'))
                                    <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ session('password_error') }}</div>
                                @endif
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">New password</label>
                                    <div class="pw-field">
                                        <input type="checkbox" class="pw-toggle-checkbox" id="pwChgNew{{ $acc->UserID }}">
                                        <div class="input-icon {{ $pwdFailed && $errors->has('password') ? 'has-error' : '' }}"><i class="bi bi-key"></i><input type="text" name="password"
                                                class="form-control pw-mask" placeholder="••••••••" minlength="8" required value="{{ $pwdFailed ? old('password') : '' }}" autocomplete="new-password"></div>
                                        <label for="pwChgNew{{ $acc->UserID }}" class="pw-eye-btn" aria-label="Show or hide password"><i class="bi bi-eye"></i><i class="bi bi-eye-slash"></i></label>
                                    </div>
                                    @if ($pwdFailed && $errors->has('password'))
                                        <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $errors->first('password') }}</div>
                                    @endif
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Confirm new password</label>
                                    <div class="pw-field">
                                        <input type="checkbox" class="pw-toggle-checkbox" id="pwChgConfirm{{ $acc->UserID }}">
                                        <div class="input-icon {{ $pwdFailed && $errors->has('password') ? 'has-error' : '' }}"><i class="bi bi-shield-lock"></i><input type="text"
                                                name="password_confirmation" class="form-control pw-mask" placeholder="••••••••" required value="{{ $pwdFailed ? old('password_confirmation') : '' }}" autocomplete="new-password"></div>
                                        <label for="pwChgConfirm{{ $acc->UserID }}" class="pw-eye-btn" aria-label="Show or hide password"><i class="bi bi-eye"></i><i class="bi bi-eye-slash"></i></label>
                                    </div>
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
    @include('partials.confirm-action-modal')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/field-restrictions.js') }}"></script>
    <script src="{{ asset('js/address-sync.js') }}"></script>
    <script src="{{ asset('js/birthdate-age.js') }}"></script>
    <script>
        document.querySelectorAll('input[type="file"][name="photo"]').forEach(function (input) {
            input.addEventListener('change', function () {
                var file = input.files[0];
                if (file && file.size > 5 * 1024 * 1024) {
                    alert('The photo is too large. Please choose an image up to 5MB only.');
                    input.value = '';
                }
            });
        });
    </script>
</body>

</html>
