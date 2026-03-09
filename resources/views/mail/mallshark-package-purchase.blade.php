<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>You’ve Successfully Purchased a Mallshark Package</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: rgb(10, 10, 10);
            background-color: #f9f9f9;
            padding: 20px;
        }

        .email-container {
            max-width: 600px;
            margin: auto;
            background-color: #ffffff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 30px;
        }

        .footer {
            margin-top: 30px;
            font-size: 0.9em;
            color: #777;
        }
    </style>
</head>

<body>
    <div class="email-container" style="font-family: Arial, sans-serif; font-size: 14px; color: #333; padding: 20px; max-width: 600px; margin: auto;">
        <div style="margin-bottom: 30px; text-align: center;">
            <a href="{{ env('FRONT_END_URL') }}">
                <img src="https://mallshark.com/assets/images/Mall-Shark-Logo.png"
                    alt="Mallshark logo" title="Mallshark logo" width="130px">
            </a>
        </div>

        <p style="margin-bottom: 20px;">Dear {{ $buyerName }},</p>

        <p style="margin-bottom: 15px;">Thank you for purchasing a seller package on <strong>Mallshark</strong>!</p>

        <p style="margin-bottom: 15px;">We're excited to support your business journey. Below are the details of your package:</p>

        <div style="margin-bottom: 20px; line-height: 1.6;">
            <strong>Package Name:</strong> {{ $packageInfo['package_name'] }}<br>
            <strong>Amount:</strong> ${{ number_format($packageInfo['amount'], 2) }}<br>
            <strong>Start Date:</strong> {{ \Carbon\Carbon::parse($packageInfo['started_at'])->toFormattedDateString() }}<br>
            <strong>Expiry Date:</strong> {{ \Carbon\Carbon::parse($packageInfo['expired_at'])->toFormattedDateString() }}
        </div>

        <p style="margin-bottom: 15px;">If you have any questions or need help accessing your package benefits, feel free to reach out to us at
            <a href="mailto:care@mallshark.com" style="color: #007BFF;">care@mallshark.com</a>.
        </p>

        <p style="margin-bottom: 40px;">We're here to help your store succeed on Mallshark!</p>

        <p style="margin-bottom: 60px;">Best regards,<br><strong>Team Mallshark</strong></p>

        <div class="mail-footer" style="border-top: 1px solid #ddd; padding-top: 20px;">
            <center>
                <div style="margin-bottom: 20px;">
                    <strong style="font-size: 16px;">
                        <a href="{{ env('FRONT_END_URL') }}/faqs/general" style="color: #183B56; text-decoration: none;">FAQs</a> |
                        <a href="{{ env('FRONT_END_URL') }}/contact-us" style="color: #183B56; text-decoration: none;">Contact Us</a>
                    </strong>
                </div>

                <div style="font-size: 12px; color: #999;">
                    Copyright © {{ date('Y') }}
                    <a href="https://mallshark.com" style="color: #073ba5; text-decoration: none;">Mallshark.com</a>, Inc. All rights reserved.
                </div>
            </center>
        </div>
    </div>
</body>

</html>