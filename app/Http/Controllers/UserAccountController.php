<?php

namespace App\Http\Controllers;

use App\Models\PatientInfo;
use App\Models\UserAccount;
use App\Services\AuditLogService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class UserAccountController extends Controller
{
    public function __construct(protected AuditLogService $auditLog)
    {
    }

    /**
     * Only a logged-in admin may access user account management.
     */
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
        $tab = $request->query('tab') === 'archived' ? 'archived' : 'active';

        $activeQuery = UserAccount::with('patientInfo')
            ->where('AccountType', 'User')
            ->where('AccountRole', '!=', 'admin')
            ->where('IsArchived', false);

        $archivedQuery = UserAccount::with('patientInfo')
            ->where('AccountType', 'User')
            ->where('AccountRole', '!=', 'admin')
            ->where('IsArchived', true);

        if ($search) {
            $filter = function ($q) use ($search) {
                $q->where('Email', 'like', "%{$search}%")
                    ->orWhereHas('patientInfo', function ($pi) use ($search) {
                        $pi->where('FirstName', 'like', "%{$search}%")
                            ->orWhere('LastName', 'like', "%{$search}%");
                    });
            };
            $activeQuery->where($filter);
            $archivedQuery->where($filter);
        }

        return view('superAdmin.user-accounts', [
            'users' => $activeQuery->orderByDesc('DateCreated')->paginate(10, ['*'], 'page')->withQueryString(),
            'archivedUsers' => $archivedQuery->orderByDesc('DateCreated')->paginate(10, ['*'], 'archived_page')->withQueryString(),
            'search' => $search,
            'tab' => $tab,
        ]);
    }

    public function update(Request $request, $id)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $account = UserAccount::where('AccountType', 'User')->findOrFail($id);
        $info = PatientInfo::where('UserID', $account->UserID)->firstOrFail();

        $data = $request->validate([
            'last_name' => 'required|string|max:100',
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'birthdate' => 'required|date|before:today',
            'gender' => 'required|string',
            'religion' => 'nullable|string|max:100',
            'nationality' => 'required|string|max:100',
            'occupation' => 'nullable|string|max:150',
            'address' => 'required|string|max:255',
            'guardian_name' => 'nullable|string|max:150',
            'guardian_occupation' => 'nullable|string|max:150',
            'email' => 'required|email|unique:tbl_useraccount,Email,' . $account->UserID . ',UserID',
            'phone' => 'required|string|max:20',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $account->Email = $data['email'];
        $account->save();

        $updates = [
            'LastName' => $data['last_name'],
            'FirstName' => $data['first_name'],
            'MiddleName' => $data['middle_name'] ?? null,
            'PhoneNumber' => $data['phone'],
            'DateOfBirth' => $data['birthdate'],
            'Age' => Carbon::parse($data['birthdate'])->age,
            'Gender' => $data['gender'],
            'Religion' => $data['religion'] ?? null,
            'Nationality' => $data['nationality'],
            'Occupation' => $data['occupation'] ?? null,
            'Address' => $data['address'],
            'ParentsName' => $data['guardian_name'] ?? null,
            'ParentsOccupation' => $data['guardian_occupation'] ?? null,
        ];

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = 'patient_' . $account->UserID . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images/profiles'), $filename);
            $updates['ProfilePicture'] = 'images/profiles/' . $filename;
        }

        $info->update($updates);

        $this->auditLog->log('Edit', "Edited user account: {$data['first_name']} {$data['last_name']} ({$data['email']}).");

        return redirect()->route('userAcc')->with('success', 'User account updated successfully.');
    }

    public function archive($id)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $account = UserAccount::where('AccountType', 'User')->with('patientInfo')->find($id);
        UserAccount::where('AccountType', 'User')->where('UserID', $id)->update(['IsArchived' => true]);

        $name = $account?->patientInfo ? trim($account->patientInfo->FirstName . ' ' . $account->patientInfo->LastName) : $account?->Email;
        $this->auditLog->log('Archive', "Archived user account: {$name}.");

        return redirect()->route('userAcc')->with('success', 'Account archived. This user can no longer log in.');
    }

    public function unarchive($id)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $account = UserAccount::where('AccountType', 'User')->with('patientInfo')->find($id);
        UserAccount::where('AccountType', 'User')->where('UserID', $id)->update(['IsArchived' => false]);

        $name = $account?->patientInfo ? trim($account->patientInfo->FirstName . ' ' . $account->patientInfo->LastName) : $account?->Email;
        $this->auditLog->log('Unarchive', "Unarchived user account: {$name}.");

        return redirect()->route('userAcc')->with('success', 'Account restored. This user can log in again.');
    }
}
