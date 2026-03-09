<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <title>Order Received</title>
    <style>
        * {
            padding: 0;
            margin: 0;
            box-sizing: border-box;
            font-family: 'Roboto', sans-serif;
        }

        .mail-container {
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .mail-body {
            background: #f4f4f4;
            min-height: 500px;
            margin-top: 30px;
            padding: 40px 15px;
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
                min-width: 120px;
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

<body>
    <div class="mail-container" style="padding-top:1.5rem;">
        <div dir="ltr" style="padding: 0 0rem; line-height: 25px; opacity: 80%;">
            <b>From:</b> Mallshark &lt;<a href="mailto:noreply@support.mallshark.net"
                target="_blank">noreply@support.mallshark.net</a>&gt;<br><b>Date:</b> 10 June 2023 at 11:15:01 PM
            GMT+5<br><b>To:</b> <a href="mailto:sajidoon@mindblazetech.com"
                target="_blank">sajidoon@mindblazetech.com</a><br><b>Subject:</b> <b>Fwd: You’ve Received an Order!
                1</b><br><br>
        </div>
        <div class="mail-body">
            <div style="box-shadow:  0px 0px 2px 2px #e2e2e2;">
                <div class="logo-section" style="background: #fff; padding: 26px 22px; margin-bottom: 10px;">
                    <a href="#" style="text-align: center;display: block;">
                        <img src="{{asset('assets/images/MallShark-Beta-new.png')}}"
                            alt="Mallshark logo" title="Mallshark logo">
                    </a>

                    <div class="mail-to">
                        <strong style="font-size: 17px;">Hi Sajidoon Nadeem,</strong>
                        <p style="margin: 30px 0 25px 0; font-size: 16px; line-height: 30px;">
                            Congratulations! You have received an order from a customer on the MallShark platform.
                            Please find details regarding the order and the customer below:
                        </p>
                        <p>Order Details:</p>
                        <p style="font-size:18px; margin-top: 25px;">Please check your store dashboard order id 1</p>
                    </div>
                </div>

                <div class="delivery-details" style="background: #fff; padding: 20px 22px;">
                    <div>
                        <h1 style="font-size:16px;">Customer detail</h1>
                    </div>
                    <div style="font-size:16px; margin-top: 25px;">
                        <span class="set-min-width" style="display: inline-block;">Name:</span><span>Sajidoon
                            Nadeem</span>
                    </div>
                    <div style="font-size:16px; margin-top: 20px;">
                        <span class="set-min-width" style="display: inline-block;">Address:</span>
                        <span style="display: inline-block; max-width: 400px; vertical-align:text-top;">169-P block  model  town extension lahore, Lahore - Model Town, Punjab</span>
                    </div>
                    <div style="font-size:16px; margin-top: 20px;">
                        <span class="set-min-width"
                            style="display: inline-block;">Email:</span><span>sajidoon@mindblazetech.com</span>
                    </div>
                    <p style="font-size:18px; margin-top: 30px;">We thank you for participating in our community and look forward to more successful orders like this one</p>
                    <p style="margin: 0 0 25px 0;">Sincerely,</p>
                    <p>The MallShark Team</p>
                    
                    <div class="mail-footer" style="max-width: 500px;  margin: 60px auto 0">
                        <center>
                            <div style="margin-bottom: 30px;">
                                <strong style="font-size: 18px;"> <a href="#" style="color: #183B56; text-decoration: none;">FAQs</a> | <a href="#" style="color: #183B56; text-decoration: none;">Contact Us</a></strong>
                            </div>
                            <div class="logo-section">
                                <a href="#" style="text-align: center;display: block;">
                                    <img src="{{asset('assets/images/MallShark-Beta-new.png')}}"
                                        alt="Mallshark logo" title="Mallshark logo">
                                </a>
                            </div>

                            <div style="margin-top:30px; width:100%; display: none;">

                                <a class="icon fb-icon" style="
                                width: 42px;
                                height: 42px;
                                background: #3b5998;
                                display: inline-block;
                                line-height: 42px;
                                border-radius: 50%;                               
                                color: #fff;
                                text-decoration: none;
                                font-size: 22px;
                                margin-right: 1rem;
                                " target="_blank" href="https://www.facebook.com/ShopMallShark"><i
                                        class="fa fa-facebook"></i></a>
                                <a target="_blank"
                                style="
                                width: 42px;
                                height: 42px;
                                background: #00aced;
                                display: inline-block;
                                line-height: 42px;
                                border-radius: 50%;                               
                                color: #fff;
                                text-decoration: none;
                                font-size: 22px;
                                margin-right: 1rem;
                                " 
                                 href="https://twitter.com/ShopMallShark" class="icon t-icon"><i
                                        class="fa fa-twitter"></i></a>
                                        <img src="" alt="">
                                <a target="_blank"
                                style="
                                width: 42px;
                                height: 42px;
                                background: red;
                                display: inline-block;
                                line-height: 42px;
                                border-radius: 50%;                               
                                color: #fff;
                                text-decoration: none;
                                font-size: 22px;
                                margin-right: 1rem;
                                " 
                                
                                href="https://www.instagram.com/shopmallshark/"
                                    class="icon ins-icon">
                                    <i class="fa fa fa-instagram"></i>
                                </a>
                                <a target="_blank"
                                style="
                                width: 42px;
                                height: 42px;
                                border-radius: 50%;
                                text-decoration: none;
                                display: inline-block;
                                vertical-align: bottom;
                                " 
                                href="https://discord.gg/hW9DZapKyH" class="icon dis-icon">
                                    <img src="https://dev.mallshark.net/assets/images/discord.svg" style="width: 100%;"></a>
                            </div>

                            <div style="font-size:12px; margin: 10px 0"> Copyright © 2023 
                                <a href="https://dev.mallshark.net" style="color: #0510fa; text-decoration: none;">Mallshark.net,</a> Inc. All Rights Reserved.<p></p>
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