<?php

use App\Http\Middleware\EnsureAdmin;
use App\Http\Middleware\EnsureAuthenticated;
use App\Http\Middleware\EnsureStaffIsVerified;
use App\Http\Middleware\EnsureSuperAdmin;
use App\Http\Middleware\EnsureSuperAdminClaimed;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Behind Railway's load balancer (and any HTTPS-terminating proxy) the
        // app only ever sees the proxy as the client. Trust it so Laravel reads
        // the real client IP and, crucially, knows the original request was
        // HTTPS — otherwise url()/route() generate http:// links and the
        // emailed verification/reset URLs come out wrong.
        $middleware->trustProxies(at: '*');

        $middleware->web(append: [
            EnsureStaffIsVerified::class,
            EnsureSuperAdminClaimed::class,
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

        // A stale CSRF token (the page sat open past the session lifetime)
        // normally renders Laravel's bare "419 | Page Expired" screen, which
        // leaves the user stuck — their session is already gone. Instead,
        // clear whatever is left of the session and send them to the login
        // page with a plain explanation, i.e. treat it as a sign-out.
        //
        // The framework has already turned the TokenMismatchException into a
        // 419 HttpException by the time render callbacks run, so match on the
        // status code. 419 only ever comes from a CSRF token mismatch.
        $exceptions->render(function (HttpException $e, Request $request) {
            if ($e->getStatusCode() !== 419 || $request->expectsJson()) {
                return null;
            }

            $request->session()->flush();
            $request->session()->regenerate();

            return redirect()->route('login')
                ->with('login_error', 'Your session expired for security. Please sign in again.');
        });

        // An email send that fails at the transport (Resend/SMTP rejected it,
        // network down, sandbox-mode recipient restriction, …) otherwise
        // white-screens the user with a 500 in the middle of signup / password
        // reset / email verification. Every one of those flows only writes to
        // the DB *after* the emailed code is confirmed, so it's safe to just
        // bounce the user back to the form with a retry message; the framework
        // still logs the real reason. Modal-based flows (login "forgot
        // password", signup OTP) reopen themselves on an `error` flash.
        $exceptions->render(function (TransportExceptionInterface $e, Request $request) {
            if ($request->expectsJson()) {
                return null;
            }

            return redirect()->back()
                ->withInput($request->except('_token', '_method'))
                ->with('error', 'We couldn\'t send the email just now. Please wait a moment and try again.');
        });
    })->create();
