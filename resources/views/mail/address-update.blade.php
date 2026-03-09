<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Address Update Required</title>
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
        <div style="margin-bottom: 10px;">
            <a href="{{ env('FRONT_END_URL') }}" style="text-align: center;display: block;">
                <img src="https://mallshark.com/assets/images/Mall-Shark-Logo.png"
                    alt="Mallshark logo" title="Mallshark logo" width="130px">
            </a>
        </div>
        <p>Dear {{ $buyerName }},</p><br>

        <p>We hope this email finds you well.</p>

        <p>
            We are reaching out regarding your recent order <strong>#{{ $orderId }}</strong>. It appears that the shipping address provided is incomplete, which may cause delays in the delivery of your order.
        </p>

        <p>
            To ensure a smooth and timely dispatch, we kindly request you to update your address at your earliest convenience. You may reply to this email with the complete address or update it directly through your account.
        </p><br>

        <p>
            <strong>Current Shipping Address:</strong><br>
            {{ $shippingAddress['street'] }},
            {{ $shippingAddress['city'] }}, {{ $shippingAddress['state']['state_name'] ?? '' }} {{ $shippingAddress['post_code'] }}<br>
        </p><br>

        <p>
            Please let us know if you need any assistance at <a href="mailto:care@mallshark.com">care@mallshark.com</a>.
        </p>

        <p>We appreciate your prompt attention to this matter.</p><br>

        <p>Best regards,<br>Team Mallshark</p>

        <div class="mail-footer" style="max-width: 500px;  margin: 60px auto 0">
            <center>
                <div style="margin-bottom: 20px;">
                    <strong style="font-size: 18px;"> <a href="{{ env('FRONT_END_URL') }}/faqs/general"
                            style="color: #183B56; text-decoration: none;">FAQs</a> | <a href="{{ env('FRONT_END_URL') }}/contact-us"
                            style="color: #183B56; text-decoration: none;">Contact Us</a></strong>
                </div>

                <div style="font-size:12px; margin: 10px 0"> Copyright © {{date('Y')}}
                    <a href="https://mallshark.com"
                        style="color: #073ba5; text-decoration: none;">Mallshark.com,</a> Inc. All
                    Rights Reserved.<p></p>
                </div>
            </center>
        </div>
    </div>
</body>

</html>