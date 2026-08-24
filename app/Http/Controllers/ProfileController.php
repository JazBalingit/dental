<?php
// Place in: app/Http/Controllers/ProfileController.php

namespace App\Http\Controllers;

use App\Models\PatientInfo;
use App\Models\UserAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ProfileController extends Controller
{
    public function update(Request $request)
    {
        if (!session('user_id')) {
            return redirect()->route('login');
        }

        $user = UserAccount::findOrFail(session('user_id'));
        $patientInfo = PatientInfo::where('UserID', $user->UserID)->firstOrFail();

        // Note: UserID, DateCreated, and Email are intentionally NOT in
        // this validation list — they belong to tbl_useraccount and are
        // never editable from this form.
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
            'phone' => 'required|string|max:20',
            'guardian_name' => 'nullable|string|max:150',
            'guardian_occupation' => 'nullable|string|max:150',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Age is recomputed from birthdate every time, same as at signup.
        $age = Carbon::parse($data['birthdate'])->age;

        $updates = [
            'LastName' => $data['last_name'],
            'FirstName' => $data['first_name'],
            'MiddleName' => $data['middle_name'] ?? null,
            'PhoneNumber' => $data['phone'],
            'DateOfBirth' => $data['birthdate'],
            'Age' => $age,
            'Gender' => $data['gender'],
            'Religion' => $data['religion'] ?? null,
            'Nationality' => $data['nationality'],
            'Address' => $data['address'],
            'Occupation' => $data['occupation'] ?? null,
            'ParentsName' => $data['guardian_name'] ?? null,
            'ParentsOccupation' => $data['guardian_occupation'] ?? null,
        ];

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = 'patient_' . $patientInfo->PatientID . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images/profiles'), $filename);
            $updates['ProfilePicture'] = 'images/profiles/' . $filename;
        }

        $patientInfo->update($updates);

        return redirect()->route('settings', ['tab' => 'profile'])->with('profile_updated', true);
    }
}