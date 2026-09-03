<?php

namespace App\Http\Controllers\Concerns;

/**
 * Shared handling for every emailed 6-digit code in the app — signup,
 * password reset (login page), in-app password reset (Settings), and staff
 * email verification. Each flow keeps its own session key prefix so the
 * codes never collide:
 *
 *   otp             — RegisterController (signup)
 *   reset           — LoginController (forgot password)
 *   settings_reset  — SettingsController (Settings → Security)
 *   staff_verify    — StaffProfileController (verify staff email)
 *
 * Session keys written per prefix:
 *   {prefix}_code        the code itself
 *   {prefix}_expires_at  ISO timestamp — codes are valid for 5 minutes
 *   {prefix}_sent_at     ISO timestamp of the last send — enforces the
 *                        60-second gap before a resend is allowed
 *   {prefix}_attempts    wrong-guess counter (managed by the caller)
 */
trait ManagesOtp
{
    /** How long an emailed code stays valid. */
    protected int $otpTtlSeconds = 300;

    /** Minimum gap between a send and the next resend. */
    protected int $otpResendCooldownSeconds = 60;

    /**
     * Generate a fresh code, stamp its expiry and send time into the
     * session, and return it so the caller can mail it.
     */
    protected function issueOtp(string $prefix): string
    {
        $code = (string) random_int(100000, 999999);

        session([
            "{$prefix}_code" => $code,
            "{$prefix}_expires_at" => now()->addSeconds($this->otpTtlSeconds)->toIso8601String(),
            "{$prefix}_sent_at" => now()->toIso8601String(),
        ]);

        return $code;
    }

    /**
     * Seconds the user still has to wait before a resend is allowed, or
     * 0 when they can resend now.
     */
    protected function otpResendRetryAfter(string $prefix): int
    {
        $sentAt = session("{$prefix}_sent_at");

        if (!$sentAt) {
            return 0;
        }

        $elapsed = now()->diffInSeconds(\Illuminate\Support\Carbon::parse($sentAt), false) * -1;

        return max(0, $this->otpResendCooldownSeconds - (int) $elapsed);
    }

    protected function otpResendTooSoon(string $prefix): bool
    {
        return $this->otpResendRetryAfter($prefix) > 0;
    }

    /**
     * Has the stored code passed its 5-minute window (or is it missing)?
     */
    protected function otpExpired(string $prefix): bool
    {
        $code = session("{$prefix}_code");
        $expiresAt = session("{$prefix}_expires_at");

        if (!$code || !$expiresAt) {
            return true;
        }

        return now()->greaterThan(\Illuminate\Support\Carbon::parse($expiresAt));
    }

    /**
     * Constant-time check of a submitted code against the stored one.
     * Returns false when the code has expired.
     */
    protected function otpMatches(string $prefix, ?string $input): bool
    {
        if ($this->otpExpired($prefix) || $input === null) {
            return false;
        }

        return hash_equals((string) session("{$prefix}_code"), $input);
    }

    protected function clearOtp(string $prefix): void
    {
        session()->forget([
            "{$prefix}_code",
            "{$prefix}_expires_at",
            "{$prefix}_sent_at",
            "{$prefix}_attempts",
        ]);
    }

    /**
     * "1 minute" / "45 seconds" — for the "try again in …" messages.
     */
    protected function otpWaitLabel(int $seconds): string
    {
        if ($seconds >= 60) {
            $minutes = (int) ceil($seconds / 60);

            return $minutes . ' minute' . ($minutes === 1 ? '' : 's');
        }

        return $seconds . ' second' . ($seconds === 1 ? '' : 's');
    }
}
