<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RedirectsToPatientRecord;
use App\Models\PatientRecord;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;

class PatientRecordsController extends Controller
{
    use RedirectsToPatientRecord;

    public function __construct(protected ActivityLogService $activityLog)
    {
    }

    protected function guard()
    {
        if (!session('user_id') || session('user_role') !== 'admin') {
            return redirect()->route('login')->with('login_error', 'Please log in as an administrator to continue.');
        }

        return null;
    }

    public function index(Request $request)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

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

        return view('superAdmin.patient-records', [
            'records' => $activeQuery->orderByDesc('created_at')->paginate(10, ['*'], 'page')->withQueryString(),
            'archivedRecords' => $archivedQuery->orderByDesc('created_at')->paginate(10, ['*'], 'archived_page')->withQueryString(),
            'search' => $search,
            'tab' => $tab,
        ]);
    }

    public function updateNotes(Request $request, $id)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

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
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $record = PatientRecord::with('patientInfo')->findOrFail($id);
        $record->update(['IsArchived' => true]);

        $name = $record->patientInfo ? trim($record->patientInfo->FirstName . ' ' . $record->patientInfo->LastName) : "record #{$id}";
        $this->activityLog->log('Archive', "Archived patient record for {$name}.");

        return redirect()->route('patientRecords')->with('success', 'Record archived.');
    }

    public function unarchive($id)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $record = PatientRecord::with('patientInfo')->findOrFail($id);
        $record->update(['IsArchived' => false]);

        $name = $record->patientInfo ? trim($record->patientInfo->FirstName . ' ' . $record->patientInfo->LastName) : "record #{$id}";
        $this->activityLog->log('Unarchive', "Unarchived patient record for {$name}.");

        return redirect()->route('patientRecords')->with('success', 'Record restored.');
    }
}
