<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RedirectsToPatientRecord;
use App\Models\PatientRecord;
use App\Models\UserAccount;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;

class PatientRecordsController extends Controller
{
    use RedirectsToPatientRecord;

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
            if (session('user_role') === 'admin') {
                return redirect()->route('patientRecords');
            }
            abort(403, 'Your patient profile is incomplete.');
        }

        $patientId = $user->patientInfo->PatientID;

        $records = PatientRecord::with(['service', 'odontogramTeeth', 'appointment.dentist.staffInfo'])
            ->where('PatientID', $patientId)
            ->where('IsArchived', false)
            ->orderByDesc('VisitDate')
            ->orderByDesc('VisitTime')
            ->paginate(10)
            ->withQueryString();

        return view('users.my-records', [
            'records' => $records,
            'patientInfo' => $user->patientInfo,
        ]);
    }

    public function index(Request $request)
    {
        $search = $request->query('search');

        $activeQuery = PatientRecord::with(['patientInfo', 'service', 'odontogramTeeth'])->where('IsArchived', false);
        $archivedQuery = PatientRecord::with(['patientInfo', 'service', 'odontogramTeeth'])->where('IsArchived', true);

        if ($search) {
            $filter = function ($q) use ($search) {
                $q->where('Service', 'like', "%{$search}%")
                    ->orWhereHas('patientInfo', function ($p) use ($search) {
                        $p->where('FirstName', 'like', "%{$search}%")
                            ->orWhere('LastName', 'like', "%{$search}%");
                    });
            };
            $activeQuery->where($filter);
            $archivedQuery->where($filter);
        }

        $tab = $request->query('tab') === 'archived' ? 'archived' : 'active';

        return $this->panelView('patient-records', [
            'records' => $activeQuery->orderByDesc('created_at')->paginate(10, ['*'], 'page')->withQueryString(),
            'archivedRecords' => $archivedQuery->orderByDesc('created_at')->paginate(10, ['*'], 'archived_page')->withQueryString(),
            'search' => $search,
            'tab' => $tab,
        ]);
    }

    public function updateNotes(Request $request, $id)
    {
        $data = $request->validate([
            'notes' => 'nullable|string|max:2000',
        ]);

        $record = PatientRecord::with('patientInfo')->findOrFail($id);
        $record->update(['Notes' => $data['notes'] ?? null]);

        $name = $record->patientInfo ? trim($record->patientInfo->FirstName . ' ' . $record->patientInfo->LastName) : "record #{$id}";
        $this->activityLog->log('Edit', "Edited patient record notes for {$name}.");

        return $this->redirectToRecord($request, $record->RecordID)->with('success', 'Patient record updated.');
    }

    public function archive($id)
    {
        $record = PatientRecord::with('patientInfo')->findOrFail($id);
        $record->update(['IsArchived' => true]);

        $name = $record->patientInfo ? trim($record->patientInfo->FirstName . ' ' . $record->patientInfo->LastName) : "record #{$id}";
        $this->activityLog->log('Archive', "Archived patient record for {$name}.");

        return redirect()->route('patientRecords')->with('success', 'Record archived.');
    }

    public function unarchive($id)
    {
        $record = PatientRecord::with('patientInfo')->findOrFail($id);
        $record->update(['IsArchived' => false]);

        $name = $record->patientInfo ? trim($record->patientInfo->FirstName . ' ' . $record->patientInfo->LastName) : "record #{$id}";
        $this->activityLog->log('Unarchive', "Unarchived patient record for {$name}.");

        return redirect()->route('patientRecords')->with('success', 'Record restored.');
    }
}
