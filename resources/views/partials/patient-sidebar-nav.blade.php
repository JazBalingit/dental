{{--
    Patient Portal sidebar navigation. Single source of truth — every
    patient-facing page includes this so the menu can't drift between
    screens.

    "Appointments" is a collapsible group with 3 sub-pages instead of a
    single link, since Book Appointment / Current Appointments / Appointment
    History used to all be crammed onto one page.

    Usage: @include('partials.patient-sidebar-nav', ['active' => 'appointments-current'])
    where $active is one of: appointments-book, appointments-current,
    appointments-history, records, settings.
--}}
@php
    $active = $active ?? '';
    $appointmentsSubpages = ['appointments-book', 'appointments-current', 'appointments-history'];
    $appointmentsOpen = in_array($active, $appointmentsSubpages, true);
@endphp
<nav class="nav">
  <div class="nav-section">My Account</div>

  <button type="button" class="nav-toggle {{ $appointmentsOpen ? 'active' : '' }}" data-bs-toggle="collapse"
    data-bs-target="#patientApptSubnav" aria-expanded="{{ $appointmentsOpen ? 'true' : 'false' }}">
    <i class="bi bi-calendar2-check"></i>
    <span>Appointments</span>
    <i class="bi bi-chevron-down nav-toggle-caret"></i>
  </button>
  <div class="collapse {{ $appointmentsOpen ? 'show' : '' }}" id="patientApptSubnav">
    <a href="{{ route('userAppointment.book') }}" class="nav-sub-link {{ $active === 'appointments-book' ? 'active' : '' }}"><i class="bi bi-calendar-plus"></i> Book Appointment</a>
    <a href="{{ route('userAppointment') }}" class="nav-sub-link {{ $active === 'appointments-current' ? 'active' : '' }}"><i class="bi bi-clock-history"></i> Current Appointments</a>
    <a href="{{ route('userAppointment.history') }}" class="nav-sub-link {{ $active === 'appointments-history' ? 'active' : '' }}"><i class="bi bi-journal-text"></i> Appointment History</a>
  </div>

  <a href="{{ route('myRecords') }}" @class(['active' => $active === 'records'])><i class="bi bi-folder2-open"></i> My Dental Records</a>
  <a href="{{ route('settings') }}" @class(['active' => $active === 'settings'])><i class="bi bi-gear"></i> Settings</a>
</nav>
