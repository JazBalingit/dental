<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\DentistSchedule;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\SystemSetting;
use App\Models\UserAccount;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(protected NotificationService $notifications)
    {
    }

    // show landing page front end
    public function showLandingPage(Request $request, \App\Http\Controllers\AppointmentBookingController $booking)
    {
        // The appointment calendar is private to signed-in patients.
        $bookingData = session('user_id') ? $booking->calendarData($request) : [];

        // Lets the Contact form skip asking for name/email when we already know them.
        $currentPatient = session('user_id') ? UserAccount::with('patientInfo')->find(session('user_id')) : null;

        // "Our Services" section — grouped by category so each admin-defined
        // category becomes its own card, instead of hardcoded copy. Services
        // with no category land in a single "Other Services" catch-all so
        // nothing silently disappears from the public page.
        $serviceCategories = ServiceCategory::with(['services' => fn ($q) => $q->where('IsArchived', false)->orderBy('ServiceName')])
            ->orderBy('DisplayOrder')->orderBy('Name')->get()
            ->filter(fn ($category) => $category->services->isNotEmpty())
            ->values();

        $uncategorizedServices = Service::where('IsArchived', false)->whereNull('CategoryID')->orderBy('ServiceName')->get();

        return view('users.landing-page', array_merge($bookingData, [
            'currentPatient' => $currentPatient,
            'appointmentSteps' => SystemSetting::appointmentSteps(),
            'aboutInfo' => SystemSetting::aboutInfo(),
            'serviceCategories' => $serviceCategories,
            'uncategorizedServices' => $uncategorizedServices,
        ]));
    }
    // show user appointment front end
    public function showUserAppointment(Request $request)
    {
        if (!session('user_id')) return redirect()->route('login');

        $user = UserAccount::with('patientInfo')->findOrFail(session('user_id'));
        abort_unless($user->patientInfo, 403, 'Please complete your patient profile first.');
        $patientId = $user->patientInfo->PatientID;
        $status = $request->query('status');
        $search = $request->query('search');

        $history = Appointment::with('service')->where('PatientID', $patientId)
            ->when($status, fn ($q) => $q->where('Status', $status))
            ->when($search, fn ($q) => $q->whereHas('service', fn ($s) => $s->where('ServiceName', 'like', "%{$search}%")))
            ->orderByDesc('AppointmentDate')->orderByDesc('AppointmentTime')
            ->paginate(10)->withQueryString();

        $current = Appointment::with('service')->where('PatientID', $patientId)
            ->whereIn('Status', ['Pending', 'Approved'])
            ->whereDate('AppointmentDate', '>=', today())
            ->orderBy('AppointmentDate')->orderBy('AppointmentTime')->first();

        return view('users.user-appointment', compact('history', 'current', 'status', 'search'));
    }

    public function removeAppointment(Request $request, Appointment $appointment)
    {
        if (!session('user_id')) return redirect()->route('login');
        $user = UserAccount::with('patientInfo')->findOrFail(session('user_id'));
        abort_unless($user->patientInfo && $appointment->PatientID === $user->patientInfo->PatientID, 403);
        abort_unless(in_array($appointment->Status, ['Pending', 'Approved']), 422);

        $this->releaseAppointmentSlots($appointment);

        $isReschedule = $request->input('action') === 'reschedule';
        $appointment->Status = 'Cancelled';
        $appointment->save();

        $timeLabel = Carbon::createFromFormat('H:i', $appointment->AppointmentTime)->format('g:i A');
        $dateLabel = $appointment->AppointmentDate->format('F j, Y');
        $patientName = trim(($user->patientInfo->FirstName ?? '') . ' ' . ($user->patientInfo->LastName ?? ''));

        if ($isReschedule) {
            $this->notifications->notifyUser(
                $user,
                'Appointment Rescheduled',
                "Your appointment on {$dateLabel} at {$timeLabel} has been cancelled so you can pick a new time.",
                'warning',
                $appointment->AppointmentID,
                'Cancelled'
            );
            $this->notifications->notifyAdmins(
                'Appointment Rescheduled',
                "{$patientName} has rescheduled their appointment.",
                'warning',
                $appointment->AppointmentID,
                'Cancelled'
            );
        } else {
            $this->notifications->notifyUser(
                $user,
                'Appointment Cancelled',
                "Your appointment on {$dateLabel} at {$timeLabel} has been cancelled.",
                'danger',
                $appointment->AppointmentID,
                'Cancelled'
            );
            $this->notifications->notifyAdmins(
                'Appointment Cancelled',
                "{$patientName} has cancelled their appointment.",
                'danger',
                $appointment->AppointmentID,
                'Cancelled'
            );
        }

        $action = $isReschedule ? 'rescheduled' : 'cancelled';
        $message = "Appointment {$action}. The time slot is available again.";

        // Rescheduling is meant to drop the patient right back on the booking
        // calendar so they can immediately pick a new slot, regardless of
        // whether they started the reschedule from the landing page or the
        // appointments page.
        if ($isReschedule) {
            return redirect(route('landingPage') . '#appointment')->with('success', $message);
        }

        return redirect()->route('userAppointment')->with('success', $message);
    }

    protected function releaseAppointmentSlots(Appointment $appointment): void
    {
        $times = DentistSchedule::slotTimes();
        $start = array_search($appointment->AppointmentTime, $times, true);
        $duration = $appointment->duration_slots;
        for ($offset = 0; $start !== false && $offset < $duration && isset($times[$start + $offset]); $offset++) {
            $time = $times[$start + $offset];
            $stillHeld = Appointment::whereKeyNot($appointment->AppointmentID)
                ->whereDate('AppointmentDate', $appointment->AppointmentDate)
                ->whereIn('Status', ['Pending', 'Approved'])->get()
                ->contains(function ($other) use ($times, $time) {
                    $otherStart = array_search($other->AppointmentTime, $times, true);
                    $otherDuration = $other->duration_slots;
                    return $otherStart !== false && in_array($time, array_slice($times, $otherStart, $otherDuration), true);
                });
            if (!$stillHeld) DentistSchedule::where('Date', $appointment->AppointmentDate->format('Y-m-d'))->where('Time', $time)->update(['Status' => 'Available']);
        }
    }
}
