<?php
// Place in: app/Models/Service.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $table = 'tbl_services';
    protected $primaryKey = 'ServiceID';

    protected $fillable = [
        'ServiceName',
        'CategoryID',
        'Description',
        'Price',
        'DurationMinutes',
        'IsArchived',
    ];

    protected $casts = [
        'IsArchived' => 'boolean',
        'Price' => 'decimal:2',
        'DurationMinutes' => 'integer',
    ];

    /**
     * "1 hr 30 min" / "45 min" / "2 hrs" — used wherever a service's
     * duration is shown to staff or patients.
     */
    public function getDurationLabelAttribute(): string
    {
        $hours = intdiv($this->DurationMinutes, 60);
        $minutes = $this->DurationMinutes % 60;

        $parts = [];
        if ($hours > 0) {
            $parts[] = $hours . ' hr' . ($hours > 1 ? 's' : '');
        }
        if ($minutes > 0) {
            $parts[] = $minutes . ' min';
        }

        return $parts ? implode(' ', $parts) : '0 min';
    }

    public function category()
    {
        return $this->belongsTo(ServiceCategory::class, 'CategoryID', 'CategoryID');
    }
}