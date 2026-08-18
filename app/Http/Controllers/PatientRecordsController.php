<?php

namespace App\Http\Controllers;

use App\Models\PatientRecord;
use Illuminate\Http\Request;

class PatientRecordsController extends Controller
{
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

        $activeQuery = PatientRecord::with(['patientInfo', 'service'])->where('IsArchived', false);
        $archivedQuery = PatientRecord::with(['patientInfo', 'service'])->where('IsArchived', true);

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

        return view('superAdmin.patient-records', [
            'records' => $activeQuery->orderByDesc('VisitDate')->orderByDesc('VisitTime')->paginate(10, ['*'], 'page')->withQueryString(),
            'archivedRecords' => $archivedQuery->orderByDesc('VisitDate')->orderByDesc('VisitTime')->paginate(10, ['*'], 'archived_page')->withQueryString(),
            'search' => $search,
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

        PatientRecord::findOrFail($id)->update(['Notes' => $data['notes'] ?? null]);

        return redirect()->route('patientRecords')->with('success', 'Patient record updated.');
    }

    public function archive($id)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        PatientRecord::findOrFail($id)->update(['IsArchived' => true]);

        return redirect()->route('patientRecords')->with('success', 'Record archived.');
    }

    public function unarchive($id)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        PatientRecord::findOrFail($id)->update(['IsArchived' => false]);

        return redirect()->route('patientRecords')->with('success', 'Record restored.');
    }
}
