<?php

namespace App\Console\Commands;

use App\Services\AppointmentExpiryService;
use App\Services\AppointmentReminderService;
use Illuminate\Console\Command;

class SendAppointmentReminders extends Command
{
    protected $signature = 'appointments:send-reminders';
    protected $description = 'Auto-cancel pending appointments that reached their time unapproved, then send each approved appointment its current reminder (day before / 1 hour / 20 minutes / now)';

    public function handle(AppointmentExpiryService $expiry, AppointmentReminderService $reminders): int
    {
        // A Pending appointment whose time has arrived was never approved —
        // cancel it (and free its slot) before anything else looks at it.
        $cancelled = $expiry->expireStalePending();
        $sent = $reminders->sendDue();

        $this->info("Auto-cancelled {$cancelled} unapproved appointment(s); sent {$sent} reminder(s).");

        return self::SUCCESS;
    }
}
