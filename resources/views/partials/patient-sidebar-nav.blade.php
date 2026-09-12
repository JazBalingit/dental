{{--
    Patient-portal sidebar navigation. Single source of truth for the three
    portal pages (Appointments, My Records, Settings) — mirrors
    partials/admin-sidebar-nav.blade.php so the two shells stay consistent.

    Usage: @include('partials.patient-sidebar-nav', ['active' => 'appointments'])
    where $active matches one of the keys below.
--}}
@php($active = $active ?? '')
<nav class="nav">
  <div class="nav-section">My Account</div>
  <a href="{{ route('userAppointment') }}" @class(['active' => $active === 'appointments'])><i class="bi bi-calendar2-check"></i> Appointments</a>
  <a href="{{ route('myRecords') }}" @class(['active' => $active === 'records'])><i class="bi bi-folder2-open"></i> My Dental Records</a>
  <a href="{{ route('settings') }}" @class(['active' => $active === 'settings'])><i class="bi bi-gear"></i> Settings</a>
</nav>
