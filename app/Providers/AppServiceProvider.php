<?php

namespace App\Providers;

use App\View\Composers\AdminNotificationComposer;
use App\View\Composers\UserNotificationComposer;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
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

        // route()/url() normally build links from whatever Host header the
        // current request arrived with, not from APP_URL. That's a problem
        // here: the app sits behind Railway's own domain AND a Cloudflare
        // Worker proxy for the custom domain, and the Worker rewrites the
        // Host header to Railway's before forwarding — so without this,
        // every generated link (nav, emails, redirects) would point at
        // whichever hostname happened to receive the request instead of the
        // real puspusdentalclinic.online domain. Forcing the root keeps
        // every generated URL consistent no matter which front door it came
        // through.
        if (config('app.url')) {
            URL::forceRootUrl(config('app.url'));
            URL::forceScheme(str_starts_with(config('app.url'), 'https://') ? 'https' : 'http');
        }

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
            'users.user-appointment-book',
            'users.user-appointment-history',
            'users.settings',
            'users.my-records',
        ], UserNotificationComposer::class);
    }
}
