<?php

use App\Http\Middleware\EnsureAdmin;
use App\Http\Middleware\EnsureAuthenticated;
use App\Http\Middleware\EnsureStaffIsVerified;
use App\Http\Middleware\EnsureSuperAdmin;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            EnsureStaffIsVerified::class,
        ]);

        $middleware->alias([
            'admin' => EnsureAdmin::class,
            'superadmin' => EnsureSuperAdmin::class,
            'auth.session' => EnsureAuthenticated::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // When a form fails validation, send the user back with EVERYTHING
        // they typed still filled in — including the password fields, which
        // Laravel normally strips. Combined with `value="{{ old(...) }}"` on
        // the inputs and the show/hide eye toggle, nobody has to retype a
        // long form just because one field was wrong.
        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->expectsJson()) {
                return null; // keep the default 422 JSON response for AJAX
            }

            return redirect($e->redirectTo ?? url()->previous())
                ->withInput($request->except('_token', '_method'))
                ->withErrors($e->errors(), $e->errorBag);
        });
    })->create();
