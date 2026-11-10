<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Appointment;
use App\Models\PatientInfo;
use App\Models\UserAccount;

/**
 * Shared by self sign-up (RegisterController) and admin-created patient
 * accounts (UserAccountController::store), so both end up with the same
 * patient file instead of a duplicate.
 */
trait LinksPatientRecords
{
    /**
     * Everything booked with this account's email becomes this account's:
     *
     *  - the patient record that IS this person (a guest booking or walk-in
     *    with this email and the same name or birthdate) gets the account
     *    attached and the new details — no duplicate patient, and their past
     *    visits, records and odontogram are in the portal right away;
     *  - people booked under this email (their children…) get this account as
     *    their guardian, so they show up under "Book for Someone Else";
     *  - appointments booked with this email are marked as booked by them.
     *
     * Returns how many existing records were linked.
     */
    protected function linkExistingRecords(UserAccount $user, array $details): int
    {
        $email = strtolower($user->Email);
        $candidates = PatientInfo::underEmail($email)->get();

        $self = $candidates
            ->filter(fn ($p) => !$p->isDependentOf($email))
            ->sortByDesc(fn ($p) => (int) (
                ($p->DateOfBirth?->format('Y-m-d') === $details['DateOfBirth'])
                + (strcasecmp($p->FirstName, $details['FirstName']) === 0 && strcasecmp($p->LastName, $details['LastName']) === 0)
            ))
            ->first(fn ($p) => $p->DateOfBirth?->format('Y-m-d') === $details['DateOfBirth']
                || (strcasecmp($p->FirstName, $details['FirstName']) === 0 && strcasecmp($p->LastName, $details['LastName']) === 0));

        if ($self) {
            $self->fill($details + ['UserID' => $user->UserID, 'Email' => $user->Email, 'IsWalkIn' => $self->IsWalkIn]);
            $self->save();
        } else {
            $self = PatientInfo::create($details + ['UserID' => $user->UserID]);
        }

        $dependents = $candidates->filter(fn ($p) => $p->PatientID !== $self->PatientID && $p->isDependentOf($email));
        foreach ($dependents as $dependent) {
            $dependent->GuardianUserID = $user->UserID;
            $dependent->GuardianEmail = $dependent->GuardianEmail ?: $user->Email;
            $dependent->save();
        }

        Appointment::whereNull('BookedByUserID')
            ->whereRaw('LOWER(BookedByEmail) = ?', [$email])
            ->update(['BookedByUserID' => $user->UserID]);

        return ($self->wasRecentlyCreated ? 0 : 1) + $dependents->count();
    }
}
