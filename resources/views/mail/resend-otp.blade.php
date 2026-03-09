<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <title>Resend OTP</title>
    <style>
        * {
            padding: 0;
            margin: 0;
            box-sizing: border-box;
            font-family: 'Roboto', sans-serif;
            line-height: 25px;
            font-size: 16px;
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

        @media(max-width:576px) {
            .mail-container p {
                font-size: 14px !important;
            }

            .mail-container h1,
            .mail-container strong {
                font-size: 14px !important;
            }

            .mail-container span {
                font-size: 14px !important;
            }
        }
    </style>
</head>

<body style="background:#F9FBFE;">
    <div class="mail-container" style="padding:1.5rem 0;">

        <div class="mail-body">

            <div
                style="border: 1px solid rgb(179, 179, 179);
            border-radius: 10px;
            overflow: hidden;">
                <div class="logo-section" style="background: #fff; padding: 26px 22px;">
                    <a href="{{ env('FRONT_END_URL') }}" style="text-align: center;display: block;">
                        <img src="https://mallshark.com/assets/images/MallShark-Beta-new.png" alt="Mallshark logo"
                            title="Mallshark logo" width="130px">
                    </a>

                    <div style="margin-top: 20px;">
                        <strong style="font-size: 17px;">Hi {{ $name ?? 'User' }},</strong>
                    </div>

                    <div style="margin-top: 15px;">
                        <p>You requested a new verification code. Please use the following code to verify your account:</p>
                    </div>
                    <div style="margin-top: 20px; font-size: 16px;">
                        <b>
                            <p>Verification Code: &nbsp;&nbsp;&nbsp;<span
                                    style="color: #0510fa; text-decoration: none; font-weight: 600;">{{ $otp }}</span>
                            </p>
                        </b>
                    </div>

                    <div style="margin-top: 20px;">
                        <p>If you did not request this code, please ignore this email.</p>
                    </div>

                    <div>
                        <p style="font-size: 16px; margin-top: 15px;">Sincerely,</p>
                        <p style="font-size: 16px;">The MallShark Team</p>
                    </div>


                    <div class="mail-footer" style="max-width: 500px;  margin: 60px auto 0">
                        <center>
                            <div style="margin-bottom: 30px;">
                                <strong style="font-size: 18px;"> <a href="{{ env('FRONT_END_URL') }}/faqs/general"
                                        style="color: #183B56; text-decoration: none;">FAQs</a> | <a
                                        href="{{ env('FRONT_END_URL') }}/contact-us"
                                        style="color: #183B56; text-decoration: none;">Contact Us</a></strong>
                            </div>

                            <div style="font-size:12px; margin: 10px 0"> Copyright &copy; {{ date('Y') }}
                                <a href="https://mallshark.com"
                                    style="color: #0510fa; text-decoration: none;">Mallshark.com,</a> Inc. All Rights
                                Reserved.<p></p>
                            </div>
                        </center>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
