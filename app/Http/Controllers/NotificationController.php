<?php

namespace App\Http\Controllers;

use App\Models\Notification;
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

        return redirect()->back();
    }
}
