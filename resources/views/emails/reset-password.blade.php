<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cashround - Reset Password Code</title>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background-color: #f4f4f5; -webkit-font-smoothing: antialiased;">
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #f4f4f5;">
        <tr>
            <td style="padding: 40px 24px;">
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="max-width: 480px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="padding: 40px 40px 24px; text-align: center; border-bottom: 1px solid #e4e4e7;">
                            <h1 style="margin: 0; font-size: 22px; font-weight: 700; color: #18181b; letter-spacing: -0.02em;">
                                {{ config('app.name') }}
                            </h1>
                        </td>
                    </tr>
                    <!-- Body -->
                    <tr>
                        <td style="padding: 32px 40px;">
                            <p style="margin: 0 0 8px; font-size: 18px; font-weight: 600; color: #18181b;">
                                Hi,
                            </p>
                            <p style="margin: 0 0 24px; font-size: 15px; line-height: 1.6; color: #52525b;">
                                Use the code below to reset your password.
                            </p>
                            <!-- Code box -->
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td style="padding: 24px; background-color: #f4f4f5; border-radius: 12px; border: 1px solid #e4e4e7; text-align: center;">
                                        <span style="font-size: 28px; font-weight: 700; letter-spacing: 0.35em; color: #18181b; font-variant-numeric: tabular-nums;">
                                            {{ $token }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                            <p style="margin: 24px 0 0; font-size: 13px; line-height: 1.5; color: #71717a;">
                                This code expires in 15 minutes. If you didn’t request this, you can safely ignore this email.
                            </p>
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td style="padding: 24px 40px 40px; text-align: center; border-top: 1px solid #e4e4e7;">
                            <p style="margin: 0; font-size: 12px; color: #a1a1aa;">
                                &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
