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
    /** The dashboard's period filter — value => dropdown label. */
    public const PERIODS = [
        'daily' => 'Daily',
        'monthly' => 'Monthly',
        'quarterly' => 'Quarterly',
        'yearly' => 'Yearly',
        'custom' => 'Custom Range',
    ];

    // show dashboard front end
    public function showDashboard(Request $request)
    {
        $period = array_key_exists($request->query('period'), self::PERIODS) ? $request->query('period') : 'daily';
        $range = $this->periodRange($period, $request->query('from'), $request->query('to'));

        return $this->panelView('dashboard', [
            'period' => $period,
            // Generate Report's "Date range" dropdown — same periods as the
            // dashboard filter, each labelled with its actual dates.
            'reportPeriods' => collect(['daily', 'monthly', 'quarterly', 'yearly'])
                ->mapWithKeys(fn ($p) => [$p => self::PERIODS[$p] . ' — ' . $this->periodRange($p)['label']])
                ->put('custom', 'Custom Range')
                ->all(),
            'periods' => self::PERIODS,
            'range' => $range,
            'stats' => $this->dashboardStats($range),
            'overviewChart' => $this->appointmentsOverviewChart($range),
            'statusDonut' => $this->appointmentsByStatusDonut($range),
            'serviceBars' => $this->appointmentsByServiceBars($range),
            'adminName' => $this->currentAdminName(),
            'recentActivity' => ActivityLog::with(['userAccount.patientInfo', 'userAccount.staffInfo'])
                ->meaningful()
                ->where('IsArchived', false)
                ->orderByRaw('COALESCE(LoggedInTime, created_at) DESC')
                ->limit(6)
                ->get(),
        ]);
    }

    /** How far back the Yearly view reaches at most, so the chart stays readable. */
    public const MAX_YEARS = 10;

    /**
     * The calendar window the dashboard is showing, and how the overview
     * chart groups it:
     *   Daily     → this week, Mon–Sun, one point per day
     *   Monthly   → this year, Jan–Dec, one point per month
     *   Quarterly → this year, Q1–Q4, one point per quarter
     *   Yearly    → first year with any appointment (at most MAX_YEARS back)
     *               → this year, one point per year
     *   Custom    → ?from=&to= (swapped if backwards; falls back to Daily
     *               when missing/invalid), grouping picked by its length
     *
     * Keys: start, end (Carbon), unit (chart grouping, null = auto),
     * from/to (Y-m-d, for the date inputs), label, noun (card/pill wording).
     */
    public function periodRange(string $period, ?string $from = null, ?string $to = null): array
    {
        $today = today();

        $range = match ($period) {
            'monthly' => [
                'start' => $today->copy()->startOfYear(),
                'end' => $today->copy()->endOfYear()->startOfDay(),
                'unit' => 'month',
                'label' => 'January – December ' . $today->year,
                'noun' => $today->year,
            ],
            'quarterly' => [
                'start' => $today->copy()->startOfYear(),
                'end' => $today->copy()->endOfYear()->startOfDay(),
                'unit' => 'quarter',
                'label' => 'Q1 – Q4 ' . $today->year,
                'noun' => $today->year,
            ],
            'yearly' => (function () use ($today) {
                $firstYear = Appointment::min('AppointmentDate');
                $firstYear = $firstYear ? \Carbon\Carbon::parse($firstYear)->year : $today->year;
                $firstYear = max(min($firstYear, $today->year), $today->year - self::MAX_YEARS + 1);

                return [
                    'start' => \Carbon\Carbon::create($firstYear, 1, 1)->startOfDay(),
                    'end' => $today->copy()->endOfYear()->startOfDay(),
                    'unit' => 'year',
                    'label' => $firstYear === $today->year ? (string) $today->year : $firstYear . ' – ' . $today->year,
                    'noun' => $firstYear === $today->year ? (string) $today->year : $firstYear . '–' . $today->year,
                ];
            })(),
            default => [
                'start' => $today->copy()->startOfWeek(\Carbon\Carbon::MONDAY),
                'end' => $today->copy()->endOfWeek(\Carbon\Carbon::SUNDAY)->startOfDay(),
                'unit' => 'day',
                'label' => 'Week of ' . $today->copy()->startOfWeek(\Carbon\Carbon::MONDAY)->format('M j')
                    . ' – ' . $today->copy()->endOfWeek(\Carbon\Carbon::SUNDAY)->format('M j, Y'),
                'noun' => 'This Week',
            ],
        };

        if ($period === 'custom') {
            try {
                $customStart = \Carbon\Carbon::createFromFormat('Y-m-d', (string) $from)->startOfDay();
                $customEnd = \Carbon\Carbon::createFromFormat('Y-m-d', (string) $to)->startOfDay();
                if ($customStart->gt($customEnd)) {
                    [$customStart, $customEnd] = [$customEnd, $customStart];
                }
                $range = [
                    'start' => $customStart,
                    'end' => $customEnd,
                    'unit' => null,
                    'label' => $customStart->format('M j, Y') . ' – ' . $customEnd->format('M j, Y'),
                    'noun' => 'Custom Range',
                ];
            } catch (\Throwable $e) {
                // Missing/invalid dates — keep the Daily window.
            }
        }

        $range['noun'] = (string) $range['noun'];
        $range['from'] = $range['start']->format('Y-m-d');
        $range['to'] = $range['end']->format('Y-m-d');

        return $range;
    }

    protected function dashboardStats(array $range): array
    {
        return [
            'totalPatients' => PatientInfo::count(),
            'appointmentsInPeriod' => Appointment::whereBetween('AppointmentDate', [
                $range['start']->format('Y-m-d'),
                $range['end']->format('Y-m-d'),
            ])->count(),
            'availableServices' => Service::where('IsArchived', false)->count(),
            'dentistAvailableSchedule' => $this->availableScheduleBetween($range['start'], $range['end']),
        ];
    }

    /**
     * Total open slots across the given date range — not just rows
     * that exist in tbl_dentistschedule with Status = 'Available', since a
     * slot nobody has ever touched has no row at all but is still
     * implicitly available. Mirrors the calculation DentistScheduleController
     * uses for its own "Available schedule this month" total.
     */
    protected function availableScheduleBetween(\Carbon\Carbon $monthStart, \Carbon\Carbon $monthEnd): int
    {
        $slots = DentistSchedule::slotTimes();
        $totalSlotsPerDay = count($slots);

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
            if (!\App\Models\ClosedDate::isClosedWeekday($cursor)) {
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
     * Appointments per bucket across the selected window, for the line
     * chart — plain labels/data arrays for Chart.js. The presets fix the
     * grouping (day / month / quarter / year); a custom range picks it from
     * its length: up to ~1 month → per day; up to ~6 months → per week;
     * up to ~3 years → per month; longer → per year. Edge buckets are
     * clipped to the window, so nothing outside it is counted.
     */
    protected function appointmentsOverviewChart(array $range): array
    {
        $start = $range['start'];
        $end = $range['end'];
        $days = $start->diffInDays($end) + 1;
        $unit = $range['unit'] ?? ($days <= 31 ? 'day' : ($days <= 186 ? 'week' : ($days <= 1100 ? 'month' : 'year')));
        $multiYear = $start->year !== $end->year;

        // [bucket start, bucket end, short label, tooltip label]
        $buckets = collect();
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $bucketEnd = match ($unit) {
                'day' => $cursor->copy(),
                'week' => $cursor->copy()->endOfWeek(\Carbon\Carbon::SUNDAY)->startOfDay(),
                'month' => $cursor->copy()->endOfMonth()->startOfDay(),
                'quarter' => $cursor->copy()->endOfQuarter()->startOfDay(),
                'year' => $cursor->copy()->endOfYear()->startOfDay(),
            };
            if ($bucketEnd->gt($end)) {
                $bucketEnd = $end->copy();
            }

            $buckets->push(match ($unit) {
                'day' => [$cursor->copy(), $bucketEnd, $days <= 8 ? $cursor->format('D j') : $cursor->format('M j'), $cursor->format('D, M j, Y')],
                'week' => [$cursor->copy(), $bucketEnd, $cursor->format('M j'), $cursor->format('M j') . ' – ' . $bucketEnd->format('M j, Y')],
                'month' => [$cursor->copy(), $bucketEnd, $cursor->format($multiYear ? "M 'y" : 'M'), $cursor->format('M j') . ' – ' . $bucketEnd->format('M j, Y')],
                'quarter' => [$cursor->copy(), $bucketEnd, 'Q' . $cursor->quarter . ($multiYear ? " '" . $cursor->format('y') : ''),
                    'Q' . $cursor->quarter . ' · ' . $cursor->format('M j') . ' – ' . $bucketEnd->format('M j, Y')],
                'year' => [$cursor->copy(), $bucketEnd, $cursor->format('Y'), $cursor->format('M j, Y') . ' – ' . $bucketEnd->format('M j, Y')],
            });

            $cursor = $bucketEnd->copy()->addDay();
        }

        $from = $start->format('Y-m-d');
        $to = $end->format('Y-m-d');
        $dates = Appointment::whereBetween('AppointmentDate', [$from, $to])
            ->pluck('AppointmentDate')
            ->map(fn ($d) => \Carbon\Carbon::parse($d)->format('Y-m-d'));

        $counts = $buckets->map(fn ($b) => $dates->filter(
            fn ($d) => $d >= $b[0]->format('Y-m-d') && $d <= $b[1]->format('Y-m-d')
        )->count());

        return [
            'labels' => $buckets->map(fn ($b) => $b[2])->values(),
            'fullLabels' => $buckets->map(fn ($b) => $b[3])->values(),
            'data' => $counts->values(),
            'total' => $counts->sum(),
            'pill' => 'Per ' . $unit . ' · ' . $range['noun'],
            'totalLabel' => 'total from ' . $start->format('M j, Y') . ' to ' . $end->format('M j, Y'),
        ];
    }

    /**
     * Appointment counts by status — plain labels/data/colors arrays for
     * Chart.js to plot as a doughnut chart. Colors match the status pills
     * used everywhere else in the app (green/blue/yellow/red).
     */
    protected function appointmentsByStatusDonut(array $range): array
    {
        $statuses = [
            'Completed' => '#22c55e',
            'Approved' => '#3b82f6',
            'Pending' => '#f59e0b',
            'Declined' => '#ef4444',
            'Cancelled' => '#b91c1c',
            'No Show' => '#64748b',
        ];

        $counts = Appointment::selectRaw('Status, COUNT(*) as cnt')
            ->whereBetween('AppointmentDate', [$range['start']->format('Y-m-d'), $range['end']->format('Y-m-d')])
            ->whereIn('Status', array_keys($statuses))
            ->groupBy('Status')
            ->pluck('cnt', 'Status');

        $segments = collect();
        foreach ($statuses as $status => $color) {
            $segments->push([
                'label' => $status,
                'count' => (int) ($counts[$status] ?? 0),
                'color' => $color,
            ]);
        }

        return [
            'segments' => $segments,
            'total' => $segments->sum('count'),
        ];
    }

    /**
     * Appointment volume per (non-archived) service — plain labels/data/
     * colors arrays for Chart.js to plot as a bar chart.
     */
    protected function appointmentsByServiceBars(array $range): array
    {
        $colors = ['#14532d', '#167d1d', '#2e8532', '#37a03e', '#33bd3c', '#059669', '#10b981'];

        $countsByService = Appointment::selectRaw('ServiceID, COUNT(*) as cnt')
            ->whereBetween('AppointmentDate', [$range['start']->format('Y-m-d'), $range['end']->format('Y-m-d')])
            ->groupBy('ServiceID')
            ->pluck('cnt', 'ServiceID');

        $services = Service::where('IsArchived', false)
            ->get()
            ->map(fn ($s) => [
                'name' => $s->ServiceName,
                'count' => (int) ($countsByService[$s->ServiceID] ?? 0),
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
