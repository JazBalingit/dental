<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\AppointmentStep;
use App\Models\DentistSchedule;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\SystemSetting;
use App\Models\UserAccount;
use App\Services\ActivityLogService;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(protected NotificationService $notifications, protected ActivityLogService $activityLog)
    {
    }

    // show landing page front end
    public function showLandingPage(Request $request, \App\Http\Controllers\AppointmentBookingController $booking)
    {
        // Booking and appointment management live in the Patient Portal now
        // (see showUserAppointment) — a logged-in patient never sees this
        // public page. Admin/staff sessions are left alone; they land on
        // /dashboard at login and have no reason to hit this route anyway.
        if (session('user_id') && !in_array(session('user_role'), UserAccount::ADMIN_ROLES, true)) {
            return redirect()->route('userAppointment');
        }

        // Read-only schedule preview for guests — calendarData() degrades
        // gracefully with no session (bookCurrentPatientId simply ends up
        // null), so this is safe to call unconditionally.
        $bookingData = $booking->calendarData($request);

        $currentPatient = null;

        // "Our Services" section — grouped by category so each admin-defined
        // category becomes its own card, instead of hardcoded copy. Services
        // with no category land in a single "Other Services" catch-all so
        // nothing silently disappears from the public page.
        $serviceCategories = ServiceCategory::with(['services' => fn ($q) => $q->where('IsArchived', false)->orderBy('ServiceName')])
            ->where('IsArchived', false)
            ->orderBy('DisplayOrder')->orderBy('Name')->get()
            ->filter(fn ($category) => $category->services->isNotEmpty())
            ->values();

        $uncategorizedServices = Service::where('IsArchived', false)->whereNull('CategoryID')->orderBy('ServiceName')->get();

        return view('users.landing-page', array_merge($bookingData, [
            'currentPatient' => $currentPatient,
            'appointmentSteps' => AppointmentStep::where('IsArchived', false)
                ->orderBy('DisplayOrder')
                ->get()
                ->values()
                ->mapWithKeys(fn ($step, $i) => [$i + 1 => ['title' => $step->Title, 'desc' => $step->Description]])
                ->all(),
            'aboutInfo' => SystemSetting::aboutInfo(),
            'serviceCategories' => $serviceCategories,
            'uncategorizedServices' => $uncategorizedServices,
        ]));
    }
    /**
     * The Patient Portal's "Appointments" section is 3 pages sharing one
     * sidebar dropdown: Current Appointments (this one — the default landing
     * page), Book Appointment, and Appointment History. All three need the
     * same patient/PatientID guard and a couple of them need the same
     * queries, so those live in the private helpers below instead of being
     * copy-pasted three times.
     */
    public function showUserAppointment(Request $request)
    {
        $patientId = $this->currentPatientId();

        $counts = $this->appointmentCounts($patientId);
        $current = $this->currentAppointment($patientId);

        return view('users.user-appointment', compact('counts', 'current'));
    }

    // Patient Portal — Book Appointment (the dentist-schedule calendar).
    public function showBookAppointment(Request $request, \App\Http\Controllers\AppointmentBookingController $booking)
    {
        $patientId = $this->currentPatientId();

        $current = $this->currentAppointment($patientId);

        // Always load the calendar — even with an active appointment, the
        // patient may want to check availability before deciding whether to
        // reschedule. Booking a second slot is still blocked server-side.
        $bookingData = $booking->calendarData($request);

        return view('users.user-appointment-book', array_merge($bookingData, compact('current')));
    }

    // Patient Portal — Appointment History (searchable/filterable past visits).
    public function showAppointmentHistory(Request $request)
    {
        $patientId = $this->currentPatientId();
        $status = $request->query('status');
        $search = $request->query('search');

        $history = Appointment::with(['service', 'dentist.staffInfo', 'patientRecord.odontogramTeeth'])->where('PatientID', $patientId)
            ->when($status, fn ($q) => $q->where('Status', $status))
            ->when($search, fn ($q) => $q->whereHas('service', fn ($s) => $s->where('ServiceName', 'like', "%{$search}%")))
            ->orderByDesc('AppointmentDate')->orderByDesc('AppointmentTime')
            ->paginate(10)->withQueryString();

        return view('users.user-appointment-history', compact('history', 'status', 'search'));
    }

    // The 'auth.session' middleware on these routes already guarantees a
    // signed-in session — this just resolves it to a PatientID.
    protected function currentPatientId(): int
    {
        $user = UserAccount::with('patientInfo')->findOrFail(session('user_id'));
        abort_unless($user->patientInfo, 403, 'Please complete your patient profile first.');

        return $user->patientInfo->PatientID;
    }

    protected function currentAppointment(int $patientId): ?Appointment
    {
        return Appointment::with(['service', 'dentist.staffInfo', 'patientRecord.odontogramTeeth'])->where('PatientID', $patientId)
            ->whereIn('Status', ['Pending', 'Approved'])
            ->whereDate('AppointmentDate', '>=', today())
            ->orderBy('AppointmentDate')->orderBy('AppointmentTime')->first();
    }

    /** Upcoming / Completed / Cancelled / Total counts for the stat cards. */
    protected function appointmentCounts(int $patientId): array
    {
        $base = Appointment::where('PatientID', $patientId);

        return [
            'upcoming' => (clone $base)->whereIn('Status', ['Pending', 'Approved'])->count(),
            'completed' => (clone $base)->where('Status', 'Completed')->count(),
            'cancelled' => (clone $base)->where('Status', 'Declined')->count(),
            'total' => (clone $base)->count(),
        ];
    }

    public function removeAppointment(Request $request, Appointment $appointment)
    {
        if (!session('user_id')) return redirect()->route('login');
        $user = UserAccount::with('patientInfo')->findOrFail(session('user_id'));
        abort_unless($user->patientInfo && $appointment->PatientID === $user->patientInfo->PatientID, 403);

        $isReschedule = $request->input('action') === 'reschedule';

        // The appointment may already be cancelled/completed/declined — e.g.
        // a stale page still showing the button, a double submit, or the
        // back button. Don't blow up with a raw 422; just tell them.
        if (!in_array($appointment->Status, ['Pending', 'Approved'], true)) {
            $target = route('userAppointment');

            return redirect()->to($target)->with(
                'success',
                'That appointment is no longer active — it may have already been ' . strtolower($appointment->Status) . '.'
            );
        }

        $this->releaseAppointmentSlots($appointment);
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

        $this->activityLog->log(
            $isReschedule ? 'Appointment Rescheduled' : 'Appointment Cancelled',
            ($isReschedule ? 'Started a reschedule of' : 'Cancelled') . " the appointment on {$dateLabel} at {$timeLabel}.",
            $user->UserID
        );

        // Rescheduling is meant to drop the patient right back on the Book
        // Appointment page so they can immediately pick a new slot; a plain
        // cancel just returns to Current Appointments (now empty).
        return redirect()->route($isReschedule ? 'userAppointment.book' : 'userAppointment')
            ->with('success', $message);
    }

    protected function releaseAppointmentSlots(Appointment $appointment): void
    {
        $times = DentistSchedule::slotTimes();
        $start = array_search($appointment->AppointmentTime, $times, true);
        $duration = $appointment->duration_slots;
        for ($offset = 0; $start !== false && $offset < $duration && isset($times[$start + $offset]); $offset++) {
            $time = $times[$start + $offset];
            $stillHeld = Appointment::whereKeyNot($appointment->AppointmentID)
                ->where('DentistID', $appointment->DentistID)
                ->whereDate('AppointmentDate', $appointment->AppointmentDate)
                ->whereIn('Status', ['Pending', 'Approved'])->get()
                ->contains(function ($other) use ($times, $time) {
                    $otherStart = array_search($other->AppointmentTime, $times, true);
                    $otherDuration = $other->duration_slots;
                    return $otherStart !== false && in_array($time, array_slice($times, $otherStart, $otherDuration), true);
                });
            if (!$stillHeld) {
                DentistSchedule::where('DentistID', $appointment->DentistID)
                    ->where('Date', $appointment->AppointmentDate->format('Y-m-d'))
                    ->where('Time', $time)
                    ->update(['Status' => 'Available']);
            }
        }
    }
}
