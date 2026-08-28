{{--
    Bottom-of-sidebar account menu — replaces the old topbar "My Profile"
    dropdown (which only ever showed for staff sessions) with one that
    works for every admin session: true admin, staff, and the config-based
    super admin. "Security" is hidden for the super admin since their
    login runs entirely on .env credentials, not a stored password.
--}}
<div class="sidebar-footer dropdown">
    <button class="sidebar-profile-badge" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <img class="avatar" src="/images/default.png" alt="">
        <div class="meta">
            <div class="name">{{ session('user_email', 'Admin') }}</div>
            <div class="role">{{ session('is_super_admin') ? 'Super Admin' : (session('account_type') === 'staff' ? 'Staff' : 'Administrator') }}</div>
        </div>
        <i class="bi bi-chevron-up ms-auto text-muted-2"></i>
    </button>
    <ul class="dropdown-menu shadow-sm">
        <li><a class="dropdown-item small" href="{{ route('staffProfile') }}"><i class="bi bi-person me-2"></i>User Profile</a></li>
        @unless (session('is_super_admin'))
            <li><a class="dropdown-item small" href="{{ route('staffProfile', ['tab' => 'security']) }}"><i class="bi bi-shield-lock me-2"></i>Security</a></li>
        @endunless
        <li><hr class="dropdown-divider"></li>
        <li>
            <form method="POST" action="{{ route('logout') }}" class="m-0">
                @csrf
                <button type="submit" class="dropdown-item text-danger small"><i class="bi bi-box-arrow-right me-1"></i> Log Out</button>
            </form>
        </li>
    </ul>
</div>
