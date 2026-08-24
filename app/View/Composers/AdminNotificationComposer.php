<?php

namespace App\View\Composers;

use App\Models\Notification;
use Illuminate\View\View;

class AdminNotificationComposer
{
    public function compose(View $view): void
    {
        $userId = session('user_id');
        $isAdmin = $userId && session('user_role') === 'admin';

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
