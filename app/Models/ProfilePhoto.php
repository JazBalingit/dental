<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;

/**
 * A profile photo (patient, staff, dentist or admin), stored in the database
 * so it survives redeploys — the hosted server's disk is reset on every
 * deploy. The owner's ProfilePicture column holds "photo:{PhotoID}"; older
 * rows may still hold a file path under public/, which keeps working while
 * that file exists. Served only to signed-in users (profilePhotos.show).
 */
class ProfilePhoto extends Model
{
    protected $table = 'tbl_profile_photos';
    protected $primaryKey = 'PhotoID';

    protected $fillable = ['MimeType', 'SizeBytes', 'Data'];

    // Never ship the image bytes in toArray()/JSON by accident.
    protected $hidden = ['Data'];

    public const PREFIX = 'photo:';

    /** Store an uploaded photo; returns the value to keep in ProfilePicture. Deletes the photo it replaces. */
    public static function storeUpload(UploadedFile $file, ?string $replacing = null): string
    {
        $photo = static::create([
            'MimeType' => $file->getMimeType() ?: 'image/jpeg',
            'SizeBytes' => (int) $file->getSize(),
            'Data' => file_get_contents($file->getRealPath()),
        ]);

        static::forget($replacing);

        return static::PREFIX . $photo->PhotoID;
    }

    /** Delete a stored photo by its ProfilePicture value (file paths are ignored). */
    public static function forget(?string $reference): void
    {
        if ($id = static::idFrom($reference)) {
            static::whereKey($id)->delete();
        }
    }

    public static function idFrom(?string $reference): ?int
    {
        return $reference && str_starts_with($reference, static::PREFIX)
            ? (int) substr($reference, strlen(static::PREFIX))
            : null;
    }

    /** The image URL for a ProfilePicture value, or null when there's no usable photo. */
    public static function urlFor(?string $reference): ?string
    {
        if ($id = static::idFrom($reference)) {
            return route('profilePhotos.show', $id);
        }

        // Legacy: a file uploaded before photos moved into the database.
        return $reference && is_file(public_path($reference)) ? asset($reference) : null;
    }
}
