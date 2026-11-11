<?php

namespace App\Http\Controllers;

use App\Models\ProfilePhoto;

/**
 * Serves profile photos from the database. Any signed-in user may load one
 * (they appear in staff lists, patient lists and account menus); visitors
 * who aren't signed in get nothing.
 */
class ProfilePhotoController extends Controller
{
    public function show($id)
    {
        abort_unless(session('user_id') || session('super_admin_setup'), 404);

        $photo = ProfilePhoto::findOrFail($id);

        // A new upload gets a new ID (new URL), so the browser can cache this one.
        return response($photo->Data, 200, [
            'Content-Type' => $photo->MimeType,
            'Content-Length' => strlen($photo->Data),
            'Cache-Control' => 'private, max-age=604800',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
