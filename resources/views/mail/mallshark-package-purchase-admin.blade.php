<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>New Seller Package Purchase Notification</title>
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

<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 14px; color: #333;">
    <div class="email-container" style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <div style="margin-bottom: 30px; text-align: center;">
            <a href="{{ env('FRONT_END_URL') }}">
                <img src="https://mallshark.com/assets/images/Mall-Shark-Logo.png"
                     alt="Mallshark logo" title="Mallshark logo" width="130px">
            </a>
        </div>

        <p style="margin-bottom: 20px;">Hello Admin,</p>

        <p style="margin-bottom: 15px;">A seller has successfully purchased a package on <strong>Mallshark</strong>.</p>

        <div style="margin-bottom: 20px; line-height: 1.6;">
            <strong>Buyer Information:</strong><br>
            Name: {{ $buyerName }}<br>
            Email: {{ $packageInfo['buyer_email'] ?? 'N/A' }}
        </div>

        <div style="margin-bottom: 20px; line-height: 1.6;">
            <strong>Package Details:</strong><br>
            Package Name: {{ $packageInfo['package_name'] }}<br>
            Amount: ${{ number_format($packageInfo['amount'], 2) }}<br>
            Start Date: {{ \Carbon\Carbon::parse($packageInfo['started_at'])->toFormattedDateString() }}<br>
            Expiry Date: {{ \Carbon\Carbon::parse($packageInfo['expired_at'])->toFormattedDateString() }}
        </div>

        <p style="margin-bottom: 20px;">You may view or manage this seller’s account in the admin dashboard.</p>

        <p style="margin-bottom: 50px;">Regards,<br>System Notification - Mallshark</p>

        <div class="mail-footer" style="border-top: 1px solid #ddd; padding-top: 20px; text-align: center;">
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
        </div>
    </div>
</body>

</html>