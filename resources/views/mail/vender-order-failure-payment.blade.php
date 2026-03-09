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

        .mail-container {
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .mail-footer .logo-section {
            display: none;
        }

        p {
            font-size: 16px;
        }

        .logo-section img {
            max-width: 150px;
        }

        .products a {
            font-size: 20px;
            font-weight: bold;
            color: #183B56;
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

        @media(min-width:768px) {
            .set-min-width {
                min-width: 80px;
            }
        }

        @media(max-width:768px) {
            .set-min-width {
                padding-right: .5rem;
            }
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
            <div style="border: 1px solid rgb(179, 179, 179); border-radius: 10px; overflow: hidden;">
                <div class="logo-section" style="background: #fff; padding: 26px 22px;">
                    <a href="{{URL::to('/')}}" style="text-align: center; display: block;">
                        <img src="https://mallshark.com/assets/images/MallShark-Beta-new.png" alt="Mallshark logo"
                             title="Mallshark logo" width="130px">
                    </a>

                    <div style="margin-top: 30px;">
                        <strong style="font-size: 17px;">Dear {{$store_name}},</strong>

                        <p style="margin: 30px 0 20px 0; font-size: 16px;">
                            Unfortunately, we were unable to process your payment of <strong>${{$payable_amount}}</strong> via the MallShark platform.
                        </p>

                        <p style="font-size: 16px;">The failed payment was related to the following order{{ count($orderIds) > 1 ? 's' : '' }}:</p>

                        <ul style="font-size: 15px; margin: 15px 0; padding-left: 20px;">
                            @foreach($orderIds as $orderId)
                                <li>Order ID: <strong>#{{ $orderId }}</strong></li>
                            @endforeach
                        </ul>

                        <p style="font-size: 16px;">
                            Please verify your payment details or contact support to resolve the issue.
                        </p>

                        <p style="font-size: 16px;">We apologize for the inconvenience and appreciate your patience.</p>
                    </div>

                    <div class="delivery-details" style="background: #fff; padding: 20px 0px;">
                        <div style="margin-top: 30px; font-size:16px;">
                            <p>Sincerely,</p>
                            <p>The MallShark Team</p>
                        </div>

                        <div class="mail-footer" style="max-width: 500px; margin: 60px auto 0">
                            <center>
                                <div style="margin-bottom: 30px;">
                                    <strong style="font-size: 18px;">
                                        <a href="{{URL::to('/buyerfaq')}}" style="color: #183B56; text-decoration: none;">FAQs</a> |
                                        <a href="{{URL::to('/contact-us')}}" style="color: #183B56; text-decoration: none;">Contact Us</a>
                                    </strong>
                                </div>

                                <div class="logo-section">
                                    <a href="#" style="text-align: center;display: block;">
                                        <img src="https://mallshark.com/assets/images/MallShark-Beta-new.png" alt="Mallshark logo" title="Mallshark logo">
                                    </a>
                                </div>

                                <div style="font-size:12px; margin: 10px 0">
                                    Copyright © {{date('Y')}} 
                                    <a href="https://mallshark.com" style="color: #0510fa; text-decoration: none;">Mallshark.com,</a> Inc. All Rights Reserved.
                                </div>
                            </center>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> 
</body>
</html>
