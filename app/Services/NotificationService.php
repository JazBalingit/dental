<?php

namespace App\Services;

use App\Mail\AppointmentStatusMail;
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
                Mail::to($user->Email)->send(new AppointmentStatusMail($title, $message));
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
     * Create a notification for every admin-role account (super admin + staff,
     * since staff share the same admin dashboard/bell). Admin notifications
     * are DB-only — no email per spec.
     */
    public function notifyAdmins(
        string $title,
        string $message,
        string $type = 'info',
        ?int $appointmentId = null,
        ?string $status = null
    ): void {
        UserAccount::where('AccountRole', 'admin')->get()->each(
            fn (UserAccount $admin) => $this->notifyUser(
                $admin, $title, $message, $type, $appointmentId, $status, null, false
            )
        );
    }
}
