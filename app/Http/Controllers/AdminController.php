<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\DentistSchedule;
use App\Models\PatientInfo;
use App\Models\Service;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    // show dashboard front end
    public function showDashboard()
    {
        return view('superAdmin.dashboard', [
            'stats' => $this->dashboardStats(),
            'overviewChart' => $this->appointmentsOverviewChart(),
            'treatmentDonut' => $this->patientsByTreatmentDonut(),
            'serviceBars' => $this->appointmentsByServiceBars(),
        ]);
    }

    protected function dashboardStats(): array
    {
        return [
            'totalPatients' => PatientInfo::count(),
            'appointmentsToday' => Appointment::whereDate('AppointmentDate', today())->count(),
            'availableServices' => Service::where('IsArchived', false)->count(),
            'doctorAvailableSchedule' => DentistSchedule::where('Status', 'Available')
                ->whereDate('Date', '>=', today())
                ->count(),
        ];
    }

    /**
     * Appointments booked per day for the last 7 days, rendered as an SVG
     * area+line path over the existing 600x240 viewBox.
     */
    protected function appointmentsOverviewChart(): array
    {
        $days = collect(range(6, 0))->map(fn ($daysAgo) => today()->subDays($daysAgo));

        $counts = $days->map(fn ($date) => Appointment::whereDate('AppointmentDate', $date)->count());
        $max = max(1, $counts->max());

        $stepX = 600 / (count($counts) - 1);
        $pointData = $counts->values()->map(function ($count, $i) use ($stepX, $max, $days) {
            $x = round($i * $stepX, 1);
            $y = round(210 - ($count / $max) * 170, 1);
            return [
                'x' => $x,
                'y' => $y,
                'count' => $count,
                'label' => $days[$i]->format('D, M j'),
                'day' => $days[$i]->format('D'),
            ];
        });

        $linePath = 'M' . $pointData->map(fn ($p) => "{$p['x']},{$p['y']}")->implode(' L');
        $areaPath = $linePath . " L600,240 L0,240 Z";

        return [
            'linePath' => $linePath,
            'areaPath' => $areaPath,
            'total' => $counts->sum(),
            'labels' => $days->map(fn ($d) => $d->format('D')),
            'points' => $pointData,
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
