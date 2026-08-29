<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RedirectsToPatientRecord;
use App\Models\OdontogramTooth;
use App\Models\PatientRecord;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class OdontogramController extends Controller
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

    /**
     * Bulk-save the odontogram for one patient record. The whole chart is
     * submitted at once: teeth present in the payload are upserted, teeth
     * that were charted before but are now absent are cleared. History is
     * untouched — this only ever writes rows scoped to $recordId.
     */
    public function save(Request $request, $recordId)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $record = PatientRecord::with('patientInfo')->findOrFail($recordId);

        $data = $request->validate([
            'teeth'                 => 'nullable|array',
            'teeth.*.tooth'         => ['required', 'string', Rule::in(OdontogramTooth::FDI_TEETH)],
            'teeth.*.condition'     => ['required', 'string', Rule::in(array_keys(OdontogramTooth::CONDITIONS))],
            'teeth.*.surfaces'      => 'nullable|array',
            'teeth.*.surfaces.*'    => ['string', Rule::in(OdontogramTooth::SURFACES)],
            'teeth.*.description'   => 'nullable|string|max:1000',
        ]);

        // Last entry wins if the same tooth somehow appears twice.
        $byTooth = collect($data['teeth'] ?? [])->keyBy('tooth');

        DB::transaction(function () use ($byTooth, $record) {
            $keptIds = [];

            foreach ($byTooth as $toothNumber => $entry) {
                $surfaces = array_values(array_intersect(OdontogramTooth::SURFACES, $entry['surfaces'] ?? []));

                $tooth = OdontogramTooth::updateOrCreate(
                    ['RecordID' => $record->RecordID, 'ToothNumber' => (string) $toothNumber],
                    [
                        'PatientID'     => $record->PatientID,
                        'AppointmentID' => $record->AppointmentID,
                        'Condition'     => $entry['condition'],
                        'Surfaces'      => $surfaces ? implode(',', $surfaces) : null,
                        'Description'   => $entry['description'] ?? null,
                    ]
                );

                $keptIds[] = $tooth->OdontogramToothID;
            }

            OdontogramTooth::where('RecordID', $record->RecordID)
                ->whereNotIn('OdontogramToothID', $keptIds ?: [0])
                ->delete();
        });

        $name = $record->patientInfo
            ? trim($record->patientInfo->FirstName . ' ' . $record->patientInfo->LastName)
            : "record #{$recordId}";

        $this->activityLog->log(
            'Edit',
            "Updated the odontogram for {$name}'s visit on " . $record->VisitDate->format('M j, Y') . '.'
        );

        return $this->redirectToRecord($request, $record->RecordID)->with('success', 'Odontogram saved.');
    }
}
