<?php
// Place in: app/Http/Controllers/StaffAccountController.php

namespace App\Http\Controllers;

use App\Models\StaffInfo;
use App\Models\UserAccount;
use App\Services\ActivityLogService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class StaffAccountController extends Controller
{
    public function __construct(protected ActivityLogService $activityLog)
    {
    }

    // Access control (logged-in super admin only) is enforced by the
    // 'admin' + 'superadmin' route middleware — see routes/web.php.

    public function index(Request $request)
    {
        $search = $request->query('search');
        $tab = $request->query('tab') === 'archived' ? 'archived' : 'active';

        $activeQuery = UserAccount::with('staffInfo')
            ->where('AccountType', 'Staff')
            ->where('IsArchived', false);

        $archivedQuery = UserAccount::with('staffInfo')
            ->where('AccountType', 'Staff')
            ->where('IsArchived', true);

        if ($search) {
            $filter = function ($q) use ($search) {
                $q->where('Email', 'like', "%{$search}%")
                    ->orWhereHas('staffInfo', function ($si) use ($search) {
                        $si->where('FirstName', 'like', "%{$search}%")
                            ->orWhere('LastName', 'like', "%{$search}%");
                    });
            };
            $activeQuery->where($filter);
            $archivedQuery->where($filter);
        }

        return view('superAdmin.staff-accounts', [
            // Switching the Active/Archived pill is client-side only, so it
            // never lands in the request's query string on its own — force
            // it onto every page link so paging the Archived table doesn't
            // bounce you back to Active.
            'staff' => $activeQuery->orderByDesc('DateCreated')->paginate(10, ['*'], 'page')->withQueryString()
                ->appends(['tab' => 'active']),
            'archivedStaff' => $archivedQuery->orderByDesc('DateCreated')->paginate(10, ['*'], 'archived_page')->withQueryString()
                ->appends(['tab' => 'archived']),
            'search' => $search,
            'tab' => $tab,
        ]);
    }

    public function store(Request $request)
    {
        $nameRule = "regex:/^[\pL\s'.-]+$/u";

        $data = $request->validate([
            'last_name' => ['required', 'string', 'max:100', $nameRule],
            'first_name' => ['required', 'string', 'max:100', $nameRule],
            'middle_name' => ['nullable', 'string', 'max:100', $nameRule],
            'birthdate' => 'required|date|before_or_equal:' . now()->subYears(18)->year . '-12-31',
            'gender' => 'required|string',
            'religion' => ['nullable', 'string', 'max:100', $nameRule],
            'nationality' => ['required', 'string', 'max:100', $nameRule],
            'role' => 'required|in:Dentist,Staff',
            'address' => 'required|string|max:255',
            'email' => 'required|email|unique:tbl_useraccount,Email',
            'phone' => 'required|digits:11',
            'password' => ['required', 'confirmed', Password::defaults()],
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ], [
            'phone.digits' => 'Mobile number must be exactly 11 digits.',
            'last_name.regex' => 'Last name may only contain letters.',
            'first_name.regex' => 'First name may only contain letters.',
            'middle_name.regex' => 'Middle name may only contain letters.',
            'religion.regex' => 'Religion may only contain letters.',
            'nationality.regex' => 'Nationality may only contain letters.',
        ]);

        $account = UserAccount::create([
            'Email' => $data['email'],
            'Password' => Hash::make($data['password']),
            'Position' => $data['role'],
            'AccountRole' => 'admin',
            'AccountType' => 'Staff',
            'DateCreated' => now(),
            'IsArchived' => false,
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = 'staff_' . $account->UserID . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images/profiles'), $filename);
            $photoPath = 'images/profiles/' . $filename;
        }

        StaffInfo::create([
            'UserID' => $account->UserID,
            'LastName' => $data['last_name'],
            'FirstName' => $data['first_name'],
            'MiddleName' => $data['middle_name'] ?? null,
            'PhoneNumber' => $data['phone'],
            'DateOfBirth' => $data['birthdate'],
            'Age' => Carbon::parse($data['birthdate'])->age,
            'Gender' => $data['gender'],
            'Religion' => $data['religion'] ?? null,
            'Nationality' => $data['nationality'],
            'Address' => $data['address'],
            'ProfilePicture' => $photoPath,
        ]);

        $this->activityLog->log('Create', "Added a new staff account: {$data['first_name']} {$data['last_name']} ({$data['email']}).");

        return redirect()->route('staffAcc')->with('success', 'Staff account created successfully.');
    }

    public function update(Request $request, $id)
    {
        $account = UserAccount::where('AccountType', 'Staff')->findOrFail($id);
        $info = StaffInfo::where('UserID', $account->UserID)->firstOrFail();

        $nameRule = "regex:/^[\pL\s'.-]+$/u";

        $data = $request->validate([
            'last_name' => ['required', 'string', 'max:100', $nameRule],
            'first_name' => ['required', 'string', 'max:100', $nameRule],
            'middle_name' => ['nullable', 'string', 'max:100', $nameRule],
            'birthdate' => 'required|date|before_or_equal:' . now()->subYears(18)->year . '-12-31',
            'gender' => 'required|string',
            'religion' => ['nullable', 'string', 'max:100', $nameRule],
            'nationality' => ['required', 'string', 'max:100', $nameRule],
            'role' => 'required|in:Dentist,Staff',
            'address' => 'required|string|max:255',
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
        ]);

        $account->Email = $data['email'];
        $account->Position = $data['role'];
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
            'Address' => $data['address'],
        ];

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = 'staff_' . $account->UserID . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images/profiles'), $filename);
            $updates['ProfilePicture'] = 'images/profiles/' . $filename;
        }

        $info->update($updates);

        $this->activityLog->log('Edit', "Edited staff account: {$data['first_name']} {$data['last_name']} ({$data['email']}).");

        return redirect()->route('staffAcc')->with('success', 'Staff account updated successfully.');
    }

    public function archive($id)
    {
        $account = UserAccount::where('AccountType', 'Staff')->with('staffInfo')->find($id);
        UserAccount::where('AccountType', 'Staff')->where('UserID', $id)->update(['IsArchived' => true]);

        $name = $account?->staffInfo ? trim($account->staffInfo->FirstName . ' ' . $account->staffInfo->LastName) : $account?->Email;
        $this->activityLog->log('Archive', "Archived staff account: {$name}.");

        return redirect()->route('staffAcc')->with('success', 'Account archived. This staff member can no longer log in.');
    }

    public function unarchive($id)
    {
        $account = UserAccount::where('AccountType', 'Staff')->with('staffInfo')->find($id);
        UserAccount::where('AccountType', 'Staff')->where('UserID', $id)->update(['IsArchived' => false]);

        $name = $account?->staffInfo ? trim($account->staffInfo->FirstName . ' ' . $account->staffInfo->LastName) : $account?->Email;
        $this->activityLog->log('Unarchive', "Unarchived staff account: {$name}.");

        return redirect()->route('staffAcc')->with('success', 'Account restored. This staff member can log in again.');
    }

    /**
     * Changing another person's password is a sensitive action, so it's
     * kept as its own step separate from the general edit form — same
     * current/new/confirm shape as StaffProfileController's self-service
     * password change, just checked against the target staff account's
     * current password instead of the acting admin's own.
     */
    public function updatePassword(Request $request, $id)
    {
        $account = UserAccount::where('AccountType', 'Staff')->findOrFail($id);

        $data = $request->validate([
            'current_password' => 'required|string',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        if (!Hash::check($data['current_password'], $account->Password)) {
            return redirect()->route('staffAcc')
                ->withInput()
                ->with('password_error', 'Current password is incorrect.');
        }

        $account->Password = Hash::make($data['password']);
        $account->save();

        $name = $account->staffInfo ? trim($account->staffInfo->FirstName . ' ' . $account->staffInfo->LastName) : $account->Email;
        $this->activityLog->log('Edit', "Changed the password for staff account: {$name}.");

        return redirect()->route('staffAcc')->with('success', 'Password updated successfully.');
    }
}
