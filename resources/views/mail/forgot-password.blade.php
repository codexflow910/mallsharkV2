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

        p{
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
        
            <div style="border: 1px solid rgb(179, 179, 179); 
            border-radius: 10px;
            overflow: hidden;">
                <div class="logo-section" style="background: #fff; padding: 26px 22px;">
                <a href="{{ env('FRONT_END_URL') }}" style="text-align: center;display: block;">
                        <img src={{URL::to('/assets/images/Mall-Shark-Logo.png')}} alt="Mallshark logo"
                            title="Mallshark logo" width="130px">
                    </a>

                    <div class="subject">
                        Reset Your Password
                    </div>
                   
                    {{-- <div style="margin-top: 30px;">
                        <strong style="font-size: 17px;">Dear {{$name}},</strong>
                    </div> --}}

                    <div style="margin-top: 20px;">
                        <p>We received your request for a password reset for your MallShark account. To complete the process: <a href="{{$forget_url}}" style="color: #073ba5; text-decoration: none; font-weight: 600;">Please click on the following link</a> .</p>
                    </div>

                    <div style="margin-top: 20px;">
                        <p>After clicking the link, you'll be asked to enter a new password for your account. For security purposes, we suggest using a combination of uppercase and lowercase letters, numbers, and special characters to create a strong password.</p>
                    </div>
                    <div style="margin-top: 20px; font-size: 16px;">
                        <p>If you have any questions or need further assistance resetting your password, please don’t hesitate to contact us and let us know how we can help!</p>
                    </div>

                    <div style="margin-top: 20px;">
                        <p>Thank you for being a loyal <strong>MallShark</strong> customer!</p>
                    </div>

                    <div>
                        <p style=" font-size: 16px; margin-top: 15px;">Sincerely,</p>
                        <p style=" font-size: 16px; ">The MallShark Team</p>
                    </div>


                    <div class="mail-footer" style="max-width: 500px;  margin: 60px auto 0">
                        <center>
                        <div style="margin-bottom: 30px;">
                                <strong style="font-size: 18px;"> <a href="{{ env('FRONT_END_URL') }}/faqs/general"
                                        style="color: #183B56; text-decoration: none;">FAQs</a> | <a href="{{ env('FRONT_END_URL') }}/contact-us"
                                        style="color: #183B56; text-decoration: none;">Contact Us</a></strong>
                            </div>
                            <div class="logo-section">
                                <a href="#" style="text-align: center;display: block;">
                                    <img src="https://mallshark.com/assets/images/MallShark-Beta-new.png" alt="Mallshark logo"
                                        title="Mallshark logo">
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
                                <a target="_blank" style="
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
                                " href="https://twitter.com/ShopMallShark" class="icon t-icon"><i
                                        class="fa fa-twitter"></i></a>
                                <img src="" alt="">
                                <a target="_blank" style="
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
                                " href="https://www.instagram.com/shopmallshark/" class="icon ins-icon">
                                    <i class="fa fa fa-instagram"></i>
                                </a>
                                <a target="_blank" style="
                                width: 42px;
                                height: 42px;
                                border-radius: 50%;
                                text-decoration: none;
                                display: inline-block;
                                vertical-align: bottom;
                                " href="https://discord.gg/hW9DZapKyH" class="icon dis-icon">
                                    <img src="https://mallshark.com/assets/images/discord.svg"
                                        style="width: 100%;"></a>
                            </div>

                            <div style="font-size:12px; margin: 10px 0"> Copyright © {{date('Y')}}
                                <a href="https://mallshark.com"
                                    style="color: #073ba5; text-decoration: none;">Mallshark.com,</a> Inc. All Rights
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