<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $campaign->subject }}</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
    <table role="presentation" style="width: 100%; border-collapse: collapse;">
        <tr>
            <td align="center" style="padding: 20px 0;">
                <table role="presentation" style="width: 600px; max-width: 100%; background-color: #ffffff; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 40px 30px;">
                            {!! $htmlContent !!}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 20px 30px; background-color: #f9f9f9; border-top: 1px solid #eeeeee; text-align: center; font-size: 12px; color: #666666;">
                            <p style="margin: 0 0 10px 0;">
                                <a href="{{ route('email.unsubscribe', ['token' => $trackingToken]) }}" style="color: #666666; text-decoration: underline;">
                                    Unsubscribe
                                </a>
                            </p>
                            <p style="margin: 0;">
                                © {{ date('Y') }} {{ $campaign->organization->name ?? 'MarketPulse' }}. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

