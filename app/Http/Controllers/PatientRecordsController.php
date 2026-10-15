<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesArchiveReason;
use App\Http\Controllers\Concerns\RedirectsToPatientRecord;
use App\Models\Appointment;
use App\Models\MedicalHistory;
use App\Models\PatientInfo;
use App\Models\PatientRecord;
use App\Models\UserAccount;
use App\Services\ActivityLogService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PatientRecordsController extends Controller
{
    use HandlesArchiveReason, RedirectsToPatientRecord;

    public function __construct(protected ActivityLogService $activityLog)
    {
    }

    // Staff-side access control is the 'admin' route middleware (routes/web.php).
    // The patient-facing mine() action below is on the 'auth.session' group and
    // scopes strictly to the caller's own PatientID.

    /**
     * A patient's read-only view of their OWN dental history — every
     * completed visit, its treatment, the dentist's notes, and the
     * odontogram charted that day. Never shows another patient's data:
     * the query is pinned to the logged-in account's PatientID.
     */
    public function mine(Request $request)
    {
        $user = UserAccount::with('patientInfo')->find(session('user_id'));

        // Staff / dentist / super-admin accounts have no patient file — send
        // them to the full management screen instead.
        if (!$user || !$user->patientInfo) {
            if (in_array(session('user_role'), UserAccount::ADMIN_ROLES, true)) {
                return redirect()->route('patientRecords');
            }
            abort(403, 'Your patient profile is incomplete.');
        }

        // Your own records, or those of someone you booked for (your child…).
        $people = collect([$user->patientInfo])->concat(PatientInfo::dependentsOf($user)->orderBy('FirstName')->get());
        $patientId = (int) ($people->firstWhere('PatientID', (int) $request->query('patient'))?->PatientID ?? $user->patientInfo->PatientID);
        $dentistId = $request->query('dentist');

        // Every dentist who has ever treated this patient — options for the
        // "Filter by dentist" dropdown, independent of the current filter so
        // a chosen dentist doesn't vanish from the list once it's applied.
        $treatedByIds = Appointment::whereHas('patientRecord', fn ($q) => $q->where('PatientID', $patientId)->where('IsArchived', false))
            ->pluck('DentistID')
            ->unique();
        $dentistOptions = UserAccount::whereIn('UserID', $treatedByIds)->with('staffInfo')->get()->sortBy('display_name');

        $records = PatientRecord::with(['service', 'odontogramTeeth', 'appointment.dentist.staffInfo'])
            ->where('PatientID', $patientId)
            ->where('IsArchived', false)
            ->when($request->query('search'), fn ($q, $term) => $q->where('Service', 'like', "%{$term}%"))
            ->when($dentistId, fn ($q, $id) => $q->whereHas('appointment', fn ($a) => $a->where('DentistID', $id)))
            ->orderByDesc('VisitDate')
            ->orderByDesc('VisitTime')
            ->paginate(10)
            ->withQueryString();

        return view('users.my-records', [
            'search' => $request->query('search'),
            'dentistId' => $dentistId,
            'dentistOptions' => $dentistOptions,
            'records' => $records,
            'patientInfo' => $user->patientInfo,
            'people' => $people,
            'viewingPatientId' => $patientId,
        ]);
    }

    /**
     * One row per patient (not per visit). A patient sits under "Active"
     * while at least one of their records is unarchived, and under
     * "Archived" once every record they have is archived. Each visit lives
     * on the patient's own history page (history()).
     */
    public function index(Request $request)
    {
        $search = $request->query('search');

        $base = fn () => PatientInfo::with('userAccount')
            ->withCount('records as records_count')
            ->withMax('records as last_visit', 'VisitDate')
            ->withMax('records as last_record_at', 'created_at')
            // Preloads the last appointment date so PatientInfo::is_inactive
            // (Active / Inactive pill) needs no extra query per row.
            ->withMax('appointments', 'AppointmentDate')
            ->whereHas('records');

        $activeQuery = $base()->whereHas('records', fn ($q) => $q->where('IsArchived', false));
        // The reason typed when the patient's records were archived (kept on
        // the records themselves) — the most recent one.
        $patientTable = (new PatientInfo)->getTable();
        $archivedQuery = $base()->whereDoesntHave('records', fn ($q) => $q->where('IsArchived', false))
            ->addSelect([
                'archive_reason' => PatientRecord::select('ArchiveReason')
                    ->whereColumn('PatientID', "{$patientTable}.PatientID")
                    ->whereNotNull('ArchiveReason')->orderByDesc('ArchivedAt')->limit(1),
                'archived_at' => PatientRecord::select('ArchivedAt')
                    ->whereColumn('PatientID', "{$patientTable}.PatientID")
                    ->whereNotNull('ArchiveReason')->orderByDesc('ArchivedAt')->limit(1),
            ]);

        if ($search) {
            $filter = function ($q) use ($search) {
                $q->where('FirstName', 'like', "%{$search}%")
                    ->orWhere('LastName', 'like', "%{$search}%")
                    ->orWhere('Email', 'like', "%{$search}%")
                    ->orWhereHas('userAccount', fn ($u) => $u->where('Email', 'like', "%{$search}%"))
                    ->orWhereHas('records', fn ($r) => $r->where('Service', 'like', "%{$search}%"));
            };
            $activeQuery->where($filter);
            $archivedQuery->where($filter);
        }

        // Registered = has an account; Walk-in = recorded at the counter;
        // Guest = booked online without an account.
        $type = in_array($request->query('type'), ['registered', 'walkin', 'guest'], true) ? $request->query('type') : null;
        if ($type) {
            $byType = fn ($q) => match ($type) {
                'registered' => $q->whereNotNull('UserID'),
                'walkin' => $q->whereNull('UserID')->where('IsWalkIn', true),
                'guest' => $q->whereNull('UserID')->where('IsWalkIn', false),
            };
            $byType($activeQuery);
            $byType($archivedQuery);
        }

        // Visit date range: patients with at least one visit inside it (either end may be left blank).
        $from = strtotime((string) $request->query('from')) ? $request->query('from') : null;
        $to = strtotime((string) $request->query('to')) ? $request->query('to') : null;
        if ($from && $to && $from > $to) {
            [$from, $to] = [$to, $from];
        }
        if ($from || $to) {
            $inRange = function ($q) use ($from, $to) {
                if ($from) {
                    $q->whereDate('VisitDate', '>=', $from);
                }
                if ($to) {
                    $q->whereDate('VisitDate', '<=', $to);
                }
            };
            $activeQuery->whereHas('records', $inRange);
            $archivedQuery->whereHas('records', $inRange);
        }

        $tab = $request->query('tab') === 'archived' ? 'archived' : 'active';

        return $this->panelView('patient-records', [
            // Switching the Active/Archived pill is client-side only, so it
            // never lands in the request's query string on its own — force
            // it onto every page link so paging the Archived table doesn't
            // bounce you back to Active.
            'patients' => $activeQuery->orderByDesc('last_record_at')->paginate(10, ['*'], 'page')->withQueryString()
                ->appends(['tab' => 'active']),
            'archivedPatients' => $archivedQuery->orderByDesc('last_record_at')->paginate(10, ['*'], 'archived_page')->withQueryString()
                ->appends(['tab' => 'archived']),
            'search' => $search,
            'type' => $type,
            'from' => $from,
            'to' => $to,
            'tab' => $tab,
        ]);
    }

    /**
     * Printable Dental Record for one visit (staff side) — patient info,
     * appointment details, odontogram and notes. Shown inside the "Print
     * Preview" modal first; printed from there.
     */
    public function printRecord($id)
    {
        $record = PatientRecord::with(['patientInfo.userAccount', 'service', 'odontogramTeeth', 'appointment.dentist.staffInfo', 'appointment.services'])
            ->findOrFail($id);

        return $this->printView($record);
    }

    /** Same Dental Record, for a patient printing one of their OWN visits. */
    public function printMine($id)
    {
        $user = UserAccount::with('patientInfo')->find(session('user_id'));
        abort_unless($user?->patientInfo, 403);

        $allowed = PatientInfo::dependentsOf($user)->pluck('PatientID')->push($user->patientInfo->PatientID);
        $record = PatientRecord::with(['patientInfo.userAccount', 'service', 'odontogramTeeth', 'appointment.dentist.staffInfo', 'appointment.services'])
            ->whereIn('PatientID', $allowed)
            ->where('IsArchived', false)
            ->findOrFail($id);

        return $this->printView($record);
    }

    protected function printView(PatientRecord $record)
    {
        return view('patient-records.print', [
            'record' => $record,
            'patient' => $record->patientInfo,
            'appointment' => $record->appointment,
            'generatedAt' => now(),
        ]);
    }

    /** Every visit for one patient — the "Patient Record History" page. */
    public function history(Request $request, $patientId)
    {
        $patient = PatientInfo::with('userAccount')->withMax('appointments', 'AppointmentDate')->findOrFail($patientId);
        $search = $request->query('search');

        // Individual visits can't be archived — only the patient as a whole
        // (archivePatient). So the history is one list of every visit on
        // file, whether or not the patient is currently archived.
        $query = PatientRecord::where('PatientID', $patient->PatientID);

        if ($search) {
            $query->where('Service', 'like', "%{$search}%");
        }

        return view('patient-records.history', [
            'patient' => $patient,
            'records' => $query->orderByDesc('VisitDate')->orderByDesc('VisitTime')->paginate(10, ['*'], 'page')->withQueryString(),
            'search' => $search,
            // Newest first — the first one is the patient's current history.
            'medicalVersions' => $patient->medicalHistories()->get(),
        ]);
    }

    /** Staff update a patient's medical history (saved as a new version). */
    public function updateMedical(Request $request, $patientId)
    {
        $patient = PatientInfo::with('latestMedicalHistory')->findOrFail($patientId);
        $data = $request->validate(MedicalHistory::rules(true), MedicalHistory::messages());

        MedicalHistory::record(
            $patient->PatientID,
            MedicalHistory::answersFrom($data['mh'], true, $patient->latestMedicalHistory?->Answers),
            'staff',
            UserAccount::current()
        );

        $this->activityLog->log('Edit', "Updated {$patient->full_name}'s medical history.");

        return redirect()->route('patientRecords.history', $patient->PatientID)->with('success', 'Medical history updated.');
    }

    public function updatePatient(Request $request, $patientId)
    {
        $patient = PatientInfo::with('userAccount')->findOrFail($patientId);

        $nameRule = "regex:/^[\pL\s'.-]+$/u";

        $data = $request->validate([
            'last_name' => ['required', 'string', 'max:100', $nameRule],
            'first_name' => ['required', 'string', 'max:100', $nameRule],
            'middle_name' => ['nullable', 'string', 'max:100', $nameRule],
            'birthdate' => 'required|date|before_or_equal:today',
            'gender' => 'required|in:male,female,other',
            'nationality' => ['required', 'string', 'max:100', $nameRule],
            'address' => 'required|string|max:255',
            'phone' => 'required|digits:11',
            // Registered patients' email belongs to their login account
            // (managed under User Accounts) — only walk-ins keep it here.
            'email' => [$patient->userAccount ? 'prohibited' : 'nullable', 'email', 'max:150'],
        ], [
            'phone.digits' => 'Mobile number must be exactly 11 digits.',
            'last_name.regex' => 'Last name may only contain letters.',
            'first_name.regex' => 'First name may only contain letters.',
            'middle_name.regex' => 'Middle name may only contain letters.',
            'nationality.regex' => 'Nationality may only contain letters.',
        ]);

        $updates = [
            'LastName' => $data['last_name'],
            'FirstName' => $data['first_name'],
            'MiddleName' => $data['middle_name'] ?? null,
            'PhoneNumber' => $data['phone'],
            'DateOfBirth' => $data['birthdate'],
            'Age' => Carbon::parse($data['birthdate'])->age,
            'Gender' => $data['gender'],
            'Nationality' => $data['nationality'],
            'Address' => $data['address'],
        ];
        if (!$patient->userAccount) {
            $updates['Email'] = $data['email'] ?? null;
        }

        $patient->update($updates);

        if (!$patient->wasChanged()) {
            return redirect()->route('patientRecords')->with('success', 'No changes to save.');
        }

        $this->activityLog->log('Edit', "Edited patient information for {$data['first_name']} {$data['last_name']}.");

        return redirect()->route('patientRecords')->with('success', 'Patient information updated.');
    }

    public function updateNotes(Request $request, $id)
    {
        $data = $request->validate([
            'notes' => 'nullable|string|max:2000',
            // Measured at this visit.
            'blood_pressure' => ['nullable', 'string', 'max:20', 'regex:/^\d{2,3}\s*\/\s*\d{2,3}$/'],
            'bleeding_time' => 'nullable|string|max:30',
        ], [
            'blood_pressure.regex' => 'Enter the blood pressure like 120/80.',
        ]);

        $record = PatientRecord::with('patientInfo')->findOrFail($id);
        $record->update([
            'Notes' => $data['notes'] ?? null,
            'BloodPressure' => isset($data['blood_pressure']) ? preg_replace('/\s+/', '', $data['blood_pressure']) : null,
            'BleedingTime' => $data['bleeding_time'] ?? null,
        ]);

        if (!$record->wasChanged()) {
            return $this->redirectToRecord($request, $record)->with('success', 'No changes to save.');
        }

        $this->activityLog->log('Edit', "Edited patient record notes for {$this->patientName($record)}.");

        return $this->redirectToRecord($request, $record)->with('success', 'Patient record updated.');
    }

    /** Archive every active visit for a patient (from the main list). */
    public function archivePatient(Request $request, $patientId)
    {
        $reason = $this->archiveReason($request);
        $patient = PatientInfo::findOrFail($patientId);
        PatientRecord::where('PatientID', $patient->PatientID)->where('IsArchived', false)->update($this->archivedState($reason));

        $this->activityLog->log('Archive', "Archived all patient records for {$patient->FirstName} {$patient->LastName}.");

        return redirect()->route('patientRecords')->with('success', 'Patient records archived.');
    }

    public function unarchivePatient($patientId)
    {
        $patient = PatientInfo::findOrFail($patientId);
        PatientRecord::where('PatientID', $patient->PatientID)->where('IsArchived', true)->update($this->restoredState());

        $this->activityLog->log('Unarchive', "Unarchived all patient records for {$patient->FirstName} {$patient->LastName}.");

        return redirect()->route('patientRecords', ['tab' => 'active'])->with('success', 'Patient records restored.');
    }

    private function patientName(PatientRecord $record): string
    {
        return $record->patientInfo
            ? trim($record->patientInfo->FirstName . ' ' . $record->patientInfo->LastName)
            : "record #{$record->RecordID}";
    }
}
