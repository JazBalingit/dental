<?php

namespace App\Http\Controllers;

use App\Models\AppointmentAttachment;
use App\Models\UserAccount;

/**
 * Serves a booking photo. These are private medical images, so they're
 * never public files: only clinic staff (any admin-panel session) or the
 * patient who owns the appointment can open one.
 */
class AppointmentAttachmentController extends Controller
{
    public function show($id)
    {
        $attachment = AppointmentAttachment::with('appointment.patientInfo')->findOrFail($id);

        $isStaff = in_array(session('user_role'), UserAccount::ADMIN_ROLES, true) && !session('super_admin_setup');
        $isOwner = (int) $attachment->appointment?->patientInfo?->UserID === (int) session('user_id');

        abort_unless($isStaff || $isOwner, 404);

        return response($attachment->Data, 200, [
            'Content-Type' => $attachment->MimeType,
            'Content-Disposition' => 'inline; filename="' . addcslashes($attachment->FileName, '"\\') . '"',
            'Cache-Control' => 'private, max-age=3600',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
