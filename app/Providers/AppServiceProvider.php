<?php

namespace App\Providers;

use App\View\Composers\AdminNotificationComposer;
use App\View\Composers\UserNotificationComposer;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer([
            'superAdmin.dashboard',
            'superAdmin.walk-in',
            'superAdmin.patient-records',
            'superAdmin.dentist-schedule',
            'superAdmin.configuration',
            'superAdmin.appointments',
            'superAdmin.staff-accounts',
            'superAdmin.user-accounts',
            'superAdmin.appointment-approval',
        ], AdminNotificationComposer::class);

        View::composer([
            'users.landing-page',
            'users.user-appointment',
        ], UserNotificationComposer::class);
    }
}
