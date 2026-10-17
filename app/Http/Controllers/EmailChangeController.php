<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ManagesOtp;
use App\Mail\EmailChangedMail;
use App\Mail\OtpMail;
use App\Models\UserAccount;
use App\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;

/**
 * Changing an account's email — the one place it can change.
 *
 * Your own account takes three steps, so someone at an unlocked, logged-in
 * screen can't move the account to an inbox they control:
 *   1. sendCurrent / verifyCurrent — a code to the CURRENT email proves it's
 *      really you. That unlocks steps 2–3 for IDENTITY_MINUTES.
 *   2. send   — a code to the NEW email.
 *   3. verify — the new email's code; only now is the email switched.
 * Nothing is saved before step 3, so a refresh, a closed tab or an expired
 * code part-way leaves the old email in place (and the next try starts over).
 *
 * An admin changing SOMEONE ELSE's email (usually because they lost the old
 * inbox) skips step 1 — the new address must still be verified, and the old
 * address is told about the change afterwards.
 *
 * Used everywhere an email is shown as changeable, through the shared
 * partials.email-change-modal (JSON endpoints):
 *   - your own account: patient Settings → Profile, admin/staff My Profile
 *   - someone else's:   User Accounts (any admin, patient accounts only)
 *                       Staff Accounts (super admin only)
 *
 * Session keys: email_change_* (new-email code, via ManagesOtp) with
 * email_change_target / email_change_new_email; email_change_old_* (current-
 * email code); email_change_identity (step 1 passed: user, email, until).
 */
class EmailChangeController extends Controller
{
    use ManagesOtp;

    protected string $prefix = 'email_change';

    /** OTP prefix for the code sent to the CURRENT email (step 1). */
    protected string $identityPrefix = 'email_change_old';

    /** How long a confirmed current email unlocks steps 2–3. */
    public const IDENTITY_MINUTES = 15;

    public function __construct(protected ActivityLogService $activityLog)
    {
    }

    /** Step 1: send a code to the account's CURRENT email (own account only). */
    public function sendCurrent(Request $request): JsonResponse
    {
        $target = $this->resolveTarget($request);
        if ($target instanceof JsonResponse) {
            return $target;
        }
        if (!$this->isSelf($target)) {
            return $this->fail('This step is only for changing your own email.', 422);
        }

        if ($this->otpResendTooSoon($this->identityPrefix)) {
            $wait = $this->otpResendRetryAfter($this->identityPrefix);

            return $this->fail("Please wait {$this->otpWaitLabel($wait)} before requesting another code.", 429, ['retryAfter' => $wait]);
        }

        if ($limited = $this->throttle()) {
            return $limited;
        }

        // A fresh start: drop anything left from an earlier, unfinished try.
        $this->forgetPending();
        session()->forget('email_change_identity');

        $code = $this->issueOtp($this->identityPrefix);
        session(['email_change_old_target' => $target->UserID, 'email_change_old_attempts' => 0]);

        try {
            Mail::to($target->Email)->send(new OtpMail($code, "confirm it's really you before changing the email of your account"));
        } catch (\Throwable $e) {
            report($e);
            $this->clearOtp($this->identityPrefix);

            return $this->fail("We couldn't send the code to your current email. Please try again later.", 500);
        }

        return response()->json([
            'ok' => true,
            'message' => "We sent a 6-digit code to your current email, {$target->Email}. It expires in 5 minutes.",
            'retryAfter' => $this->otpResendCooldownSeconds,
        ]);
    }

    /** Step 1b: check the current email's code → unlock the new-email steps. */
    public function verifyCurrent(Request $request): JsonResponse
    {
        $target = $this->resolveTarget($request);
        if ($target instanceof JsonResponse) {
            return $target;
        }
        if (!$this->isSelf($target)) {
            return $this->fail('This step is only for changing your own email.', 422);
        }

        $request->validate(['code' => 'required|string']);

        if ((int) session('email_change_old_target') !== $target->UserID || $this->otpExpired($this->identityPrefix)) {
            return $this->fail('Your code expired or was not requested. Please send a new code.', 422);
        }

        if (!$this->otpMatches($this->identityPrefix, $request->input('code'))) {
            $attempts = (int) session('email_change_old_attempts', 0) + 1;
            if ($attempts >= 5) {
                $this->clearOtp($this->identityPrefix);

                return $this->fail('Too many incorrect attempts. Please request a new code.', 422, ['restart' => 'identity']);
            }
            session(['email_change_old_attempts' => $attempts]);

            return $this->fail('Incorrect code. Please try again.', 422);
        }

        $this->clearOtp($this->identityPrefix);
        session()->forget(['email_change_old_target', 'email_change_old_attempts']);
        session(['email_change_identity' => [
            'user_id' => $target->UserID,
            'email' => mb_strtolower((string) $target->Email),
            'until' => now()->addMinutes(self::IDENTITY_MINUTES)->toIso8601String(),
        ]]);

        return response()->json(['ok' => true, 'message' => 'Your current email is confirmed. Now enter your new email.']);
    }

    /** Step 2: send a code to the NEW email. */
    public function send(Request $request): JsonResponse
    {
        $target = $this->resolveTarget($request);
        if ($target instanceof JsonResponse) {
            return $target;
        }

        if ($blocked = $this->requireIdentity($target)) {
            return $blocked;
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

        if ($limited = $this->throttle()) {
            return $limited;
        }

        $code = $this->issueOtp($this->prefix);
        session([
            'email_change_target' => $target->UserID,
            'email_change_new_email' => $newEmail,
            'email_change_attempts' => 0,
        ]);

        try {
            Mail::to($newEmail)->send(new OtpMail($code, 'confirm this as the new email address of your account'));
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

    /** Step 3: check the new email's code → switch the email. */
    public function verify(Request $request): JsonResponse
    {
        $target = $this->resolveTarget($request);
        if ($target instanceof JsonResponse) {
            return $target;
        }

        $request->validate(['code' => 'required|string']);

        if ($blocked = $this->requireIdentity($target)) {
            return $blocked;
        }

        $newEmail = session('email_change_new_email');
        if ((int) session('email_change_target') !== $target->UserID || !$newEmail || $this->otpExpired($this->prefix)) {
            return $this->fail('Your code expired or was not requested. Please send a new code.', 422);
        }

        if (!$this->otpMatches($this->prefix, $request->input('code'))) {
            $attempts = (int) session('email_change_attempts', 0) + 1;
            if ($attempts >= 5) {
                $this->forgetPending();

                return $this->fail('Too many incorrect attempts. Please request a new code.', 422, ['restart' => 'new']);
            }
            session(['email_change_attempts' => $attempts]);

            return $this->fail('Incorrect code. Please try again.', 422);
        }

        // Re-check — the address could have been taken since the code was sent.
        if (UserAccount::where('Email', $newEmail)->where('UserID', '!=', $target->UserID)->exists()) {
            $this->forgetPending();

            return $this->fail('That email is already used by another account.', 422, ['restart' => 'new']);
        }

        $oldEmail = $target->Email;
        $target->Email = $newEmail;
        $target->EmailVerifiedAt = now();
        $target->save();

        $isSelf = $this->isSelf($target);
        if ($isSelf) {
            session(['user_email' => $newEmail]);
        }

        $this->forgetPending();
        session()->forget('email_change_identity');

        // Tell the old inbox too, so an unexpected change doesn't go unnoticed.
        if ($oldEmail) {
            try {
                Mail::to($oldEmail)->send(new EmailChangedMail($newEmail, $isSelf));
            } catch (\Throwable $e) {
                report($e);
            }
        }

        $this->activityLog->log(
            'Email Changed',
            $isSelf
                ? "Changed own account email from {$oldEmail} to {$newEmail} (current and new email verified by code)."
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

    protected function isSelf(UserAccount $target): bool
    {
        return $target->UserID === (int) session('user_id');
    }

    /**
     * Own account: step 1 (current email confirmed) must have happened, for
     * this account and its current email, within IDENTITY_MINUTES. Null when
     * it has; otherwise an error that sends the modal back to step 1.
     */
    protected function requireIdentity(UserAccount $target): ?JsonResponse
    {
        if (!$this->isSelf($target)) {
            return null;
        }

        $id = session('email_change_identity');
        $ok = is_array($id)
            && (int) ($id['user_id'] ?? 0) === $target->UserID
            && ($id['email'] ?? null) === mb_strtolower((string) $target->Email)
            && now()->lessThan(Carbon::parse($id['until'] ?? '1970-01-01'));

        if ($ok) {
            return null;
        }

        session()->forget('email_change_identity');
        $this->forgetPending();

        return $this->fail('For your security, please confirm your current email first.', 403, ['restart' => 'identity']);
    }

    /** At most 8 code emails (both steps together) per 10 minutes per signed-in user. */
    protected function throttle(): ?JsonResponse
    {
        $throttleKey = 'email-change:' . session('user_id');
        if (RateLimiter::tooManyAttempts($throttleKey, 8)) {
            $minutes = (int) ceil(RateLimiter::availableIn($throttleKey) / 60);

            return $this->fail("Too many code requests. Please try again in {$minutes} minute(s).", 429);
        }
        RateLimiter::hit($throttleKey, 600);

        return null;
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
