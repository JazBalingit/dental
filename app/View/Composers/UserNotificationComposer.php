<?php

namespace App\View\Composers;

use App\Models\Notification;
use Illuminate\View\View;

class UserNotificationComposer
{
    public function compose(View $view): void
    {
        $userId = session('user_id');
        $isPatient = $userId && session('account_type') !== 'staff' && session('user_role') !== 'admin';

        if (!$isPatient) {
            $view->with([
                'userNotifications' => collect(),
                'userUnreadCount' => 0,
                'userAllNotifications' => collect(),
                'userNotifDate' => null,
            ]);

            return;
        }

        $base = Notification::where('UserID', $userId);

        $notifDate = request()->query('notif_date');

        $view->with([
            'userNotifications' => (clone $base)->orderByDesc('created_at')->limit(8)->get(),
            'userUnreadCount' => (clone $base)->where('IsRead', false)->count(),
            'userAllNotifications' => (clone $base)
                ->when($notifDate, fn ($q) => $q->whereDate('created_at', $notifDate))
                ->orderByDesc('created_at')
                ->limit(100)
                ->get(),
            'userNotifDate' => $notifDate,
        ]);
    }
}
