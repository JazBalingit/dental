<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\DentistSchedule;
use App\Models\Appointment;
use App\Models\UserAccount;
use App\Services\ActivityLogService;
use App\Services\NotificationService;

class DentistScheduleController extends Controller
{
    public function __construct(
        protected ActivityLogService $activityLog,
        protected NotificationService $notifications
    ) {
    }

    protected function slots(): array
    {
        return DentistSchedule::slotLabels();
    }

    /**
     * The dentist whose grid we're viewing/editing. Reads ?dentist=ID (or
     * a dentist_id form field), falling back to the first active dentist.
     * Returns null only when the clinic has no dentist accounts at all.
     */
    protected function resolveDentist(Request $request): ?UserAccount
    {
        $dentists = UserAccount::dentists()->get();
        $requested = (int) ($request->input('dentist_id') ?: $request->query('dentist'));

        return $dentists->firstWhere('UserID', $requested) ?? $dentists->first();
    }

    protected function redirectToSchedule(Request $request, ?int $dentistId)
    {
        return redirect()->route('dentistSchedule', array_filter([
            'month' => $request->input('month', now()->format('Y-m')),
            'dentist' => $dentistId,
        ]));
    }

    public function index(Request $request)
    {
        // Accept either ?month=Y-m (used by prev/next arrows)
        // or ?year=YYYY&monthNum=M (used by the year/month picker form)
        if ($request->filled('year') && $request->filled('monthNum')) {
            $current = Carbon::createFromDate(
                (int) $request->input('year'),
                (int) $request->input('monthNum'),
                1
            )->startOfMonth();
        } else {
            $defaultMonth = now()->format('Y-m');
            $monthParam = $request->query('month', $defaultMonth);
            try {
                $current = Carbon::createFromFormat('Y-m', $monthParam)->startOfMonth();
            } catch (\Exception $e) {
                $current = Carbon::createFromFormat('Y-m', $defaultMonth)->startOfMonth();
            }
        }

        $dentists = UserAccount::dentists()->get();
        $selectedDentist = $this->resolveDentist($request);
        $selectedDentistId = $selectedDentist?->UserID;

        $startDay = $current->copy()->startOfMonth()->startOfWeek(Carbon::MONDAY);
        $endDay = $current->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);

        // Every schedule row for THIS dentist in the visible range.
        $schedules = DentistSchedule::where('DentistID', $selectedDentistId)
            ->whereBetween('Date', [$startDay->format('Y-m-d'), $endDay->format('Y-m-d')])
            ->get()
            ->groupBy(fn($row) => $row->Date->format('Y-m-d'))
            ->map(fn($rows) => $rows->keyBy('Time'));

        $appointments = Appointment::with('patientInfo')
            ->where('DentistID', $selectedDentistId)
            ->whereBetween('AppointmentDate', [$startDay->format('Y-m-d'), $endDay->format('Y-m-d')])
            ->whereIn('Status', ['Pending', 'Approved', 'Completed'])
            ->get();
        $occupiedSlots = $this->occupiedSlots($appointments);

        // Distinct completed appointments per day — used to collapse a past
        // day's per-slot "Completed" chips into a single "N completed" line.
        $completedCountByDate = $appointments
            ->where('Status', 'Completed')
            ->groupBy(fn ($a) => $a->AppointmentDate->format('Y-m-d'))
            ->map(fn ($rows) => $rows->count());

        $totalSlotsPerDay = count($this->slots());

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

        // total AVAILABLE slots across the whole month (Sundays excluded — clinic closed)
        $totalAvailableThisMonth = 0;
        $monthCursor = $current->copy();
        while ($monthCursor->month === $current->month) {
            if (!$monthCursor->isSunday()) {
                $dStr = $monthCursor->format('Y-m-d');
                $rows = $schedules[$dStr] ?? collect();
                $occupiedCount = collect($this->slots())->filter(fn($_, $time) => isset($occupiedSlots[$dStr . '_' . $time]))->count();
                $manualUnavailable = $rows->where('Status', 'Not Available')->count();
                $totalAvailableThisMonth += ($totalSlotsPerDay - max($occupiedCount, $manualUnavailable));
            }
            $monthCursor->addDay();
        }

        return $this->panelView('dentist-schedule', [
            'weeks' => $weeks,
            'current' => $current,
            'schedules' => $schedules,
            'slots' => $this->slots(),
            'totalSlotsPerDay' => $totalSlotsPerDay,
            'today' => now()->format('Y-m-d'),
            'totalAvailableThisMonth' => $totalAvailableThisMonth,
            'occupiedSlots' => $occupiedSlots,
            'completedCountByDate' => $completedCountByDate,
            'dentists' => $dentists,
            'selectedDentist' => $selectedDentist,
            'selectedDentistId' => $selectedDentistId,
        ]);
    }

    public function toggle(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'time' => 'required|string',
            'month' => 'nullable|string',
            'dentist_id' => 'nullable|integer',
        ]);

        $dentist = $this->resolveDentist($request);
        $dentistId = $dentist?->UserID;

        if (!$dentistId) {
            return $this->redirectToSchedule($request, null)->with('error', 'Add a dentist account first.');
        }

        $date = Carbon::parse($request->date);

        if ($date->isSunday()) {
            return $this->redirectToSchedule($request, $dentistId)->with('error', 'Sundays are not available for scheduling.');
        }

        if ($this->isAppointmentSlot($request->date, $request->time, $dentistId)) {
            return $this->redirectToSchedule($request, $dentistId)
                ->with('error', 'This slot is held by a pending or booked appointment and cannot be edited.');
        }

        $schedule = DentistSchedule::firstOrCreate(
            ['DentistID' => $dentistId, 'Date' => $request->date, 'Time' => $request->time],
            ['Status' => 'Available']
        );

        $schedule->Status = $schedule->Status === 'Available' ? 'Not Available' : 'Available';
        $schedule->save();

        $this->activityLog->log('Edit', "Set {$dentist->display_name}'s {$request->date} {$request->time} slot to {$schedule->Status}.");

        return $this->redirectToSchedule($request, $dentistId)->with('success', 'Schedule updated.');
    }

    protected function occupiedSlots($appointments): array
    {
        $occupied = [];
        $times = array_keys($this->slots());
        foreach ($appointments as $appointment) {
            $start = array_search($appointment->AppointmentTime, $times, true);
            if ($start === false) continue;
            $duration = $appointment->duration_slots;
            for ($offset = 0; $offset < $duration && isset($times[$start + $offset]); $offset++) {
                $occupied[$appointment->AppointmentDate->format('Y-m-d') . '_' . $times[$start + $offset]] = $appointment;
            }
        }
        return $occupied;
    }

    protected function isAppointmentSlot(string $date, string $time, int $dentistId): bool
    {
        return isset($this->occupiedSlots(
            Appointment::where('DentistID', $dentistId)
                ->whereDate('AppointmentDate', $date)
                ->whereIn('Status', ['Pending', 'Approved', 'Completed'])
                ->get()
        )[$date . '_' . $time]);
    }

    /**
     * Opens or closes a whole day for one dentist in a single click.
     *
     * Closing a day is a real "the dentist isn't in" decision: every
     * Pending or Approved appointment that day is cancelled and the patient
     * notified, then all slots are marked Not Available. Reopening the day
     * makes every slot Available again (a Completed appointment already
     * happened — its slot row is left as recorded).
     */
    public function toggleDay(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'month' => 'nullable|string',
            'dentist_id' => 'nullable|integer',
        ]);

        $dentist = $this->resolveDentist($request);
        $dentistId = $dentist?->UserID;

        if (!$dentistId) {
            return $this->redirectToSchedule($request, null)->with('error', 'Add a dentist account first.');
        }

        $date = Carbon::parse($request->date);

        if ($date->isSunday()) {
            return $this->redirectToSchedule($request, $dentistId)->with('error', 'Sundays are not available for scheduling.');
        }

        $allTimes = array_keys($this->slots());

        // Slots this dentist has already completed can't be reopened/closed.
        $completed = Appointment::where('DentistID', $dentistId)
            ->whereDate('AppointmentDate', $request->date)
            ->where('Status', 'Completed')
            ->get();
        $completedTimes = array_map(
            fn ($key) => explode('_', $key, 2)[1],
            array_keys($this->occupiedSlots($completed))
        );
        $editableTimes = array_values(array_diff($allTimes, $completedTimes));

        if (empty($editableTimes)) {
            return $this->redirectToSchedule($request, $dentistId)
                ->with('error', 'Every slot that day is already held by a completed appointment.');
        }

        $rows = DentistSchedule::where('DentistID', $dentistId)
            ->where('Date', $request->date)
            ->whereIn('Time', $editableTimes)
            ->get()->keyBy('Time');
        $allClosed = collect($editableTimes)->every(fn ($time) => ($rows[$time]->Status ?? 'Available') === 'Not Available');
        $newStatus = $allClosed ? 'Available' : 'Not Available';

        $cancelledCount = 0;

        if ($newStatus === 'Not Available') {
            // Closing the day: cancel this dentist's open appointments so the
            // grid is genuinely clear and reopening starts fully available.
            $affected = Appointment::with('patientInfo.userAccount')
                ->where('DentistID', $dentistId)
                ->whereDate('AppointmentDate', $request->date)
                ->whereIn('Status', ['Pending', 'Approved'])
                ->get();

            $dateLabel = $date->format('F j, Y');

            foreach ($affected as $appointment) {
                $appointment->Status = 'Cancelled';
                $appointment->DeclineReason = "{$dentist->display_name} is not available on this date.";
                $appointment->save();
                $cancelledCount++;

                $timeLabel = Carbon::createFromFormat('H:i', $appointment->AppointmentTime)->format('g:i A');
                $patientUser = $appointment->patientInfo->userAccount ?? null;

                if ($patientUser) {
                    $this->notifications->notifyUser(
                        $patientUser,
                        'Appointment Cancelled — Dentist Unavailable',
                        "We're sorry — your appointment with {$dentist->display_name} on {$dateLabel} at {$timeLabel} has been cancelled because the dentist won't be available that day. Please book another date at your convenience.",
                        'danger',
                        $appointment->AppointmentID,
                        'Cancelled'
                    );
                }
            }

            if ($cancelledCount > 0) {
                $this->notifications->notifyAdmins(
                    'Appointments Cancelled — Day Closed',
                    "{$cancelledCount} appointment(s) with {$dentist->display_name} on {$dateLabel} were cancelled because the day was closed.",
                    'warning'
                );
            }
        }

        // Apply the new status to every editable slot for this dentist.
        foreach ($editableTimes as $time) {
            $slot = DentistSchedule::firstOrCreate(
                ['DentistID' => $dentistId, 'Date' => $request->date, 'Time' => $time],
                ['Status' => 'Available']
            );
            $slot->Status = $newStatus;
            $slot->save();
        }

        $this->activityLog->log(
            'Edit',
            "Set every slot on {$request->date} for {$dentist->display_name} to {$newStatus}."
                . ($cancelledCount > 0 ? " Cancelled {$cancelledCount} appointment(s)." : '')
        );

        $message = $newStatus === 'Not Available'
            ? ('Day closed.' . ($cancelledCount > 0 ? " {$cancelledCount} appointment(s) were cancelled and the patient(s) notified." : ''))
            : 'Day reopened — every slot is available again.';

        return $this->redirectToSchedule($request, $dentistId)->with('success', $message);
    }
}
