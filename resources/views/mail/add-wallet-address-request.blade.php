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
        .cta-button{display:inline-block;background:#0510fa;color:#fff;padding:12px 22px;border-radius:8px;text-decoration:none;font-weight:600}
    </style>
</head>

<body style="background:#F9FBFE;">
    <div class="mail-container" style="padding:1.5rem 0;">
       
        <div class="mail-body">

            <div style="border: 1px solid rgb(179, 179, 179); 
            border-radius: 10px;
            overflow: hidden;">
                <div class="logo-section" style="background: #fff; padding: 26px 22px;">
                <a href="{{ env('FRONT_END_URL') }}" style="text-align: center;display: block;">
                        <img src="{{ URL::to('/assets/images/Mall-Shark-Logo.png') }}" alt="Mallshark"
                            title="Mallshark logo" width="130px">
                    </a>
                </div>
                    <div style="margin-top:20px; padding: 0 22px 40px; background: #fff;">
                        <h6>Dear {{ $userName }}</h6>
                        <p style="margin-top:10px;">Set Up Your Wallet to Receive Refunds</p>

                        <p style="color:#333;">
                            To process your crypto refund, please add your wallet address. This ensures your refund is securely transferred to your account.
                        </p>

                        <div style="margin:16px 0; text-align: center;">
                            <a href="<?php echo $activate_url; ?>" target="_blank" class="cta-button">Add Wallet for Refund</a>
                        </div>

                        <p style="font-size:13px;color:#6b7280;margin-top:6px;">
                            If the button doesn’t work, copy and paste this link into your browser:<br>
                            <a href="<?php echo $activate_url; ?>" target="_blank" style="color:#0510fa;word-break:break-all; text-align: center;"><?php echo $activate_url; ?></a>
                        </p>

                        <p style="margin-top:16px;color:#333;">
                            Once you’ve added your wallet, we’ll send your refund automatically to your crypto address.
                        </p>
                    </div>

                    <div style="padding: 0 22px 40px; background: #fff;">
                        <p style=" font-size: 16px; margin-top: 15px;">Sincerely,</p>
                        <p style=" font-size: 16px; ">The MallShark Team</p>
                    </div>

                    <div class="mail-footer" style="max-width: 500px;  margin: 60px auto 0">
                        <center>
                            <div style="margin-bottom: 10px;">
                                <strong style="font-size: 18px;"> <a href="{{ env('FRONT_END_URL') }}/faqs/general"
                                    style="color: #183B56; text-decoration: none;">FAQs</a> | <a href="{{ env('FRONT_END_URL') }}/contact-us"
                                    style="color: #183B56; text-decoration: none;">Contact Us</a></strong>
                            </div>

                            <div style="margin-top:30px; width:100%; display: none;">

                                <a class="icon fb-icon"
                                    style="
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
                                "
                                    target="_blank" href="https://www.facebook.com/ShopMallShark"><i
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
                                    href="https://www.instagram.com/shopmallshark/" class="icon ins-icon">
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
                                    <img src="https://mallshark.com/assets/images/discord.svg"
                                        style="width: 100%;"></a>
                            </div>

                            <div style="font-size:12px; margin: 10px 0"> Copyright © {{date('Y')}}
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
