<?php
// Place in: app/Http/Controllers/Concerns/BuildsBookingCalendar.php

namespace App\Http\Controllers\Concerns;

use App\Models\Appointment;
use App\Models\DentistSchedule;
use App\Models\Service;
use App\Models\UserAccount;
use App\Services\AppointmentExpiryService;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Shared by AppointmentBookingController (landing page) and WalkInController
 * (walk-in page) so both pages render the exact same booking calendar off
 * the exact same data — see resources/views/partials/booking-calendar.blade.php.
 */
trait BuildsBookingCalendar
{
    protected array $slots = [
        '09:00' => '9:00 AM',
        '10:00' => '10:00 AM',
        '11:00' => '11:00 AM',
        '13:00' => '1:00 PM',
        '14:00' => '2:00 PM',
        '15:00' => '3:00 PM',
        '16:00' => '4:00 PM',
        '17:00' => '5:00 PM',
        '18:00' => '6:00 PM',
    ];

    /**
     * Returns everything the booking calendar section needs. Call this
     * from whatever controller renders the calendar and merge the
     * result into that page's view data.
     */
    public function calendarData(Request $request): array
    {
        // Sweep expired Pending requests first so a slot that just lapsed
        // shows up as open again immediately, instead of staying stuck
        // "occupied" until someone happens to approve/decline it.
        app(AppointmentExpiryService::class)->expireStalePending();

        $monthParam = $request->query('bookMonth', now()->format('Y-m'));

        try {
            $current = Carbon::createFromFormat('Y-m', $monthParam)->startOfMonth();
        } catch (\Exception $e) {
            $current = now()->startOfMonth();
        }

        $startDay = $current->copy()->startOfMonth()->startOfWeek(Carbon::MONDAY);
        $endDay = $current->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);

        $schedules = DentistSchedule::whereBetween('Date', [
            $startDay->format('Y-m-d'),
            $endDay->format('Y-m-d'),
        ])
            ->get()
            ->groupBy(fn($row) => $row->Date->format('Y-m-d'))
            ->map(fn($rows) => $rows->keyBy('Time'));

        // Pull appointments in range so we can label taken slots as
        // Booked (Approved) vs Pending instead of just "Not Available".
        $appointments = Appointment::with('service')->whereBetween('AppointmentDate', [
            $startDay->format('Y-m-d'),
            $endDay->format('Y-m-d'),
        ])
            ->whereIn('Status', ['Pending', 'Approved'])
            ->get();

        // An approved appointment occupies every slot in its approved duration.
        // A pending request holds only its requested starting slot.
        $occupiedSlots = [];
        $slotTimes = array_keys($this->slots);
        foreach ($appointments as $appointment) {
            $start = array_search($appointment->AppointmentTime, $slotTimes, true);
            if ($start === false) {
                continue;
            }

            $duration = $appointment->Status === 'Approved'
                ? max(1, (int) ($appointment->DurationHours ?? 1))
                : 1;

            for ($offset = 0; $offset < $duration && isset($slotTimes[$start + $offset]); $offset++) {
                $occupiedSlots[$appointment->AppointmentDate->format('Y-m-d') . '_' . $slotTimes[$start + $offset]] = $appointment;
            }
        }

        $weeks = [];
        $cursor = $startDay->copy();
        while ($cursor->lte($endDay)) {
            $week = [];
            for ($i = 0; $i < 7; $i++) {
                $week[] = $cursor->copy();
                $cursor->addDay();
            }
            $weeks[] = $week;
        }

        return [
            'bookWeeks' => $weeks,
            'bookCurrent' => $current,
            'bookSchedules' => $schedules,
            'bookOccupiedSlots' => $occupiedSlots,
            'bookSlots' => $this->slots,
            'bookToday' => now()->format('Y-m-d'),
            'services' => Service::orderBy('ServiceName')->get(),
            'bookCurrentPatientId' => UserAccount::with('patientInfo')->find(session('user_id'))?->patientInfo?->PatientID,
        ];
    }
}
