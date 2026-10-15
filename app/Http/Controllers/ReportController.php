<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\PatientInfo;
use App\Models\PatientRecord;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    protected const TYPES = ['appointments', 'patients', 'summary'];

    /** tbl_appointments.Source value => label shown on reports. */
    public const SOURCES = [
        'Online' => 'Online Appointment',
        'Walk-in' => 'Walk-in',
        'Follow-up' => 'Follow-up',
    ];

    // Access control (logged-in admin only) is the 'admin' route middleware.

    public function generate(Request $request)
    {
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

        // Reports count COMPLETED visits only — the ones that actually
        // happened: every completed appointment (online, walk-in or
        // follow-up), plus completed walk-in visits staff recorded straight
        // into Patient Records without an appointment. Pending / approved /
        // cancelled / declined are left out.
        $completed = $this->completedVisits($start, $end);

        if (in_array($type, ['appointments', 'summary'], true)) {
            // Categorize by the services each visit covered. A visit with
            // several services is listed under each of them.
            $byService = [];
            foreach ($completed as $appointment) {
                foreach ($this->serviceNames($appointment) as $name) {
                    $byService[$name][] = $appointment;
                }
            }
            uksort($byService, fn ($a, $b) => count($byService[$b]) <=> count($byService[$a]) ?: strcasecmp($a, $b));

            $data['appointmentStats'] = [
                'completed' => $completed->count(),
                'patients' => $completed->pluck('PatientID')->unique()->count(),
                'services' => count($byService),
            ];
            $data['appointmentsByService'] = $byService;

            // Every booking type is always listed, even at zero, so the report
            // shows Online, Walk-in and Follow-up side by side.
            $bySource = collect(self::SOURCES)->mapWithKeys(fn ($label, $source) => [
                $label => $completed->where('Source', $source)->count(),
            ]);
            $data['appointmentsBySource'] = $bySource->all();

            if ($includeCharts) {
                $data['sourceChart'] = $this->barRows($bySource, true);
                $data['dailyChart'] = $this->visitsTimeSeries($completed, $start, $end);
                $data['completedServiceChart'] = $this->barRows(collect($byService)->map(fn ($rows) => count($rows)));
            }
        }

        if (in_array($type, ['patients', 'summary'], true)) {
            // Every patient (registered or walk-in) with at least one
            // completed visit in range.
            $byPatient = $completed->groupBy('PatientID');

            $patients = PatientInfo::with('userAccount')
                ->whereIn('PatientID', $byPatient->keys())
                ->orderBy('LastName')
                ->orderBy('FirstName')
                ->get()
                ->each(function ($patient) use ($byPatient) {
                    $rows = $byPatient[$patient->PatientID];
                    $patient->completedVisits = $rows->count();
                    $patient->lastCompletedVisit = $rows->max('AppointmentDate');
                });

            $data['patientStats'] = [
                'total' => $patients->count(),
                'registered' => $patients->filter(fn ($p) => $p->patient_type === 'Registered')->count(),
                'guest' => $patients->filter(fn ($p) => $p->patient_type === 'Guest')->count(),
                'walkin' => $patients->filter(fn ($p) => $p->patient_type === 'Walk-in')->count(),
                'male' => $patients->where('Gender', 'Male')->count(),
                'female' => $patients->where('Gender', 'Female')->count(),
            ];

            if ($includeCharts) {
                // Distinct patients per treatment.
                $perService = [];
                foreach ($completed as $appointment) {
                    foreach ($this->serviceNames($appointment) as $name) {
                        $perService[$name][$appointment->PatientID] = true;
                    }
                }
                $data['treatmentBreakdown'] = $this->barRows(collect($perService)->map(fn ($ids) => count($ids))->sortDesc());
            }

            if ($includePatientDetails) {
                $data['patientsList'] = $patients->values();
            }
        }

        if ($type === 'summary' && $includeCharts) {
            $perService = [];
            foreach ($completed as $appointment) {
                foreach ($this->serviceNames($appointment) as $name) {
                    $perService[$name] = ($perService[$name] ?? 0) + 1;
                }
            }
            $data['serviceBreakdown'] = $this->barRows(collect($perService)->sortDesc());
        }


        return view('superAdmin.report', $data);
    }

    /**
     * @return array{0: ?Carbon, 1: ?Carbon, 2: string}
     */
    protected function resolveRange(string $range, ?string $from, ?string $to): array
    {
        $today = today();

        // Same calendar periods as the dashboard filter (Daily = this week,
        // Monthly = this year by month, Quarterly = this year by quarter,
        // Yearly = every year on record).
        if (in_array($range, ['daily', 'monthly', 'quarterly', 'yearly'], true)) {
            $p = app(AdminController::class)->periodRange($range);
            $label = match ($range) {
                'monthly' => $p['label'] . ' (by month)',
                'yearly' => 'All years (' . $p['label'] . ')',
                default => $p['label'],
            };

            return [$p['start'], $p['end'], $label];
        }

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

    /**
     * Every completed visit within the range, oldest first, as Appointment
     * models: completed appointments, plus completed Patient Records entries
     * with no appointment behind them (walk-ins recorded directly by staff).
     * Those are built as unsaved Appointments with Source "Walk-in" so every
     * report section can treat them the same way.
     */
    protected function completedVisits(?Carbon $start, ?Carbon $end)
    {
        $appointments = Appointment::query()
            ->where('Status', 'Completed')
            ->when($start, fn ($q) => $q->whereDate('AppointmentDate', '>=', $start))
            ->when($end, fn ($q) => $q->whereDate('AppointmentDate', '<=', $end))
            ->with(['patientInfo.userAccount', 'service', 'services', 'dentist.staffInfo'])
            ->get();

        $walkInRecords = PatientRecord::query()
            ->whereNull('AppointmentID')
            ->where('Status', 'Completed')
            ->when($start, fn ($q) => $q->whereDate('VisitDate', '>=', $start))
            ->when($end, fn ($q) => $q->whereDate('VisitDate', '<=', $end))
            ->with(['patientInfo.userAccount', 'service'])
            ->get()
            ->map(function ($record) {
                $visit = new Appointment([
                    'PatientID' => $record->PatientID,
                    'ServiceID' => $record->ServiceID,
                    'AppointmentDate' => $record->VisitDate,
                    'AppointmentTime' => $record->VisitTime ? substr($record->VisitTime, 0, 5) : null,
                    'TypeOfAppointment' => $record->Service,
                    'Source' => 'Walk-in',
                    'Status' => 'Completed',
                ]);
                $visit->fromRecord = true;
                $visit->setRelation('patientInfo', $record->getRelation('patientInfo'));
                $visit->setRelation('service', $record->getRelation('service'));
                $visit->setRelation('services', collect());
                $visit->setRelation('dentist', null);

                return $visit;
            });

        return $appointments->toBase()->concat($walkInRecords)
            ->sortBy(fn ($a) => $a->AppointmentDate->format('Y-m-d') . ' ' . $a->AppointmentTime)
            ->values();
    }

    /** The service names a visit covered — never empty. */
    protected function serviceNames(Appointment $appointment): array
    {
        $names = $appointment->services->pluck('ServiceName')->unique()->values()->all();
        if ($names) {
            return $names;
        }

        // "Cleaning, Tooth Extraction" → ['Cleaning', 'Tooth Extraction']
        $raw = $appointment->TypeOfAppointment ?: ($appointment->service->ServiceName ?? '');
        $names = array_values(array_unique(array_filter(array_map('trim', explode(',', $raw)))));

        return $names ?: ['Unspecified service'];
    }

    /** label => count  →  bar-chart rows with a width percentage. */
    protected function barRows($counts, bool $keepZeros = false): array
    {
        $max = max(1, $counts->max() ?? 1);

        return $counts->map(fn ($count, $label) => [
            'label' => $label,
            'count' => $count,
            'pct' => ($keepZeros && !$count) ? 0 : max(4, (int) round($count / $max * 100)),
        ])->values()->all();
    }

    /**
     * Bucket visit counts by day (short ranges) or by month (long/all-time ranges).
     */
    protected function visitsTimeSeries($visits, ?Carbon $start, ?Carbon $end): array
    {
        if (!$start || !$end || $start->diffInDays($end) > 31) {
            $counts = $visits->groupBy(fn ($a) => $a->AppointmentDate->format('Y-m'))
                ->sortKeys()
                ->mapWithKeys(fn ($rows, $period) => [Carbon::createFromFormat('Y-m', $period)->format('M Y') => $rows->count()]);
        } else {
            $perDay = $visits->countBy(fn ($a) => $a->AppointmentDate->format('Y-m-d'));
            $counts = collect();
            for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
                $counts[$d->format('M j')] = $perDay[$d->format('Y-m-d')] ?? 0;
            }
        }

        return $this->barRows($counts);
    }
}
