<?php
// Place in: app/Http/Controllers/StaffAccountController.php

namespace App\Http\Controllers;

use App\Models\StaffInfo;
use App\Models\UserAccount;
use App\Services\AuditLogService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StaffAccountController extends Controller
{
    public function __construct(protected AuditLogService $auditLog)
    {
    }

    /**
     * Only a logged-in admin may access staff account management.
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
            'staff' => $activeQuery->orderByDesc('DateCreated')->paginate(10, ['*'], 'page')->withQueryString(),
            'archivedStaff' => $archivedQuery->orderByDesc('DateCreated')->paginate(10, ['*'], 'archived_page')->withQueryString(),
            'search' => $search,
            'tab' => $tab,
        ]);
    }

    public function store(Request $request)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $data = $request->validate([
            'last_name' => 'required|string|max:100',
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'birthdate' => 'required|date|before:today',
            'gender' => 'required|string',
            'religion' => 'nullable|string|max:100',
            'nationality' => 'required|string|max:100',
            'role' => 'required|in:Dentist,Staff',
            'address' => 'required|string|max:255',
            'email' => 'required|email|unique:tbl_useraccount,Email',
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
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

        $this->auditLog->log('Create', "Added a new staff account: {$data['first_name']} {$data['last_name']} ({$data['email']}).");

        return redirect()->route('staffAcc')->with('success', 'Staff account created successfully.');
    }

    public function update(Request $request, $id)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $account = UserAccount::where('AccountType', 'Staff')->findOrFail($id);
        $info = StaffInfo::where('UserID', $account->UserID)->firstOrFail();

        $data = $request->validate([
            'last_name' => 'required|string|max:100',
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'birthdate' => 'required|date|before:today',
            'gender' => 'required|string',
            'religion' => 'nullable|string|max:100',
            'nationality' => 'required|string|max:100',
            'role' => 'required|in:Dentist,Staff',
            'address' => 'required|string|max:255',
            'email' => 'required|email|unique:tbl_useraccount,Email,' . $account->UserID . ',UserID',
            'phone' => 'required|string|max:20',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
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

        $this->auditLog->log('Edit', "Edited staff account: {$data['first_name']} {$data['last_name']} ({$data['email']}).");

        return redirect()->route('staffAcc')->with('success', 'Staff account updated successfully.');
    }

    public function archive($id)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $account = UserAccount::where('AccountType', 'Staff')->with('staffInfo')->find($id);
        UserAccount::where('AccountType', 'Staff')->where('UserID', $id)->update(['IsArchived' => true]);

        $name = $account?->staffInfo ? trim($account->staffInfo->FirstName . ' ' . $account->staffInfo->LastName) : $account?->Email;
        $this->auditLog->log('Archive', "Archived staff account: {$name}.");

        return redirect()->route('staffAcc')->with('success', 'Account archived. This staff member can no longer log in.');
    }

    public function unarchive($id)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $account = UserAccount::where('AccountType', 'Staff')->with('staffInfo')->find($id);
        UserAccount::where('AccountType', 'Staff')->where('UserID', $id)->update(['IsArchived' => false]);

        $name = $account?->staffInfo ? trim($account->staffInfo->FirstName . ' ' . $account->staffInfo->LastName) : $account?->Email;
        $this->auditLog->log('Unarchive', "Unarchived staff account: {$name}.");

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
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $account = UserAccount::where('AccountType', 'Staff')->findOrFail($id);

        $data = $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if (!Hash::check($data['current_password'], $account->Password)) {
            return redirect()->route('staffAcc')->with('error', 'Current password is incorrect.');
        }

        $account->Password = Hash::make($data['password']);
        $account->save();

        $name = $account->staffInfo ? trim($account->staffInfo->FirstName . ' ' . $account->staffInfo->LastName) : $account->Email;
        $this->auditLog->log('Edit', "Changed the password for staff account: {$name}.");

        return redirect()->route('staffAcc')->with('success', 'Password updated successfully.');
    }
}
