<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Landing page "How to Book Your Appointment": the old steps assumed you
 * must create an account first. Booking now works as a guest OR with an
 * account, so the first six active steps get copy that says so (and the
 * pros of each). Steps beyond six, and archived ones, are left alone.
 * Copy is inlined (not read from SystemSetting) so this migration never
 * changes meaning if the defaults are edited later.
 */
return new class extends Migration {
    protected array $new = [
        ['Pick a Dentist & Date', 'Scroll down to "Book an Appointment", choose your dentist, then tap any date that still has open slots.'],
        ['Choose a Time & Service', 'Pick an open time and up to 3 services. Add a note or a photo so your dentist knows what you need.'],
        ['Book as a Guest', "No account needed — just confirm your email with a 6-digit code. It's the quickest way to book, and you manage it from the link we email you."],
        ['Or Use a Free Account', 'Keep every visit, your dental records and history in one Patient Portal, get notified in the app, and book for your family in a few taps.'],
        ['Wait for Approval', "Your request starts as Pending. We'll email you (and notify you in your portal) as soon as the clinic approves it."],
        ['Reschedule or Cancel Anytime', 'Use your Patient Portal or the "Manage my appointment" link in your email. If the clinic suggests a new time, accept it or keep yours.'],
    ];

    protected array $old = [
        ['Create an Account or Log In', 'Sign up with your basic details, or log back in if you already have an account with us.'],
        ['Open the Appointment Section', 'Once you\'re logged in, scroll down to "Book Now" — the dentist\'s monthly schedule appears right here on this page.'],
        ['Pick an Open Date', 'Click any date that still has open slots to see that day\'s available times.'],
        ['Choose a Time & Service', 'Pick an open time slot, then select the dental treatment you need for that visit.'],
        ['Review & Confirm', 'Double-check the service, date, and time, then confirm to submit your appointment request.'],
        ['Wait for Approval', 'Your request starts as Pending — we\'ll notify you once staff approves it. Track it anytime on your Appointments page.'],
    ];

    public function up(): void
    {
        $this->apply($this->new);
    }

    public function down(): void
    {
        $this->apply($this->old);
    }

    protected function apply(array $copy): void
    {
        $now = now();
        $existing = DB::table('tbl_appointment_steps')
            ->where('IsArchived', false)
            ->orderBy('DisplayOrder')
            ->orderBy('StepID')
            ->limit(count($copy))
            ->pluck('StepID')
            ->values();

        foreach ($copy as $i => [$title, $desc]) {
            if (isset($existing[$i])) {
                DB::table('tbl_appointment_steps')->where('StepID', $existing[$i])
                    ->update(['Title' => $title, 'Description' => $desc, 'updated_at' => $now]);
            } else {
                DB::table('tbl_appointment_steps')->insert([
                    'Title' => $title,
                    'Description' => $desc,
                    'DisplayOrder' => (int) DB::table('tbl_appointment_steps')->max('DisplayOrder') + 1,
                    'IsArchived' => false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
};
