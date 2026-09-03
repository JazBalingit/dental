<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $table = 'tbl_systemSettings';
    protected $primaryKey = 'SystemSettingID';

    protected $fillable = [
        'SettingKey',
        'SettingValue',
    ];

    /**
     * Every call site passes the current hardcoded text as $default, so a
     * key with no row yet (nothing ever saved) renders identically to
     * before this table existed.
     */
    public static function get(string $key, $default = null)
    {
        $value = static::where('SettingKey', $key)->value('SettingValue');

        return $value !== null ? $value : $default;
    }

    public static function set(string $key, $value): void
    {
        static::updateOrCreate(['SettingKey' => $key], ['SettingValue' => $value]);
    }

    public static function forget(string $key): void
    {
        static::where('SettingKey', $key)->delete();
    }

    /**
     * Default copy for the landing page's "How It Works" steps — single
     * source of truth shared by the admin editor (ConfigurationController)
     * and the public landing page (UserController), so a step that's never
     * been saved renders identically wherever it's read.
     */
    public static function appointmentStepDefaults(): array
    {
        return [
            1 => ['title' => 'Create an Account or Log In', 'desc' => 'Sign up with your basic details, or log back in if you already have an account with us.'],
            2 => ['title' => 'Open the Appointment Section', 'desc' => 'Once you\'re logged in, scroll down to "Book Now" — the dentist\'s monthly schedule appears right here on this page.'],
            3 => ['title' => 'Pick an Open Date', 'desc' => 'Click any date that still has open slots to see that day\'s available times.'],
            4 => ['title' => 'Choose a Time & Service', 'desc' => 'Pick an open time slot, then select the dental treatment you need for that visit.'],
            5 => ['title' => 'Review & Confirm', 'desc' => 'Double-check the service, date, and time, then confirm to submit your appointment request.'],
            6 => ['title' => 'Wait for Approval', 'desc' => 'Your request starts as Pending — we\'ll notify you once staff approves it. Track it anytime on your Appointments page.'],
        ];
    }

    /**
     * How many appointment-process steps currently exist. Admins can add or
     * remove steps in Configuration, so this is no longer fixed at 6.
     */
    public static function appointmentStepCount(): int
    {
        return (int) static::get('appt_step_count', count(static::appointmentStepDefaults()));
    }

    /**
     * Every step, with the saved value (if any) merged over the default.
     * Steps beyond the original defaults (added by an admin) fall back to
     * generic placeholder copy.
     */
    public static function appointmentSteps(): array
    {
        $defaults = static::appointmentStepDefaults();
        $steps = [];

        for ($n = 1; $n <= static::appointmentStepCount(); $n++) {
            $default = $defaults[$n] ?? ['title' => 'New Step', 'desc' => 'Describe this step.'];
            $steps[$n] = [
                'title' => static::get("appt_step_{$n}_title", $default['title']),
                'desc' => static::get("appt_step_{$n}_desc", $default['desc']),
            ];
        }

        return $steps;
    }

    /**
     * Icons cycle for the landing page step cards — there are more icons
     * than the original 6 steps so newly added steps still get one.
     */
    public static function appointmentStepIcons(): array
    {
        return [
            'fa-solid fa-user-plus',
            'fa-regular fa-calendar',
            'fa-regular fa-calendar-days',
            'fa-regular fa-clock',
            'fa-regular fa-circle-check',
            'fa-regular fa-bell',
            'fa-regular fa-comment-dots',
            'fa-solid fa-notes-medical',
        ];
    }

    public static function aboutInfoDefaults(): array
    {
        return [
            'address' => 'Blk 6 Lot 2 Poblacion 2, GMA, Cavite',
            'operatingDays' => 'Monday – Saturday',
            'operatingHours' => '9:00 AM – 6:00 PM',
            'description' => "We're conveniently located in Pacita Complex, San Pedro. Walk-ins welcome, but booking ahead means no wait.",
            'image' => '/images/clinic2.jpg',
            'heroImage' => '/images/dental_chair.jpg',
            'logo' => '/images/puspus_logo.png',
            'phone' => '(02) 8404-5642',
            'mobile' => '+63 968-476-5943',
            'email' => 'jahzielhawan@gmail.com',
        ];
    }

    public static function aboutInfo(): array
    {
        $defaults = static::aboutInfoDefaults();

        return [
            'address' => static::get('about_address', $defaults['address']),
            'operatingDays' => static::get('about_operating_days', $defaults['operatingDays']),
            'operatingHours' => static::get('about_operating_hours', $defaults['operatingHours']),
            'description' => static::get('about_description', $defaults['description']),
            'image' => static::get('about_image', $defaults['image']),
            'heroImage' => static::get('hero_image', $defaults['heroImage']),
            'logo' => $defaults['logo'],
            'phone' => static::get('contact_phone', $defaults['phone']),
            'mobile' => static::get('contact_mobile', $defaults['mobile']),
            'email' => static::get('contact_email', $defaults['email']),
        ];
    }

    /**
     * Where the landing-page "Contact Us" form delivers messages. Same value
     * shown publicly as the clinic email — editable in Configuration →
     * System Information.
     */
    public static function contactEmail(): string
    {
        return static::get('contact_email', static::aboutInfoDefaults()['email']);
    }
}
