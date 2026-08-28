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
use Illuminate\Support\Facades\DB;

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
            'service_ids' => 'required|array|min:1',
            'service_ids.*' => 'distinct|exists:tbl_services,ServiceID',
        ]);

        $user = UserAccount::with('patientInfo')->findOrFail(session('user_id'));

        if (!$user->patientInfo) {
            return redirect()->to(route('landingPage') . '#appointment')->with('booking_error', 'Your patient profile is incomplete.');
        }

        // One active appointment at a time — the next slot opens up once
        // this one is completed, declined, or cancelled.
        if (Appointment::where('PatientID', $user->patientInfo->PatientID)->whereIn('Status', ['Pending', 'Approved'])->exists()) {
            return redirect()->to(route('landingPage') . '#appointment')
                ->with('booking_error', 'You already have an upcoming appointment. Please wait until it\'s completed, or cancel it, before booking another.');
        }

        $date = Carbon::parse($data['date']);

        if ($date->isSunday() || $date->lt(today())) {
            return redirect()->to(route('landingPage') . '#appointment')
                ->with('booking_error', 'That date is not available for booking.');
        }

        // A same-day slot whose start time has already gone by can't be
        // booked either — the calendar hides these, but the check belongs
        // here too since this is what actually decides what gets created.
        if (Carbon::parse($data['date'] . ' ' . $data['time'])->lt(now())) {
            return redirect()->to(route('landingPage') . '#appointment')
                ->with('booking_error', 'That time has already passed today. Please choose an upcoming time.');
        }

        if ($date->gt(today()->addMonths(2))) {
            return redirect()->to(route('landingPage') . '#appointment')
                ->with('booking_error', 'Appointments can only be booked up to 2 months in advance.');
        }

        $services = Service::whereIn('ServiceID', $data['service_ids'])->get();
        $slotsNeeded = $this->slotsNeededForServices($services);

        try {
            DB::beginTransaction();

            // Reserve every slot the total service duration needs — either
            // the whole block flips to Not Available, or none of it does.
            $scheduleRows = $this->reserveSlotBlock($data['date'], $data['time'], $slotsNeeded);

            $appointment = Appointment::create([
                'PatientID' => $user->patientInfo->PatientID,
                'ScheduleID' => $scheduleRows[0]->ScheduleID,
                'ServiceID' => $services->first()?->ServiceID,
                'AppointmentDate' => $data['date'],
                'AppointmentTime' => $data['time'],
                'TypeOfAppointment' => $services->pluck('ServiceName')->implode(', ') ?: null,
                'DurationHours' => round($slotsNeeded * DentistSchedule::SLOT_MINUTES / 60, 1),
                'Status' => 'Pending',
            ]);

            $appointment->services()->sync($services->pluck('ServiceID'));

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            $failureMessage = $e->getMessage() ?: 'Could not book this appointment. Please try again.';

            // Keep a record of why in the patient's notifications — the
            // flash toast disappears in a few seconds, but this needs
            // no email since they're already looking right at it.
            $this->notifications->notifyUser(
                $user,
                'Booking Failed',
                $failureMessage,
                'danger',
                null,
                null,
                null,
                false
            );

            return redirect()->to(route('landingPage') . '#appointment')
                ->with('booking_error', $failureMessage);
        }

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

        $durationLabel = DentistSchedule::formatSlotDuration($slotsNeeded);

        return redirect()->to(route('landingPage') . '#appointment')
            ->with('booking_success', "Booked! {$dateLabel} at {$timeLabel} for {$durationLabel} — status: Pending.");
    }
}
