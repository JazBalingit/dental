<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

/**
 * Starter landing-page copy for a freshly provisioned database — the hero
 * banner text, about-section blurb, and footer/contact details. Written
 * with firstOrCreate keyed by SettingKey, so it never overwrites a value an
 * admin has already set through Configuration; once the real value exists,
 * the seeded placeholder is simply gone.
 */
class SystemSettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = SystemSetting::aboutInfoDefaults();

        $settings = [
            'hero_image' => $defaults['heroImage'],
            'hero_title' => $defaults['heroTitle'],
            'hero_subtitle' => $defaults['heroSubtitle'],
            'hero_description' => $defaults['heroDescription'],
            'about_image' => $defaults['image'],
            'about_description' => $defaults['description'],
            'about_address' => $defaults['address'],
            'about_operating_days' => $defaults['operatingDays'],
            'contact_phone' => $defaults['phone'],
            'contact_mobile' => $defaults['mobile'],
            'contact_email' => $defaults['email'],
            'footer_description' => $defaults['footerDescription'],
            'footer_copyright' => $defaults['footerCopyright'],
        ];

        foreach ($settings as $key => $value) {
            SystemSetting::firstOrCreate(['SettingKey' => $key], ['SettingValue' => $value]);
        }
    }
}
