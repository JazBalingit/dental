{{-- Landing-page navbar, reused on the login / sign-up pages so the whole
     site reads as one. Section links jump back to the landing page. --}}
<nav class="navbar navbar-expand-lg navbar-light fixed-top auth-navbar shadow-sm">
  <div class="container-fluid px-3 px-lg-5">
    <a class="navbar-brand d-flex align-items-center" href="{{ route('landingPage') }}">
      <img class="auth-nav-logo" src="/images/puspus_logo.png" alt="Pus-Pus Britanico logo">
      <span class="auth-nav-title ms-1" style="color:#0f7a2d;">PUS-PUS</span>
      <span class="auth-nav-title ms-2" style="color:#144d25;">BRITANICO</span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#authNavbar">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="authNavbar">
      <ul class="navbar-nav mx-auto text-center">
        <li class="nav-item"><a class="nav-link auth-navh" href="{{ route('landingPage') }}#home">Home</a></li>
        <li class="nav-item"><a class="nav-link auth-navh" href="{{ route('landingPage') }}#services">Services</a></li>
        <li class="nav-item"><a class="nav-link auth-navh" href="{{ route('landingPage') }}#how">How It Works</a></li>
        <li class="nav-item"><a class="nav-link auth-navh" href="{{ route('landingPage') }}#about">About</a></li>
        <li class="nav-item"><a class="nav-link auth-navh" href="{{ route('landingPage') }}#contact">Contact</a></li>
      </ul>
      <ul class="navbar-nav ms-lg-3">
        <li class="nav-item d-flex justify-content-center gap-2 py-2 py-lg-0">
          <a href="{{ route('login') }}"
            class="nav-link auth-navh signin-btn @if (request()->routeIs('login')) is-current @endif">Sign In</a>
          <a href="{{ route('signup') }}"
            class="nav-link auth-navh signup-btn @if (request()->routeIs('signup')) is-current @endif">Sign Up</a>
        </li>
      </ul>
    </div>
  </div>
</nav>
