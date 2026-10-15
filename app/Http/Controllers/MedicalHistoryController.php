<?php

namespace App\Http\Controllers;

use App\Models\MedicalHistory;
use App\Models\PatientInfo;
use App\Models\UserAccount;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;

/**
 * Patient Portal → Medical History: view and update your own medical
 * history, or that of someone you book for (your child…). Every save is a
 * new version; earlier ones stay on file for the clinic.
 */
class MedicalHistoryController extends Controller
{
    public function __construct(protected ActivityLogService $activityLog)
    {
    }

    public function show(Request $request)
    {
        [$user, $people] = $this->people();
        $patient = $people->firstWhere('PatientID', (int) $request->query('patient')) ?? $people->first();

        return view('users.medical-history', [
            'people' => $people,
            'patient' => $patient,
            'current' => $patient->latestMedicalHistory,
            'editing' => $request->boolean('edit') || !$patient->latestMedicalHistory,
        ]);
    }

    public function update(Request $request)
    {
        [$user, $people] = $this->people();
        $patient = $people->firstWhere('PatientID', (int) $request->input('patient_id'));
        abort_unless($patient, 404);

        $data = $request->validate(MedicalHistory::rules(), MedicalHistory::messages());

        MedicalHistory::record(
            $patient->PatientID,
            MedicalHistory::answersFrom($data['mh'], false, $patient->latestMedicalHistory?->Answers),
            'patient',
            $user,
            $user->patientInfo?->full_name
        );

        $whose = $patient->UserID === $user->UserID ? 'your' : "{$patient->full_name}'s";
        $this->activityLog->log('Edit', "Updated {$whose} medical history.", $user->UserID);

        return redirect()->route('myMedicalHistory', ['patient' => $patient->PatientID])
            ->with('success', ucfirst($whose) . ' medical history was updated. The clinic will see it on your next visit.');
    }

    /** The signed-in patient first, then everyone they book for. */
    protected function people(): array
    {
        $user = UserAccount::with('patientInfo.latestMedicalHistory')->findOrFail(session('user_id'));
        abort_unless($user->patientInfo, 403, 'Please complete your patient profile first.');

        $people = collect([$user->patientInfo])
            ->concat(PatientInfo::dependentsOf($user)->with('latestMedicalHistory')->orderBy('FirstName')->get());

        return [$user, $people];
    }
}
