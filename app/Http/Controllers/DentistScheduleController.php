<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\DentistSchedule;
use App\Models\Appointment;
use App\Services\AuditLogService;
use App\Services\NotificationService;

class DentistScheduleController extends Controller
{
    public function __construct(protected AuditLogService $auditLog, protected NotificationService $notifications)
    {
    }

    protected function guard()
    {
        if (!session('user_id') || session('user_role') !== 'admin') {
            return redirect()->route('login')->with('login_error', 'Please log in as an administrator to continue.');
        }

        return null;
    }

    // 24h value => "9:00 AM - 9:30 AM" range label, from the single
    // source of truth for the clinic's slot grid.
    protected function slots(): array
    {
        return DentistSchedule::slotLabels();
    }

    public function index(Request $request)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

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

        $startDay = $current->copy()->startOfMonth()->startOfWeek(Carbon::MONDAY);
        $endDay = $current->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);

        // pull every schedule row in the visible range in one query, grouped by date
        $schedules = DentistSchedule::whereBetween('Date', [
            $startDay->format('Y-m-d'),
            $endDay->format('Y-m-d'),
        ])
            ->get()
            ->groupBy(fn($row) => $row->Date->format('Y-m-d'))
            ->map(fn($rows) => $rows->keyBy('Time'));

        $appointments = Appointment::with('patientInfo')
            ->whereBetween('AppointmentDate', [$startDay->format('Y-m-d'), $endDay->format('Y-m-d')])
            ->whereIn('Status', ['Pending', 'Approved', 'Completed'])
            ->get();
        $occupiedSlots = $this->occupiedSlots($appointments);

        $totalSlotsPerDay = count($this->slots());

        // build weeks => array of Carbon dates, 7 per row
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

        return view('superAdmin.dentist-schedule', [
            'weeks' => $weeks,
            'current' => $current,
            'schedules' => $schedules,
            'slots' => $this->slots(),
            'totalSlotsPerDay' => $totalSlotsPerDay,
            'today' => now()->format('Y-m-d'),
            'totalAvailableThisMonth' => $totalAvailableThisMonth,
            'occupiedSlots' => $occupiedSlots,
        ]);
    }

    public function toggle(Request $request)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $request->validate([
            'date' => 'required|date',
            'time' => 'required|string',
            'month' => 'nullable|string',
        ]);

        $date = Carbon::parse($request->date);

        // Sundays are not toggleable — clinic is closed
        if ($date->isSunday()) {
            return redirect()
                ->route('dentistSchedule', ['month' => $request->input('month', now()->format('Y-m'))])
                ->with('error', 'Sundays are not available for scheduling.');
        }

        if ($this->isAppointmentSlot($request->date, $request->time)) {
            return redirect()
                ->route('dentistSchedule', ['month' => $request->input('month', now()->format('Y-m'))])
                ->with('error', 'This slot is held by a pending or booked appointment and cannot be edited.');
        }

        // create the row as Available on first click, then flip it
        $schedule = DentistSchedule::firstOrCreate(
            ['Date' => $request->date, 'Time' => $request->time],
            ['Status' => 'Available']
        );

        $schedule->Status = $schedule->Status === 'Available' ? 'Not Available' : 'Available';
        $schedule->save();

        $this->auditLog->log('Edit', "Set {$request->date} {$request->time} schedule slot to {$schedule->Status}.");

        return redirect()
            ->route('dentistSchedule', ['month' => $request->input('month', now()->format('Y-m'))])
            ->with('success', 'Schedule updated.');
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

    protected function isAppointmentSlot(string $date, string $time): bool
    {
        return isset($this->occupiedSlots(
            Appointment::whereDate('AppointmentDate', $date)->whereIn('Status', ['Pending', 'Approved', 'Completed'])->get()
        )[$date . '_' . $time]);
    }

    /**
     * Turns every slot on a date to Not Available (or back to Available if
     * the day is already fully closed) in one click, instead of toggling
     * each half-hour slot by hand. Closing the day cancels any Pending or
     * Approved appointments still on it and emails each patient — a
     * Completed appointment already happened, so its slot is left alone.
     */
    public function toggleDay(Request $request)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $request->validate([
            'date' => 'required|date',
            'month' => 'nullable|string',
        ]);

        $date = Carbon::parse($request->date);

        if ($date->isSunday()) {
            return redirect()
                ->route('dentistSchedule', ['month' => $request->input('month', now()->format('Y-m'))])
                ->with('error', 'Sundays are not available for scheduling.');
        }

        $completedAppointments = Appointment::whereDate('AppointmentDate', $request->date)
            ->where('Status', 'Completed')
            ->get();
        $completedTimes = collect($this->occupiedSlots($completedAppointments))
            ->keys()
            ->map(fn ($key) => explode('_', $key, 2)[1])
            ->all();

        $editableTimes = array_diff(array_keys($this->slots()), $completedTimes);

        if (empty($editableTimes)) {
            return redirect()
                ->route('dentistSchedule', ['month' => $request->input('month', now()->format('Y-m'))])
                ->with('error', 'Every slot that day is already held by a completed appointment.');
        }

        $rows = DentistSchedule::where('Date', $request->date)->whereIn('Time', $editableTimes)->get()->keyBy('Time');
        $allClosed = collect($editableTimes)->every(fn ($time) => ($rows[$time]->Status ?? 'Available') === 'Not Available');
        $newStatus = $allClosed ? 'Available' : 'Not Available';

        foreach ($editableTimes as $time) {
            $slot = DentistSchedule::firstOrCreate(
                ['Date' => $request->date, 'Time' => $time],
                ['Status' => 'Available']
            );
            $slot->Status = $newStatus;
            $slot->save();
        }

        $cancelledCount = 0;

        if ($newStatus === 'Not Available') {
            $affectedAppointments = Appointment::with('patientInfo.userAccount')
                ->whereDate('AppointmentDate', $request->date)
                ->whereIn('Status', ['Pending', 'Approved'])
                ->get();

            $dateLabel = $date->format('F j, Y');

            foreach ($affectedAppointments as $appointment) {
                $appointment->Status = 'Cancelled';
                $appointment->DeclineReason = 'The clinic is closed on this date.';
                $appointment->save();
                $cancelledCount++;

                $timeLabel = Carbon::createFromFormat('H:i', $appointment->AppointmentTime)->format('g:i A');
                $user = $appointment->patientInfo->userAccount ?? null;

                if ($user) {
                    $this->notifications->notifyUser(
                        $user,
                        'Appointment Cancelled — Clinic Closed',
                        "We're sorry, but your appointment on {$dateLabel} at {$timeLabel} has been cancelled because the clinic will be closed that day. Please feel free to book another available date.",
                        'danger',
                        $appointment->AppointmentID,
                        'Cancelled'
                    );
                }
            }

            if ($cancelledCount > 0) {
                $this->notifications->notifyAdmins(
                    'Appointments Cancelled — Clinic Closed',
                    "{$cancelledCount} appointment(s) on {$dateLabel} were cancelled because the day was marked closed.",
                    'warning'
                );
            }
        }

        $this->auditLog->log(
            'Edit',
            "Set all slots on {$request->date} to {$newStatus}."
                . ($cancelledCount > 0 ? " Cancelled {$cancelledCount} appointment(s) due to closure." : '')
        );

        $successMessage = $newStatus === 'Not Available'
            ? ('Day marked as closed.' . ($cancelledCount > 0 ? " {$cancelledCount} appointment(s) were cancelled and the patient(s) notified." : ''))
            : 'Day reopened.';

        return redirect()
            ->route('dentistSchedule', ['month' => $request->input('month', now()->format('Y-m'))])
            ->with('success', $successMessage);
    }
}
