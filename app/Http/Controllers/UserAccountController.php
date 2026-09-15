<?php

namespace App\Http\Controllers;

use App\Models\PatientInfo;
use App\Models\UserAccount;
use App\Services\ActivityLogService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class UserAccountController extends Controller
{
    public function __construct(protected ActivityLogService $activityLog)
    {
    }

    // Access control (logged-in admin only) is the 'admin' route middleware.

    public function index(Request $request)
    {
        $search = $request->query('search');
        $tab = $request->query('tab') === 'archived' ? 'archived' : 'active';

        $activeQuery = UserAccount::with('patientInfo')
            ->where('AccountType', 'User')
            ->whereNotIn('AccountRole', UserAccount::ADMIN_ROLES)
            ->where('IsArchived', false);

        $archivedQuery = UserAccount::with('patientInfo')
            ->where('AccountType', 'User')
            ->whereNotIn('AccountRole', UserAccount::ADMIN_ROLES)
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

        return $this->panelView('user-accounts', [
            // Switching the Active/Archived pill is client-side only, so it
            // never lands in the request's query string on its own — force
            // it onto every page link so paging the Archived table doesn't
            // bounce you back to Active.
            'users' => $activeQuery->orderByDesc('DateCreated')->paginate(10, ['*'], 'page')->withQueryString()
                ->appends(['tab' => 'active']),
            'archivedUsers' => $archivedQuery->orderByDesc('DateCreated')->paginate(10, ['*'], 'archived_page')->withQueryString()
                ->appends(['tab' => 'archived']),
            'search' => $search,
            'tab' => $tab,
        ]);
    }

    public function update(Request $request, $id)
    {
        $account = UserAccount::where('AccountType', 'User')->findOrFail($id);
        $info = PatientInfo::where('UserID', $account->UserID)->firstOrFail();

        $nameRule = "regex:/^[\pL\s'.-]+$/u";

        $data = $request->validate([
            'last_name' => ['required', 'string', 'max:100', $nameRule],
            'first_name' => ['required', 'string', 'max:100', $nameRule],
            'middle_name' => ['nullable', 'string', 'max:100', $nameRule],
            'birthdate' => 'required|date|before_or_equal:2023-12-31',
            'gender' => 'required|string',
            'religion' => ['nullable', 'string', 'max:100', $nameRule],
            'nationality' => ['required', 'string', 'max:100', $nameRule],
            'occupation' => ['nullable', 'string', 'max:150', $nameRule],
            'address' => 'required|string|max:255',
            'guardian_name' => ['nullable', 'string', 'max:150', $nameRule],
            'guardian_occupation' => ['nullable', 'string', 'max:150', $nameRule],
            'email' => 'required|email|unique:tbl_useraccount,Email,' . $account->UserID . ',UserID',
            'phone' => 'required|digits:11',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ], [
            'phone.digits' => 'Mobile number must be exactly 11 digits.',
            'last_name.regex' => 'Last name may only contain letters.',
            'first_name.regex' => 'First name may only contain letters.',
            'middle_name.regex' => 'Middle name may only contain letters.',
            'religion.regex' => 'Religion may only contain letters.',
            'nationality.regex' => 'Nationality may only contain letters.',
            'occupation.regex' => 'Occupation may only contain letters.',
            'guardian_name.regex' => "Guardian's name may only contain letters.",
            'guardian_occupation.regex' => "Guardian's occupation may only contain letters.",
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

        $this->activityLog->log('Edit', "Edited user account: {$data['first_name']} {$data['last_name']} ({$data['email']}).");

        return redirect()->route('userAcc')->with('success', 'User account updated successfully.');
    }

    public function archive($id)
    {
        $account = UserAccount::where('AccountType', 'User')->with('patientInfo')->find($id);
        UserAccount::where('AccountType', 'User')->where('UserID', $id)->update(['IsArchived' => true]);

        $name = $account?->patientInfo ? trim($account->patientInfo->FirstName . ' ' . $account->patientInfo->LastName) : $account?->Email;
        $this->activityLog->log('Archive', "Archived user account: {$name}.");

        return redirect()->route('userAcc')->with('success', 'Account archived. This user can no longer log in.');
    }

    public function unarchive($id)
    {
        $account = UserAccount::where('AccountType', 'User')->with('patientInfo')->find($id);
        UserAccount::where('AccountType', 'User')->where('UserID', $id)->update(['IsArchived' => false]);

        $name = $account?->patientInfo ? trim($account->patientInfo->FirstName . ' ' . $account->patientInfo->LastName) : $account?->Email;
        $this->activityLog->log('Unarchive', "Unarchived user account: {$name}.");

        return redirect()->route('userAcc')->with('success', 'Account restored. This user can log in again.');
    }
}
