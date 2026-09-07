{{--
    Bottom-of-sidebar account menu — one menu for every admin session: super
    admin, plain admin, and staff/dentist. All of them have a real stored
    password now (the bootstrap super admin is locked to the setup screen and
    never reaches an admin page), so "Security" always shows.

    $adminAccountName / $adminAccountEmail / $adminAccountPhoto come from
    AdminNotificationComposer (real "Dr. First Last" name + profile photo
    where StaffInfo exists, otherwise a name derived from the email and the
    shared default avatar). Fall back defensively if the composer didn't run.
--}}
@php
    $accountRole = session('is_super_admin')
        ? 'Super Admin'
        : (session('account_type') === 'staff' ? 'Staff' : 'Administrator');

    $displayName = $adminAccountName ?? session('user_email', 'Account');
    $accountEmail = $adminAccountEmail ?? session('user_email');
    $accountPhoto = $adminAccountPhoto ?? asset('images/default.png');
@endphp
<div class="sidebar-footer dropdown">
    <button class="sidebar-profile-badge" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <img class="avatar" src="{{ $accountPhoto }}" alt=""
            onerror="this.onerror=null;this.src='{{ asset('images/default.png') }}'">
        <span class="meta">
            <span class="name">{{ $displayName }}</span>
            <span class="role">{{ $accountRole }}</span>
        </span>
        <i class="bi bi-chevron-expand caret" aria-hidden="true"></i>
    </button>
    <ul class="dropdown-menu shadow-sm account-menu">
        <li class="account-menu-head">
            <img class="avatar" src="{{ $accountPhoto }}" alt=""
                onerror="this.onerror=null;this.src='{{ asset('images/default.png') }}'">
            <div class="account-menu-id">
                <div class="account-menu-name">{{ $displayName }}</div>
                @if ($accountEmail && $accountEmail !== $displayName)
                    <div class="account-menu-email">{{ $accountEmail }}</div>
                @endif
                <div class="account-menu-role">{{ $accountRole }}</div>
            </div>
        </li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item" href="{{ route('staffProfile') }}"><i class="bi bi-person-circle"></i> Admin Profile</a></li>
        <li><a class="dropdown-item" href="{{ route('staffProfile', ['tab' => 'security']) }}"><i class="bi bi-shield-lock"></i> Security</a></li>
        <li><hr class="dropdown-divider"></li>
        <li>
            <form method="POST" action="{{ route('logout') }}" class="m-0">
                @csrf
                <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right"></i> Log Out</button>
            </form>
        </li>
    </ul>
</div>
