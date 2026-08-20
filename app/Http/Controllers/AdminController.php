<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    // show dashboard front end
    public function showDashboard()
    {
        return view('superAdmin.dashboard');
    }
    // show staff account front end
    public function showStaffAcc()
    {
        return view('superAdmin.staff-accounts');
    }
    // show user account front end
    public function showUserAcc()
    {
        return view('superAdmin.user-accounts');
    }
    // show dentist schedule front end
    public function showDentistSchedule()
    {
        return view('superAdmin.dentist-schedule');
    }
    // show walk in front end
    public function showWalkIn()
    {
        return view('superAdmin.walk-in');
    }
    // show appointment approval front end
    public function showAppointmentApproval()
    {
        return view('superAdmin.appointment-approval');
    }
    // show appointments front end
    public function showAppointments()
    {
        return view('superAdmin.appointments');
    }
    // show patient records front end
    public function showPatientRecords()
    {
        return view('superAdmin.patient-records');
    }
}
