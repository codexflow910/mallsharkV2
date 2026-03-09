<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <style>
        * {
            padding: 0;
            margin: 0;
            box-sizing: border-box;
            font-family: 'Roboto', sans-serif;
            line-height: 25px;
        }

        .subject {
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            margin-top: 20px;
            text-decoration: underline;
        }

        .mail-container {
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .mail-footer .logo-section {
            display: none;
        }

        .logo-section img {
            max-width: 150px;
        }

        p,
        h1,
        h2,
        span,
        strong {
            color: #000;
            opacity: 80%;
        }

        p {
            line-height: 25px !important;
        }

        pre {
            background: #f8f8f8;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            white-space: pre-wrap;
            word-break: break-word;
            font-size: 14px;
        }

        @media(max-width:576px) {

            .mail-container p,
            .mail-container strong,
            .mail-container span {
                font-size: 14px !important;
            }
        }
    </style>
</head>

<body style="background:#F9FBFE;">
    <div class="mail-container" style="padding:1.5rem 0;">
        <div class="mail-body">
            <div style="border: 1px solid rgb(179, 179, 179); 
            border-radius: 10px;
            overflow: hidden;">
                <div class="logo-section" style="background: #fff; padding: 26px 22px;">
                    <a href="{{ env('FRONT_END_URL') }}" target="_blank" style="text-align: center;display: block;">
                        <img src="{{ URL::to('/assets/images/Mall-Shark-Logo.png') }}"
                            alt="MallShark logo"
                            title="Mallshark logo" width="130px">
                    </a>

                    <div class="subject" style="font-size:20px; margin-top: 20px; font-weight:bold;">
                        🚨 ShipStation Order Failure Alert
                    </div>

                    <div style="margin-top: 30px;">
                        <p style="margin: 8px 0; font-size: 16px; color:#555;">
                            <strong>Order ID:</strong> {{ $orderId }}
                        </p>
                        <p style="margin: 8px 0; font-size: 16px; color:#555;">
                            <strong>Order Details:</strong>
                        </p>

                        <p style="margin: 8px 0; font-size: 16px; color:#b71c1c; font-weight: bold;">
                            ⚠️ Please log in to ShipStation and place this order manually.
                        </p>
                    </div>

                    <div style="margin-top: 30px; font-size:16px;">
                        <p>Thank you,</p>
                        <p>The MallShark Tech Team</p>
                    </div>

                    <div class="mail-footer" style="max-width: 500px;  margin: 60px auto 0">
                        <center>
                            <div style="margin-bottom: 30px;">
                                <strong style="font-size: 18px;">
                                    <a href="{{ env('FRONT_END_URL') }}/faqs/general" style="color: #183B56; text-decoration: none;">FAQs</a> |
                                    <a href="{{ env('FRONT_END_URL') }}/contact-us" style="color: #183B56; text-decoration: none;">Contact Us</a>
                                </strong>
                            </div>

                            <div style="font-size:12px; margin: 10px 0">
                                Copyright © {{date('Y')}}
                                <a href="https://mallshark.com" style="color: #073ba5; text-decoration: none;">Mallshark.com</a>, Inc. All Rights Reserved.
                            </div>
                        </center>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>