<?php

namespace App\Http\Controllers\Concerns;

use App\Models\PatientRecord;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Shared by the controllers behind the patient history "View" modal
 * (notes + odontogram). After a save we send the user back to the same
 * history page state they came from — same tab / search / page — with a
 * #viewModal<id> fragment so the record's modal re-opens automatically.
 *
 * The list state travels in a `ret[...]` hidden-input group on each form
 * (see partials/record-return-fields.blade.php).
 */
trait RedirectsToPatientRecord
{
    protected function redirectToRecord(Request $request, PatientRecord $record): RedirectResponse
    {
        $query = collect((array) $request->input('ret', []))
            ->only(['search', 'tab', 'page', 'archived_page'])
            ->filter(fn ($v) => $v !== null && $v !== '')
            ->all();

        return redirect()->to(
            route('patientRecords.history', ['patientId' => $record->PatientID] + $query) . '#viewModal' . $record->RecordID
        );
    }
}
