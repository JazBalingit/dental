<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\ClosedDate;
use App\Models\DentistSchedule;
use App\Models\MedicalHistory;
use App\Models\PatientInfo;
use App\Models\PatientRecord;
use App\Models\Service;
use App\Models\UserAccount;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Demo walk-in patients with completed visits, for presenting the system:
 *
 *   6 INACTIVE  — last completed visit 7–14 months ago (the six-month rule)
 *   7 ACTIVE    — last completed visit within the past 5 months
 *
 * Each patient gets 1–3 completed appointments (Source "Walk-in"), a Patient
 * Record per visit (so they show in Patient Records and Reports) and a
 * staff-entered medical history. Dates are relative to the day it runs.
 *
 * Safe to run more than once: a patient whose name + birthdate already exists
 * is skipped. Sends no emails or notifications. remove() deletes exactly what
 * this seeder adds.
 *
 *   php artisan db:seed --class=DemoPatientsSeeder
 */
class DemoPatientsSeeder extends Seeder
{
    /** [first, middle, last, birthdate, gender, occupation, religion, address, phone, visits (months ago, last first)] */
    public const PATIENTS = [
        // Inactive — last visit more than 6 months ago.
        ['Maria Lourdes', 'Garcia', 'Santos', '1985-03-14', 'Female', 'Teacher', 'Roman Catholic', 'Blk 3 Lot 12 Brgy. San Gabriel, GMA, Cavite', '09171234501', [8, 13]],
        ['Roberto', 'Lim', 'Dela Cruz', '1978-07-02', 'Male', 'Driver', 'Roman Catholic', 'Phase 2 Brgy. Poblacion 1, GMA, Cavite', '09181234502', [9]],
        ['Angelica', 'Torres', 'Reyes', '1996-11-21', 'Female', 'Call Center Agent', 'Christian', 'Blk 7 Lot 4 Brgy. San Jose, GMA, Cavite', '09191234503', [7, 11]],
        ['Ramon', 'Cruz', 'Villanueva', '1969-01-30', 'Male', 'Businessman', 'Roman Catholic', 'Brgy. Kapitan Kua, GMA, Cavite', '09201234504', [12, 14]],
        ['Kristine Mae', 'Ocampo', 'Bautista', '2001-05-09', 'Female', 'Student', 'Iglesia ni Cristo', 'Blk 10 Lot 8 Brgy. Poblacion 5, GMA, Cavite', '09211234505', [10]],
        ['Jerome', 'Pascual', 'Aquino', '1990-09-17', 'Male', 'Electrician', 'Roman Catholic', 'Brgy. Benjamin Tirona, GMA, Cavite', '09221234506', [7, 9, 12]],

        // Active — last visit within the past 6 months.
        ['Patricia', 'Lopez', 'Gonzales', '1993-02-25', 'Female', 'Nurse', 'Roman Catholic', 'Blk 5 Lot 2 Brgy. Poblacion 3, GMA, Cavite', '09231234507', [1, 4]],
        ['Mark Anthony', 'Rivera', 'Ramos', '1988-12-05', 'Male', 'Engineer', 'Christian', 'Brgy. San Gabriel, GMA, Cavite', '09241234508', [2]],
        ['Jasmine', 'Flores', 'Mendoza', '1999-04-18', 'Female', 'Cashier', 'Roman Catholic', 'Blk 1 Lot 20 Brgy. Poblacion 2, GMA, Cavite', '09251234509', [3, 5]],
        ['Carlo', 'Santiago', 'Fernandez', '1982-08-11', 'Male', 'Security Guard', 'Roman Catholic', 'Brgy. Kapitan Kua, GMA, Cavite', '09261234510', [1]],
        ['Liza', 'Domingo', 'Manalo', '1975-06-27', 'Female', 'Housewife', 'Iglesia ni Cristo', 'Blk 12 Lot 6 Brgy. San Jose, GMA, Cavite', '09271234511', [2, 4]],
        ['Paolo', 'Mercado', 'Castillo', '2003-10-03', 'Male', 'Student', 'Roman Catholic', 'Brgy. Benjamin Tirona, GMA, Cavite', '09281234512', [4]],
        ['Sofia', 'Aguilar', 'Navarro', '1997-01-15', 'Female', 'Graphic Designer', 'Christian', 'Blk 9 Lot 3 Brgy. Poblacion 4, GMA, Cavite', '09291234513', [0.5, 3]],
    ];

    /** A few believable clinical notes, picked per visit. */
    protected const NOTES = [
        'Oral prophylaxis done. Mild gingivitis noted; advised proper brushing and flossing.',
        'Patient complained of tooth sensitivity. Applied fluoride varnish. Follow up if pain persists.',
        'Extraction done under local anesthesia. Post-operative instructions given.',
        'Routine check-up. No new caries found. Advised cleaning every 6 months.',
        'Consultation for braces. Advised panoramic X-ray before treatment planning.',
        'Patient tolerated the procedure well. Prescribed pain reliever as needed.',
    ];

    protected const BLOOD_PRESSURES = ['110/70', '120/80', '118/76', '125/82', '130/85', '115/75'];

    public function run(): void
    {
        $dentists = UserAccount::dentists()->get();
        $services = Service::where('IsArchived', false)->orderBy('ServiceID')->get();

        if ($dentists->isEmpty() || $services->isEmpty()) {
            $this->say('Demo patients skipped: add at least one dentist and one service first.');

            return;
        }

        $added = 0;
        foreach (self::PATIENTS as $i => $row) {
            [$first, $middle, $last, $birthdate] = $row;
            if ($this->find($first, $last, $birthdate)) {
                continue;
            }

            DB::transaction(function () use ($row, $i, $dentists, $services) {
                $this->addPatient($row, $i, $dentists, $services);
            });
            $added++;
        }

        $this->say("Demo patients added: {$added} (" . (count(self::PATIENTS) - $added) . ' already there).');
    }

    /** Delete every demo patient this seeder created, with their visits. */
    public function remove(): void
    {
        foreach (self::PATIENTS as [$first, , $last, $birthdate]) {
            $patient = $this->find($first, $last, $birthdate);
            if (!$patient || $patient->UserID) {
                continue;
            }

            DB::transaction(function () use ($patient) {
                foreach (Appointment::where('PatientID', $patient->PatientID)->get() as $appointment) {
                    $appointment->services()->detach();
                    $appointment->delete();
                }
                PatientRecord::where('PatientID', $patient->PatientID)->delete();
                MedicalHistory::where('PatientID', $patient->PatientID)->delete();
                $patient->delete();
            });
        }
    }

    protected function addPatient(array $row, int $i, $dentists, $services): void
    {
        [$first, $middle, $last, $birthdate, $gender, $occupation, $religion, $address, $phone, $visitsAgo] = $row;

        $visitDays = array_map(fn ($months) => $this->clinicDay(now()->subDays((int) round($months * 30.4))), $visitsAgo);
        $firstVisit = min($visitDays);

        $patient = new PatientInfo([
            'IsWalkIn' => true,
            'FirstName' => $first,
            'MiddleName' => $middle,
            'LastName' => $last,
            'PhoneNumber' => $phone,
            'DateOfBirth' => $birthdate,
            'Age' => Carbon::parse($birthdate)->age,
            'Gender' => $gender,
            'Nationality' => 'Filipino',
            'Religion' => $religion,
            'Occupation' => $occupation,
            'Address' => $address,
        ]);
        $patient->created_at = $firstVisit->copy()->setTime(9, 0);
        $patient->updated_at = $patient->created_at;
        $patient->save();

        $dentist = $dentists[$i % $dentists->count()];
        $medical = $this->addMedicalHistory($patient, $i, $dentist, $firstVisit);

        // Oldest visit first, so record IDs read in order.
        foreach (array_reverse($visitDays) as $v => $day) {
            $service = $services[($i + $v) % $services->count()];
            $slots = max(1, (int) ceil(($service->DurationMinutes ?: 60) / DentistSchedule::SLOT_MINUTES));
            [$day, $time, $scheduleId] = $this->freeSlot($dentist->UserID, $day, $slots, $i + $v);
            $at = Carbon::parse($day->format('Y-m-d') . ' ' . $time);

            $appointment = new Appointment([
                'PatientID' => $patient->PatientID,
                'DentistID' => $dentist->UserID,
                'ScheduleID' => $scheduleId,
                'ServiceID' => $service->ServiceID,
                'AppointmentDate' => $day->format('Y-m-d'),
                'AppointmentTime' => $time,
                'TypeOfAppointment' => $service->ServiceName,
                'DurationHours' => round($slots * DentistSchedule::SLOT_MINUTES / 60, 1),
                'Source' => 'Walk-in',
                'Status' => 'Completed',
                'MedicalHistoryID' => $medical->MedicalHistoryID,
            ]);
            $appointment->ApprovedAt = $at;
            $appointment->created_at = $at;
            $appointment->updated_at = $at->copy()->addMinutes($slots * DentistSchedule::SLOT_MINUTES);
            $appointment->save();
            $appointment->services()->sync([$service->ServiceID]);

            $record = new PatientRecord([
                'PatientID' => $patient->PatientID,
                'AppointmentID' => $appointment->AppointmentID,
                'ServiceID' => $service->ServiceID,
                'VisitDate' => $day->format('Y-m-d'),
                'VisitTime' => $time,
                'Service' => $service->ServiceName,
                'Status' => 'Completed',
                'Notes' => self::NOTES[($i + $v) % count(self::NOTES)],
                'BloodPressure' => self::BLOOD_PRESSURES[($i + $v) % count(self::BLOOD_PRESSURES)],
            ]);
            $record->created_at = $appointment->updated_at;
            $record->updated_at = $appointment->updated_at;
            $record->save();
        }
    }

    protected function addMedicalHistory(PatientInfo $patient, int $i, UserAccount $by, Carbon $on): MedicalHistory
    {
        $answers = [];
        foreach (MedicalHistory::QUESTIONS as $key => [, $followUp]) {
            $answers[$key] = $key === 'good_health' ? 'yes' : 'no';
            if ($followUp) {
                $answers["{$key}_detail"] = '';
            }
        }
        foreach (array_keys(MedicalHistory::WOMEN) as $key) {
            $answers[$key] = null;
        }
        // A little variety so the dentist alerts show on some patients.
        $answers['allergies'] = $i % 4 === 1 ? ['penicillin'] : [];
        $answers['allergies_other'] = '';
        $answers['conditions'] = match ($i % 5) {
            2 => ['high_bp'],
            4 => ['asthma'],
            default => [],
        };
        $answers['conditions_other'] = '';
        foreach (['physician_name', 'physician_specialty', 'physician_office', 'physician_phone', 'previous_dentist', 'last_dental_visit'] as $key) {
            $answers[$key] = '';
        }
        $answers['blood_type'] = MedicalHistory::BLOOD_TYPES[$i % count(MedicalHistory::BLOOD_TYPES)];

        $history = new MedicalHistory([
            'PatientID' => $patient->PatientID,
            'Answers' => $answers,
            'Source' => 'staff',
            'UpdatedByUserID' => $by->UserID,
            'UpdatedByName' => $by->display_name,
        ]);
        $history->created_at = $on->copy()->setTime(9, 0);
        $history->updated_at = $history->created_at;
        $history->save();

        return $history;
    }

    /**
     * A start time on $day (or the clinic day before it) where the dentist
     * has nothing else booked for $slots slots. Reserves the schedule rows
     * like a walk-in does and returns [day, time, first ScheduleID].
     */
    protected function freeSlot(int $dentistId, Carbon $day, int $slots, int $seed): array
    {
        $times = DentistSchedule::slotTimes();

        for ($tries = 0; $tries < 30; $tries++) {
            $date = $day->format('Y-m-d');
            $taken = [];
            foreach (Appointment::where('DentistID', $dentistId)->whereDate('AppointmentDate', $date)
                ->whereNotIn('Status', ['Cancelled', 'Declined'])->get() as $other) {
                $start = array_search($other->AppointmentTime, $times, true);
                if ($start !== false) {
                    $taken = array_merge($taken, array_slice($times, $start, $other->duration_slots));
                }
            }

            $count = count($times);
            for ($k = 0; $k < $count; $k++) {
                $startIndex = ($seed * 3 + $k) % $count;
                $block = array_slice($times, $startIndex, $slots);
                if (count($block) === $slots && !array_intersect($block, $taken)) {
                    $firstId = null;
                    foreach ($block as $t) {
                        $row = DentistSchedule::firstOrNew(['DentistID' => $dentistId, 'Date' => $date, 'Time' => $t]);
                        $row->Status = 'Not Available';
                        $row->save();
                        $firstId ??= $row->ScheduleID;
                    }

                    return [$day, $block[0], $firstId];
                }
            }

            $day = $this->clinicDay($day->copy()->subDay());
        }

        throw new \RuntimeException('No free slot found for a demo visit.');
    }

    /** $day, or the closest earlier day the clinic is open. */
    protected function clinicDay(Carbon $day): Carbon
    {
        $day = $day->copy()->startOfDay();
        for ($i = 0; $i < 14 && (ClosedDate::isClosedWeekday($day) || ClosedDate::isClosed($day)); $i++) {
            $day->subDay();
        }

        return $day;
    }

    protected function find(string $first, string $last, string $birthdate): ?PatientInfo
    {
        return PatientInfo::where('FirstName', $first)->where('LastName', $last)
            ->whereDate('DateOfBirth', $birthdate)->first();
    }

    protected function say(string $message): void
    {
        if ($this->command) {
            $this->command->info($message);
        } elseif (app()->runningInConsole()) {
            echo $message . PHP_EOL; // e.g. inside `migrate` on deploy
        }
        \Illuminate\Support\Facades\Log::info($message);
    }
}
