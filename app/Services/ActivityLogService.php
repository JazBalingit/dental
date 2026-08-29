<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\UserAccount;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Writes to the single system-wide activity trail (tbl_activityLogs).
 *
 * Replaces the old AuditLogService — admin/staff actions and patient
 * actions now land in the same place, alongside login/logout sessions
 * and failed login / failed password-change attempts.
 *
 * Every write is wrapped so a logging failure can never break the user
 * action it was recording — a broken audit trail is bad, a checkout /
 * cancellation / login that 500s because of the audit trail is worse.
 */
class ActivityLogService
{
    /**
     * Record an action performed by the currently logged-in user (or by
     * an explicit $userId). Used by every admin/staff guard()-protected
     * controller and by the patient-facing flows.
     */
    public function log(string $activityType, string $description, ?int $userId = null): ?ActivityLog
    {
        $userId ??= session('user_id');

        return $this->write([
            'UserID' => $userId,
            'ActivityType' => $activityType,
            'Description' => $description,
            'ActorName' => $this->resolveActorName($userId),
            'IpAddress' => $this->ip(),
        ]);
    }

    /**
     * A failed login attempt. $email is whatever was typed; $userId is set
     * only when that email actually matches an account.
     */
    public function failedLogin(string $email, ?int $userId = null): ?ActivityLog
    {
        return $this->write([
            'UserID' => $userId,
            'ActivityType' => 'Failed Login',
            'Description' => "Failed login attempt for \"{$email}\" (incorrect email or password).",
            'ActorName' => $userId ? $this->resolveActorName($userId) : $email,
            'IpAddress' => $this->ip(),
        ]);
    }

    /**
     * Opens a login session row and returns its id (stored in the session
     * so logout can close it). Returns null if the write failed.
     */
    public function startSession(int $userId): ?int
    {
        $log = $this->write([
            'UserID' => $userId,
            'ActivityType' => 'Login',
            'Description' => 'Signed in.',
            'ActorName' => $this->resolveActorName($userId),
            'IpAddress' => $this->ip(),
            'LoggedInTime' => now(),
        ]);

        return $log?->ActivityLogsID;
    }

    /**
     * Closes the session row opened by startSession().
     */
    public function endSession(?int $activityLogId): void
    {
        if (!$activityLogId) {
            return;
        }

        try {
            ActivityLog::where('ActivityLogsID', $activityLogId)
                ->whereNull('LoggedOutTime')
                ->update(['LoggedOutTime' => now(), 'updated_at' => now()]);
        } catch (Throwable $e) {
            Log::warning('ActivityLogService::endSession failed: ' . $e->getMessage());
        }
    }

    protected function write(array $attributes): ?ActivityLog
    {
        try {
            return ActivityLog::create($attributes + [
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (Throwable $e) {
            Log::warning('ActivityLogService write failed: ' . $e->getMessage(), $attributes);

            return null;
        }
    }

    protected function ip(): ?string
    {
        try {
            return request()->ip();
        } catch (Throwable $e) {
            return null;
        }
    }

    protected function resolveActorName(?int $userId): ?string
    {
        if (!$userId) {
            return null;
        }

        try {
            $user = UserAccount::with(['staffInfo', 'patientInfo'])->find($userId);
        } catch (Throwable $e) {
            return null;
        }

        if (!$user) {
            return null;
        }

        $info = $user->AccountType === 'Staff' ? $user->staffInfo : $user->patientInfo;
        $name = $info ? trim(($info->FirstName ?? '') . ' ' . ($info->LastName ?? '')) : '';

        return $name !== '' ? $name : $user->Email;
    }
}
