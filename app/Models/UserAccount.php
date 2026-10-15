<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAccount extends Model
{
    protected $table = 'tbl_useraccount';
    protected $primaryKey = 'UserID';
    public $timestamps = false; // uses DateCreated instead of created_at/updated_at

    /**
     * The AccountRole values that get into the admin panel. 'super admin' is
     * the claimed super admin (also flagged IsSuperAdmin); 'admin' is every
     * staff/dentist account and the un-claimed .env bootstrap. Anything that
     * gates on "is this an admin-panel user" checks against this list so the
     * super admin is never accidentally treated as a patient or locked out.
     */
    public const ADMIN_ROLES = ['admin', 'super admin'];

    protected $fillable = [
        'Email',
        'Password',
        'AccountRole',
        'AccountType',
        'Position',
        'DateCreated',
        'IsArchived',
        'ArchiveReason',
        'ArchivedAt',
        'EmailVerifiedAt',
        'LastLoginAt',
        'IsSuperAdmin',
    ];

    protected $casts = [
        'EmailVerifiedAt' => 'datetime',
        'LastLoginAt' => 'datetime',
        'IsArchived' => 'boolean',
        'IsSuperAdmin' => 'boolean',
    ];

    protected $hidden = [
        'Password',
    ];

    public function patientInfo()
    {
        return $this->hasOne(PatientInfo::class, 'UserID', 'UserID');
    }

    public function staffInfo()
    {
        return $this->hasOne(StaffInfo::class, 'UserID', 'UserID');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'UserID', 'UserID');
    }

    public function dentistSchedules()
    {
        return $this->hasMany(DentistSchedule::class, 'DentistID', 'UserID');
    }

    /**
     * Every active dentist (staff account, Position = 'Dentist'), with the
     * name info the filter dropdowns render. Ordered so the same dentist is
     * always the default first option.
     */
    public function scopeDentists($query)
    {
        return $query->where('AccountType', 'Staff')
            ->where('Position', 'Dentist')
            ->where('IsArchived', false)
            ->with('staffInfo')
            ->orderBy('UserID');
    }

    /** The logged-in admin-panel account, memoized per request. */
    protected static ?UserAccount $sessionAccount = null;

    public static function current(): ?UserAccount
    {
        $id = session('user_id');
        if (!$id) {
            return null;
        }

        if (static::$sessionAccount?->UserID !== $id) {
            static::$sessionAccount = static::find($id);
        }

        return static::$sessionAccount;
    }

    /**
     * Configuration is open to the super admin and to accounts with the
     * Staff role — never to a Dentist. (Activity Logs inside it stay
     * super-admin only; see canViewActivityLogs.)
     */
    public static function currentCanManageConfiguration(): bool
    {
        if (session('super_admin_setup')) {
            return false;
        }

        return session('is_super_admin') || static::current()?->Position === 'Staff';
    }

    /**
     * A dentist (not the super admin) only ever sees their own schedule.
     */
    public static function currentIsDentist(): bool
    {
        return !session('is_super_admin') && static::current()?->Position === 'Dentist';
    }

    /**
     * "Dr. Jane Cruz" for a dentist account, falling back to the email.
     */
    public function getDisplayNameAttribute(): string
    {
        $info = $this->staffInfo;

        if ($info) {
            $name = trim($info->FirstName . ' ' . $info->LastName);

            return $this->Position === 'Dentist' ? "Dr. {$name}" : $name;
        }

        return $this->Email;
    }
}
