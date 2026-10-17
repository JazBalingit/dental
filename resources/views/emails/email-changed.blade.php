<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your account email was changed</title>
</head>

<body style="font-family: Arial, sans-serif; background:#f4f6f5; padding:24px; margin:0; -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; text-size-adjust:100%;">
    <div style="max-width:480px;margin:0 auto;background:#ffffff;border-radius:10px;padding:32px;">
        <h2 style="color:#167d1d; margin-top:0;">Pus-Pus Britanico Dental Clinic</h2>
        <p style="color:#1f2a24;">
            The email address of your clinic account was changed
            {{ $changedBySelf ? '' : 'by the clinic' }} to:
        </p>

        <div style="font-size:16px;font-weight:700;text-align:center;word-break:break-all;
                    background:#e9f7ea;color:#0f7a33;padding:14px 8px;border-radius:8px;margin:20px 0;">
            {{ $newEmail }}
        </div>

        <p style="color:#1f2a24;">
            From now on, sign in and receive clinic updates with the new address.
            This email address will no longer receive them.
        </p>

        <p style="color:#6b7a70;font-size:13px;">
            If you did not make or ask for this change, please contact the clinic right away so we can secure your account.
        </p>
    </div>
</body>

</html>
