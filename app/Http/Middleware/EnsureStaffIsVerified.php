<?php

namespace App\Http\Middleware;

use App\Models\UserAccount;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * A staff/dentist account created via Staff Accounts (tbl_useraccount,
 * AccountType = 'Staff') starts with EmailVerifiedAt = null. Until they
 * verify their email, they can't be trusted with clinic data yet, so this
 * locks their session down to the verification screen itself (plus logout)
 * and blocks every other page — dashboard, patient records, reports, etc.
 *
 * Patients and the config-based super admin (session('is_super_admin'),
 * no tbl_useraccount row at all) are untouched by this check.
 */
class EnsureStaffIsVerified
{
    protected array $allowedRouteNames = [
        'staffProfile',
        'staffProfile.sendVerification',
        'staffProfile.verifyEmail',
        'logout',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (!session('user_id') || session('is_super_admin') || session('account_type') !== 'staff') {
            return $next($request);
        }

        $routeName = $request->route()?->getName();

        if ($routeName !== null && in_array($routeName, $this->allowedRouteNames, true)) {
            return $next($request);
        }

        $staff = UserAccount::find(session('user_id'));

        if (!$staff || $staff->EmailVerifiedAt) {
            return $next($request);
        }

        return redirect()->route('staffProfile')
            ->with('error', 'Please verify your email before you can use the system.');
    }
}
