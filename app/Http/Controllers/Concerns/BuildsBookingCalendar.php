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
        // Booked (Approved), Pending, or Completed instead of just "Not Available".
        $appointments = Appointment::with('service')->whereBetween('AppointmentDate', [
            $startDay->format('Y-m-d'),
            $endDay->format('Y-m-d'),
        ])
            ->whereIn('Status', ['Pending', 'Approved', 'Completed'])
            ->get();

        // Every appointment — Pending or Approved — occupies its full
        // service-driven duration from the moment it's booked (DurationHours
        // is computed from the selected services' total time at booking, not
        // assigned later at approval), so a slot is reserved end-to-end
        // regardless of status.
        $occupiedSlots = [];
        $slotTimes = DentistSchedule::slotTimes();
        foreach ($appointments as $appointment) {
            $start = array_search($appointment->AppointmentTime, $slotTimes, true);
            if ($start === false) {
                continue;
            }

            $duration = $appointment->duration_slots;

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
            'bookSlots' => DentistSchedule::slotLabels(),
            'bookToday' => now()->format('Y-m-d'),
            'services' => Service::orderBy('ServiceName')->get(),
            'bookCurrentPatientId' => UserAccount::with('patientInfo')->find(session('user_id'))?->patientInfo?->PatientID,
        ];
    }

    /**
     * How many half-hour slots a set of services needs, rounded up — a
     * 45-minute service needs ceil(45 / 30) = 2 half-hour slots.
     */
    protected function slotsNeededForServices($services): int
    {
        $minutes = collect($services)->sum(fn ($s) => $s->DurationMinutes ?? 60);

        return max(1, (int) ceil($minutes / DentistSchedule::SLOT_MINUTES));
    }

    /**
     * Reserves $slotsNeeded consecutive half-hour slots starting at
     * $startTime on $date — either every one of them flips to Not
     * Available, or none do. Returns the reserved DentistSchedule rows.
     *
     * @throws \RuntimeException with a patient-facing explanation if the
     *         full block isn't available (runs past closing, or another
     *         appointment already holds one of the slots).
     */
    protected function reserveSlotBlock(string $date, string $startTime, int $slotsNeeded): array
    {
        $slotTimes = DentistSchedule::slotTimes();
        $startIndex = array_search($startTime, $slotTimes, true);

        if ($startIndex === false) {
            throw new \RuntimeException('That time is not a valid appointment slot.');
        }

        $durationLabel = DentistSchedule::formatSlotDuration($slotsNeeded);
        $times = array_slice($slotTimes, $startIndex, $slotsNeeded);

        if (count($times) < $slotsNeeded) {
            throw new \RuntimeException(
                "Booking failed: your selected services need {$durationLabel} total, which runs past closing time from this slot. Please choose an earlier start time or a date with more room."
            );
        }

        $rows = [];
        foreach ($times as $time) {
            $schedule = DentistSchedule::firstOrCreate(['Date' => $date, 'Time' => $time], ['Status' => 'Available']);

            if ($schedule->Status !== 'Available') {
                $conflictLabel = Carbon::createFromFormat('H:i', $time)->format('g:i A');
                throw new \RuntimeException(
                    "Booking failed: your total service duration is {$durationLabel}, but {$conflictLabel} on this day is already booked. Please choose a start time with your full {$durationLabel} available."
                );
            }

            $rows[] = $schedule;
        }

        foreach ($rows as $schedule) {
            $schedule->Status = 'Not Available';
            $schedule->save();
        }

        return $rows;
    }
}
