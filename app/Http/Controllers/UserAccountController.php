<?php

namespace App\Http\Controllers;

use App\Models\PatientInfo;
use App\Models\UserAccount;
use App\Services\ActivityLogService;
use App\Services\BookingService;
use Carbon\Carbon;
use App\Http\Controllers\Concerns\HandlesArchiveReason;
use App\Http\Controllers\Concerns\LinksPatientRecords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserAccountController extends Controller
{
    use HandlesArchiveReason, LinksPatientRecords;

    public function __construct(protected ActivityLogService $activityLog)
    {
    }

    // Access control (logged-in admin only) is the 'admin' route middleware.

    public function index(Request $request)
    {
        $search = $request->query('search');
        $tab = $request->query('tab') === 'archived' ? 'archived' : 'active';

        // withLastVisit preloads each patient's last clinic visit (latest
        // Completed appointment) in the same query, so PatientInfo::is_inactive
        // can classify every row without an extra query per patient.
        $withInactivity = fn ($q) => $q->withLastVisit();

        $activeQuery = UserAccount::with(['patientInfo' => $withInactivity])
            ->where('AccountType', 'User')
            ->whereNotIn('AccountRole', UserAccount::ADMIN_ROLES)
            ->where('IsArchived', false);

        $archivedQuery = UserAccount::with(['patientInfo' => $withInactivity])
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

        // Activity status — "Inactive" mirrors PatientInfo::is_inactive (has a
        // completed visit, none in the last 6 months); "Active" is everyone else.
        $status = in_array($request->query('status'), ['active', 'inactive'], true) ? $request->query('status') : null;
        if ($status) {
            $cutoff = now()->subMonths(6)->toDateString();
            $visits = fn ($a) => $a->where('Status', 'Completed');
            $recent = fn ($a) => $a->where('Status', 'Completed')->whereDate('AppointmentDate', '>=', $cutoff);
            $statusFilter = function ($q) use ($status, $visits, $recent) {
                if ($status === 'inactive') {
                    $q->whereHas('patientInfo', fn ($p) => $p->whereHas('appointments', $visits)->whereDoesntHave('appointments', $recent));
                } else {
                    $q->where(function ($w) use ($visits, $recent) {
                        $w->whereDoesntHave('patientInfo')
                            ->orWhereHas('patientInfo', fn ($p) => $p->whereDoesntHave('appointments', $visits)->orWhereHas('appointments', $recent));
                    });
                }
            };
            $activeQuery->where($statusFilter);
            $archivedQuery->where($statusFilter);
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
            'status' => $status,
            'tab' => $tab,
        ]);
    }

    /**
     * An admin creates a patient account with the same details and rules as
     * self sign-up (RegisterController::store). The account is saved right
     * away but unverified — EnsurePatientIsVerified keeps the patient on the
     * verify-email screen until they confirm the address with a code, the
     * same way a new staff account works.
     */
    public function store(Request $request)
    {
        $nameRule = "regex:/^[\pL\s'.-]+$/u";

        $data = $request->validate([
            'last_name' => ['required', 'string', 'max:100', $nameRule],
            'first_name' => ['required', 'string', 'max:100', $nameRule],
            'middle_name' => ['nullable', 'string', 'max:100', $nameRule],
            'birthdate' => 'required|date|before_or_equal:' . BookingService::adultCutoff(),
            'gender' => 'required|string',
            'religion' => ['nullable', 'string', 'max:100', $nameRule],
            'nationality' => ['required', 'string', 'max:100', $nameRule],
            'occupation' => ['nullable', 'string', 'max:150', $nameRule],
            'address' => 'required|string|max:255',
            'email' => 'required|email|unique:tbl_useraccount,Email',
            'phone' => 'required|digits:11',
            'password' => ['required', 'confirmed', Password::defaults()],
        ], [
            'birthdate.before_or_equal' => 'The patient must be 18 or older to have an account. A parent or guardian can book for a minor from their own account using "Book for Someone Else".',
            'phone.digits' => 'Mobile number must be exactly 11 digits.',
            'last_name.regex' => 'Last name may only contain letters.',
            'first_name.regex' => 'First name may only contain letters.',
            'middle_name.regex' => 'Middle name may only contain letters.',
            'religion.regex' => 'Religion may only contain letters.',
            'nationality.regex' => 'Nationality may only contain letters.',
            'occupation.regex' => 'Occupation may only contain letters.',
        ]);

        $user = UserAccount::create([
            'Email' => $data['email'],
            'Password' => Hash::make($data['password']),
            'AccountRole' => 'patient',
            'AccountType' => 'User',
            'DateCreated' => now(),
            'IsArchived' => false,
            // Left null on purpose: the patient proves the email on first login.
            'EmailVerifiedAt' => null,
        ]);

        $linked = $this->linkExistingRecords($user, [
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
            'Occupation' => $data['occupation'] ?? null,
        ]);

        $this->activityLog->log('Create', "Added a new user account: {$data['first_name']} {$data['last_name']} ({$data['email']})."
            . ($linked ? " Linked {$linked} existing record(s) booked with this email." : ''));

        return redirect()->route('userAcc')->with('success', 'User account created. The patient must verify their email when they first log in.');
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
            // Email isn't edited here — it only changes through the
            // code-verified "Change email" flow (EmailChangeController).
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
            $updates['ProfilePicture'] = \App\Models\ProfilePhoto::storeUpload($request->file('photo'), $info->ProfilePicture);
        }

        $info->update($updates);

        if (!$info->wasChanged()) {
            return redirect()->route('userAcc')->with('success', 'No changes to save.');
        }

        $this->activityLog->log('Edit', "Edited user account: {$data['first_name']} {$data['last_name']} ({$account->Email}).");

        return redirect()->route('userAcc')->with('success', 'User account updated successfully.');
    }

    public function archive(Request $request, $id)
    {
        $reason = $this->archiveReason($request);
        $account = UserAccount::where('AccountType', 'User')->with('patientInfo')->find($id);
        UserAccount::where('AccountType', 'User')->where('UserID', $id)->update($this->archivedState($reason));

        $name = $account?->patientInfo ? trim($account->patientInfo->FirstName . ' ' . $account->patientInfo->LastName) : $account?->Email;
        $this->activityLog->log('Archive', "Archived user account: {$name}.");

        return redirect()->route('userAcc')->with('success', 'Account archived. This user can no longer log in.');
    }

    public function unarchive($id)
    {
        $account = UserAccount::where('AccountType', 'User')->with('patientInfo')->find($id);
        UserAccount::where('AccountType', 'User')->where('UserID', $id)->update($this->restoredState());

        $name = $account?->patientInfo ? trim($account->patientInfo->FirstName . ' ' . $account->patientInfo->LastName) : $account?->Email;
        $this->activityLog->log('Unarchive', "Unarchived user account: {$name}.");

        return redirect()->route('userAcc')->with('success', 'Account restored. This user can log in again.');
    }

    /**
     * Same current/new/confirm shape as StaffAccountController::updatePassword,
     * checked against the patient account's current password.
     */
    public function updatePassword(Request $request, $id)
    {
        $account = UserAccount::where('AccountType', 'User')
            ->whereNotIn('AccountRole', UserAccount::ADMIN_ROLES)
            ->with('patientInfo')
            ->findOrFail($id);

        $data = $request->validate([
            'current_password' => 'required|string',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        if (!Hash::check($data['current_password'], $account->Password)) {
            return redirect()->route('userAcc')
                ->withInput()
                ->with('password_error', 'Current password is incorrect.');
        }

        $account->Password = Hash::make($data['password']);
        $account->save();

        $name = $account->patientInfo ? trim($account->patientInfo->FirstName . ' ' . $account->patientInfo->LastName) : $account->Email;
        $this->activityLog->log('Edit', "Changed the password for user account: {$name}.");

        return redirect()->route('userAcc')->with('success', 'Password updated successfully.');
    }
}
