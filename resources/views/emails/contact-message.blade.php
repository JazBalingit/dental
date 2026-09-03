<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us message</title>
</head>

<body style="font-family: Arial, sans-serif; background:#f4f6f5; padding:24px; margin:0;">
    <div style="max-width:560px;margin:0 auto;background:#ffffff;border-radius:10px;padding:32px;">
        <h2 style="color:#167d1d; margin-top:0;">New message from the website</h2>
        <p style="color:#6b7a70;font-size:13px;margin-top:-6px;">Sent through the “Contact Us” form on the landing page.</p>

        <table style="width:100%;border-collapse:collapse;margin:20px 0;">
            <tr>
                <td style="padding:8px 0;color:#6b7a70;font-size:13px;width:110px;">From</td>
                <td style="padding:8px 0;color:#1f2a24;font-size:14px;font-weight:600;">{{ $senderName }}</td>
            </tr>
            <tr>
                <td style="padding:8px 0;color:#6b7a70;font-size:13px;">Email</td>
                <td style="padding:8px 0;color:#1f2a24;font-size:14px;">
                    <a href="mailto:{{ $senderEmail }}" style="color:#0f7a33;">{{ $senderEmail }}</a>
                </td>
            </tr>
            <tr>
                <td style="padding:8px 0;color:#6b7a70;font-size:13px;">Account</td>
                <td style="padding:8px 0;color:#1f2a24;font-size:14px;">{{ $accountNote }}</td>
            </tr>
            <tr>
                <td style="padding:8px 0;color:#6b7a70;font-size:13px;">Subject</td>
                <td style="padding:8px 0;color:#1f2a24;font-size:14px;font-weight:600;">{{ $subjectLine }}</td>
            </tr>
        </table>

        <div style="background:#f4f6f5;border-radius:8px;padding:16px;color:#1f2a24;font-size:14px;line-height:1.6;white-space:pre-wrap;">{{ $body }}</div>

        <p style="color:#6b7a70;font-size:13px;margin-top:24px;">
            Reply directly to this email to respond to {{ $senderName }}.
        </p>
    </div>
</body>

</html>
