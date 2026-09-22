<?php
// Place in: app/Http/Controllers/WalkInController.php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\BuildsBookingCalendar;
use App\Models\Appointment;
use App\Models\DentistSchedule;
use App\Models\PatientInfo;
use App\Models\Service;
use App\Models\UserAccount;
use App\Services\ActivityLogService;
use App\Services\AppointmentReminderService;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WalkInController extends Controller
{
    use BuildsBookingCalendar;

    public function __construct(
        protected NotificationService $notifications,
        protected ActivityLogService $activityLog,
        protected AppointmentReminderService $reminders
    )
    {
    }

    /** Appointment Bookings → Walk-in Patient: registers a brand-new walk-in patient. */
    public function index(Request $request)
    {
        return $this->panelView('walk-in', $this->calendarData($request) + ['mode' => 'walkin']);
    }

    /** Appointment Bookings → Follow-up Appointment: books an existing patient. */
    public function followUp(Request $request)
    {
        return $this->panelView('walk-in', $this->calendarData($request) + ['mode' => 'followup']);
    }

    /**
     * Read-only patient lookup used by Step 1's "Load Patient" search —
     * the app's only AJAX endpoint, kept deliberately small and scoped
     * so the wizard doesn't have to reload the page (which would lose
     * whatever the receptionist already typed).
     */
    public function searchPatient(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        if ($q === '') {
            return response()->json([]);
        }

        $patients = PatientInfo::with('userAccount')
            ->where(function ($query) use ($q) {
                $query->where('FirstName', 'like', "%{$q}%")
                    ->orWhere('LastName', 'like', "%{$q}%")
                    ->orWhereRaw("CONCAT(FirstName, ' ', LastName) LIKE ?", ["%{$q}%"]);

                if (is_numeric($q)) {
                    $query->orWhere('PatientID', (int) $q);
                }
            })
            ->orderBy('LastName')
            ->limit(10)
            ->get();

        return response()->json($patients->map(fn ($p) => [
            'PatientID' => $p->PatientID,
            'FirstName' => $p->FirstName,
            'LastName' => $p->LastName,
            'MiddleName' => $p->MiddleName,
            'DateOfBirth' => optional($p->DateOfBirth)->format('Y-m-d'),
            'Age' => optional($p->DateOfBirth)->age,
            'Gender' => $p->Gender,
            'Address' => $p->Address,
            'PhoneNumber' => $p->PhoneNumber,
            'Email' => $p->userAccount?->Email ?? $p->Email,
            'PhotoUrl' => $p->photo_url,
        ]));
    }

    public function store(Request $request)
    {
        // The two booking screens are separate now — the mode decides who the
        // patient is (never trust a client-supplied patient_source).
        $mode = $request->input('booking_mode') === 'followup' ? 'followup' : 'walkin';
        $request->merge(['patient_source' => $mode === 'followup' ? 'existing' : 'new']);

        $nameRule = "regex:/^[\pL\s'.-]+$/u";

        // A minor (under 18) has no phone/email of their own here — their
        // parent or guardian's details are collected instead.
        $birth = (string) $request->input('birthdate');
        $isMinor = $mode === 'walkin' && strtotime($birth) && Carbon::parse($birth)->age < 18;

        $data = $request->validate([
            'patient_source' => 'required|in:existing,new',
            'patient_id' => 'required_if:patient_source,existing|nullable|integer|exists:tbl_patientInfo,PatientID',
            'last_name' => ['required_if:patient_source,new', 'nullable', 'string', 'max:100', $nameRule],
            'first_name' => ['required_if:patient_source,new', 'nullable', 'string', 'max:100', $nameRule],
            'middle_name' => ['nullable', 'string', 'max:100', $nameRule],
            'birthdate' => 'required_if:patient_source,new|nullable|date|before_or_equal:2023-12-31',
            'gender' => 'required_if:patient_source,new|nullable|string',
            'address' => 'required_if:patient_source,new|nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => $isMinor ? 'nullable|digits:11' : 'required_if:patient_source,new|nullable|digits:11',
            'guardian_name' => $isMinor ? ['required', 'string', 'max:150', $nameRule] : ['nullable', 'string', 'max:150', $nameRule],
            'guardian_occupation' => ['nullable', 'string', 'max:150', $nameRule],
            'guardian_phone' => $isMinor ? 'required|digits:11' : 'nullable|digits:11',
            'guardian_email' => 'nullable|email|max:255',
            'service_ids' => 'required|array|min:1',
            'service_ids.*' => 'distinct|exists:tbl_services,ServiceID',
            // A walk-in is booked automatically for "now" — only a follow-up
            // picks a date and time from the calendar.
            'date' => $mode === 'followup' ? 'required|date' : 'nullable',
            'time' => $mode === 'followup' ? 'required|string' : 'nullable',
            'dentist_id' => 'required|integer',
        ], [
            'phone.digits' => 'Mobile number must be exactly 11 digits.',
            'guardian_phone.digits' => "Parent/guardian's contact number must be exactly 11 digits.",
            'guardian_name.required' => "A minor needs a parent/guardian's name.",
            'guardian_phone.required' => "A minor needs a parent/guardian's contact number.",
            'guardian_name.regex' => "Parent/guardian's name may only contain letters.",
            'guardian_occupation.regex' => "Parent/guardian's occupation may only contain letters.",
            'last_name.regex' => 'Last name may only contain letters.',
            'first_name.regex' => 'First name may only contain letters.',
            'middle_name.regex' => 'Middle name may only contain letters.',
        ]);

        $dentist = UserAccount::dentists()->where('UserID', $data['dentist_id'])->first();

        if (!$dentist) {
            return back()->withInput()->with('error', 'Please choose a dentist for this appointment.')
                ->with('walkin_error_step', 2);
        }

        // One active appointment at a time — only meaningful for an existing
        // patient, since a "new" one can't already have an appointment.
        if ($data['patient_source'] === 'existing'
            && Appointment::where('PatientID', $data['patient_id'])->whereIn('Status', ['Pending', 'Approved'])->exists()) {
            return back()->withInput()->with('error', 'This patient already has an upcoming appointment. Please wait until it\'s completed, or cancel it, before booking another.')
                ->with('walkin_error_step', 2);
        }

        $date = $mode === 'followup' ? Carbon::parse($data['date']) : today();

        if ($date->isSunday() || $date->lt(today())) {
            return back()->withInput()->with('error', $mode === 'followup' ? 'That date is not available for booking.' : "The clinic is closed on Sundays — walk-ins can't be booked today.")
                ->with('walkin_error_step', 2);
        }

        if ($mode === 'followup') {
            // A same-day slot whose start time has already gone by can't be
            // booked either — the calendar hides these, but the check belongs
            // here too since this is what actually decides what gets created.
            if (Carbon::parse($data['date'] . ' ' . $data['time'])->lt(now())) {
                return back()->withInput()->with('error', 'That time has already passed today. Please choose an upcoming time.')
                    ->with('walkin_error_step', 2);
            }

            if ($date->gt(today()->addMonths(2))) {
                return back()->withInput()->with('error', 'Appointments can only be booked up to 2 months in advance.')
                    ->with('walkin_error_step', 2);
            }
        }

        $user = null;
        $patientInfo = null;
        $isNewPatient = false;
        $hasEmail = false;

        try {
            DB::beginTransaction();

            if ($data['patient_source'] === 'existing') {
                $patientInfo = PatientInfo::with('userAccount')->findOrFail($data['patient_id']);
                $user = $patientInfo->userAccount;
            } else {
                // Walk-ins are recorded by staff, not the patient themselves —
                // no tbl_useraccount row is created for them. Creating one here
                // used to squat on the patient's real email, which then blocked
                // them from ever signing up for a real account with it.
                $hasEmail = !$isMinor && filled($data['email'] ?? null);

                $patientInfo = PatientInfo::create([
                    'UserID' => null,
                    'IsWalkIn' => true,
                    'LastName' => $data['last_name'],
                    'FirstName' => $data['first_name'],
                    'MiddleName' => $data['middle_name'] ?? null,
                    // The column is required, so a minor's record carries the
                    // parent/guardian's number as its contact number.
                    'PhoneNumber' => $isMinor ? $data['guardian_phone'] : $data['phone'],
                    'Email' => $hasEmail ? $data['email'] : null,
                    'ParentsName' => $isMinor ? $data['guardian_name'] : null,
                    'ParentsOccupation' => $isMinor ? ($data['guardian_occupation'] ?? null) : null,
                    'ParentsContactNumber' => $isMinor ? $data['guardian_phone'] : null,
                    'ParentsEmail' => $isMinor ? ($data['guardian_email'] ?? null) : null,
                    'DateOfBirth' => $data['birthdate'],
                    'Age' => Carbon::parse($data['birthdate'])->age,
                    'Gender' => $data['gender'],
                    'Address' => $data['address'],
                    'Nationality' => 'Filipino',
                ]);

                $isNewPatient = true;
            }

            $services = Service::whereIn('ServiceID', $data['service_ids'])->get();
            $slotsNeeded = $this->slotsNeededForServices($services);

            // Reserve every slot the total service duration needs — either
            // the whole block flips to Not Available, or none of it does.
            // Same pattern as AppointmentBookingController::store().
            if ($mode === 'walkin') {
                // No calendar: the visit starts now, in whichever half-hour
                // slot the current time falls inside, and runs for the full
                // service duration (skipping the lunch break).
                [$data['date'], $data['time'], $scheduleRows] = $this->reserveWalkInBlock($slotsNeeded, $dentist->UserID);
            } else {
                $scheduleRows = $this->reserveSlotBlock($data['date'], $data['time'], $slotsNeeded, $dentist->UserID);
            }

            $appointment = Appointment::create([
                'PatientID' => $patientInfo->PatientID,
                'DentistID' => $dentist->UserID,
                'ScheduleID' => $scheduleRows[0]->ScheduleID,
                'ServiceID' => $services->first()?->ServiceID,
                'AppointmentDate' => $data['date'],
                'AppointmentTime' => $data['time'],
                'TypeOfAppointment' => $services->pluck('ServiceName')->implode(', ') ?: null,
                'DurationHours' => round($slotsNeeded * DentistSchedule::SLOT_MINUTES / 60, 1),
                // A follow-up for a patient with an account is an online-patient
                // appointment (they see it in their portal); everyone else is a
                // walk-in.
                'Source' => ($mode === 'followup' && $user) ? 'Online' : 'Walk-in',
                // Anything booked from the admin / super-admin side (walk-in or
                // follow-up) is booked by staff, so it is approved straight away —
                // only patient self-bookings go through Appointment Approval.
                'Status' => 'Approved',
                'ApprovedAt' => now(),
            ]);

            $appointment->services()->sync($services->pluck('ServiceID'));

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            $failureMessage = $e->getMessage() ?: 'Could not record the walk-in.';

            // Keep a record of why in the patient's notifications when they
            // have an account to see it — no email, they're at the counter.
            if ($user) {
                $this->notifications->notifyUser($user, 'Booking Failed', $failureMessage, 'danger', null, null, null, false);
            }

            return back()->withInput()->with('error', $failureMessage)->with('walkin_error_step', 2);
        }

        $timeLabel = Carbon::createFromFormat('H:i', $data['time'])->format('g:i A');
        $dateLabel = $date->format('F j, Y');
        // End time = the last reserved slot + one slot (lunch is never reserved).
        $endLabel = Carbon::createFromFormat('H:i', substr($scheduleRows[count($scheduleRows) - 1]->Time, 0, 5))
            ->addMinutes(DentistSchedule::SLOT_MINUTES)->format('g:i A');
        $patientName = trim($patientInfo->FirstName . ' ' . $patientInfo->LastName);

        // This is auto-approved the instant it's created, so it may already
        // sit inside a reminder window (e.g. a follow-up booked for later
        // today) — send that reminder right now instead of waiting for the
        // next scheduled tick. No-ops for a walk-in with no account.
        $this->reminders->checkOne($appointment);

        // Walk-in patients get nothing sent (no account, at the counter).
        // A follow-up goes through the same notification path as an online
        // booking: in-app notification + email, and it appears in the
        // patient's own Appointments pages (same PatientID).
        if ($mode === 'followup' && $user) {
            $this->notifications->notifyUser(
                $user,
                'Follow-up Appointment Booked',
                "A follow-up appointment for {$appointment->TypeOfAppointment} has been scheduled and approved for you on {$dateLabel} at {$timeLabel}.",
                'success',
                $appointment->AppointmentID,
                'Approved'
            );
        }

        $this->notifications->notifyAdmins(
            $mode === 'followup' ? 'New Follow-up Appointment' : 'New Walk-in',
            $mode === 'followup'
                ? "A follow-up appointment has been scheduled for {$patientName} — {$appointment->TypeOfAppointment}."
                : "A walk-in has been recorded for {$patientName} — {$appointment->TypeOfAppointment}.",
            'info',
            $appointment->AppointmentID,
            'Approved'
        );

        $performer = UserAccount::with('staffInfo')->find(session('user_id'));
        $performerName = $performer?->staffInfo
            ? trim($performer->staffInfo->FirstName . ' ' . $performer->staffInfo->LastName)
            : ($performer->Email ?? 'Staff');

        $this->activityLog->log(
            'Create',
            "{$performerName} recorded a " . ($mode === 'followup' ? 'follow-up appointment' : 'walk-in') . " for {$patientName} (" . ($isNewPatient ? 'new patient' : "Patient ID {$patientInfo->PatientID}") . ") with {$dentist->display_name} — {$dateLabel} at {$timeLabel}."
        );

        $durationLabel = DentistSchedule::formatSlotDuration($slotsNeeded);

        if ($mode === 'walkin') {
            return redirect()->route('appointments')->with('success', "Walk-in booked for {$patientName} — today, {$timeLabel} to {$endLabel} ({$durationLabel}).");
        }

        return redirect()->route('appointments')->with('success', "Follow-up booked for {$patientName} — {$dateLabel} at {$timeLabel} for {$durationLabel}. It's already approved.");
    }


    /**
     * Books a walk-in for "right now": the visit starts in the half-hour slot
     * the current time falls inside (11:12 → the 11:00 slot; 2:31 PM → 2:30)
     * and runs for the full service duration. Lunch is never a slot, so a long
     * service simply continues after it (11:00 + 3h → 11:00–12:00, 1:00–3:00).
     *
     * A walk-in can only be booked while the clinic is open: before opening,
     * during lunch, after closing and on Sundays there is no slot for "now"
     * and it is refused. If the dentist already holds that slot (or part of
     * the block), it is refused too — pick another dentist.
     *
     * @return array{0:string,1:string,2:array} [date, start time, reserved rows]
     * @throws \RuntimeException when the clinic is closed or the block is taken
     */
    protected function reserveWalkInBlock(int $slotsNeeded, int $dentistId): array
    {
        $today = today()->format('Y-m-d');
        $nowMinutes = now()->hour * 60 + now()->minute;

        $start = null;
        foreach (DentistSchedule::slotTimes() as $time) {
            [$h, $m] = array_map('intval', explode(':', $time));
            $slotStart = $h * 60 + $m;
            if ($nowMinutes >= $slotStart && $nowMinutes < $slotStart + DentistSchedule::SLOT_MINUTES) {
                $start = $time;
                break;
            }
        }

        if ($start === null) {
            throw new \RuntimeException(DentistSchedule::walkInClosedMessage());
        }

        return [$today, $start, $this->reserveSlotBlock($today, $start, $slotsNeeded, $dentistId)];
    }
}
