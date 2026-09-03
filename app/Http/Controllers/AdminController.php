<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Appointment;
use App\Models\DentistSchedule;
use App\Models\PatientInfo;
use App\Models\Service;
use App\Models\UserAccount;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    // show dashboard front end
    public function showDashboard()
    {
        return $this->panelView('dashboard', [
            'stats' => $this->dashboardStats(),
            'overviewChart' => $this->appointmentsOverviewChart(),
            'treatmentDonut' => $this->patientsByTreatmentDonut(),
            'serviceBars' => $this->appointmentsByServiceBars(),
            'adminName' => $this->currentAdminName(),
            'recentActivity' => ActivityLog::with(['userAccount.patientInfo', 'userAccount.staffInfo'])
                ->where('IsArchived', false)
                ->orderByRaw('COALESCE(LoggedInTime, created_at) DESC')
                ->limit(6)
                ->get(),
        ]);
    }

    protected function dashboardStats(): array
    {
        return [
            'totalPatients' => PatientInfo::count(),
            'appointmentsToday' => Appointment::whereDate('AppointmentDate', today())->count(),
            'availableServices' => Service::where('IsArchived', false)->count(),
            'dentistAvailableSchedule' => $this->availableScheduleThisMonth(),
        ];
    }

    /**
     * Total open slots across the current calendar month — not just rows
     * that exist in tbl_dentistschedule with Status = 'Available', since a
     * slot nobody has ever touched has no row at all but is still
     * implicitly available. Mirrors the calculation DentistScheduleController
     * uses for its own "Available schedule this month" total.
     */
    protected function availableScheduleThisMonth(): int
    {
        $slots = DentistSchedule::slotTimes();
        $totalSlotsPerDay = count($slots);
        $monthStart = today()->startOfMonth();
        $monthEnd = today()->endOfMonth();

        $schedules = DentistSchedule::whereBetween('Date', [
            $monthStart->format('Y-m-d'),
            $monthEnd->format('Y-m-d'),
        ])->get()->groupBy(fn ($row) => $row->Date->format('Y-m-d'));

        $appointments = Appointment::whereBetween('AppointmentDate', [
            $monthStart->format('Y-m-d'),
            $monthEnd->format('Y-m-d'),
        ])->whereIn('Status', ['Pending', 'Approved', 'Completed'])->get();

        $occupied = [];
        foreach ($appointments as $appointment) {
            $start = array_search($appointment->AppointmentTime, $slots, true);
            if ($start === false) {
                continue;
            }
            $duration = $appointment->duration_slots;
            for ($offset = 0; $offset < $duration && isset($slots[$start + $offset]); $offset++) {
                $occupied[$appointment->AppointmentDate->format('Y-m-d') . '_' . $slots[$start + $offset]] = true;
            }
        }

        $total = 0;
        $cursor = $monthStart->copy();
        while ($cursor->lte($monthEnd)) {
            if (!$cursor->isSunday()) {
                $dateKey = $cursor->format('Y-m-d');
                $rows = $schedules->get($dateKey, collect());
                $occupiedCount = collect($slots)->filter(fn ($time) => isset($occupied[$dateKey . '_' . $time]))->count();
                // Distinct times any dentist has marked Not Available — kept
                // per-time so multiple dentists closing the same slot only
                // counts once toward "the clinic has nothing open here".
                $manualUnavailable = $rows->where('Status', 'Not Available')->pluck('Time')->unique()->count();
                $total += max(0, $totalSlotsPerDay - max($occupiedCount, $manualUnavailable));
            }
            $cursor->addDay();
        }

        return $total;
    }

    /**
     * Display name for the "Welcome back" greeting. Falls back gracefully:
     * staff accounts show their StaffInfo name, the seeded admin (no
     * StaffInfo row) shows their email, and the config-based super admin
     * (no tbl_useraccount row at all) shows the session email or "Admin".
     */
    protected function currentAdminName(): string
    {
        $user = UserAccount::with('staffInfo')->find(session('user_id'));

        $info = $user?->staffInfo;
        if ($info) {
            $name = trim(($info->FirstName ?? '') . ' ' . ($info->LastName ?? ''));
            if ($name !== '') {
                return $name;
            }
        }

        return $user->Email ?? session('user_email', 'Admin');
    }

    /**
     * Appointments booked per day for the last 7 days — plain labels/data
     * arrays for Chart.js to plot as a line chart.
     */
    protected function appointmentsOverviewChart(): array
    {
        $days = collect(range(6, 0))->map(fn ($daysAgo) => today()->subDays($daysAgo));
        $counts = $days->map(fn ($date) => Appointment::whereDate('AppointmentDate', $date)->count());

        return [
            'labels' => $days->map(fn ($d) => $d->format('D'))->values(),
            'fullLabels' => $days->map(fn ($d) => $d->format('D, M j'))->values(),
            'data' => $counts->values(),
            'total' => $counts->sum(),
        ];
    }

    /**
     * Distinct patients per service (top 3 + "Other") — plain labels/data/
     * colors arrays for Chart.js to plot as a doughnut chart.
     */
    protected function patientsByTreatmentDonut(): array
    {
        $colors = ['#167d1d', '#008f07', '#55d85e', '#10b981'];

        $byService = Appointment::selectRaw('ServiceID, COUNT(DISTINCT PatientID) as patient_count')
            ->whereNotNull('ServiceID')
            ->groupBy('ServiceID')
            ->orderByDesc('patient_count')
            ->with('service')
            ->get();

        $top = $byService->take(3);
        $otherCount = $byService->slice(3)->sum('patient_count');
        $total = $byService->sum('patient_count');

        $segments = collect();
        foreach ($top as $i => $row) {
            $segments->push([
                'label' => $row->service->ServiceName ?? 'Service',
                'count' => $row->patient_count,
                'color' => $colors[$i] ?? '#94a3b8',
            ]);
        }
        if ($otherCount > 0) {
            $segments->push([
                'label' => 'Other',
                'count' => $otherCount,
                'color' => $colors[3],
            ]);
        }

        return [
            'segments' => $segments,
            'total' => $total,
        ];
    }

    /**
     * Appointment volume per (non-archived) service — plain labels/data/
     * colors arrays for Chart.js to plot as a bar chart.
     */
    protected function appointmentsByServiceBars(): array
    {
        $colors = ['#14532d', '#167d1d', '#2e8532', '#37a03e', '#33bd3c', '#059669', '#10b981'];

        $services = Service::where('IsArchived', false)
            ->get()
            ->map(fn ($s) => [
                'name' => $s->ServiceName,
                'count' => Appointment::where('ServiceID', $s->ServiceID)->count(),
            ])
            ->sortByDesc('count')
            ->take(7)
            ->values();

        $bars = $services->map(fn ($s, $i) => [
            'name' => $s['name'],
            'count' => $s['count'],
            'color' => $colors[$i % count($colors)],
        ]);

        return ['bars' => $bars];
    }

}
