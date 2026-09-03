<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gate for every clinic-staff page. A session counts as "admin" when it
 * has a user id and session('user_role') === 'admin' — which covers both
 * real staff/dentist accounts and the config-based super admin.
 *
 * This replaces the hand-copied guard() method that used to sit at the
 * top of every admin controller: the check now lives on the route, so a
 * new admin route can't accidentally ship without protection.
 */
class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!session('user_id') || session('user_role') !== 'admin') {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized.'], 401);
            }

            return redirect()->route('login')
                ->with('login_error', 'Please log in as an administrator to continue.');
        }

        return $next($request);
    }
}
