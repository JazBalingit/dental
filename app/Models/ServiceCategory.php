<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceCategory extends Model
{
    protected $table = 'tbl_service_categories';
    protected $primaryKey = 'CategoryID';

    protected $fillable = [
        'Name',
        'Icon',
        'DisplayOrder',
    ];

    protected $casts = [
        'DisplayOrder' => 'integer',
    ];

    /**
     * Preset icon choices offered when creating/editing a category — kept
     * to Font Awesome classes already used elsewhere on the landing page,
     * so admins pick from a short labeled list instead of typing a raw
     * class name.
     */
    public static function iconOptions(): array
    {
        return [
            'fa-solid fa-tooth' => 'Tooth',
            'fa-solid fa-teeth' => 'Teeth',
            'fa-solid fa-teeth-open' => 'Teeth (open)',
            'fa-solid fa-stethoscope' => 'Stethoscope',
            'fa-solid fa-syringe' => 'Syringe',
            'fa-solid fa-tooth fa-flip' => 'Tooth (flipped)',
            'fa-solid fa-user-doctor' => 'Doctor',
            'fa-solid fa-notes-medical' => 'Medical Notes',
            'fa-solid fa-kit-medical' => 'Medical Kit',
            'fa-solid fa-heart-pulse' => 'Heart Pulse',
            'fa-solid fa-bone' => 'Bone',
            'fa-solid fa-x-ray' => 'X-Ray',
        ];
    }

    public function services()
    {
        return $this->hasMany(Service::class, 'CategoryID', 'CategoryID');
    }
}
