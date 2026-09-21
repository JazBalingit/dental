<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;

/**
 * Archiving anything (accounts, patient records, services, categories,
 * appointment steps, activity logs) requires a written reason from the archive
 * dialog. It is stored on the archived record itself (ArchiveReason /
 * ArchivedAt) and also lands in the Activity Log via ActivityLogService.
 * Unarchiving clears both.
 */
trait HandlesArchiveReason
{
    /** The reason typed in the archive dialog; validation error (toast) when blank. */
    protected function archiveReason(Request $request): string
    {
        $data = $request->validate(
            ['reason' => ['required', 'string', 'max:500']],
            ['reason.required' => 'Please enter a reason for archiving.']
        );

        return trim($data['reason']);
    }

    /** Column values that mark a row archived. */
    protected function archivedState(string $reason): array
    {
        return ['IsArchived' => true, 'ArchiveReason' => $reason, 'ArchivedAt' => now()];
    }

    /** Column values that restore an archived row. */
    protected function restoredState(): array
    {
        return ['IsArchived' => false, 'ArchiveReason' => null, 'ArchivedAt' => null];
    }
}
