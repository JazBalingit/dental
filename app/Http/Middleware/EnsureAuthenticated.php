<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gate for pages that just need *some* signed-in user — patient self-service
 * (settings, profile, my appointments, my records, booking) and the shared
 * "mark notification read" endpoint used by both patients and admins.
 *
 * Controllers still do their own data-scoped checks on top of this (e.g.
 * "does this appointment belong to you", "is your patient profile complete").
 */
class EnsureAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!session('user_id')) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect()->route('login')
                ->with('login_error', 'Please log in to continue.');
        }

        return $next($request);
    }
}
