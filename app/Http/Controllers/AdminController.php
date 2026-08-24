<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\DentistSchedule;
use App\Models\PatientInfo;
use App\Models\Service;
use App\Models\UserAccount;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    // Same 9-slot day used everywhere else this schedule is computed
    // (BuildsBookingCalendar, DentistScheduleController, AppointmentApprovalController).
    protected array $slots = ['09:00', '10:00', '11:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00'];

    // show dashboard front end
    public function showDashboard()
    {
        return view('superAdmin.dashboard', [
            'stats' => $this->dashboardStats(),
            'overviewChart' => $this->appointmentsOverviewChart(),
            'treatmentDonut' => $this->patientsByTreatmentDonut(),
            'serviceBars' => $this->appointmentsByServiceBars(),
            'adminName' => $this->currentAdminName(),
        ]);
    }

    protected function dashboardStats(): array
    {
        return [
            'totalPatients' => PatientInfo::count(),
            'appointmentsToday' => Appointment::whereDate('AppointmentDate', today())->count(),
            'availableServices' => Service::where('IsArchived', false)->count(),
            'doctorAvailableSchedule' => $this->availableScheduleThisMonth(),
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
        $totalSlotsPerDay = count($this->slots);
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
            $start = array_search($appointment->AppointmentTime, $this->slots, true);
            if ($start === false) {
                continue;
            }
            $duration = in_array($appointment->Status, ['Approved', 'Completed'], true)
                ? max(1, (int) ($appointment->DurationHours ?? 1))
                : 1;
            for ($offset = 0; $offset < $duration && isset($this->slots[$start + $offset]); $offset++) {
                $occupied[$appointment->AppointmentDate->format('Y-m-d') . '_' . $this->slots[$start + $offset]] = true;
            }
        }

        $total = 0;
        $cursor = $monthStart->copy();
        while ($cursor->lte($monthEnd)) {
            if (!$cursor->isSunday()) {
                $dateKey = $cursor->format('Y-m-d');
                $rows = $schedules->get($dateKey, collect());
                $occupiedCount = collect($this->slots)->filter(fn ($time) => isset($occupied[$dateKey . '_' . $time]))->count();
                $manualUnavailable = $rows->where('Status', 'Not Available')->count();
                $total += $totalSlotsPerDay - max($occupiedCount, $manualUnavailable);
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
     * Appointments booked per day for the last 7 days, rendered as a smooth
     * SVG area+line path over the existing 600x240 viewBox. Top/bottom
     * padding keeps value pills and day labels from being clipped or
     * crowding the plotted line.
     */
    protected function appointmentsOverviewChart(): array
    {
        $days = collect(range(6, 0))->map(fn ($daysAgo) => today()->subDays($daysAgo));

        $counts = $days->map(fn ($date) => Appointment::whereDate('AppointmentDate', $date)->count());
        $max = max(1, $counts->max());

        $padTop = 34;
        $padBottom = 50;
        $plotHeight = 240 - $padTop - $padBottom;
        $baselineY = $padTop + $plotHeight;
        $lastIndex = count($counts) - 1;

        $stepX = 600 / $lastIndex;
        $pointData = $counts->values()->map(function ($count, $i) use ($stepX, $max, $days, $padTop, $plotHeight, $lastIndex) {
            $x = round($i * $stepX, 1);
            $y = round($padTop + (1 - $count / $max) * $plotHeight, 1);
            $anchor = $i === 0 ? 'start' : ($i === $lastIndex ? 'end' : 'middle');
            $pillWidth = 30;
            $pillX = round(match ($anchor) {
                'start' => $x,
                'end' => $x - $pillWidth,
                default => $x - $pillWidth / 2,
            }, 1);

            return [
                'x' => $x,
                'y' => $y,
                'count' => $count,
                'label' => $days[$i]->format('D, M j'),
                'day' => $days[$i]->format('D'),
                'anchor' => $anchor,
                'pillX' => $pillX,
                'pillWidth' => $pillWidth,
            ];
        });

        // Smooth cubic-bezier curve through the points (control points sit
        // at the horizontal midpoint of each segment) instead of straight
        // line segments, which looked jagged for sparse day-to-day data.
        $linePath = '';
        foreach ($pointData as $i => $point) {
            if ($i === 0) {
                $linePath .= "M{$point['x']},{$point['y']}";
                continue;
            }
            $prev = $pointData[$i - 1];
            $midX = round($prev['x'] + ($point['x'] - $prev['x']) / 2, 1);
            $linePath .= " C{$midX},{$prev['y']} {$midX},{$point['y']} {$point['x']},{$point['y']}";
        }

        $areaPath = $linePath . " L600,{$baselineY} L0,{$baselineY} Z";

        $gridLines = collect(range(0, 3))->map(fn ($i) => round($padTop + ($plotHeight / 3) * $i, 1));

        return [
            'linePath' => $linePath,
            'areaPath' => $areaPath,
            'total' => $counts->sum(),
            'labels' => $days->map(fn ($d) => $d->format('D')),
            'points' => $pointData,
            'gridLines' => $gridLines,
            'baselineY' => $baselineY,
        ];
    }

    /**
     * Distinct patients per service (top 3 + "Other"), as donut segments.
     * Radius 70 / stroke-width 28 match the existing SVG exactly, so the
     * circumference (2 * pi * 70 ≈ 440) lines up with the pre-existing
     * stroke-dasharray total used in the markup.
     */
    protected function patientsByTreatmentDonut(): array
    {
        $circumference = 2 * M_PI * 70;
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
        $offset = 0;
        foreach ($top as $i => $row) {
            $length = $total > 0 ? ($row->patient_count / $total) * $circumference : 0;
            $segments->push([
                'label' => $row->service->ServiceName ?? 'Service',
                'count' => $row->patient_count,
                'color' => $colors[$i] ?? '#94a3b8',
                'dasharray' => round($length, 1) . ' ' . round($circumference, 1),
                'dashoffset' => round(-$offset, 1),
            ]);
            $offset += $length;
        }
        if ($otherCount > 0) {
            $length = $total > 0 ? ($otherCount / $total) * $circumference : 0;
            $segments->push([
                'label' => 'Other',
                'count' => $otherCount,
                'color' => $colors[3],
                'dasharray' => round($length, 1) . ' ' . round($circumference, 1),
                'dashoffset' => round(-$offset, 1),
            ]);
        }

        return [
            'segments' => $segments,
            'total' => $total,
        ];
    }

    /**
     * Appointment volume per (non-archived) service — replaces the old
     * hardcoded "Revenue by Service" bar chart now that price/revenue
     * tracking has been removed from the system.
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

        $max = max(1, $services->max('count') ?? 1);

        $bars = $services->map(function ($s, $i) use ($max, $colors) {
            $x = 40 + $i * 80;
            $height = max(6, round(($s['count'] / $max) * 180));
            $y = 220 - $height;
            return [
                'name' => $s['name'],
                'count' => $s['count'],
                'x' => $x,
                'y' => $y,
                'height' => $height,
                'color' => $colors[$i % count($colors)],
            ];
        });

        return ['bars' => $bars];
    }

    // show staff account front end
    public function showStaffAcc()
    {
        return view('superAdmin.staff-accounts');
    }
    // show user account front end
    public function showUserAcc()
    {
        return view('superAdmin.user-accounts');
    }
    // show dentist schedule front end
    public function showDentistSchedule()
    {
        return view('superAdmin.dentist-schedule');
    }
    // show appointment approval front end
    public function showAppointmentApproval()
    {
        return view('superAdmin.appointment-approval');
    }
    // show appointments front end
    public function showAppointments()
    {
        return view('superAdmin.appointments');
    }
    // show patient records front end
    public function showPatientRecords()
    {
        return view('superAdmin.patient-records');
    }
}
