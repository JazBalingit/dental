<?php
// Place in: app/Http/Controllers/AppointmentApprovalController.php

namespace App\Http\Controllers;

use App\Mail\AppointmentStatusMail;
use App\Models\Appointment;
use App\Models\DentistSchedule;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class AppointmentApprovalController extends Controller
{
    // Same ordered slot list used by the Dentist Schedule calendar —
    // needed here to figure out which slots to block when a staff member
    // approves an appointment with a multi-hour duration.
    protected array $slotOrder = ['09:00', '10:00', '11:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00'];

    public function index(Request $request)
    {
        $status = $request->query('status');
        $search = $request->query('search');

        $query = Appointment::with(['patientInfo', 'service', 'schedule'])
            ->orderByDesc('AppointmentDate')
            ->orderBy('AppointmentTime');

        if ($status && in_array($status, ['Pending', 'Approved', 'Declined', 'Completed'])) {
            $query->where('Status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('patientInfo', function ($p) use ($search) {
                    $p->where('FirstName', 'like', "%{$search}%")
                        ->orWhere('LastName', 'like', "%{$search}%");
                })->orWhereHas('service', function ($s) use ($search) {
                    $s->where('ServiceName', 'like', "%{$search}%");
                });
            });
        }

        $appointments = $query->paginate(10)->withQueryString();

        $today = now()->format('Y-m-d');

        $stats = [
            'today' => Appointment::whereDate('AppointmentDate', $today)->count(),
            'approved' => Appointment::where('Status', 'Approved')->count(),
            'pending' => Appointment::where('Status', 'Pending')->count(),
            'declined' => Appointment::where('Status', 'Declined')->count(),
        ];

        return view('superAdmin.appointment-approval', [
            'appointments' => $appointments,
            'stats' => $stats,
            'status' => $status,
            'search' => $search,
        ]);
    }

    public function approve(Request $request, $id)
    {
        $data = $request->validate([
            'duration_hours' => 'required|integer|min:1|max:8',
        ]);

        $appointment = Appointment::with(['patientInfo.userAccount'])->findOrFail($id);

        $appointment->Status = 'Approved';
        $appointment->DurationHours = $data['duration_hours'];
        $appointment->save();

        $this->blockSubsequentSlots(
            $appointment->AppointmentDate->format('Y-m-d'),
            $appointment->AppointmentTime,
            (int) $data['duration_hours']
        );

        $timeLabel = Carbon::createFromFormat('H:i', $appointment->AppointmentTime)->format('g:i A');
        $dateLabel = $appointment->AppointmentDate->format('F j, Y');

        $this->notify(
            $appointment,
            'Appointment Approved',
            "Your appointment has been approved! Please be there on {$dateLabel} at {$timeLabel}.",
            'success'
        );

        return redirect()->route('appointmentApproval')->with('success', 'Appointment approved.');
    }

    public function decline(Request $request, $id)
    {
        $data = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $appointment = Appointment::with(['patientInfo.userAccount'])->findOrFail($id);

        $appointment->Status = 'Declined';
        $appointment->DeclineReason = $data['reason'];
        $appointment->save();

        // Release the single slot that was held while this was pending.
        DentistSchedule::where('ScheduleID', $appointment->ScheduleID)->update(['Status' => 'Available']);

        $timeLabel = Carbon::createFromFormat('H:i', $appointment->AppointmentTime)->format('g:i A');
        $dateLabel = $appointment->AppointmentDate->format('F j, Y');

        $this->notify(
            $appointment,
            'Appointment Declined',
            "Your appointment on {$dateLabel} at {$timeLabel} was declined. Reason: {$data['reason']}",
            'danger'
        );

        return redirect()->route('appointmentApproval')->with('success', 'Appointment declined.');
    }

    /**
     * Marks the starting slot plus the next (duration - 1) slots on the
     * same day as Not Available — e.g. a 1:00 PM booking approved for
     * 4 hours blocks 1:00, 2:00, 3:00, and 4:00 PM.
     */
    protected function blockSubsequentSlots(string $date, string $startTime, int $durationHours): void
    {
        $startIndex = array_search($startTime, $this->slotOrder);

        if ($startIndex === false) {
            return;
        }

        for ($i = 0; $i < $durationHours; $i++) {
            $index = $startIndex + $i;

            if (!isset($this->slotOrder[$index])) {
                break; // ran past the last slot of the day
            }

            $slot = DentistSchedule::firstOrCreate(
                ['Date' => $date, 'Time' => $this->slotOrder[$index]],
                ['Status' => 'Available']
            );
            $slot->Status = 'Not Available';
            $slot->save();
        }
    }

    protected function notify(Appointment $appointment, string $title, string $message, string $type): void
    {
        $user = $appointment->patientInfo->userAccount ?? null;

        if (!$user) {
            return;
        }

        Notification::create([
            'UserID' => $user->UserID,
            'Title' => $title,
            'Message' => $message,
            'Type' => $type,
        ]);

        Mail::to($user->Email)->send(new AppointmentStatusMail($title, $message));
    }
}
