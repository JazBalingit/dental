{{--
    Shared flash-message toasts — fixed top-right, auto-dismiss after 8s
    (long enough to read the longer booking-failure explanations).
    Covers every page-level session flash key used across the app plus the
    default Laravel validation error bag. Modal-scoped alerts (OTP modal,
    forgot/reset-password modals) are NOT part of this partial — those stay
    inline at the top of their modal, unchanged.

    Optional: pass ['topOffset' => '20px'] when including on a page with no
    sticky topbar/navbar (e.g. login/signup). Defaults to 84px, which clears
    the 68px admin `.topbar`. Pages using the public `.navbar.fixed-top`
    (landing page, patient settings/profile/appointments) pass '100px'.
--}}
@php
    $topOffset = $topOffset ?? '84px';

    $flashes = [
        ['key' => 'success', 'type' => 'success'],
        ['key' => 'error', 'type' => 'danger'],
        ['key' => 'registered', 'type' => 'success', 'text' => 'Account successfully created! You can now log in.'],
        ['key' => 'password_reset', 'type' => 'success', 'text' => 'Password reset successful! You can now log in with your new password.'],
        ['key' => 'login_error', 'type' => 'danger'],
        ['key' => 'verify_sent', 'type' => 'success', 'text' => 'Verification code sent! Check your inbox (and spam folder).'],
        ['key' => 'booking_success', 'type' => 'success'],
        ['key' => 'booking_error', 'type' => 'danger'],
        ['key' => 'password_updated', 'type' => 'success', 'text' => 'Your password has been updated.'],
        ['key' => 'password_error', 'type' => 'danger'],
        ['key' => 'profile_updated', 'type' => 'success', 'text' => 'Your profile has been updated.'],
    ];

    $icons = [
        'success' => 'bi-check-circle-fill',
        'danger' => 'bi-exclamation-triangle-fill',
    ];

    $titles = [
        'success' => 'Success',
        'danger' => 'Something needs your attention',
    ];
@endphp

<style>
    .flash-toast-container { position: fixed; right: 1.25rem; z-index: 1080; display: flex; flex-direction: column; align-items: flex-end; pointer-events: none; }
    .flash-toast { pointer-events: auto; position: relative; display: flex; align-items: flex-start; gap: .7rem; background: #fff; border-radius: var(--radius-lg, 1rem); box-shadow: var(--shadow-lg, 0 18px 40px -12px rgba(15,23,42,.18)); padding: .85rem 2.3rem .85rem .9rem; margin-bottom: .7rem; width: 340px; max-width: calc(100vw - 2.5rem); overflow: hidden; border-left: 4px solid var(--success, #10b981); opacity: 0; transform: translateX(18px); animation: flashToastIn .28s ease forwards; }
    .flash-toast.flash-toast-danger { border-left-color: var(--danger, #ef4444); }
    .flash-toast-icon { flex: none; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: rgba(16,185,129,.12); color: var(--success, #10b981); font-size: 1rem; margin-top: 1px; }
    .flash-toast.flash-toast-danger .flash-toast-icon { background: rgba(239,68,68,.12); color: var(--danger, #ef4444); }
    .flash-toast-title { font-weight: 600; font-size: .85rem; color: var(--ink-900, #0f172a); margin-bottom: .1rem; }
    .flash-toast-body { flex: 1; font-size: .82rem; color: var(--ink-700, #334155); line-height: 1.45; }
    .flash-toast-body ul { padding-left: 1.1rem; margin: 0; }
    .flash-toast-close { position: absolute; top: .55rem; right: .55rem; border: none; background: transparent; color: var(--ink-500, #64748b); width: 22px; height: 22px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: .65rem; cursor: pointer; transition: background .15s, color .15s; }
    .flash-toast-close:hover { background: var(--ink-100, #f1f5f9); color: var(--ink-900, #0f172a); }
    .flash-toast-progress { position: absolute; left: 0; bottom: 0; height: 3px; background: var(--success, #10b981); width: 100%; animation: flashToastProgress 8s linear forwards; }
    .flash-toast.flash-toast-danger .flash-toast-progress { background: var(--danger, #ef4444); }
    .flash-toast.hide-toast { animation: flashToastOut .2s ease forwards; }
    @keyframes flashToastIn { to { opacity: 1; transform: translateX(0); } }
    @keyframes flashToastOut { to { opacity: 0; transform: translateX(18px); } }
    @keyframes flashToastProgress { from { width: 100%; } to { width: 0%; } }
</style>

<div class="flash-toast-container" style="top: {{ $topOffset }};">
    @foreach ($flashes as $flash)
        @if (session($flash['key']))
            <div class="flash-toast flash-toast-{{ $flash['type'] }}" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="flash-toast-icon"><i class="bi {{ $icons[$flash['type']] }}"></i></div>
                <div class="flash-toast-body">
                    <div class="flash-toast-title">{{ $titles[$flash['type']] }}</div>
                    {{ $flash['text'] ?? session($flash['key']) }}
                </div>
                <button type="button" class="flash-toast-close" aria-label="Close"><i class="bi bi-x-lg"></i></button>
                <div class="flash-toast-progress"></div>
            </div>
        @endif
    @endforeach

    @if ($errors->any())
        <div class="flash-toast flash-toast-danger" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="flash-toast-icon"><i class="bi {{ $icons['danger'] }}"></i></div>
            <div class="flash-toast-body">
                <div class="flash-toast-title">{{ $titles['danger'] }}</div>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            <button type="button" class="flash-toast-close" aria-label="Close"><i class="bi bi-x-lg"></i></button>
            <div class="flash-toast-progress"></div>
        </div>
    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.flash-toast').forEach(function (el) {
            function dismiss() {
                if (el.classList.contains('hide-toast')) return;
                el.classList.add('hide-toast');
                setTimeout(function () { el.remove(); }, 200);
            }
            el.querySelector('.flash-toast-close')?.addEventListener('click', dismiss);
            setTimeout(dismiss, 8000);
        });
    });

    /*
     * Keep a modal open when its form comes back with an error.
     *
     * These pages submit modal forms with a normal POST; a validation or
     * business-rule failure redirects back and the page reloads with the
     * modal closed. Here we remember which modal a submitted form lived in
     * and, if the reloaded page carries an error (the default validation
     * bag or any of the error flash keys) and NO success flash, we re-open
     * that modal so the alert is read against the form that caused it.
     * On success nothing is re-opened, so the modal closes as before.
     *
     * Only data-entry modals are tracked (a form with a real input/select/
     * textarea) — plain confirm/delete dialogs still just close.
     */
    (function () {
        var ERROR_ON_LOAD = @json(
            $errors->any()
            || session()->has('error')
            || session()->has('login_error')
            || session()->has('booking_error')
            || session()->has('password_error')
            || session()->has('walkin_error_step')
        );
        var SUCCESS_ON_LOAD = @json(
            session()->has('success')
            || session()->has('registered')
            || session()->has('password_reset')
            || session()->has('booking_success')
            || session()->has('password_updated')
            || session()->has('profile_updated')
        );

        var KEY = 'modalPersist:' + location.pathname;

        function store(id) { try { sessionStorage.setItem(KEY, id); } catch (e) {} }
        function take() {
            var v = null;
            try { v = sessionStorage.getItem(KEY); sessionStorage.removeItem(KEY); } catch (e) {}
            return v;
        }

        // Capture phase so this runs even if the form's own handler stops propagation.
        document.addEventListener('submit', function (e) {
            var form = e.target;
            if (!(form instanceof HTMLFormElement)) return;

            var method = (form.getAttribute('method') || 'get').toLowerCase();
            var modal = form.closest('.modal');
            var hasField = form.querySelector(
                'input:not([type=hidden]):not([type=submit]):not([type=button]):not([type=reset]), select, textarea'
            );

            if (method === 'post' && modal && modal.id && hasField) {
                store(modal.id);
            } else {
                take();
            }
        }, true);

        function reopen() {
            var id = take();
            if (!id || !ERROR_ON_LOAD || SUCCESS_ON_LOAD) return;

            var el = document.getElementById(id);
            if (!el || !window.bootstrap || !bootstrap.Modal) return;

            bootstrap.Modal.getOrCreateInstance(el).show();

            // Nudge the first field into view once the modal has painted.
            setTimeout(function () {
                var field = el.querySelector('.is-invalid, input:not([type=hidden]), select, textarea');
                if (field) field.focus({ preventScroll: false });
            }, 300);
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', reopen);
        } else {
            reopen();
        }
    })();
</script>
