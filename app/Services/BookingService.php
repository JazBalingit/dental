<?php

namespace App\Services;

use App\Http\Controllers\Concerns\BuildsBookingCalendar;
use App\Models\Appointment;
use App\Models\AppointmentAttachment;
use App\Models\ClosedDate;
use App\Models\DentistSchedule;
use App\Models\PatientInfo;
use App\Models\Service;
use App\Models\UserAccount;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Creating an online appointment request — shared by the Patient Portal
 * (booking for yourself or for someone else) and the public landing page
 * (booking without an account), so all three follow the exact same rules:
 * open day, not in the past, at most 2 months ahead, one upcoming
 * appointment per patient, the whole service duration reserved at once.
 */
class BookingService
{
    use BuildsBookingCalendar;

    /** Validation rules for the patient's own details — the same fields as signup. */
    public static function selfRules(): array
    {
        $nameRule = "regex:/^[\pL\s'.-]+$/u";

        return [
            'last_name' => ['required', 'string', 'max:100', $nameRule],
            'first_name' => ['required', 'string', 'max:100', $nameRule],
            'middle_name' => ['nullable', 'string', 'max:100', $nameRule],
            'birthdate' => ['required', 'date', 'before_or_equal:' . static::adultCutoff()],
            'gender' => 'required|string|in:male,female,other',
            'religion' => ['nullable', 'string', 'max:100', $nameRule],
            'nationality' => ['required', 'string', 'max:100', $nameRule],
            'occupation' => ['nullable', 'string', 'max:150', $nameRule],
            'addr_street' => 'nullable|string|max:120',
            'addr_barangay' => 'nullable|string|max:120',
            'addr_city' => 'required|string|max:120',
            'addr_province' => 'required|string|max:120',
            'phone' => 'required|digits:11',
        ];
    }

    /** Validation rules for someone else's details (the person the booking is for). */
    public static function otherRules(): array
    {
        $nameRule = "regex:/^[\pL\s'.-]+$/u";

        return [
            'other_last_name' => ['required', 'string', 'max:100', $nameRule],
            'other_first_name' => ['required', 'string', 'max:100', $nameRule],
            'other_middle_name' => ['nullable', 'string', 'max:100', $nameRule],
            'other_birthdate' => 'required|date|before_or_equal:today',
            'other_gender' => 'required|string|in:male,female,other',
            'other_relationship' => 'required|in:' . implode(',', PatientInfo::RELATIONSHIPS),
            'other_phone' => 'nullable|digits:11',
            'other_same_address' => 'nullable|boolean',
            'other_addr_street' => 'nullable|string|max:120',
            'other_addr_barangay' => 'nullable|string|max:120',
            'other_addr_city' => 'required_unless:other_same_address,1|nullable|string|max:120',
            'other_addr_province' => 'required_unless:other_same_address,1|nullable|string|max:120',
        ];
    }

    public static function messages(): array
    {
        return [
            'birthdate.before_or_equal' => 'You must be 18 or older to book for yourself. A parent or guardian can book for you using "Someone else".',
            'other_birthdate.before_or_equal' => "The patient's birthdate can't be in the future.",
            'phone.digits' => 'Mobile number must be exactly 11 digits.',
            'other_phone.digits' => "The patient's mobile number must be exactly 11 digits.",
            'booker_phone.digits' => 'Your mobile number must be exactly 11 digits.',
            'other_relationship.required' => 'Please choose how the patient is related to you.',
            'other_addr_city.required_unless' => "Please enter the patient's city, or tick \"Same address as mine\".",
            'other_addr_province.required_unless' => "Please enter the patient's province, or tick \"Same address as mine\".",
            '*.regex' => 'Names may only contain letters.',
        ];
    }

    /** Latest birthdate that is still 18 today. */
    public static function adultCutoff(): string
    {
        return today()->subYears(PatientInfo::ADULT_AGE)->format('Y-m-d');
    }

    public static function joinAddress(array $data, string $prefix = ''): string
    {
        return collect(['addr_street', 'addr_barangay', 'addr_city', 'addr_province'])
            ->map(fn ($k) => trim((string) ($data[$prefix . $k] ?? '')))
            ->filter()
            ->implode(', ');
    }

    /** Attributes for a new PatientInfo from the signup-style "myself" fields. */
    public static function selfAttributes(array $data, string $email): array
    {
        return [
            'LastName' => $data['last_name'],
            'FirstName' => $data['first_name'],
            'MiddleName' => $data['middle_name'] ?? null,
            'PhoneNumber' => $data['phone'],
            'Email' => $email,
            'DateOfBirth' => $data['birthdate'],
            'Age' => Carbon::parse($data['birthdate'])->age,
            'Gender' => $data['gender'],
            'Religion' => $data['religion'] ?? null,
            'Nationality' => $data['nationality'],
            'Occupation' => $data['occupation'] ?? null,
            'Address' => static::joinAddress($data),
        ];
    }

    /**
     * Attributes for the person being booked for. The booker becomes their
     * guardian on file (name, number, email) — so a minor can never end up
     * without one.
     */
    public static function otherAttributes(array $data, array $booker, ?string $bookerAddress): array
    {
        $address = !empty($data['other_same_address']) && $bookerAddress
            ? $bookerAddress
            : static::joinAddress($data, 'other_');

        // The booker is the parent/guardian on file only for a minor; an
        // adult (e.g. your mother) just stays linked to whoever booked.
        $isMinor = Carbon::parse($data['other_birthdate'])->age < PatientInfo::ADULT_AGE;

        return [
            'IsWalkIn' => false,
            'LastName' => $data['other_last_name'],
            'FirstName' => $data['other_first_name'],
            'MiddleName' => $data['other_middle_name'] ?? null,
            // The column is required — fall back to the booker's number.
            'PhoneNumber' => ($data['other_phone'] ?? null) ?: $booker['phone'],
            'Email' => null,
            'DateOfBirth' => $data['other_birthdate'],
            'Age' => Carbon::parse($data['other_birthdate'])->age,
            'Gender' => $data['other_gender'],
            'Nationality' => 'Filipino',
            'Address' => $address ?: ($bookerAddress ?: '—'),
            'Relationship' => $data['other_relationship'],
            'ParentsName' => $isMinor ? $booker['name'] : null,
            'ParentsContactNumber' => $isMinor ? $booker['phone'] : null,
            'ParentsEmail' => $isMinor ? $booker['email'] : null,
            'GuardianEmail' => $booker['email'],
            'GuardianUserID' => $booker['user_id'] ?? null,
        ];
    }

    /** Date/time rules every online booking follows. */
    public function assertBookable(string $date, string $time): void
    {
        $day = Carbon::parse($date);

        if (ClosedDate::isClosedWeekday($day) || $day->lt(today()) || ClosedDate::isClosed($day)) {
            throw new RuntimeException('That date is not available for booking.');
        }
        if (Carbon::parse($date . ' ' . $time)->lt(now())) {
            throw new RuntimeException('That time has already passed today. Please choose an upcoming time.');
        }
        if ($day->gt(today()->addMonths(2))) {
            throw new RuntimeException('Appointments can only be booked up to 2 months in advance.');
        }
    }

    public function assertNoUpcoming(PatientInfo $patient): void
    {
        if (Appointment::where('PatientID', $patient->PatientID)->upcoming()->exists()) {
            throw new RuntimeException("{$patient->full_name} already has an upcoming appointment. Please wait until it's completed, or cancel it, before booking another.");
        }
    }

    public function dentist(int $dentistId): UserAccount
    {
        $dentist = UserAccount::dentists()->where('UserID', $dentistId)->first();
        if (!$dentist) {
            throw new RuntimeException('Please choose a dentist for your appointment.');
        }

        return $dentist;
    }

    /**
     * Reserve the slots and create the Pending request.
     *
     * @param  array  $slot     date, time, service_ids, patient_notes
     * @param  array  $photos   [['name','mime','size','data'], …]
     * @param  array  $booker   user_id?, email?, name?, phone?, for_other (bool)
     * @param  ?callable $createPatient  runs inside the same transaction to
     *         create the patient record (so a failed booking leaves no orphan)
     * @param  ?callable $afterCreate  fn (Appointment) — also inside the
     *         transaction (e.g. saving the medical history), so the booking
     *         and everything saved with it succeed or fail together
     */
    public function create(?PatientInfo $patient, UserAccount $dentist, array $slot, array $photos, array $booker, ?callable $createPatient = null, ?callable $afterCreate = null): Appointment
    {
        $this->assertBookable($slot['date'], $slot['time']);

        $services = Service::whereIn('ServiceID', $slot['service_ids'])->where('IsArchived', false)->get();
        if ($services->isEmpty()) {
            throw new RuntimeException('Please choose at least one service.');
        }
        $slotsNeeded = $this->slotsNeededForServices($services);

        return DB::transaction(function () use ($patient, $dentist, $slot, $photos, $booker, $createPatient, $afterCreate, $services, $slotsNeeded) {
            $patient = $patient ?? $createPatient();
            $this->assertNoUpcoming($patient);

            $scheduleRows = $this->reserveSlotBlock($slot['date'], $slot['time'], $slotsNeeded, $dentist->UserID);

            $appointment = Appointment::create([
                'PatientID' => $patient->PatientID,
                'DentistID' => $dentist->UserID,
                'ScheduleID' => $scheduleRows[0]->ScheduleID,
                'ServiceID' => $services->first()->ServiceID,
                'AppointmentDate' => $slot['date'],
                'AppointmentTime' => $slot['time'],
                'TypeOfAppointment' => $services->pluck('ServiceName')->implode(', '),
                'PatientNotes' => trim((string) ($slot['patient_notes'] ?? '')) ?: null,
                'DurationHours' => round($slotsNeeded * DentistSchedule::SLOT_MINUTES / 60, 1),
                'Source' => 'Online',
                'BookedByUserID' => $booker['user_id'] ?? null,
                'BookedByEmail' => $booker['email'] ?? null,
                'BookedByName' => $booker['name'] ?? null,
                'BookedByPhone' => $booker['phone'] ?? null,
                'BookedForOther' => (bool) ($booker['for_other'] ?? false),
                'Status' => 'Pending',
            ]);

            $appointment->services()->sync($services->pluck('ServiceID'));

            foreach ($photos as $photo) {
                AppointmentAttachment::create([
                    'AppointmentID' => $appointment->AppointmentID,
                    'FileName' => mb_substr($photo['name'] ?: 'photo.jpg', 0, 150),
                    'MimeType' => $photo['mime'] ?: 'image/jpeg',
                    'SizeBytes' => $photo['size'],
                    'Data' => $photo['data'],
                ]);
            }

            if ($afterCreate) {
                $afterCreate($appointment);
            }

            return $appointment;
        });
    }

    /** Uploaded files → the plain arrays create() takes. */
    public static function photosFromUploads(array $files): array
    {
        return collect($files)->map(fn ($file) => [
            'name' => $file->getClientOriginalName(),
            'mime' => $file->getMimeType(),
            'size' => $file->getSize(),
            'data' => file_get_contents($file->getRealPath()),
        ])->all();
    }
}
