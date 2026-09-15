<?php
// Place in: app/Http/Controllers/ProfileController.php

namespace App\Http\Controllers;

use App\Models\PatientInfo;
use App\Models\UserAccount;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ProfileController extends Controller
{
    public function __construct(protected ActivityLogService $activityLog)
    {
    }

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
            'phone' => 'required|digits:11',
            'guardian_name' => ['nullable', 'string', 'max:150', $nameRule],
            'guardian_occupation' => ['nullable', 'string', 'max:150', $nameRule],
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

        // Work out which fields actually changed so the activity log can
        // name them ("Updated profile: First name, Address").
        $labels = [
            'LastName' => 'Last name', 'FirstName' => 'First name', 'MiddleName' => 'Middle name',
            'PhoneNumber' => 'Phone number', 'DateOfBirth' => 'Birthdate', 'Age' => 'Age',
            'Gender' => 'Gender', 'Religion' => 'Religion', 'Nationality' => 'Nationality',
            'Address' => 'Address', 'Occupation' => 'Occupation',
            'ParentsName' => "Parent/Guardian's name", 'ParentsOccupation' => "Parent/Guardian's occupation",
            'ProfilePicture' => 'Profile photo',
        ];
        $changed = [];
        foreach ($updates as $key => $value) {
            if ((string) ($patientInfo->getOriginal($key) ?? '') !== (string) ($value ?? '')) {
                $changed[] = $labels[$key] ?? $key;
            }
        }

        $patientInfo->update($updates);

        if ($changed) {
            $this->activityLog->log('Profile Updated', 'Updated profile: ' . implode(', ', $changed) . '.', $user->UserID);
        }

        return redirect()->route('settings', ['tab' => 'profile'])->with('profile_updated', true);
    }
}