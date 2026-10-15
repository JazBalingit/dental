<?php

namespace App\Services;

use App\Mail\AppointmentStatusMail;
use App\Models\Appointment;
use App\Models\Notification;
use App\Models\UserAccount;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    /**
     * Create a notification for a single recipient, optionally emailing them.
     */
    public function notifyUser(
        UserAccount $user,
        string $title,
        string $message,
        string $type = 'info',
        ?int $appointmentId = null,
        ?string $status = null,
        ?string $reminderType = null,
        bool $sendEmail = true
    ): Notification {
        $notification = Notification::create([
            'UserID' => $user->UserID,
            'AppointmentID' => $appointmentId,
            'Title' => $title,
            'Message' => $message,
            'Type' => $type,
            'Status' => $status,
            'ReminderType' => $reminderType,
        ]);

        // Email is best-effort: the notification row above is the durable
        // record. A mail-transport failure (bad API key, unverified sender
        // domain, recipient rejected, provider down) must never bubble up
        // and break the caller's workflow — approving an appointment still
        // succeeds even if the patient's email bounces.
        if ($sendEmail && $user->Email) {
            try {
                // Every appointment email carries the full appointment details
                // (date, time, services, dentist…), not just the one-line message.
                $appointment = $appointmentId
                    ? Appointment::with(['patientInfo', 'service', 'services', 'dentist.staffInfo'])->find($appointmentId)
                    : null;

                // Patients get a "Manage my appointment" button (works from the
                // email without logging in); clinic staff don't need one.
                $manageUrl = $appointment && !in_array($user->AccountRole, UserAccount::ADMIN_ROLES, true)
                    ? $appointment->manage_url
                    : null;

                Mail::to($user->Email)->send(new AppointmentStatusMail($title, $message, $appointment, $manageUrl));
            } catch (\Throwable $e) {
                Log::warning('Notification email failed to send', [
                    'user_id' => $user->UserID,
                    'email' => $user->Email,
                    'title' => $title,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $notification;
    }

    /**
     * Tell the patient side about an appointment — whoever that is:
     *
     *  - the account that booked it (a parent who booked for their child),
     *    else the patient's own account, else the patient's guardian
     *    account → bell notification + email, same as notifyUser();
     *  - nobody with an account (guest booking, walk-in with an email) →
     *    email only, to the address that booked it, with a "Manage my
     *    appointment" link since they have no portal. Logged as a
     *    notification row with no UserID, which also keeps reminders from
     *    being sent twice.
     *
     * Returns null when there's nobody to reach (a walk-in with no email).
     */
    public function notifyPatient(
        Appointment $appointment,
        string $title,
        string $message,
        string $type = 'info',
        ?string $status = null,
        ?string $reminderType = null,
        bool $sendEmail = true
    ): ?Notification {
        $appointment->loadMissing(['patientInfo.userAccount', 'patientInfo.guardianAccount', 'bookedBy']);
        $patient = $appointment->patientInfo;
        $status ??= $appointment->Status;

        $user = $appointment->bookedBy
            ?? $patient?->userAccount
            ?? $patient?->guardianAccount;

        if ($user && !$user->IsArchived) {
            return $this->notifyUser($user, $title, $message, $type, $appointment->AppointmentID, $status, $reminderType, $sendEmail);
        }

        $email = $appointment->BookedByEmail
            ?: ($patient?->Email ?: ($patient?->GuardianEmail ?: $patient?->ParentsEmail));

        if (!$email) {
            return null;
        }

        $notification = Notification::create([
            'UserID' => null,
            'Email' => $email,
            'AppointmentID' => $appointment->AppointmentID,
            'Title' => $title,
            'Message' => $message,
            'Type' => $type,
            'Status' => $status,
            'ReminderType' => $reminderType,
            'IsRead' => true,
        ]);

        if ($sendEmail) {
            try {
                $fresh = Appointment::with(['patientInfo', 'service', 'services', 'dentist.staffInfo'])->find($appointment->AppointmentID);
                Mail::to($email)->send(new AppointmentStatusMail($title, $message, $fresh, $fresh?->manage_url));
            } catch (\Throwable $e) {
                Log::warning('Guest notification email failed to send', [
                    'appointment_id' => $appointment->AppointmentID,
                    'email' => $email,
                    'title' => $title,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $notification;
    }

    /**
     * Notify every active admin-panel account (super admin + staff + dentists,
     * who share the admin bell) — in-app AND by email, with the full
     * appointment details. Archived accounts and the un-claimed .env setup
     * login are skipped, and whoever triggered the event isn't emailed about
     * their own action (they still get the in-app notice).
     */
    public function notifyAdmins(
        string $title,
        string $message,
        string $type = 'info',
        ?int $appointmentId = null,
        ?string $status = null
    ): void {
        $actorId = (int) session('user_id');
        $setupEmail = config('superadmin.email');

        UserAccount::whereIn('AccountRole', UserAccount::ADMIN_ROLES)
            ->where('IsArchived', false)
            ->get()
            ->reject(fn (UserAccount $admin) => !$admin->IsSuperAdmin && $setupEmail && strcasecmp((string) $admin->Email, $setupEmail) === 0)
            ->each(fn (UserAccount $admin) => $this->notifyUser(
                $admin, $title, $message, $type, $appointmentId, $status, null,
                $admin->UserID !== $actorId
            ));
    }
}
