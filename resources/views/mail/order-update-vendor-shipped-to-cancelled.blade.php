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
                        Update on Your MallShark Order - {{$orderId}}
                    </div>

                    <div style="margin-top: 30px;">
                        <strong style="font-size: 17px;">Dear {{$userData->name}},</strong>
                        <p style="margin: 30px 0 20px 0; font-size: 16px;">
                            We are reaching out to inform you of a change in the status of your recent order 
                            <a style="font-size:18px; color: #073ba5;" href="{{ env('FRONT_END_URL') }}/orders">
                                #{{ $orderId }}
                            </a>. 
                            Due to an unexpected issue, the vendor has canceled your order. 
                            We apologize for any inconvenience this may cause.
                        </p>

                        @if(strtolower($paymentMethod) === 'crypto')
                            <p style="margin-bottom: 30px; font-size: 16px;">
                                Since your payment was made using <strong>cryptocurrency</strong>, your refund is currently 
                                <strong>in progress</strong>. We’ll notify you once the refund has been completed.
                            </p>
                        @endif

                    </div>
                    <div class="cancel-order-detail">
                        <div>
                            <strong>Order Summary:</strong>
                        </div>
                        <table style="border-spacing: 0 10px; width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="border-bottom: 1px solid #e0e0e0; background-color: #f8f8f8;">
                                    <th style="text-align: left; padding: 10px; font-size: 16px;">Product Name</th>
                                    <th style="text-align: center; padding: 10px; font-size: 16px;">Unit Price</th>
                                    <th style="text-align: center; padding: 10px; font-size: 16px;">Quantity</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($orderItems as $order)
                                @php 
                                    $url = env('base_url');
                                @endphp
                        
                                <tr style="border-bottom: 1px solid #e0e0e0;">
                                   
                                    <td style="padding: 10px; text-align: left; font-size: 16px;">
                                        {{ $order['title'] }}
                                    </td>
                                   <td style="padding: 10px; text-align: center; font-size: 16px;">
                                    @if(array_key_exists('sold_price', $order) && !empty($order['sold_price']) && $order['sold_price'] != 0)
                                        ${{ $order['sold_price'] }}
                                    @else
                                        ${{ $order['price'] ?? 0 }}
                                    @endif
                                </td>

                                    <td style="padding: 10px; text-align: center; font-size: 16px;">
                                        {{ $order['quantity'] }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>                        
                    </div> 
                    @php 
                        $apartmentNo = $shippingAddress['apartment_no'] ?? '';
                        $shipping_street  = $shippingAddress['street'];
                        $shipping_city    = $shippingAddress['city'];
                        $shipping_country = $shippingAddress['country'];
                        $shipping_post_code = $shippingAddress['post_code'];
                        $shipping_state    = $shippingAddress['state'];

                        $order_state = isset($shipping_state) ? $shipping_state : '';
                        $state_name = DB::table('states')->where('state_id', '=', $order_state)->select('state_name')->first();
                        $state_name = isset($state_name->state_name) ? $state_name->state_name : '';
                    @endphp
                    <div class="delivery-details" style="background: #fff; padding: 20px 0px;">
                        <div style="margin-top: 30px;" class="customer-detail">
                            <strong>Delivery Information:</strong>
                            <div style="margin-top: 10px;">
                                <div style="font-size:16px;">
                                    <span class="set-min-width" style="display: inline-block;">Name:</span><span>{{$shippingAddress['full_name']}}</span>
                                </div>
                                <div style="font-size:16px; margin-top: 5px;">
                                    <span class="set-min-width" style="display: inline-block;">Address:</span>
                                    <span style="display: inline-block; max-width: 430px; vertical-align:text-top;"> {{$apartmentNo}} {{$shipping_street}} {{$shipping_city}}, {{$state_name}} {{$shipping_post_code}}, US</span>
                                </div>
                                <div style="font-size:16px; margin-top: 5px;">
                                    <span class="set-min-width"
                                        style="display: inline-block;">Email:</span><span>{{$userData->email}}</span>
                                </div>
                            </div>
                        </div>
                        <div style="margin-top: 30px;">
                            <p style="font-size: 16px;">We are processing a full refund. If you have any questions or need further assistance, please contact us at <a href="mailto:{{ env('CARE_EMAIL') }}" style="color: #073ba5;">{{ env('CARE_EMAIL') }}</a></p>
                        </div>
                        <div style="margin-top: 30px;">
                            <p style="font-size: 16px;">Thank you for your understanding.</p>
                        </div>
                        <div style="margin-top: 30px; font-size:16px;">
                            <p>Best regards,</p>
                            <p>The MallShark Team</p>
                        </div>

                        <div class="mail-footer" style="max-width: 500px;  margin: 60px auto 0">
                            <center>
                                <div style="margin-bottom: 30px;">
                                    <strong style="font-size: 18px;"> <a href="{{ env('FRONT_END_URL') }}/faqs/general"
                                            style="color: #183B56; text-decoration: none;">FAQs</a> | <a href="{{ env('FRONT_END_URL') }}/contact-us"
                                            style="color: #183B56; text-decoration: none;">Contact Us</a> | <a href="{{ env('FRONT_END_URL') }}/return-policy"
                                            style="color: #183B56; text-decoration: none;">Return Policy</a></strong>
                                </div>
                                <div class="logo-section">
                                    <a href="#" style="text-align: center;display: block;">
                                        <img src="https://mallshark.com/assets/images/MallShark-Beta-new.png"
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
                                        style="color: #073ba5; text-decoration: none;">Mallshark.com,</a> Inc. All
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