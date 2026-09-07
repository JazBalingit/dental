<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\UserAccount;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Marks a single notification as read. Ownership is always checked
     * against the logged-in session — never a frontend-supplied user id.
     */
    public function markRead(Request $request, $id)
    {
        if (!session('user_id')) {
            return redirect()->route('login');
        }

        Notification::where('NotificationID', $id)
            ->where('UserID', session('user_id'))
            ->update(['IsRead' => true]);

        $isPatient = session('account_type') !== 'staff'
            && !in_array(session('user_role'), UserAccount::ADMIN_ROLES, true);

        // Patient-side notifications are always about an appointment, so send
        // them straight to their appointments page instead of just refreshing.
        if ($isPatient) {
            return redirect()->route('userAppointment');
        }

        return redirect()->back();
    }
}
