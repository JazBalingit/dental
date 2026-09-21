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

        // The notification list marks a card read in the background when it
        // opens the appointment details — no page change wanted.
        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        $isPatient = session('account_type') !== 'staff'
            && !in_array(session('user_role'), UserAccount::ADMIN_ROLES, true);

        // Patient-side notifications are always about an appointment, so send
        // them straight to their appointments page instead of just refreshing.
        if ($isPatient) {
            return redirect()->route('userAppointment');
        }

        return redirect()->back();
    }

    /**
     * The appointment details shown when a booked / approved / completed
     * notification is clicked, returned as an HTML fragment for the details
     * modal. Ownership is checked against the session, as in markRead().
     */
    public function details($id)
    {
        if (!session('user_id')) {
            abort(401);
        }

        $notification = Notification::with([
            'appointment.patientInfo',
            'appointment.service',
            'appointment.services',
            'appointment.dentist.staffInfo',
        ])
            ->where('NotificationID', $id)
            ->where('UserID', session('user_id'))
            ->first();

        abort_unless($notification && $notification->opensAppointmentDetails() && $notification->appointment, 404);

        return view('partials.notification-appointment-details', [
            'appointment' => $notification->appointment,
            'isAdminSide' => in_array(session('user_role'), UserAccount::ADMIN_ROLES, true),
        ]);
    }

    /**
     * Marks every one of the logged-in user's notifications as read — fired
     * via fetch() the moment they open the bell dropdown, so the unread
     * badge clears without navigating away from whatever page they're on.
     */
    public function markAllRead(Request $request)
    {
        if (!session('user_id')) {
            return response()->json(['ok' => false], 401);
        }

        Notification::where('UserID', session('user_id'))
            ->where('IsRead', false)
            ->update(['IsRead' => true]);

        return response()->json(['ok' => true]);
    }
}
