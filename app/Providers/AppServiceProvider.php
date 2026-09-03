<?php

namespace App\Providers;

use App\View\Composers\AdminNotificationComposer;
use App\View\Composers\UserNotificationComposer;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

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

        // One password policy for every "create / change password" form in the
        // app (signup, staff accounts, all the reset flows): at least 8
        // characters, with upper- and lower-case letters, a number, and a
        // symbol (e.g. ! % @ #). Validation messages come from the framework.
        Password::defaults(fn () => Password::min(8)
            ->mixedCase()
            ->numbers()
            ->symbols());

        // Covers every admin-panel page in both folders — resources/views/superAdmin/*
        // (super admin) and resources/views/admin/* (staff / dentist) — plus the
        // shared staff profile page. The notification partials they all include
        // (admin-notif-dropdown / admin-notif-modal) depend on this composer.
        View::composer([
            'superAdmin.*',
            'admin.*',
            'staff.staff-userprofile',
        ], AdminNotificationComposer::class);

        View::composer([
            'users.landing-page',
            'users.user-appointment',
            'users.settings',
            'users.my-records',
        ], UserNotificationComposer::class);
    }
}
