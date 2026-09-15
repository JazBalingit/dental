// Disables a "Resend" button and counts down the seconds left in the
// server-side resend cooldown (see ManagesOtp::otpResendRetryAfter).
// Usage: add data-resend-cooldown="{{ $secondsRemaining }}" to the button.
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-resend-cooldown]').forEach(function (btn) {
        var remaining = parseInt(btn.getAttribute('data-resend-cooldown'), 10) || 0;
        if (remaining <= 0) {
            return;
        }

        var baseLabel = btn.textContent.trim();
        btn.disabled = true;
        btn.textContent = baseLabel + ' (' + remaining + 's)';

        var timer = setInterval(function () {
            remaining--;

            if (remaining <= 0) {
                clearInterval(timer);
                btn.disabled = false;
                btn.textContent = baseLabel;
                return;
            }

            btn.textContent = baseLabel + ' (' + remaining + 's)';
        }, 1000);
    });
});
