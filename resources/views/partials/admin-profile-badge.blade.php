{{--
    Bottom-of-sidebar account menu — replaces the old topbar "My Profile"
    dropdown (which only ever showed for staff sessions) with one that
    works for every admin session: true admin, staff, and the config-based
    super admin. "Security" is hidden for the super admin since their
    login runs entirely on .env credentials, not a stored password.
--}}
@php
    $accountRole = session('is_super_admin')
        ? 'Super Admin'
        : (session('account_type') === 'staff' ? 'Staff' : 'Administrator');
    $accountName = session('user_email', 'Admin');
@endphp
<div class="sidebar-footer dropdown">
    <button class="sidebar-profile-badge" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <img class="avatar" src="/images/default.png" alt="">
        <div class="meta">
            <div class="name">{{ $accountName }}</div>
            <div class="role">{{ $accountRole }}</div>
        </div>
        <i class="bi bi-chevron-up ms-auto"></i>
    </button>
    <ul class="dropdown-menu shadow-sm account-menu">
        <li class="account-menu-head">
            <img src="/images/default.png" alt="">
            <div class="account-menu-id">
                <div class="account-menu-name">{{ $accountName }}</div>
                <div class="account-menu-role">{{ $accountRole }}</div>
            </div>
        </li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item" href="{{ route('staffProfile') }}"><i class="bi bi-person-circle"></i> User Profile</a></li>
        @unless (session('is_super_admin'))
            <li><a class="dropdown-item" href="{{ route('staffProfile', ['tab' => 'security']) }}"><i class="bi bi-shield-lock"></i> Security</a></li>
        @endunless
        <li><hr class="dropdown-divider"></li>
        <li>
            <form method="POST" action="{{ route('logout') }}" class="m-0">
                @csrf
                <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right"></i> Log Out</button>
            </form>
        </li>
    </ul>
</div>
