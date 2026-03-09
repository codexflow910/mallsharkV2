<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
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
        <div class="email-container">
            <div style="text-align: center; padding: 20px 0;">
                <img src="{{ URL::to('/assets/images/Mall-Shark-Logo.png') }}" 
                    alt="Mallshark logo"
                    title="Mallshark logo"
                    width="200"
                    style="display: block; margin: 0 auto 20px; border: none; outline: none; height: auto; max-width: 100%;">
            </div>

            <p>Dear {{ $userName }},</p><br>

            <p>We hope this email finds you well.</p>

            <p>We are excited to inform you that you have been assigned to manage the store: <a href="{{ env('FRONT_END_URL') }}/brand/{{ $storeSlug }}">{{ $storeName }}</a>.</p>
            <p>
                You will have access to various tools and features to help you effectively oversee store operations.
                @if ($isNewUser)
                    To get started, please <a href="{{ $loginUrl }}">set your password and log in</a> to your new account.
                @else
                    To get started, please <a href="{{ env('FRONT_END_URL') }}/login">log in</a> to your account.
                @endif
            </p>
            <p>If you have any questions or need assistance, feel free to reach out to our support team at 
                <a href="mailto:{{ env('SUPPORT_EMAIL') }}">{{ env('SUPPORT_EMAIL') }}</a>
            </p>
            <br>
            <p>Best regards,<br>Team Mallshark</p>

            <div class="mail-footer" style="max-width: 500px; margin: 60px auto 0; text-align: center;">
                <div style="margin-bottom: 20px;">
                    <strong style="font-size: 18px;">
                        <a href="{{ env('FRONT_END_URL') }}/faqs/general"
                        style="color: #183B56; text-decoration: none;">FAQs</a> |
                        <a href="{{ env('FRONT_END_URL') }}/contact-us"
                        style="color: #183B56; text-decoration: none;">Contact Us</a>
                    </strong>
                </div>

                <div style="font-size:12px; margin: 10px 0">
                    Copyright © {{ date('Y') }}
                    <a href="https://mallshark.com" style="color: #073ba5; text-decoration: none;">Mallshark.com</a>, Inc. All
                    Rights Reserved.
                </div>
            </div>
        </div>
    </body>
</html>