<?php
// Place in: app/Http/Controllers/StaffAccountController.php

namespace App\Http\Controllers;

use App\Models\StaffAccount;
use App\Models\StaffInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StaffAccountController extends Controller
{
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

        $activeQuery = StaffAccount::with('staffInfo')
            ->where('AccountStatus', 'Active');

        $archivedQuery = StaffAccount::with('staffInfo')
            ->where('AccountStatus', 'Archived');

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

        return view('admin.staff-accounts', [
            'staff' => $activeQuery->orderByDesc('DateCreated')->paginate(10, ['*'], 'page')->withQueryString(),
            'archivedStaff' => $archivedQuery->orderByDesc('DateCreated')->get(),
            'search' => $search,
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
            'birthdate' => 'required|date',
            'age' => 'nullable|integer|min:0|max:150',
            'gender' => 'required|string',
            'religion' => 'nullable|string|max:100',
            'nationality' => 'required|string|max:100',
            'role' => 'required|in:Dentist,Staff',
            'address' => 'required|string|max:255',
            'email' => 'required|email|unique:tbl_staffAcc,Email',
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $account = StaffAccount::create([
            'Email' => $data['email'],
            'Password' => Hash::make($data['password']),
            'Position' => $data['role'],
            'DateCreated' => now(),
            'AccountStatus' => 'Active',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = 'staff_' . $account->StaffID . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images/profiles'), $filename);
            $photoPath = 'images/profiles/' . $filename;
        }

        StaffInfo::create([
            'StaffID' => $account->StaffID,
            'LastName' => $data['last_name'],
            'FirstName' => $data['first_name'],
            'MiddleName' => $data['middle_name'] ?? null,
            'PhoneNumber' => $data['phone'],
            'DateOfBirth' => $data['birthdate'],
            'Age' => $data['age'] ?? null,
            'Gender' => $data['gender'],
            'Religion' => $data['religion'] ?? null,
            'Nationality' => $data['nationality'],
            'Address' => $data['address'],
            'ProfilePicture' => $photoPath,
        ]);

        return redirect()->route('staffAcc')->with('success', 'Staff account created successfully.');
    }

    public function update(Request $request, $id)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $account = StaffAccount::findOrFail($id);
        $info = StaffInfo::where('StaffID', $account->StaffID)->firstOrFail();

        $data = $request->validate([
            'last_name' => 'required|string|max:100',
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'birthdate' => 'required|date',
            'age' => 'nullable|integer|min:0|max:150',
            'gender' => 'required|string',
            'religion' => 'nullable|string|max:100',
            'nationality' => 'required|string|max:100',
            'role' => 'required|in:Dentist,Staff',
            'address' => 'required|string|max:255',
            'email' => 'required|email|unique:tbl_staffAcc,Email,' . $account->StaffID . ',StaffID',
            'phone' => 'required|string|max:20',
            'password' => 'nullable|string|min:8|confirmed',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $account->Email = $data['email'];
        $account->Position = $data['role'];
        if (!empty($data['password'])) {
            $account->Password = Hash::make($data['password']);
        }
        $account->save();

        $updates = [
            'LastName' => $data['last_name'],
            'FirstName' => $data['first_name'],
            'MiddleName' => $data['middle_name'] ?? null,
            'PhoneNumber' => $data['phone'],
            'DateOfBirth' => $data['birthdate'],
            'Age' => $data['age'] ?? null,
            'Gender' => $data['gender'],
            'Religion' => $data['religion'] ?? null,
            'Nationality' => $data['nationality'],
            'Address' => $data['address'],
        ];

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = 'staff_' . $account->StaffID . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images/profiles'), $filename);
            $updates['ProfilePicture'] = 'images/profiles/' . $filename;
        }

        $info->update($updates);

        return redirect()->route('staffAcc')->with('success', 'Staff account updated successfully.');
    }

    public function archive($id)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        StaffAccount::where('StaffID', $id)->update(['AccountStatus' => 'Archived']);

        return redirect()->route('staffAcc')->with('success', 'Account archived. This staff member can no longer log in.');
    }

    public function unarchive($id)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        StaffAccount::where('StaffID', $id)->update(['AccountStatus' => 'Active']);

        return redirect()->route('staffAcc')->with('success', 'Account restored. This staff member can log in again.');
    }
}