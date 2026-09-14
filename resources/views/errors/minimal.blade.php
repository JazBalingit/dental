{{--
    Shared shell for every custom error page (500, 503, 404, 403, 419).
    Laravel resolves resources/views/errors/{code}.blade.php automatically
    when APP_DEBUG is off (production/Railway) — each of those files just
    @includes this partial with its own code/title/message.

    Always gives the visitor a way out instead of a dead end: "Go back"
    (browser history) plus a link to wherever makes sense for them (their
    dashboard/appointments if logged in, the homepage/login otherwise).
--}}
@php
    $homeUrl = route('landingPage');
    $homeLabel = 'Go to homepage';

    if (session('user_id')) {
        if (in_array(session('user_role'), \App\Models\UserAccount::ADMIN_ROLES, true)) {
            $homeUrl = route('dashboard');
            $homeLabel = 'Go to dashboard';
        } else {
            $homeUrl = route('userAppointment');
            $homeLabel = 'Go to my appointments';
        }
    }
@endphp
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <link rel="icon" type="image/png" href="/images/puspus_logo.png">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ $code ?? 'Error' }} • Dental Clinic</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand-700: #37a03e;
            --brand-500: #33bd3c;
            --ink-900: #0f172a;
            --ink-500: #64748b;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            font-family: 'Inter', system-ui, sans-serif;
            background: radial-gradient(circle at top, #eafaec 0%, #f4f7fb 55%);
            color: var(--ink-900);
        }

        .error-card {
            width: 100%;
            max-width: 480px;
            text-align: center;
            background: #fff;
            border-radius: 20px;
            padding: 2.5rem 2rem;
            box-shadow: 0 24px 60px -20px rgba(15, 23, 42, .18);
        }

        .error-emblem {
            width: 72px;
            height: 72px;
            margin: 0 auto 1.25rem;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--brand-700), var(--brand-500));
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 12px 24px -10px rgba(15, 122, 51, .5);
        }

        .error-emblem img {
            width: 40px;
            height: 40px;
            object-fit: contain;
        }

        .error-code {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            font-size: 2.75rem;
            line-height: 1;
            margin: 0 0 .5rem;
            color: var(--brand-700);
        }

        .error-title {
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            font-size: 1.15rem;
            margin: 0 0 .5rem;
        }

        .error-message {
            color: var(--ink-500);
            font-size: .92rem;
            line-height: 1.6;
            margin: 0 0 1.75rem;
        }

        .error-actions {
            display: flex;
            gap: .6rem;
            flex-wrap: wrap;
            justify-content: center;
        }

        .btn {
            font-family: inherit;
            font-size: .88rem;
            font-weight: 600;
            padding: .7rem 1.3rem;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: .4rem;
        }

        .btn-brand {
            background: linear-gradient(135deg, var(--brand-700), var(--brand-500));
            color: #fff;
        }

        .btn-ghost {
            background: #f1f5f9;
            color: var(--ink-900);
        }
    </style>
</head>

<body>
    <div class="error-card">
        <div class="error-emblem"><img src="/images/puspus_logo.png" alt=""></div>
        <p class="error-code">{{ $code ?? '' }}</p>
        <h1 class="error-title">{{ $title ?? 'Something went wrong' }}</h1>
        <p class="error-message">{{ $message ?? "We hit a snag loading this page. It's not you — please try again in a moment." }}</p>
        <div class="error-actions">
            <a href="#" onclick="if (history.length > 1) { history.back(); return false; }" class="btn btn-ghost">Go back</a>
            <a href="{{ $homeUrl }}" class="btn btn-brand">{{ $homeLabel }}</a>
        </div>
    </div>
</body>

</html>
