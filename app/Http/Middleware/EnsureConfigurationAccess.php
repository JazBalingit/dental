<?php

namespace App\Http\Middleware;

use App\Models\UserAccount;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gate for Configuration — the super admin, or an account with the Staff
 * role. A Dentist is turned away. Assumes EnsureAdmin has already run.
 * (Activity Logs inside Configuration stay behind EnsureSuperAdmin.)
 */
class EnsureConfigurationAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if (session('super_admin_setup')) {
            return redirect()->route('superAdminSetup');
        }

        if (!UserAccount::currentCanManageConfiguration()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Forbidden.'], 403);
            }

            return redirect()->route('dashboard')
                ->with('error', 'That page is only available to staff and the super admin.');
        }

        return $next($request);
    }
}
