<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ManagesOtp;
use App\Mail\OtpMail;
use App\Models\UserAccount;
use App\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;

/**
 * Changing an account's email — the one place it can change. A 6-digit
 * code is emailed to the NEW address and must be entered before the email
 * is switched, so nobody can move an account to an inbox they don't own.
 *
 * Used everywhere an email is shown as changeable, through the shared
 * partials.email-change-modal (JSON endpoints):
 *   - your own account: patient Settings → Profile, admin/staff My Profile
 *   - someone else's:   User Accounts (any admin, patient accounts only)
 *                       Staff Accounts (super admin only)
 *
 * Session keys: email_change_* (via ManagesOtp) + email_change_target /
 * email_change_new_email, so a code only ever confirms the exact account
 * and address it was sent for.
 */
class EmailChangeController extends Controller
{
    use ManagesOtp;

    protected string $prefix = 'email_change';

    public function __construct(protected ActivityLogService $activityLog)
    {
    }

    public function send(Request $request): JsonResponse
    {
        $target = $this->resolveTarget($request);
        if ($target instanceof JsonResponse) {
            return $target;
        }

        $data = $request->validate([
            'new_email' => [
                'required', 'email', 'max:150',
                Rule::unique('tbl_useraccount', 'Email')->ignore($target->UserID, 'UserID'),
            ],
        ], [
            'new_email.unique' => 'That email is already used by another account.',
        ]);
        $newEmail = mb_strtolower(trim($data['new_email']));

        if (strcasecmp($newEmail, (string) $target->Email) === 0) {
            return $this->fail('That is already the current email.', 422);
        }

        // Same new address for the same account → respect the resend cooldown.
        $sameRequest = (int) session('email_change_target') === $target->UserID
            && session('email_change_new_email') === $newEmail;
        if ($sameRequest && $this->otpResendTooSoon($this->prefix)) {
            $wait = $this->otpResendRetryAfter($this->prefix);

            return $this->fail("Please wait {$this->otpWaitLabel($wait)} before requesting another code.", 429, ['retryAfter' => $wait]);
        }

        $throttleKey = 'email-change:' . session('user_id');
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $minutes = (int) ceil(RateLimiter::availableIn($throttleKey) / 60);

            return $this->fail("Too many code requests. Please try again in {$minutes} minute(s).", 429);
        }
        RateLimiter::hit($throttleKey, 600);

        $code = $this->issueOtp($this->prefix);
        session([
            'email_change_target' => $target->UserID,
            'email_change_new_email' => $newEmail,
            'email_change_attempts' => 0,
        ]);

        try {
            Mail::to($newEmail)->send(new OtpMail($code));
        } catch (\Throwable $e) {
            report($e);
            $this->forgetPending();

            return $this->fail('We couldn\'t send the code to that email. Please check the address and try again.', 500);
        }

        return response()->json([
            'ok' => true,
            'message' => "We sent a 6-digit code to {$newEmail}. It expires in 5 minutes.",
            'retryAfter' => $this->otpResendCooldownSeconds,
        ]);
    }

    public function verify(Request $request): JsonResponse
    {
        $target = $this->resolveTarget($request);
        if ($target instanceof JsonResponse) {
            return $target;
        }

        $request->validate(['code' => 'required|string']);

        $newEmail = session('email_change_new_email');
        if ((int) session('email_change_target') !== $target->UserID || !$newEmail || $this->otpExpired($this->prefix)) {
            return $this->fail('Your code expired or was not requested. Please send a new code.', 422);
        }

        if (!$this->otpMatches($this->prefix, $request->input('code'))) {
            $attempts = (int) session('email_change_attempts', 0) + 1;
            if ($attempts >= 5) {
                $this->forgetPending();

                return $this->fail('Too many incorrect attempts. Please request a new code.', 422, ['restart' => true]);
            }
            session(['email_change_attempts' => $attempts]);

            return $this->fail('Incorrect code. Please try again.', 422);
        }

        // Re-check — the address could have been taken since the code was sent.
        if (UserAccount::where('Email', $newEmail)->where('UserID', '!=', $target->UserID)->exists()) {
            $this->forgetPending();

            return $this->fail('That email is already used by another account.', 422, ['restart' => true]);
        }

        $oldEmail = $target->Email;
        $target->Email = $newEmail;
        $target->EmailVerifiedAt = now();
        $target->save();

        $isSelf = $target->UserID === (int) session('user_id');
        if ($isSelf) {
            session(['user_email' => $newEmail]);
        }

        $this->forgetPending();

        $this->activityLog->log(
            'Email Changed',
            $isSelf
                ? "Changed own account email from {$oldEmail} to {$newEmail} (verified by code)."
                : "Changed the email of {$target->display_name}'s account from {$oldEmail} to {$newEmail} (verified by code)."
        );

        session()->flash('success', 'Email updated to ' . $newEmail . '.');

        return response()->json(['ok' => true, 'message' => 'Email updated to ' . $newEmail . '.']);
    }

    /**
     * The account whose email is being changed (?user_id=, default: you),
     * or a JSON error when the current session may not change it.
     */
    protected function resolveTarget(Request $request): UserAccount|JsonResponse
    {
        if (session('super_admin_setup')) {
            return $this->fail('Finish setting up the super admin account first.', 403);
        }

        $selfId = (int) session('user_id');
        $targetId = (int) ($request->input('user_id') ?: $selfId);
        $target = UserAccount::with(['staffInfo', 'patientInfo'])->find($targetId);

        if (!$target) {
            return $this->fail('Account not found.', 404);
        }

        if ($target->UserID === $selfId) {
            return $target;
        }

        $isAdmin = in_array(session('user_role'), UserAccount::ADMIN_ROLES, true);
        $isSuperAdmin = (bool) session('is_super_admin');

        // Nobody changes the super admin's email but the super admin.
        if ($target->IsSuperAdmin) {
            return $this->fail('You can\'t change this account\'s email.', 403);
        }

        // Staff/dentist accounts: super admin only (same as Staff Accounts).
        if ($target->AccountType === 'Staff') {
            return $isSuperAdmin ? $target : $this->fail('Only the super admin can change a staff account\'s email.', 403);
        }

        // Patient accounts: any admin-panel user (same as User Accounts).
        return $isAdmin ? $target : $this->fail('You can\'t change this account\'s email.', 403);
    }

    protected function forgetPending(): void
    {
        $this->clearOtp($this->prefix);
        session()->forget(['email_change_target', 'email_change_new_email']);
    }

    protected function fail(string $message, int $status, array $extra = []): JsonResponse
    {
        return response()->json(['ok' => false, 'message' => $message] + $extra, $status);
    }
}
