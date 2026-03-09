<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <title>User Signup</title>
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

            <div style="border: 1px solid rgb(179, 179, 179); 
            border-radius: 10px;
            overflow: hidden;">
                <div class="logo-section" style="background: #fff; padding: 26px 22px;">
                    <a href="{{ env('FRONT_END_URL') }}" style="text-align: center;display: block;">
                        <img src="https://mallshark.com/assets/images/MallShark-Beta-new.png" alt="Mallshark logo"
                            title="Mallshark logo" width="130px">
                    </a>

                    <div style="margin-top: 30px;">
                        <strong style="font-size: 17px;">Dear {{$store_name}},</strong>
                    </div>

                    <div style="margin-top: 20px;">
                        <p>We regret to inform you that your product has been removed from our platform. We appreciate
                            your hard work and dedication to providing high-quality products on our site.</p>
                    </div>

                    @php
                    $imageUrl = getAttachmentUrlSmallById($product->feature_image, 'small');
                    @endphp

                    <table style="border-spacing: 0; width: 100%; border-collapse: collapse; margin-top:10px;">
                        <thead>
                            <tr style="background-color: #f5f5f5; border: 1px solid #e0e0e0;">
                                <th style="padding: 10px; text-align: left; font-size: 16px; border-bottom: 2px solid #e0e0e0;">Image</th>
                                <th style="padding: 10px; text-align: left; font-size: 16px; border-bottom: 2px solid #e0e0e0;">Product Name</th>
                                <th style="padding: 10px; text-align: center; font-size: 16px; border-bottom: 2px solid #e0e0e0;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr style="border-bottom: 1px solid #e0e0e0;">
                                <td style="text-align:left; padding: 10px;">
                                    <img src="{{ $imageUrl }}"
                                        alt="{{ $product->title }}"
                                        style="width: 80px; height: 80px; object-fit: contain; border: 1px solid #ddd; border-radius: 6px;">
                                </td>
                                <td style="padding: 10px; text-align: left; font-size: 16px;">
                                    <a href="{{ env('FRONT_END_URL') }}/product/{{ $product->slug }}" style="color: #073ba5; text-decoration: none;">
                                        {{ $product->title }}
                                    </a>
                                </td>

                            </tr>
                        </tbody>
                    </table>
                    <div style="margin-top: 20px;">
                        <p>We understand this may be disappointing news and thank you for your understanding in advance.
                            If you would like more information about why your product was removed from the site, please
                            contact us at the email address provided below.</p>
                    </div>
                    <div style="margin-top: 20px; font-size: 16px;">
                        <p>Thank you for being a part of the <strong>MallShark</strong> community!</p>
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