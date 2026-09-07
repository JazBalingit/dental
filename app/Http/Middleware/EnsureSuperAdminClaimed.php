<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The bootstrap super admin (logged in with the config/superadmin.php .env
 * credentials — session('super_admin_setup'), no real DB password) can do
 * nothing except set up a permanent account: pick a real email, verify it by
 * an emailed code, set a real password. This locks that session to the setup
 * screen (plus logout) and blocks every other page, exactly like
 * EnsureStaffIsVerified does for an unverified staff account.
 *
 * Every other session (patients, staff, the claimed super admin, plain
 * admins) is untouched — none of them carry the flag.
 */
class EnsureSuperAdminClaimed
{
    protected array $allowedRouteNames = [
        'superAdminSetup',
        'superAdminSetup.sendCode',
        'superAdminSetup.resendCode',
        'superAdminSetup.verify',
        'logout',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (!session('super_admin_setup')) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();

        if ($routeName !== null && in_array($routeName, $this->allowedRouteNames, true)) {
            return $next($request);
        }

        return redirect()->route('superAdminSetup');
    }
}
