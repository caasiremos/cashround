<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contribution reminder - {{ $group->name }}</title>
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
                                Hi {{ !empty($memberFirstName) ? $memberFirstName : 'there' }},
                            </p>
                            @if($isLastDay)
                                <p style="margin: 0 0 24px; font-size: 15px; line-height: 1.6; color: #52525b;">
                                    This is a reminder that your contribution for the group <strong>{{ $group->name }}</strong> is due today ({{ \Carbon\Carbon::parse($dueDate)->format('F j, Y') }}).
                                </p>
                            @else
                                <p style="margin: 0 0 24px; font-size: 15px; line-height: 1.6; color: #52525b;">
                                    The contribution period for <strong>{{ $group->name }}</strong> has started. Your contribution of <strong>{{ number_format($group->amount) }}</strong> is due by {{ \Carbon\Carbon::parse($dueDate)->format('F j, Y') }}.
                                </p>
                            @endif
                            <p style="margin: 0 0 24px; font-size: 15px; line-height: 1.6; color: #52525b;">
                                Amount due: <strong>{{ number_format($group->amount) }}</strong>
                            </p>
                            <p style="margin: 0; font-size: 13px; line-height: 1.5; color: #71717a;">
                                Please make your contribution before the due date to keep the group on track.
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
