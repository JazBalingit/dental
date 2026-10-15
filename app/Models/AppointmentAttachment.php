<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A photo the patient attached when booking (teeth, X-ray, previous dental
 * records…). The image bytes live in `Data`; it is only ever served through
 * the access-checked appointmentAttachments.show route, never publicly.
 */
class AppointmentAttachment extends Model
{
    protected $table = 'tbl_appointment_attachments';
    protected $primaryKey = 'AttachmentID';

    /** At most this many photos per appointment. */
    public const MAX_FILES = 3;

    /** Per-photo limit, in KB, after the browser has compressed it. */
    public const MAX_KB = 950;

    protected $fillable = [
        'AppointmentID',
        'FileName',
        'MimeType',
        'SizeBytes',
        'Data',
    ];

    // Never ship the image bytes in toArray()/JSON by accident.
    protected $hidden = ['Data'];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'AppointmentID', 'AppointmentID');
    }

    public function getUrlAttribute(): string
    {
        return route('appointmentAttachments.show', $this->AttachmentID);
    }
}
