<?php

namespace App\Http\Controllers;

use App\Exceptions\BackupException;
use App\Services\ActivityLogService;
use App\Services\BackupService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Throwable;

/**
 * Configuration → Backup & Restore. Super admin only (routes sit in the
 * 'superadmin' group). See BackupService for the file format.
 */
class BackupController extends Controller
{
    public function __construct(
        protected BackupService $backups,
        protected ActivityLogService $activityLog,
    ) {
    }

    public function store()
    {
        try {
            $this->backups->create('manual', $this->actorName());
        } catch (Throwable $e) {
            report($e);

            return $this->back()->with('error', $this->message($e, 'The backup could not be created.'));
        }

        $this->activityLog->log('Backup Created', 'Created a system backup.');

        return $this->back()->with('success', 'Backup created. Use "Save to computer" to keep a copy off the website.');
    }

    public function download(string $name)
    {
        try {
            $path = $this->backups->path($name);
        } catch (BackupException $e) {
            return $this->back()->with('error', $e->getMessage());
        }

        return response()->download($path, $name, ['Content-Type' => 'application/zip']);
    }

    public function destroy(string $name)
    {
        try {
            $this->backups->delete($name);
        } catch (BackupException $e) {
            return $this->back()->with('error', $e->getMessage());
        }

        $this->activityLog->log('Backup Deleted', 'Deleted a saved system backup.');

        return $this->back()->with('success', 'Backup copy deleted.');
    }

    /** Restore one of the copies saved on the website. */
    public function restore(Request $request, string $name)
    {
        try {
            $path = $this->backups->path($name);
        } catch (BackupException $e) {
            return $this->back()->with('error', $e->getMessage());
        }

        return $this->runRestore($request, $path);
    }

    /** Restore a ZIP the super admin previously saved to their computer. */
    public function restoreUpload(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|mimes:zip|max:512000',
        ], [
            'backup_file.required' => 'Choose a backup ZIP file to restore.',
            'backup_file.mimes' => 'The backup must be a .zip file downloaded from this system.',
        ]);

        return $this->runRestore($request, $request->file('backup_file')->getRealPath());
    }

    protected function runRestore(Request $request, string $path)
    {
        $actor = $this->actorName();

        try {
            $manifest = $this->backups->restore($path, $actor);
        } catch (Throwable $e) {
            report($e);

            return $this->back()->with('error', $this->message($e, 'The restore failed. No records were changed.'));
        }

        $takenAt = Carbon::parse($manifest['created_at'])->timezone(config('app.timezone'))->format('M j, Y g:i A');
        $this->activityLog->log('Backup Restored', "Restored the system from the backup taken {$takenAt}.");

        // The accounts table was just replaced, so this session may now point
        // at a different (or missing) account — sign in again.
        $request->session()->forget([
            'user_id', 'user_role', 'user_email', 'account_type', 'activity_log_id',
            'is_super_admin', 'super_admin_setup',
        ]);
        $request->session()->regenerate();

        return redirect()->route('login')
            ->with('success', "System restored from the backup taken {$takenAt}. Please sign in again.");
    }

    protected function back()
    {
        return redirect()->route('configuration', ['settingsTab' => 'backup']);
    }

    protected function actorName(): string
    {
        $userId = session('user_id');

        return ($userId ? $this->activityLog->resolveActorName((int) $userId) : null) ?? 'Super Admin';
    }

    /** BackupExceptions are written for the user; raw DB/Zip errors aren't. */
    protected function message(Throwable $e, string $fallback): string
    {
        return $e instanceof BackupException ? $e->getMessage() : $fallback;
    }
}
