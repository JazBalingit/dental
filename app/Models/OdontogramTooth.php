<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OdontogramTooth extends Model
{
    protected $table = 'tbl_odontogram_teeth';
    protected $primaryKey = 'OdontogramToothID';

    protected $fillable = [
        'RecordID',
        'PatientID',
        'AppointmentID',
        'ToothNumber',
        'Condition',
        'Surfaces',
        'Description',
    ];

    /**
     * condition key => human label. Single source of truth shared by the
     * chart legend, the tooth editor and the save-endpoint validation.
     */
    public const CONDITIONS = [
        'healthy'    => 'Healthy',
        'caries'     => 'Caries / Decay',
        'filling'    => 'Filling / Restoration',
        'root_canal' => 'Root Canal',
        'crown'      => 'Crown',
        'missing'    => 'Missing / Extracted',
    ];

    /**
     * condition key => swatch colour. Kept here so the server renders the
     * legend with the exact colours the chart paints each tooth with.
     */
    public const CONDITION_COLORS = [
        'healthy'    => '#22c55e',
        'caries'     => '#ef4444',
        'filling'    => '#3b82f6',
        'root_canal' => '#a855f7',
        'crown'      => '#f97316',
        'missing'    => '#1f2937',
    ];

    /** Tooth surfaces staff can tag (FDI adult dentition). */
    public const SURFACES = ['mesial', 'distal', 'occlusal', 'buccal', 'lingual', 'incisal'];

    /**
     * Every permanent-tooth FDI number, in chart display order per arch.
     * Upper: 18→11 then 21→28. Lower: 48→41 then 31→38.
     */
    public const FDI_TEETH = [
        '18', '17', '16', '15', '14', '13', '12', '11',
        '21', '22', '23', '24', '25', '26', '27', '28',
        '48', '47', '46', '45', '44', '43', '42', '41',
        '31', '32', '33', '34', '35', '36', '37', '38',
    ];

    public function record()
    {
        return $this->belongsTo(PatientRecord::class, 'RecordID', 'RecordID');
    }

    public function patientInfo()
    {
        return $this->belongsTo(PatientInfo::class, 'PatientID', 'PatientID');
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'AppointmentID', 'AppointmentID');
    }

    /** Surfaces as an array (the column stores a csv string). */
    public function getSurfaceListAttribute(): array
    {
        return $this->Surfaces ? explode(',', $this->Surfaces) : [];
    }
}
