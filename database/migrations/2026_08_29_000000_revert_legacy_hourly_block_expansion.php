<?php
// Place in: database/migrations/
// Reverts 2026_08_28_000002_expand_legacy_hourly_blocks_to_half_hour_grid.
//
// That migration expanded old manually-closed hourly slots (from before the
// calendar moved to 30-minute slots) to also block their new half-hour
// sibling, on the theory that closing "1:00 PM" under the old system meant
// the whole 1:00-2:00 PM hour was intended to be closed. In practice the
// underlying rows it was expanding were stale/seed-era clutter, not
// deliberate staff closures — expanding them tipped several near-term days
// from "partially open" to "fully closed", blocking real bookings days in
// advance for no legitimate reason. Deleting exactly the rows that
// migration created (identified by its precise run timestamp) restores the
// calendar to its actual availability without touching anything else —
// real appointments, and any manual closures made outside that one run,
// are untouched.

use App\Models\DentistSchedule;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        DentistSchedule::where('created_at', '2026-08-28 20:14:54')->delete();
    }

    public function down(): void
    {
        // Intentionally not reversible — see 2026_08_28_000002 if the
        // expansion needs to be reapplied.
    }
};
