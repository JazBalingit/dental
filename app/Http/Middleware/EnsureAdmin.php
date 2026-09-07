<?php

namespace App\Http\Middleware;

use App\Models\UserAccount;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gate for every clinic-staff page. A session counts as "admin" when it has
 * a user id and session('user_role') is one of UserAccount::ADMIN_ROLES
 * ('admin' or 'super admin') — which covers real staff/dentist accounts,
 * the claimed super admin, and the un-claimed .env bootstrap.
 *
 * This replaces the hand-copied guard() method that used to sit at the
 * top of every admin controller: the check now lives on the route, so a
 * new admin route can't accidentally ship without protection.
 */
class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!session('user_id') || !in_array(session('user_role'), UserAccount::ADMIN_ROLES, true)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized.'], 401);
            }

            return redirect()->route('login')
                ->with('login_error', 'Please log in as an administrator to continue.');
        }

        return $next($request);
    }
}
