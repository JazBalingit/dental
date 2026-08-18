<?php
// Place in: app/Http/Controllers/AppointmentBookingController.php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\DentistSchedule;
use App\Models\Service;
use App\Models\UserAccount;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AppointmentBookingController extends Controller
{
    public function __construct(protected NotificationService $notifications)
    {
    }

    protected array $slots = [
        '09:00' => '9:00 AM',
        '10:00' => '10:00 AM',
        '11:00' => '11:00 AM',
        '13:00' => '1:00 PM',
        '14:00' => '2:00 PM',
        '15:00' => '3:00 PM',
        '16:00' => '4:00 PM',
        '17:00' => '5:00 PM',
        '18:00' => '6:00 PM',
    ];

    /**
     * Returns everything the booking calendar section needs. Call this
     * from whatever controller renders the landing page and merge the
     * result into that page's view data — see the README for exactly
     * where this plugs in.
     */
    public function calendarData(Request $request): array
    {
        $monthParam = $request->query('bookMonth', now()->format('Y-m'));

        try {
            $current = Carbon::createFromFormat('Y-m', $monthParam)->startOfMonth();
        } catch (\Exception $e) {
            $current = now()->startOfMonth();
        }

        $startDay = $current->copy()->startOfMonth()->startOfWeek(Carbon::MONDAY);
        $endDay = $current->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);

        $schedules = DentistSchedule::whereBetween('Date', [
            $startDay->format('Y-m-d'),
            $endDay->format('Y-m-d'),
        ])
            ->get()
            ->groupBy(fn($row) => $row->Date->format('Y-m-d'))
            ->map(fn($rows) => $rows->keyBy('Time'));

        // Pull appointments in range so we can label taken slots as
        // Booked (Approved) vs Pending instead of just "Not Available".
        $appointments = Appointment::with('service')->whereBetween('AppointmentDate', [
            $startDay->format('Y-m-d'),
            $endDay->format('Y-m-d'),
        ])
            ->whereIn('Status', ['Pending', 'Approved'])
            ->get();

        // An approved appointment occupies every slot in its approved duration.
        // A pending request holds only its requested starting slot.
        $occupiedSlots = [];
        $slotTimes = array_keys($this->slots);
        foreach ($appointments as $appointment) {
            $start = array_search($appointment->AppointmentTime, $slotTimes, true);
            if ($start === false) {
                continue;
            }

            $duration = $appointment->Status === 'Approved'
                ? max(1, (int) ($appointment->DurationHours ?? 1))
                : 1;

            for ($offset = 0; $offset < $duration && isset($slotTimes[$start + $offset]); $offset++) {
                $occupiedSlots[$appointment->AppointmentDate->format('Y-m-d') . '_' . $slotTimes[$start + $offset]] = $appointment;
            }
        }

        $weeks = [];
        $cursor = $startDay->copy();
        while ($cursor->lte($endDay)) {
            $week = [];
            for ($i = 0; $i < 7; $i++) {
                $week[] = $cursor->copy();
                $cursor->addDay();
            }
            $weeks[] = $week;
        }

        return [
            'bookWeeks' => $weeks,
            'bookCurrent' => $current,
            'bookSchedules' => $schedules,
            'bookOccupiedSlots' => $occupiedSlots,
            'bookSlots' => $this->slots,
            'bookToday' => now()->format('Y-m-d'),
            'services' => Service::orderBy('ServiceName')->get(),
            'bookCurrentPatientId' => UserAccount::with('patientInfo')->find(session('user_id'))?->patientInfo?->PatientID,
        ];
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
            return redirect()->route('landingPage')->with('booking_error', 'Your patient profile is incomplete.') . '#appointment';
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
