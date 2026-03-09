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
            <div style="border: 1px solid rgb(179, 179, 179); 
            border-radius: 10px;
            overflow: hidden;">
                <div class="logo-section" style="background: #fff; padding: 26px 22px;">
                    <a href="{{ env('FRONT_END_URL') }}" style="text-align: center;display: block;">
                    <img src={{URL::to('/assets/images/Mall-Shark-Logo.png')}} alt=""
                    title="Mallshark logo" width="130px">
                    </a>
                    <div style="margin-top: 30px;">

                        <strong style="font-size: 17px;">Dear {{$storeName}},</strong>
                        <p style="margin: 30px 0 20px 0; font-size: 16px;">
                            We hope this email finds you well. There has been a dispute filed in Zendesk on Order <a style="font-size:18px;" href="{{ env('BACK_END_URL') }}/orders" > #{{$order_id}}</a>. We realize that customers can be un-reasonable at times and ask for refunds when it is not justified.

                        </p>
                        <P style="margin-bottom: 30PX; font-size: 16px;">However, from our experience if you cater to their requests, it adds to your credibility as a vendor and leads to long term success. People can become repeat customers when they trust marketplaces with the money that they spent.

                        </P>

                        <P style="margin-bottom: 30PX; font-size: 16px;">Please click <a style="font-size:18px;" href="mailto:{{ env('ZENDESK_SUPPORT_EMAIL') }}" >here</a> and provide details. Our support team will check all the facts provided and determine if this transaction qualifies for a refund.

                        </P>

                        <!-- <p style="margin-top: 30px; font-size: 16px;">If you have any further questions or concerns, please feel free to reach out to our dedicated customer support team at <a href="mailto:{{ env('ZENDESK_SUPPORT_EMAIL') }}" style="color: #0510fa; font-weight:600;">here</a></p> -->
                        <p style="font-size: 16px;">Thank you for your understanding and cooperation. </p>
                        
                    </div>

                    <div class="delivery-details" style="background: #fff; padding: 20px 0px;">
                    
                      
                        <div style="margin-top: 30px; font-size:16px;">
                            <p>Sincerely,</p>
                            <p>The MallShark Team</p>
                        </div>

                        <div class="mail-footer" style="max-width: 500px;  margin: 60px auto 0">
                            <center>
                                <div style="margin-bottom: 30px;">
                                    <strong style="font-size: 18px;"> <a href="{{ env('FRONT_END_URL') }}/faqs/general" style="color: #183B56; text-decoration: none;">FAQs</a> | <a href="{{ env('FRONT_END_URL') }}/contact-us" style="color: #183B56; text-decoration: none;">Contact Us</a> | <a href="{{ env('FRONT_END_URL') }}/return-policy"
                                            style="color: #183B56; text-decoration: none;">Return Policy</a></strong>
                                </div>
                                <div class="logo-section">
                                    <a href="#" style="text-align: center;display: block;">
                                        <img src="https://mallshark.com/assets/images/MallShark-Beta-new.png" alt="Mallshark logo" title="Mallshark logo">
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
                                    " target="_blank" href="https://www.facebook.com/ShopMallShark"><i class="fa fa-facebook"></i></a>
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
                                    " href="https://twitter.com/ShopMallShark" class="icon t-icon"><i class="fa fa-twitter"></i></a>
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
                                        <img src="https://mallshark.com/assets/images/discord.svg" style="width: 100%;"></a>
                                </div>

                                <div style="font-size:12px; margin: 10px 0"> Copyright © {{date('Y')}}
                                    <a href="https://mallshark.com" style="color: #0510fa; text-decoration: none;">Mallshark.com,</a> Inc. All
                                    Rights Reserved.<p></p>
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