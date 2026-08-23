<?php
// Place in: app/Http/Controllers/AppointmentBookingController.php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\BuildsBookingCalendar;
use App\Models\Appointment;
use App\Models\DentistSchedule;
use App\Models\Service;
use App\Models\UserAccount;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AppointmentBookingController extends Controller
{
    use BuildsBookingCalendar;

    public function __construct(protected NotificationService $notifications)
    {
    }

    public function store(Request $request)
    {
        if (!session('user_id')) {
            return redirect()->route('login')->with('login_error', 'Please log in to book an appointment.');
        }

        $data = $request->validate([
            'date' => 'required|date',
            'time' => 'required|string',
            'service_id' => 'required|exists:tbl_services,ServiceID',
        ]);

        $user = UserAccount::with('patientInfo')->findOrFail(session('user_id'));

        if (!$user->patientInfo) {
            return redirect()->to(route('landingPage') . '#appointment')->with('booking_error', 'Your patient profile is incomplete.');
        }

        $date = Carbon::parse($data['date']);

        if ($date->isSunday() || $date->lt(today())) {
            return redirect()->to(route('landingPage') . '#appointment')
                ->with('booking_error', 'That date is not available for booking.');
        }

        // Reserve the slot atomically-ish: create it as Available if it
        // doesn't exist yet, then immediately check + flip it.
        $schedule = DentistSchedule::firstOrCreate(
            ['Date' => $data['date'], 'Time' => $data['time']],
            ['Status' => 'Available']
        );

        if ($schedule->Status !== 'Available') {
            return redirect()->to(route('landingPage') . '#appointment')
                ->with('booking_error', 'Sorry, that slot was just taken. Please pick another time.');
        }

        $schedule->Status = 'Not Available';
        $schedule->save();

        $appointment = Appointment::create([
            'PatientID' => $user->patientInfo->PatientID,
            'ScheduleID' => $schedule->ScheduleID,
            'ServiceID' => $data['service_id'],
            'AppointmentDate' => $data['date'],
            'AppointmentTime' => $data['time'],
            'TypeOfAppointment' => \App\Models\Service::find($data['service_id'])->ServiceName ?? null,
            'Status' => 'Pending',
        ]);

        $timeLabel = Carbon::createFromFormat('H:i', $data['time'])->format('g:i A');
        $dateLabel = $date->format('F j, Y');

        $this->notifications->notifyUser(
            $user,
            'Appointment Booked',
            'Your appointment has been successfully booked.',
            'warning',
            $appointment->AppointmentID,
            'Pending'
        );

        $patientName = trim(($user->patientInfo->FirstName ?? '') . ' ' . ($user->patientInfo->LastName ?? ''));
        $this->notifications->notifyAdmins(
            'New Appointment',
            "{$patientName} has booked an appointment.",
            'info',
            $appointment->AppointmentID,
            'Pending'
        );

        return redirect()->to(route('landingPage') . '#appointment')
            ->with('booking_success', "Booked! {$dateLabel} at {$timeLabel} — status: Pending.");
    }
}
