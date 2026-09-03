<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gate for the pages only the super admin may open — Staff Accounts and
 * Configuration. Assumes EnsureAdmin has already run (so we know this is
 * at least a logged-in admin); here we only reject a plain staff/dentist.
 */
class EnsureSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!session('is_super_admin')) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Forbidden.'], 403);
            }

            return redirect()->route('dashboard')
                ->with('error', 'That page is only available to the super admin.');
        }

        return $next($request);
    }
}
