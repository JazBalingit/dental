<!doctype html>
<html>
<body style="font-family: Arial, sans-serif; background:#f4f6f5; padding:24px; margin:0;">
    <div style="max-width:480px;margin:0 auto;background:#ffffff;border-radius:10px;padding:32px;">
        <h2 style="color:#167d1d; margin-top:0;">Pus-Pus Britanico Dental Clinic</h2>
        <h3 style="color:#1f2a24;">{{ $heading }}</h3>
        <p style="color:#1f2a24; line-height:1.6;">{{ $body }}</p>

        {{-- The appointment's date and time, front and centre. --}}
        @if (!empty($schedule))
            <div style="margin-top:20px;background:#167d1d;border-radius:8px;padding:16px 18px;color:#ffffff;">
                <div style="font-size:11px;letter-spacing:.08em;text-transform:uppercase;opacity:.85;">Appointment schedule</div>
                <div style="font-size:18px;font-weight:bold;margin-top:6px;">{{ $schedule[0] }}</div>
                <div style="font-size:18px;font-weight:bold;margin-top:2px;">{{ $schedule[1] }}</div>
            </div>
        @endif

        @if (!empty($details))
            <div style="margin-top:16px;border:1px solid #d9e6dc;border-radius:8px;overflow:hidden;">
                <div style="background:#f1fcf0;color:#167d1d;font-weight:bold;font-size:13px;letter-spacing:.04em;text-transform:uppercase;padding:10px 16px;">
                    Appointment details
                </div>
                <table role="presentation" cellpadding="0" cellspacing="0" style="width:100%;border-collapse:collapse;">
                    @foreach ($details as $label => $value)
                        @continue(!empty($schedule) && in_array($label, ['Date', 'Time'], true))
                        <tr>
                            <td style="padding:9px 16px;color:#6b7a70;font-size:13px;width:110px;vertical-align:top;border-top:1px solid #eef3ef;">{{ $label }}</td>
                            <td style="padding:9px 16px;color:#1f2a24;font-size:14px;font-weight:bold;border-top:1px solid #eef3ef;">{{ $value }}</td>
                        </tr>
                    @endforeach
                </table>
            </div>
        @endif

        <p style="color:#6b7a70;font-size:13px;margin-top:24px;">
            This is an automated message. Should you have any questions, please contact the clinic directly.
        </p>
    </div>
</body>
</html>
