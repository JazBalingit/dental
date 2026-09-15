<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
  <link rel="icon" type="image/png" href="/images/puspus_logo.png">
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
            @include('partials.admin-sidebar-nav', ['active' => 'userAcc'])
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
                        <h2>User Accounts</h2>
                        <div class="crumbs">View, edit, and manage patient accounts.</div>
                    </div>
                </div>

                @include('partials.flash-toasts')

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
                                                <td>
                                                    @if ($pi?->is_inactive)
                                                        <span class="pill pill-warning" title="No appointment in the last 6 months">Inactive</span>
                                                    @else
                                                        <span class="pill pill-success">Active</span>
                                                    @endif
                                                </td>
                                                <td class="text-end">
                                                    <button class="btn btn-pill btn-pill-edit me-1" data-bs-toggle="modal"
                                                        data-bs-target="#editUserModal{{ $acc->UserID }}"><i class="bi bi-pencil-square"></i>
                                                        Edit</button>
                                                    <button type="button" class="btn btn-pill btn-pill-archive" data-bs-toggle="modal"
                                                        data-bs-target="#confirmActionModal"
                                                        data-action-url="{{ route('userAcc.archive', $acc->UserID) }}"
                                                        data-title="Archive User"
                                                        data-message="Archive {{ trim(($pi->FirstName ?? '') . ' ' . ($pi->LastName ?? '')) ?: $acc->Email }}? They won't be able to log in until you unarchive their account."
                                                        data-confirm-label="Archive" data-confirm-class="btn-pill-archive">
                                                        <i class="bi bi-archive"></i> Archive</button>
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
                                    @include('partials.pagination-pages', ['paginator' => $users])
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
                                                    <button type="button" class="btn btn-pill btn-pill-archive" data-bs-toggle="modal"
                                                        data-bs-target="#confirmActionModal"
                                                        data-action-url="{{ route('userAcc.unarchive', $acc->UserID) }}"
                                                        data-title="Unarchive User"
                                                        data-message="Restore {{ trim(($pi->FirstName ?? '') . ' ' . ($pi->LastName ?? '')) ?: $acc->Email }}? They'll be able to log in again."
                                                        data-confirm-label="Unarchive" data-confirm-class="btn-pill-archive">
                                                        <i class="bi bi-archive"></i> Unarchive</button>
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
                                    @include('partials.pagination-pages', ['paginator' => $archivedUsers])
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
            $eErr = fn ($field) => $editFailed && $errors->has($field) ? 'has-error' : '';
            $eMsg = fn ($field) => $editFailed && $errors->has($field) ? $errors->first($field) : null;
            $addrParts = array_pad(array_map('trim', explode(',', $pi->Address ?? '', 4)), 4, '');
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
                                    <div class="small text-muted-2 mt-1">JPG or PNG, max 5MB.</div>
                                </div>
                            </div>

                            <div class="section-label">Personal Information</div>
                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Last name</label>
                                    <div class="input-icon {{ $eErr('last_name') }}"><i class="bi bi-person"></i><input type="text" name="last_name" class="form-control"
                                            value="{{ $ev('last_name', $pi->LastName ?? '') }}" required /></div>
                                    @if ($eMsg('last_name')) <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $eMsg('last_name') }}</div> @endif
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">First name</label>
                                    <div class="input-icon {{ $eErr('first_name') }}"><i class="bi bi-person"></i><input type="text" name="first_name" class="form-control"
                                            value="{{ $ev('first_name', $pi->FirstName ?? '') }}" required /></div>
                                    @if ($eMsg('first_name')) <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $eMsg('first_name') }}</div> @endif
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Middle name</label>
                                    <div class="input-icon {{ $eErr('middle_name') }}"><i class="bi bi-person"></i><input type="text" name="middle_name" class="form-control"
                                            value="{{ $ev('middle_name', $pi->MiddleName ?? '') }}" /></div>
                                    @if ($eMsg('middle_name')) <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $eMsg('middle_name') }}</div> @endif
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Birthdate</label>
                                    <div class="input-icon {{ $eErr('birthdate') }}"><i class="bi bi-calendar-event"></i><input type="date" name="birthdate"
                                            class="form-control" value="{{ $ev('birthdate', optional($pi->DateOfBirth ?? null)->format('Y-m-d')) }}" max="2023-12-31" required
                                            data-age-target="#userAge{{ $acc->UserID }}" data-minor-target="#userMinorSection{{ $acc->UserID }}" />
                                    </div>
                                    @if ($eMsg('birthdate'))
                                        <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $eMsg('birthdate') }}</div>
                                    @else
                                        <div class="small text-muted-2 mt-1">Age is calculated automatically from the birthdate.</div>
                                    @endif
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Age</label>
                                    <div class="input-icon"><i class="bi bi-person-vcard"></i><input type="text" class="form-control" id="userAge{{ $acc->UserID }}" placeholder="—" disabled></div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Gender</label>
                                    <div class="input-icon {{ $eErr('gender') }}">
                                        <select class="form-select" name="gender" required>
                                            <option value="male" {{ $ev('gender', $pi->Gender ?? '') === 'male' ? 'selected' : '' }}>Male</option>
                                            <option value="female" {{ $ev('gender', $pi->Gender ?? '') === 'female' ? 'selected' : '' }}>Female</option>
                                            <option value="other" {{ $ev('gender', $pi->Gender ?? '') === 'other' ? 'selected' : '' }}>Other</option>
                                        </select>
                                    </div>
                                    @if ($eMsg('gender')) <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $eMsg('gender') }}</div> @endif
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Religion</label>
                                    <div class="input-icon {{ $eErr('religion') }}"><i class="bi bi-book"></i><input class="form-control" name="religion"
                                            value="{{ $ev('religion', $pi->Religion ?? '') }}" />
                                    </div>
                                    @if ($eMsg('religion')) <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $eMsg('religion') }}</div> @endif
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Nationality</label>
                                    <div class="input-icon {{ $eErr('nationality') }}"><i class="bi bi-flag"></i><input class="form-control" name="nationality"
                                            value="{{ $ev('nationality', $pi->Nationality ?? '') }}" required />
                                    </div>
                                    @if ($eMsg('nationality')) <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $eMsg('nationality') }}</div> @endif
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Occupation</label>
                                    <div class="input-icon {{ $eErr('occupation') }}"><i class="bi bi-briefcase"></i><input class="form-control" name="occupation"
                                            value="{{ $ev('occupation', $pi->Occupation ?? '') }}" />
                                    </div>
                                    @if ($eMsg('occupation')) <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $eMsg('occupation') }}</div> @endif
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
                                <input type="hidden" name="address" class="{{ $eErr('address') }}" value="{{ $ev('address', $pi->Address ?? '') }}">
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
                                            value="{{ $ev('phone', $pi->PhoneNumber ?? '') }}" required /></div>
                                    @if ($eMsg('phone')) <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $eMsg('phone') }}</div> @endif
                                </div>
                            </div>

                            <div id="userMinorSection{{ $acc->UserID }}">
                                <div class="section-label mt-2">Parent / Guardian (for minors)</div>
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Guardian's name</label>
                                        <div class="input-icon {{ $eErr('guardian_name') }}"><i class="bi bi-person-heart"></i><input class="form-control" name="guardian_name"
                                                value="{{ $ev('guardian_name', $pi->ParentsName ?? '') }}" />
                                        </div>
                                        @if ($eMsg('guardian_name')) <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $eMsg('guardian_name') }}</div> @endif
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Guardian's occupation</label>
                                        <div class="input-icon {{ $eErr('guardian_occupation') }}"><i class="bi bi-briefcase"></i><input class="form-control" name="guardian_occupation"
                                                value="{{ $ev('guardian_occupation', $pi->ParentsOccupation ?? '') }}" />
                                        </div>
                                        @if ($eMsg('guardian_occupation')) <div class="field-error"><i class="bi bi-exclamation-circle-fill"></i> {{ $eMsg('guardian_occupation') }}</div> @endif
                                    </div>
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
