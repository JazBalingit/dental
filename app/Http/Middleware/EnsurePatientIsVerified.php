<?php

namespace App\Http\Middleware;

use App\Models\UserAccount;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * A patient account created by an admin (User Accounts → Add User) starts
 * with EmailVerifiedAt = null. Until the patient proves the email, this locks
 * their session to the verify-email screen (plus logout) — the patient-side
 * twin of EnsureStaffIsVerified. Self sign-ups are verified before the
 * account exists, so they never land here.
 *
 * Admins, staff and guests are untouched by this check.
 */
class EnsurePatientIsVerified
{
    protected array $allowedRouteNames = [
        'patientVerify',
        'patientVerify.send',
        'patientVerify.verify',
        'logout',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (!session('user_id') || session('account_type') !== 'user' || session('user_role') !== 'patient') {
            return $next($request);
        }

        $routeName = $request->route()?->getName();

        if ($routeName !== null && in_array($routeName, $this->allowedRouteNames, true)) {
            return $next($request);
        }

        $patient = UserAccount::find(session('user_id'));

        if (!$patient || $patient->EmailVerifiedAt) {
            return $next($request);
        }

        return redirect()->route('patientVerify');
    }
}
