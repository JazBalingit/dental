<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One version of a patient's medical history — the questions from the
 * clinic's paper Patient Information Record. Updates add a new row; the
 * newest row is the current one (see PatientInfo::latestMedicalHistory).
 *
 * Answers (JSON) keys:
 *   yes/no questions  QUESTIONS keys, each 'yes' | 'no', plus "{key}_detail"
 *   allergies         ALLERGIES keys[], allergies_other
 *   women             WOMEN keys, each 'yes' | 'no'
 *   conditions        CONDITIONS keys[], conditions_other
 *   optional          physician_name, physician_specialty, physician_office,
 *                     physician_phone, previous_dentist, last_dental_visit
 *   blood_type        optional — blank means "I don't know"
 *   (blood pressure / bleeding time are per visit: tbl_patientRecord)
 */
class MedicalHistory extends Model
{
    protected $table = 'tbl_medical_histories';
    protected $primaryKey = 'MedicalHistoryID';

    protected $fillable = ['PatientID', 'Answers', 'Source', 'UpdatedByUserID', 'UpdatedByName'];

    protected $casts = ['Answers' => 'array'];

    /** key => [question, follow-up label (asked when "Yes") or null]. */
    public const QUESTIONS = [
        'good_health' => ['Are you in good health?', null],
        'under_treatment' => ['Are you under medical treatment now?', 'What condition is being treated?'],
        'serious_illness' => ['Have you ever had a serious illness or surgical operation?', 'What illness or operation?'],
        'hospitalized' => ['Have you ever been hospitalized?', 'When and why?'],
        'medication' => ['Are you taking any prescription or non-prescription medication?', 'Please specify'],
        'tobacco' => ['Do you use tobacco products?', null],
        'alcohol_drugs' => ['Do you use alcohol, cocaine or other dangerous drugs?', null],
    ];

    public const ALLERGIES = [
        'local_anesthetic' => 'Local anesthetic (e.g. Lidocaine)',
        'penicillin' => 'Penicillin / Antibiotics',
        'sulfa' => 'Sulfa drugs',
        'aspirin' => 'Aspirin',
        'latex' => 'Latex',
    ];

    public const WOMEN = [
        'pregnant' => 'Are you pregnant?',
        'nursing' => 'Are you nursing?',
        'birth_control' => 'Are you taking birth control pills?',
    ];

    public const CONDITIONS = [
        'high_bp' => 'High blood pressure',
        'low_bp' => 'Low blood pressure',
        'epilepsy' => 'Epilepsy / Convulsions',
        'hiv' => 'AIDS or HIV infection',
        'std' => 'Sexually transmitted disease',
        'stomach' => 'Stomach troubles / Ulcers',
        'fainting' => 'Fainting / Seizure',
        'weight_loss' => 'Rapid weight loss',
        'radiation' => 'Radiation therapy',
        'joint_replacement' => 'Joint replacement / Implant',
        'heart_surgery' => 'Heart surgery',
        'heart_attack' => 'Heart attack',
        'thyroid' => 'Thyroid problem',
        'heart_disease' => 'Heart disease',
        'heart_murmur' => 'Heart murmur',
        'hepatitis_liver' => 'Hepatitis / Liver disease',
        'rheumatic_fever' => 'Rheumatic fever',
        'hay_fever' => 'Hay fever / Allergies',
        'respiratory' => 'Respiratory problem',
        'hepatitis_jaundice' => 'Hepatitis / Jaundice',
        'tuberculosis' => 'Tuberculosis',
        'swollen_ankles' => 'Swollen ankles',
        'kidney' => 'Kidney disease',
        'diabetes' => 'Diabetes',
        'chest_pain' => 'Chest pain',
        'stroke' => 'Stroke',
        'cancer' => 'Cancer / Tumors',
        'anemia' => 'Anemia',
        'angina' => 'Angina',
        'asthma' => 'Asthma',
        'emphysema' => 'Emphysema',
        'bleeding' => 'Bleeding problems',
        'blood_disease' => 'Blood diseases',
        'head_injury' => 'Head injuries',
        'arthritis' => 'Arthritis / Rheumatism',
    ];

    /** Conditions the dentist must not miss — shown as red warnings. */
    public const HIGH_RISK = [
        'high_bp', 'low_bp', 'epilepsy', 'fainting', 'heart_surgery', 'heart_attack', 'heart_disease', 'heart_murmur',
        'rheumatic_fever', 'chest_pain', 'stroke', 'angina', 'bleeding', 'blood_disease', 'diabetes', 'hiv',
        'hepatitis_liver', 'hepatitis_jaundice', 'tuberculosis', 'kidney', 'radiation', 'joint_replacement', 'cancer', 'asthma',
    ];

    public const BLOOD_TYPES = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];

    public function patientInfo()
    {
        return $this->belongsTo(PatientInfo::class, 'PatientID', 'PatientID');
    }

    // ------------------------------------------------------------------
    // Form handling
    // ------------------------------------------------------------------

    /** Validation rules for the submitted "mh" array. Staff fields only when $staff. */
    public static function rules(bool $staff = false): array
    {
        $rules = [
            'mh' => 'required|array',
            'mh.allergies' => 'nullable|array',
            'mh.allergies.*' => 'in:' . implode(',', array_keys(self::ALLERGIES)),
            'mh.allergies_other' => 'nullable|string|max:200',
            'mh.conditions' => 'nullable|array',
            'mh.conditions.*' => 'in:' . implode(',', array_keys(self::CONDITIONS)),
            'mh.conditions_other' => 'nullable|string|max:200',
            'mh.physician_name' => 'nullable|string|max:150',
            'mh.physician_specialty' => 'nullable|string|max:150',
            'mh.physician_office' => 'nullable|string|max:255',
            'mh.physician_phone' => 'nullable|string|max:30',
            'mh.previous_dentist' => 'nullable|string|max:150',
            'mh.last_dental_visit' => 'nullable|string|max:100',
            // Replaces the paper form's signature.
            'mh.certify' => $staff ? 'nullable' : 'accepted',
        ];

        foreach (self::QUESTIONS as $key => [, $followUp]) {
            $rules["mh.{$key}"] = 'required|in:yes,no';
            if ($followUp) {
                $rules["mh.{$key}_detail"] = "nullable|required_if:mh.{$key},yes|string|max:300";
            }
        }
        foreach (array_keys(self::WOMEN) as $key) {
            $rules["mh.{$key}"] = 'nullable|in:yes,no';
        }
        $rules['mh.blood_type'] = 'nullable|in:' . implode(',', self::BLOOD_TYPES);

        return $rules;
    }

    public static function messages(): array
    {
        $messages = [
            'mh.required' => 'Please fill in the medical history.',
            'mh.certify.accepted' => 'Please confirm that the medical information is true and complete.',
        ];
        foreach (self::QUESTIONS as $key => [$question, $followUp]) {
            $messages["mh.{$key}.required"] = "Please answer: {$question}";
            if ($followUp) {
                $messages["mh.{$key}_detail.required_if"] = "You answered Yes to \"{$question}\" — please add the details.";
            }
        }

        return $messages;
    }

    /**
     * Validated "mh" input → the stored Answers. Staff-only values are kept
     * from $previous when a patient (not staff) saves.
     */
    public static function answersFrom(array $mh, bool $staff = false, ?array $previous = null): array
    {
        $answers = [];
        foreach (self::QUESTIONS as $key => [, $followUp]) {
            $answers[$key] = $mh[$key] ?? null;
            if ($followUp) {
                $answers["{$key}_detail"] = ($mh[$key] ?? null) === 'yes' ? trim((string) ($mh["{$key}_detail"] ?? '')) : '';
            }
        }
        foreach (array_keys(self::WOMEN) as $key) {
            $answers[$key] = $mh[$key] ?? null;
        }
        $answers['allergies'] = array_values(array_intersect(array_keys(self::ALLERGIES), (array) ($mh['allergies'] ?? [])));
        $answers['allergies_other'] = trim((string) ($mh['allergies_other'] ?? ''));
        $answers['conditions'] = array_values(array_intersect(array_keys(self::CONDITIONS), (array) ($mh['conditions'] ?? [])));
        $answers['conditions_other'] = trim((string) ($mh['conditions_other'] ?? ''));
        foreach (['physician_name', 'physician_specialty', 'physician_office', 'physician_phone', 'previous_dentist', 'last_dental_visit'] as $key) {
            $answers[$key] = trim((string) ($mh[$key] ?? ''));
        }
        $answers['blood_type'] = trim((string) ($mh['blood_type'] ?? ''));

        return $answers;
    }

    /**
     * When several people's forms share one page, each posts under its own
     * prefix (mh_p12, mh_self…). Copy the chosen person's answers to the
     * plain "mh" / "mh_status" keys that the rules and messages use.
     */
    public static function takeInput(\Illuminate\Http\Request $request, string $prefix): void
    {
        if ($prefix === 'mh') {
            return;
        }
        $request->merge([
            'mh' => $request->input($prefix),
            'mh_status' => $request->input("{$prefix}_status"),
        ]);
    }

    /**
     * Rules to merge into a booking's validation. A returning patient who
     * answered "still the same" only sends mh_status; anyone else sends the
     * full form.
     */
    public static function bookingRules(?string $status, ?self $latest, bool $staff = false): array
    {
        if ($latest && $status === 'same') {
            return ['mh_status' => 'required|in:same'];
        }

        return ['mh_status' => 'nullable|in:new,update,same'] + self::rules($staff);
    }

    /**
     * After a booking is made: the version to link it to — the one on file
     * ("still the same"), or a new version from what was submitted.
     */
    public static function forBooking(array $validated, ?self $latest, int $patientId, string $source, ?UserAccount $by = null, ?string $byName = null): self
    {
        if ($latest && ($validated['mh_status'] ?? null) === 'same') {
            return $latest;
        }

        $staff = $source === 'staff';
        $answers = self::answersFrom($validated['mh'] ?? [], $staff, $latest?->Answers);

        // The form comes pre-filled with the last answers — if nothing was
        // changed, keep using that version instead of saving a copy.
        if ($latest && self::sameAnswers($answers, $latest->Answers ?? [])) {
            return $latest;
        }

        return self::record($patientId, $answers, $source, $by, $byName);
    }

    /** Same answers, ignoring key order and the order of ticked boxes. */
    public static function sameAnswers(array $a, array $b): bool
    {
        $normalize = function (array $x) {
            foreach (['allergies', 'conditions'] as $list) {
                $x[$list] = (array) ($x[$list] ?? []);
                sort($x[$list]);
            }
            ksort($x);

            return array_map(fn ($v) => is_array($v) ? $v : (string) $v, $x);
        };

        return $normalize($a) == $normalize($b);
    }

    /** Save a new version for a patient (never overwrites the previous one). */
    public static function record(int $patientId, array $answers, string $source, ?UserAccount $by = null, ?string $byName = null): self
    {
        return self::create([
            'PatientID' => $patientId,
            'Answers' => $answers,
            'Source' => $source,
            'UpdatedByUserID' => $by?->UserID,
            'UpdatedByName' => $byName ?? $by?->display_name,
        ]);
    }

    // ------------------------------------------------------------------
    // Reading it back
    // ------------------------------------------------------------------

    public function answer(string $key)
    {
        return $this->Answers[$key] ?? null;
    }

    /** Allergy labels, including "Others". */
    public function getAllergyListAttribute(): array
    {
        $list = array_map(fn ($k) => self::ALLERGIES[$k] ?? $k, $this->answer('allergies') ?? []);
        if ($other = $this->answer('allergies_other')) {
            $list[] = $other;
        }

        return $list;
    }

    /** Condition labels, including "Other". */
    public function getConditionListAttribute(): array
    {
        $list = array_map(fn ($k) => self::CONDITIONS[$k] ?? $k, $this->answer('conditions') ?? []);
        if ($other = $this->answer('conditions_other')) {
            $list[] = $other;
        }

        return $list;
    }

    /**
     * The warnings a dentist must see before treatment:
     * [['level' => 'danger'|'warning', 'text' => '…'], …]
     */
    public function getAlertsAttribute(): array
    {
        $alerts = [];
        foreach ($this->allergy_list as $allergy) {
            $alerts[] = ['level' => 'danger', 'text' => "Allergic: {$allergy}"];
        }
        if ($this->answer('pregnant') === 'yes') {
            $alerts[] = ['level' => 'danger', 'text' => 'Pregnant'];
        }
        if ($this->answer('nursing') === 'yes') {
            $alerts[] = ['level' => 'warning', 'text' => 'Nursing'];
        }
        foreach ($this->answer('conditions') ?? [] as $key) {
            $alerts[] = [
                'level' => in_array($key, self::HIGH_RISK, true) ? 'danger' : 'warning',
                'text' => self::CONDITIONS[$key] ?? $key,
            ];
        }
        if ($other = $this->answer('conditions_other')) {
            $alerts[] = ['level' => 'warning', 'text' => $other];
        }
        if ($this->answer('under_treatment') === 'yes') {
            $alerts[] = ['level' => 'warning', 'text' => 'Under treatment' . ($this->answer('under_treatment_detail') ? ': ' . $this->answer('under_treatment_detail') : '')];
        }
        if ($this->answer('medication') === 'yes') {
            $alerts[] = ['level' => 'warning', 'text' => 'Medication' . ($this->answer('medication_detail') ? ': ' . $this->answer('medication_detail') : '')];
        }

        return $alerts;
    }

    /** One line for the "Still the same?" summary. */
    public function getSummaryAttribute(): string
    {
        $parts = [];
        $parts[] = 'Allergies: ' . ($this->allergy_list ? implode(', ', $this->allergy_list) : 'none');
        $parts[] = 'Conditions: ' . ($this->condition_list ? implode(', ', $this->condition_list) : 'none');
        if ($this->answer('medication') === 'yes') {
            $parts[] = 'Medication: ' . ($this->answer('medication_detail') ?: 'yes');
        }
        if ($this->answer('pregnant') === 'yes') {
            $parts[] = 'Pregnant';
        }

        return implode(' · ', $parts);
    }

    /** The answers as an "mh" array for prefilling the form. */
    public function getFormValuesAttribute(): array
    {
        return $this->Answers ?? [];
    }
}
