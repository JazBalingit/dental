{{-- Shared right-hand side of the public/user navbar: the notification bell
     (desktop position), and either the account menu or the Sign In / Sign Up
     buttons. On phones/tablets the bell is rendered separately next to the
     hamburger (see each page's <nav>), so here it only shows from lg up. --}}
<style>
  /* Collapsed mobile menu needs a solid panel — the navbar itself is a
     translucent blur, so without this the links float over the page. */
  @media (max-width: 991.98px) {
    .navbar .navbar-collapse {
      background: #fff;
      border-radius: 0 0 16px 16px;
      margin-top: .35rem;
      padding: .5rem .9rem 1rem;
      box-shadow: 0 18px 34px -16px rgba(15, 23, 42, .28);
    }

    .navbar .icon-btn {
      width: 44px;
      height: 44px;
      font-size: 1.25rem;
    }

    /* Notification panel: a fixed sheet under the navbar so it can't push the
       page around or spill off-screen from the bell's cramped position. */
    .navbar .notif-dropdown.dropdown-menu {
      position: fixed;
      inset: 64px 8px auto 8px;
      width: auto !important;
      max-width: none !important;
      max-height: 72vh !important;
      overflow-y: auto;
    }
  }

  .user-nav-actions .nav-item > div {
    min-width: 0;
  }

  /* Username / avatar button that opens the account dropdown */
  .account-toggle {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    max-width: 240px;
    padding: 5px 12px 5px 6px;
    border: 1px solid rgba(15, 122, 51, .18);
    border-radius: 999px;
    background: #fff;
    color: #0f7a33;
    font-weight: 500;
    font-size: .92rem;
    line-height: 1;
    transition: background .2s ease, box-shadow .2s ease, border-color .2s ease;
  }

  .account-toggle:hover,
  .account-toggle[aria-expanded="true"] {
    background: #f1fcf0;
    border-color: rgba(15, 122, 51, .4);
    box-shadow: 0 4px 12px rgba(59, 217, 101, .22);
  }

  .account-toggle .account-avatar {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    object-fit: cover;
    flex: none;
    background: #e9edf2;
  }

  .account-toggle .account-email {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  .account-toggle .bi-chevron-down {
    font-size: .7rem;
    flex: none;
    transition: transform .2s ease;
  }

  .account-toggle[aria-expanded="true"] .bi-chevron-down {
    transform: rotate(180deg);
  }

  /* Guest buttons: green text on the outlined "Sign Up" (the stylesheet's
     value is an invalid gradient-as-color). */
  .user-nav-actions .navh.signup-btn {
    color: #0f7a33 !important;
  }

  .user-nav-actions .navh.signup-btn:hover {
    color: #fff !important;
  }

  @media (max-width: 991.98px) {
    .account-toggle {
      max-width: 100%;
    }
  }
</style>

<ul class="navbar-nav ms-lg-3 user-nav-actions">
  <li class="nav-item">
    <div class="d-flex align-items-center justify-content-center justify-content-lg-end gap-2 py-2 py-lg-0">
      @if (session('user_id'))
        {{-- Desktop: bell sits next to the account menu. On mobile it's up beside the hamburger. --}}
        <div class="d-none d-lg-block">
          @include('partials.user-notif-dropdown')
        </div>
      @endif

      @if (session('user_email'))
        <div class="dropdown">
          <button type="button" class="account-toggle" data-bs-toggle="dropdown"
            aria-expanded="false" aria-label="Account menu">
            <img src="{{ $navUserPhoto ?? asset('images/default.png') }}" alt="" class="account-avatar">
            <span class="account-email">{{ session('user_email') }}</span>
            <i class="bi bi-chevron-down"></i>
          </button>
          <ul class="dropdown-menu dropdown-menu-end shadow-sm">
            <li><a class="dropdown-item small" href="{{ route('userAppointment') }}"><i
                  class="bi bi-calendar-check me-2"></i>User Appointments</a></li>
            <li><a class="dropdown-item small" href="{{ route('myRecords') }}"><i
                  class="bi bi-folder2-open me-2"></i>My Dental Records</a></li>
            <li><a class="dropdown-item small" href="{{ route('settings') }}"><i
                  class="bi bi-gear me-2"></i>Settings</a></li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <form method="POST" action="{{ route('logout') }}" class="m-0">
                @csrf
                <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-1"></i> Log
                  Out</button>
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
