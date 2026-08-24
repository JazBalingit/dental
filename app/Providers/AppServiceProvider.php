<?php

namespace App\Providers;

use App\View\Composers\AdminNotificationComposer;
use App\View\Composers\UserNotificationComposer;
use Illuminate\Pagination\Paginator;
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
        // The app's custom pagination styling (e.g. .history-footer .pagination
        // in user_appointments.css) targets Bootstrap 5's markup, not Laravel's
        // default Tailwind pagination view — without this, ->links() renders
        // unstyled and clashes with the custom prev/next controls around it.
        Paginator::useBootstrapFive();

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
            'users.settings',
        ], UserNotificationComposer::class);
    }
}
