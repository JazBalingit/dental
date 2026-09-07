<?php

namespace App\View\Composers;

use App\Models\Notification;
use App\Models\UserAccount;
use Illuminate\View\View;

class AdminNotificationComposer
{
    public function compose(View $view): void
    {
        $userId = session('user_id');
        $isAdmin = $userId && in_array(session('user_role'), UserAccount::ADMIN_ROLES, true);

        // The sidebar account badge (partials/admin-profile-badge) renders on
        // every one of these pages, including the staff profile a dentist sees —
        // give it a real name (display_name = "Dr. First Last" / "First Last"),
        // not the raw email, on all of them regardless of the admin check below.
        $account = $userId ? UserAccount::with('staffInfo')->find($userId) : null;
        $email = $account?->Email ?? session('user_email');
        $name = $account?->display_name;

        // display_name falls back to the email when there's no StaffInfo row
        // (e.g. the claimed super admin). Turn "jahziel.hawan@cvsu.edu.ph" into
        // "Jahziel Hawan" so the badge never shows a raw address as the name.
        if (!$name || $name === $email) {
            $local = str_contains((string) $email, '@') ? strstr($email, '@', true) : (string) $email;
            $name = \Illuminate\Support\Str::of($local)->replace(['.', '_', '-'], ' ')->squish()->title()->value() ?: 'Account';
        }

        $view->with([
            'adminAccountName' => $name,
            'adminAccountEmail' => $email,
            // Staff profile photo for the sidebar badge; falls back to the
            // shared default avatar when there's no StaffInfo / no upload.
            'adminAccountPhoto' => $account?->staffInfo?->photo_url ?? asset('images/default.png'),
        ]);

        if (!$isAdmin) {
            $view->with([
                'adminNotifications' => collect(),
                'adminUnreadCount' => 0,
                'adminLatestNotifications' => collect(),
                'adminAllNotifications' => collect(),
                'adminNotifDate' => null,
            ]);

            return;
        }

        // Admin notifications are never reminder-type (those are user-only).
        $base = Notification::where('UserID', $userId)->whereNull('ReminderType');

        $notifDate = request()->query('notif_date');

        $view->with([
            'adminNotifications' => (clone $base)->orderByDesc('created_at')->limit(8)->get(),
            'adminUnreadCount' => (clone $base)->where('IsRead', false)->count(),
            // "Latest" tab in the all-notifications modal — the 10 most recent, no filter.
            'adminLatestNotifications' => (clone $base)->orderByDesc('created_at')->limit(10)->get(),
            'adminAllNotifications' => (clone $base)
                ->when($notifDate, fn ($q) => $q->whereDate('created_at', $notifDate))
                ->orderByDesc('created_at')
                ->limit(100)
                ->get(),
            'adminNotifDate' => $notifDate,
        ]);
    }
}
