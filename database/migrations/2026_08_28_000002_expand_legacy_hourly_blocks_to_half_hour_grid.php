<?php
// Place in: database/migrations/
// Data migration, not a schema change. Before 2026-08-28 the booking
// calendar ran on 1-hour slots (09:00, 10:00, ... 18:00). Manually closing
// "1:00 PM" back then meant the whole 1:00-2:00 PM hour was blocked. The
// calendar now runs on 30-minute slots, but those old rows only ever
// covered the on-the-hour mark — the new half-hour mark within that same
// hour (e.g. 1:30 PM) was never blocked, leaving an invisible gap a patient
// could book into on a day a staff member intended to be closed.
//
// This expands every pre-migration hourly block to also cover its new
// half-hour sibling within the same hour.

use App\Models\DentistSchedule;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        $cutoff = '2026-08-28 00:00:00';

        DentistSchedule::where('Status', 'Not Available')
            ->where('created_at', '<', $cutoff)
            ->where('Time', 'like', '%:00')
            ->where('Time', '!=', '18:00') // no 18:30 slot exists — the day already ends there
            ->get()
            ->each(function (DentistSchedule $row) {
                $hour = (int) substr($row->Time, 0, 2);
                $siblingTime = sprintf('%02d:30', $hour);

                DentistSchedule::updateOrCreate(
                    ['Date' => $row->Date->format('Y-m-d'), 'Time' => $siblingTime],
                    ['Status' => 'Not Available']
                );
            });
    }

    public function down(): void
    {
        // Not reversible — there's no way to tell which :30 rows this
        // migration created versus ones a real half-hour booking
        // legitimately needed afterward.
    }
};
