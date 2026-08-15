<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\DentistSchedule;
use App\Models\Appointment;

class DentistScheduleController extends Controller
{
    // 24h value => display label, matches the original static modal
    protected array $slots = [
        '09:00' => '9:00',
        '10:00' => '10:00',
        '11:00' => '11:00',
        '13:00' => '1:00',
        '14:00' => '2:00',
        '15:00' => '3:00',
        '16:00' => '4:00',
        '17:00' => '5:00',
        '18:00' => '6:00',
    ];

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
            // Default: year 2026, current calendar month (e.g. loading in August
            // with no query params opens August 2026, not the real current year)
            $defaultMonth = '2026-' . now()->format('m');
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
            ->whereIn('Status', ['Pending', 'Approved'])
            ->get();
        $occupiedSlots = $this->occupiedSlots($appointments);

        $totalSlotsPerDay = count($this->slots);

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
                $occupiedCount = collect($this->slots)->filter(fn($_, $time) => isset($occupiedSlots[$dStr . '_' . $time]))->count();
                $manualUnavailable = $rows->where('Status', 'Not Available')->count();
                $totalAvailableThisMonth += ($totalSlotsPerDay - max($occupiedCount, $manualUnavailable));
            }
            $monthCursor->addDay();
        }

        return view('superAdmin.dentist-schedule', [
            'weeks' => $weeks,
            'current' => $current,
            'schedules' => $schedules,
            'slots' => $this->slots,
            'totalSlotsPerDay' => $totalSlotsPerDay,
            'today' => now()->format('Y-m-d'),
            'totalAvailableThisMonth' => $totalAvailableThisMonth,
            'occupiedSlots' => $occupiedSlots,
        ]);
    }

    public function toggle(Request $request)
    {
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

        return redirect()
            ->route('dentistSchedule', ['month' => $request->input('month', now()->format('Y-m'))])
            ->with('success', 'Schedule updated.');
    }

    protected function occupiedSlots($appointments): array
    {
        $occupied = [];
        $times = array_keys($this->slots);
        foreach ($appointments as $appointment) {
            $start = array_search($appointment->AppointmentTime, $times, true);
            if ($start === false) continue;
            $duration = $appointment->Status === 'Approved' ? max(1, (int) ($appointment->DurationHours ?? 1)) : 1;
            for ($offset = 0; $offset < $duration && isset($times[$start + $offset]); $offset++) {
                $occupied[$appointment->AppointmentDate->format('Y-m-d') . '_' . $times[$start + $offset]] = $appointment;
            }
        }
        return $occupied;
    }

    protected function isAppointmentSlot(string $date, string $time): bool
    {
        return isset($this->occupiedSlots(
            Appointment::whereDate('AppointmentDate', $date)->whereIn('Status', ['Pending', 'Approved'])->get()
        )[$date . '_' . $time]);
    }
}
