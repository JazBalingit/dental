<?php

namespace App\Http\Middleware;

use App\Models\UserAccount;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Logged-out pages only — the public landing page, Log In and Sign Up.
 *
 * Someone who is still signed in (e.g. closed the tab without logging out,
 * then typed the site address again) is taken back to their own side of
 * the system instead: the dashboard for staff / the super admin, the
 * Patient Portal for patients. The public pages only show again after
 * Log Out (or once the session expires).
 *
 * Can't loop: the targets only send someone to Log In when there is no
 * session or the role doesn't match, and homeRouteFor() picks the target
 * from that same role.
 */
class RedirectIfSignedIn
{
    public function handle(Request $request, Closure $next): Response
    {
        if (session('user_id')) {
            return redirect()->route(UserAccount::homeRouteFor(session('user_role')));
        }

        return $next($request);
    }
}
