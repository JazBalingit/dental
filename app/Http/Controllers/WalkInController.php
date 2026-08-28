<?php
// Place in: app/Http/Controllers/WalkInController.php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\BuildsBookingCalendar;
use App\Models\Appointment;
use App\Models\DentistSchedule;
use App\Models\PatientInfo;
use App\Models\Service;
use App\Models\UserAccount;
use App\Services\AuditLogService;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WalkInController extends Controller
{
    use BuildsBookingCalendar;

    public function __construct(protected NotificationService $notifications, protected AuditLogService $auditLog)
    {
    }

    protected function guard()
    {
        if (!session('user_id') || !in_array(session('user_role'), ['admin', 'staff'], true)) {
            return redirect()->route('login')->with('login_error', 'Please log in to continue.');
        }

        return null;
    }

    public function index(Request $request)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        return view('superAdmin.walk-in', $this->calendarData($request));
    }

    /**
     * Read-only patient lookup used by Step 1's "Load Patient" search —
     * the app's only AJAX endpoint, kept deliberately small and scoped
     * so the wizard doesn't have to reload the page (which would lose
     * whatever the receptionist already typed).
     */
    public function searchPatient(Request $request)
    {
        if ($redirect = $this->guard()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

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
        ]));
    }

    public function store(Request $request)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $data = $request->validate([
            'patient_source' => 'required|in:existing,new',
            'patient_id' => 'required_if:patient_source,existing|nullable|integer|exists:tbl_patientInfo,PatientID',
            'last_name' => 'required_if:patient_source,new|nullable|string|max:100',
            'first_name' => 'required_if:patient_source,new|nullable|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'birthdate' => 'required_if:patient_source,new|nullable|date|before:today',
            'gender' => 'required_if:patient_source,new|nullable|string',
            'address' => 'required_if:patient_source,new|nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'required_if:patient_source,new|nullable|string|max:20',
            'service_ids' => 'required|array|min:1',
            'service_ids.*' => 'distinct|exists:tbl_services,ServiceID',
            'date' => 'required|date',
            'time' => 'required|string',
        ]);

        // One active appointment at a time — only meaningful for an existing
        // patient, since a "new" one can't already have an appointment.
        if ($data['patient_source'] === 'existing'
            && Appointment::where('PatientID', $data['patient_id'])->whereIn('Status', ['Pending', 'Approved'])->exists()) {
            return back()->withInput()->with('error', 'This patient already has an upcoming appointment. Please wait until it\'s completed, or cancel it, before booking another.')
                ->with('walkin_error_step', 2);
        }

        $date = Carbon::parse($data['date']);

        if ($date->isSunday() || $date->lt(today())) {
            return back()->withInput()->with('error', 'That date is not available for booking.')
                ->with('walkin_error_step', 2);
        }

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
                $hasEmail = filled($data['email'] ?? null);

                $patientInfo = PatientInfo::create([
                    'UserID' => null,
                    'IsWalkIn' => true,
                    'LastName' => $data['last_name'],
                    'FirstName' => $data['first_name'],
                    'MiddleName' => $data['middle_name'] ?? null,
                    'PhoneNumber' => $data['phone'],
                    'Email' => $hasEmail ? $data['email'] : null,
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
            $scheduleRows = $this->reserveSlotBlock($data['date'], $data['time'], $slotsNeeded);

            $appointment = Appointment::create([
                'PatientID' => $patientInfo->PatientID,
                'ScheduleID' => $scheduleRows[0]->ScheduleID,
                'ServiceID' => $services->first()?->ServiceID,
                'AppointmentDate' => $data['date'],
                'AppointmentTime' => $data['time'],
                'TypeOfAppointment' => $services->pluck('ServiceName')->implode(', ') ?: null,
                'DurationHours' => round($slotsNeeded * DentistSchedule::SLOT_MINUTES / 60, 1),
                'Source' => 'Walk-in',
                'Status' => 'Pending',
            ]);

            $appointment->services()->sync($services->pluck('ServiceID'));

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            $failureMessage = $e->getMessage() ?: 'Could not create the walk-in appointment.';

            // Keep a record of why in the patient's notifications when they
            // have an account to see it — no email, they're at the counter.
            if ($user) {
                $this->notifications->notifyUser($user, 'Booking Failed', $failureMessage, 'danger', null, null, null, false);
            }

            return back()->withInput()->with('error', $failureMessage)->with('walkin_error_step', 2);
        }

        $timeLabel = Carbon::createFromFormat('H:i', $data['time'])->format('g:i A');
        $dateLabel = $date->format('F j, Y');
        $patientName = trim($patientInfo->FirstName . ' ' . $patientInfo->LastName);

        if ($user) {
            $this->notifications->notifyUser(
                $user,
                'Walk-in Appointment Recorded',
                "A walk-in appointment for {$appointment->TypeOfAppointment} was recorded on {$dateLabel} at {$timeLabel}.",
                'info',
                $appointment->AppointmentID,
                'Pending',
                null,
                $isNewPatient ? $hasEmail : true
            );
        }

        $this->notifications->notifyAdmins(
            'New Walk-in Appointment',
            "{$patientName} was walked in for {$appointment->TypeOfAppointment}.",
            'info',
            $appointment->AppointmentID,
            'Pending'
        );

        $performer = UserAccount::with('staffInfo')->find(session('user_id'));
        $performerName = $performer?->staffInfo
            ? trim($performer->staffInfo->FirstName . ' ' . $performer->staffInfo->LastName)
            : ($performer->Email ?? 'Staff');

        $this->auditLog->log(
            'Create',
            "{$performerName} recorded a walk-in appointment for {$patientName} (" . ($isNewPatient ? 'new patient' : "Patient ID {$patientInfo->PatientID}") . ") — {$dateLabel} at {$timeLabel}."
        );

        $durationLabel = DentistSchedule::formatSlotDuration($slotsNeeded);

        return redirect()->route('appointmentApproval')->with('success', "Walk-in appointment recorded for {$patientName} — {$dateLabel} at {$timeLabel} for {$durationLabel}. It's now pending approval.");
    }
}
