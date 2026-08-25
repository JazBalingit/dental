<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\DentistSchedule;
use App\Models\PatientInfo;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    protected const TYPES = ['appointments', 'patients', 'schedule', 'summary'];

    protected function guard()
    {
        if (!session('user_id') || session('user_role') !== 'admin') {
            return redirect()->route('login')->with('login_error', 'Please log in as an administrator to continue.');
        }

        return null;
    }

    public function generate(Request $request)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $type = in_array($request->query('type'), self::TYPES, true) ? $request->query('type') : 'appointments';
        $range = $request->query('range', 'week');
        [$start, $end, $rangeLabel] = $this->resolveRange($range, $request->query('from'), $request->query('to'));

        $includeCharts = $request->boolean('charts', true);
        $includePatientDetails = $request->boolean('patients', false);

        $data = [
            'type' => $type,
            'rangeLabel' => $rangeLabel,
            'start' => $start,
            'end' => $end,
            'includeCharts' => $includeCharts,
            'includePatientDetails' => $includePatientDetails,
            'generatedAt' => now(),
        ];

        if (in_array($type, ['appointments', 'summary'], true)) {
            $appointments = $this->appointmentsQuery($start, $end);

            $data['appointmentStats'] = [
                'total' => (clone $appointments)->count(),
                'pending' => (clone $appointments)->where('Status', 'Pending')->count(),
                'approved' => (clone $appointments)->where('Status', 'Approved')->count(),
                'completed' => (clone $appointments)->where('Status', 'Completed')->count(),
                'cancelled' => (clone $appointments)->where('Status', 'Cancelled')->count(),
                'declined' => (clone $appointments)->where('Status', 'Declined')->count(),
            ];

            if ($includeCharts) {
                $data['dailyChart'] = $this->appointmentsTimeSeries($start, $end);
            }

            if ($includePatientDetails) {
                $data['appointmentsList'] = (clone $appointments)
                    ->with(['patientInfo.userAccount', 'service'])
                    ->orderByDesc('AppointmentDate')
                    ->orderByDesc('AppointmentTime')
                    ->get();
            }
        }

        if (in_array($type, ['patients', 'summary'], true)) {
            $patients = PatientInfo::with('userAccount')->get();

            if ($start && $end) {
                $idsWithAppointment = (clone $this->appointmentsQuery($start, $end))->pluck('PatientID')->unique();
                $patients = $patients->filter(function ($patient) use ($idsWithAppointment, $start, $end) {
                    if ($idsWithAppointment->contains($patient->PatientID)) {
                        return true;
                    }
                    $registered = $patient->userAccount?->DateCreated;
                    return $registered && Carbon::parse($registered)->between($start, $end);
                })->values();
            }

            $data['patientStats'] = [
                'total' => $patients->count(),
                'male' => $patients->where('Gender', 'Male')->count(),
                'female' => $patients->where('Gender', 'Female')->count(),
            ];

            if ($includeCharts) {
                $data['treatmentBreakdown'] = $this->serviceBreakdown($start, $end, true);
            }

            if ($includePatientDetails) {
                $data['patientsList'] = $patients->values();
            }
        }

        if (in_array($type, ['schedule', 'summary'], true)) {
            $schedule = $this->scheduleQuery($start, $end);

            $data['scheduleStats'] = [
                'total' => (clone $schedule)->count(),
                'available' => (clone $schedule)->where('Status', 'Available')->count(),
                'booked' => (clone $schedule)->where('Status', '!=', 'Available')->count(),
            ];

            if ($includeCharts) {
                $data['scheduleBreakdown'] = $this->scheduleBreakdown($start, $end);
            }

            if ($includePatientDetails) {
                $data['scheduleList'] = (clone $schedule)
                    ->orderBy('Date')
                    ->orderBy('Time')
                    ->get();
            }
        }

        if ($type === 'summary' && $includeCharts) {
            $data['serviceBreakdown'] = $this->serviceBreakdown($start, $end, false);
        }

        return view('superAdmin.report', $data);
    }

    /**
     * @return array{0: ?Carbon, 1: ?Carbon, 2: string}
     */
    protected function resolveRange(string $range, ?string $from, ?string $to): array
    {
        $today = today();

        return match ($range) {
            'today' => [$today->copy(), $today->copy(), 'Today (' . $today->format('M j, Y') . ')'],
            'month' => [$today->copy()->startOfMonth(), $today->copy(), 'This month (' . $today->format('F Y') . ')'],
            'all' => [null, null, 'All time'],
            'custom' => $this->resolveCustomRange($from, $to),
            default => [$today->copy()->subDays(6), $today->copy(), 'Last 7 days'],
        };
    }

    protected function resolveCustomRange(?string $from, ?string $to): array
    {
        $today = today();
        $start = $from ? Carbon::parse($from) : $today->copy()->subDays(6);
        $end = $to ? Carbon::parse($to) : $today->copy();

        if ($start->gt($end)) {
            [$start, $end] = [$end, $start];
        }

        return [$start, $end, $start->format('M j, Y') . ' – ' . $end->format('M j, Y')];
    }

    protected function appointmentsQuery(?Carbon $start, ?Carbon $end)
    {
        return Appointment::query()
            ->when($start, fn ($q) => $q->whereDate('AppointmentDate', '>=', $start))
            ->when($end, fn ($q) => $q->whereDate('AppointmentDate', '<=', $end));
    }

    protected function scheduleQuery(?Carbon $start, ?Carbon $end)
    {
        return DentistSchedule::query()
            ->when($start, fn ($q) => $q->whereDate('Date', '>=', $start))
            ->when($end, fn ($q) => $q->whereDate('Date', '<=', $end));
    }

    /**
     * Bucket appointment counts by day (short ranges) or by month (long/all-time ranges).
     */
    protected function appointmentsTimeSeries(?Carbon $start, ?Carbon $end): array
    {
        if (!$start || !$end || $start->diffInDays($end) > 31) {
            $query = Appointment::query()
                ->when($start, fn ($q) => $q->whereDate('AppointmentDate', '>=', $start))
                ->when($end, fn ($q) => $q->whereDate('AppointmentDate', '<=', $end));

            $rows = $query->selectRaw("DATE_FORMAT(AppointmentDate, '%Y-%m') as period, COUNT(*) as cnt")
                ->groupBy('period')
                ->orderBy('period')
                ->get();

            $series = $rows->map(fn ($r) => [
                'label' => Carbon::createFromFormat('Y-m', $r->period)->format('M Y'),
                'count' => (int) $r->cnt,
            ]);
        } else {
            $series = collect();
            for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
                $series->push([
                    'label' => $d->format('M j'),
                    'count' => Appointment::whereDate('AppointmentDate', $d)->count(),
                ]);
            }
        }

        $max = max(1, $series->max('count') ?? 1);

        return $series->map(fn ($s) => array_merge($s, ['pct' => max(4, (int) round($s['count'] / $max * 100))]))
            ->values()
            ->all();
    }

    /**
     * Appointment volume per service within the range, optionally counted by distinct patient.
     */
    protected function serviceBreakdown(?Carbon $start, ?Carbon $end, bool $byDistinctPatient): array
    {
        $select = $byDistinctPatient
            ? 'ServiceID, COUNT(DISTINCT PatientID) as cnt'
            : 'ServiceID, COUNT(*) as cnt';

        $rows = Appointment::query()
            ->when($start, fn ($q) => $q->whereDate('AppointmentDate', '>=', $start))
            ->when($end, fn ($q) => $q->whereDate('AppointmentDate', '<=', $end))
            ->whereNotNull('ServiceID')
            ->selectRaw($select)
            ->groupBy('ServiceID')
            ->orderByDesc('cnt')
            ->with('service')
            ->get();

        $max = max(1, $rows->max('cnt') ?? 1);

        return $rows->map(fn ($r) => [
            'label' => $r->service->ServiceName ?? 'Service',
            'count' => (int) $r->cnt,
            'pct' => max(4, (int) round($r->cnt / $max * 100)),
        ])->values()->all();
    }

    /**
     * Dentist schedule slots grouped by status within the range.
     */
    protected function scheduleBreakdown(?Carbon $start, ?Carbon $end): array
    {
        $rows = $this->scheduleQuery($start, $end)
            ->selectRaw('Status, COUNT(*) as cnt')
            ->groupBy('Status')
            ->orderByDesc('cnt')
            ->get();

        $max = max(1, $rows->max('cnt') ?? 1);

        return $rows->map(fn ($r) => [
            'label' => $r->Status,
            'count' => (int) $r->cnt,
            'pct' => max(4, (int) round($r->cnt / $max * 100)),
        ])->values()->all();
    }
}
