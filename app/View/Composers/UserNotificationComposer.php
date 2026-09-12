<?php

namespace App\View\Composers;

use App\Models\Notification;
use App\Models\UserAccount;
use Illuminate\View\View;

class UserNotificationComposer
{
    public function compose(View $view): void
    {
        $userId = session('user_id');
        $isPatient = $userId && session('account_type') !== 'staff'
            && !in_array(session('user_role'), UserAccount::ADMIN_ROLES, true);

        // Avatar + display name for the navbar/portal account menu — the
        // patient's uploaded photo and "First Last" name, otherwise the
        // shared default avatar and their email. Available on every
        // user-facing page.
        $navUserPhoto = asset('images/default.png');
        $navUserName = session('user_email');
        if ($userId) {
            $account = UserAccount::with('patientInfo')->find($userId);
            $photo = $account?->patientInfo?->ProfilePicture;
            if ($photo) {
                $navUserPhoto = asset($photo);
            }
            $fullName = trim(($account?->patientInfo?->FirstName ?? '') . ' ' . ($account?->patientInfo?->LastName ?? ''));
            if ($fullName) {
                $navUserName = $fullName;
            }
        }
        $view->with(['navUserPhoto' => $navUserPhoto, 'navUserName' => $navUserName]);

        if (!$isPatient) {
            $view->with([
                'userNotifications' => collect(),
                'userUnreadCount' => 0,
                'userLatestNotifications' => collect(),
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
            // "Latest" tab in the all-notifications modal — the 10 most recent, no filter.
            'userLatestNotifications' => (clone $base)->orderByDesc('created_at')->limit(10)->get(),
            'userAllNotifications' => (clone $base)
                ->when($notifDate, fn ($q) => $q->whereDate('created_at', $notifDate))
                ->orderByDesc('created_at')
                ->limit(100)
                ->get(),
            'userNotifDate' => $notifDate,
        ]);
    }
}
