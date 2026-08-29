{{--
    Super-admin sidebar navigation. Single source of truth — every admin
    page includes this so the menu can't drift between screens.

    Usage: @include('partials.admin-sidebar-nav', ['active' => 'dashboard'])
    where $active matches one of the keys below.
--}}
@php($active = $active ?? '')
<nav class="nav">
  <div class="nav-section">Main</div>
  <a href="{{ route('dashboard') }}" @class(['active' => $active === 'dashboard'])><i class="bi bi-grid-1x2-fill"></i> Dashboard</a>
  <a href="{{ route('dentistSchedule') }}" @class(['active' => $active === 'dentistSchedule'])><i class="bi bi-calendar-week"></i> Dentist Schedule</a>
  <a href="{{ route('appointmentApproval') }}" @class(['active' => $active === 'appointmentApproval'])><i class="bi bi-clipboard2-check"></i> Appointment Approval</a>
  <a href="{{ route('appointments') }}" @class(['active' => $active === 'appointments'])><i class="bi bi-card-checklist"></i> Appointments</a>
  <a href="{{ route('walkIn') }}" @class(['active' => $active === 'walkIn'])><i class="bi bi-person-walking"></i> Walk-in Appointments</a>
  <a href="{{ route('patientRecords') }}" @class(['active' => $active === 'patientRecords'])><i class="bi bi-folder2-open"></i> Patient Records</a>

  <div class="nav-section">User Management</div>
  <a href="{{ route('userAcc') }}" @class(['active' => $active === 'userAcc'])><i class="bi bi-person-vcard"></i> User Accounts</a>
  <a href="{{ route('staffAcc') }}" @class(['active' => $active === 'staffAcc'])><i class="bi bi-person-badge"></i> Staff Accounts</a>

  <div class="nav-section">System</div>
  <a href="{{ route('configuration') }}" @class(['active' => $active === 'configuration'])><i class="bi bi-sliders2"></i> Configuration</a>
</nav>
