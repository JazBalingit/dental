<?php

namespace App\Services;

use App\Mail\AppointmentStatusMail;
use App\Models\Notification;
use App\Models\UserAccount;
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

        if ($sendEmail) {
            Mail::to($user->Email)->send(new AppointmentStatusMail($title, $message));
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
