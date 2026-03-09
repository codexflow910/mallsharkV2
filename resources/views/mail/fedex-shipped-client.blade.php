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
                        <img src={{URL::to('/assets/images/Mall-Shark-Logo.png')}} alt=""
                            title="Mallshark logo" width="130px">
                    </a>

                    <div class="subject">
                        Your order has been shipped - {{$orderId}}
                    </div>

                    <div style="margin-top: 30px;">
                        <strong style="font-size: 17px;">Dear {{$userData['name']}},</strong>
                        <p style="margin: 30px 0 20px 0; font-size: 16px;">
                            We’re excited to let you know that your MallShark order is on its way! Here are the details for your order and delivery:
                        </p>
                        <p>
                            Click here to <a href="{{ $trackingUrl }}" target="_blank" style="color: #3498db; text-decoration: underline; font-weight: bold;">track your order</a>.
                        </p>
                    </div>
                    <div class="cancel-order-detail">
                        <div>
                            <strong>Order Summary:</strong>
                        </div>
                        <table style="border-spacing: 0; width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="background-color: #f5f5f5; border: 1px solid #e0e0e0;">
                                    <th style="padding: 10px; text-align: left; font-size: 16px; border-bottom: 2px solid #e0e0e0;">Product Name</th>
                                    <th style="padding: 10px; text-align: center; font-size: 16px; border-bottom: 2px solid #e0e0e0;">Unit Price</th>
                                    <th style="padding: 10px; text-align: center; font-size: 16px; border-bottom: 2px solid #e0e0e0;">Quantity</th>
                                    <th style="padding: 10px; text-align: center; font-size: 16px; border-bottom: 2px solid #e0e0e0;">Shipping Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                           
                                @foreach($orderItems as $order)
                                @php 
                                    $variations = array();
                                        if (!empty($order['variation_id'])) {
                                            $variation = getvariableByProduct_id($order['variation_id']);
                                            $name = '';
                                                if(isset($variation->option1) && !is_null($variation->option1)){
                                                    $variation1 = ['attribute' => $variation->attribute1,'option'=>$variation->option1,'unit' => isset($variation->unit1) ? $variation->unit1 : ''];
                                                    $variations[]=$variation1;  
                                                }if(isset($variation->option2) && !is_null($variation->option2)){
                                                    $variation2 = ['attribute' => $variation->attribute2,'option'=>$variation->option2,'unit' => isset($variation->unit2) ? $variation->unit2 : ''];
                                                    $variations[]=$variation2; 
                                                }if(isset($variation->option3) && !is_null($variation->option3)){
                                                    $variation3 = ['attribute' => $variation->attribute3,'option'=>$variation->option3,'unit' => isset($variation->unit3) ? $variation->unit3 : ''];
                                                    $variations[]=$variation3; 
                                                }
                                                
                                        }
                                        $product_variation_image = !empty($order['variable_image']) ? $order['variable_image'] : $order['feature_image'];
                                        $product_type =  $order['product_type'];
                                        $image =  $order['feature_image'];
                                        $image_url = '';
                        
                                        if(!empty($image)){
                                            if($product_type == 'simple' ){
                                                $image_url = getAttachmentUrlByIdEmailTemplete($image, 'medium');
                                            } elseif($product_type == 'virtual'){
                                                $image_url = getAttachmentUrlByIdEmailTemplete((int) $image, 'medium');
                                            } else { 
                                                $image_url = getAttachmentUrlByIdEmailTemplete((int) $product_variation_image, 'medium');
                                            }
                                            $image_url = str_replace([' ', '(', ')'], ['%20', '%28', '%29'], $image_url);
                                        } 
                                        $url = env('base_url');
                                    @endphp

                        
                                    <tr style="border-bottom: 1px solid #e0e0e0;">
                                        <td style="padding: 10px; text-align: left; font-size: 16px;">
                                            <a href="{{ env('FRONT_END_URL') }}/product/{{ $order['slug'] }}" style="color: #073ba5; text-decoration: none;">
                                                {{ $order['title'] }}

                                            </a>
                                            <br>
                                                @if(isset($order['variation_id']))
                                                    @foreach ($variations as $variation)
                                                    <small>{{$variation['attribute']}} : {{$variation['option']}}</small>
                                                    <br>
                                                    @endforeach    
                                                @endif
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
                                        <td style="padding: 10px; text-align: center; font-size: 16px;">
                                        @if(array_key_exists('shipping_amount', $order) && !empty($order['shipping_amount']) && $order['shipping_amount'] != 0)
                                                ${{ $order['shipping_amount'] }}
                                            @else
                                               0
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        
                    </div>
                        @php
                            $shippingStreet     = $shippingAddress['street'] ?? '';
                            $shippingCity       = $shippingAddress['city'] ?? '';
                            $shippingPostCode  = $shippingAddress['post_code'] ?? '';
                            $stateName          = $shippingAddress['state']['state_name'] ?? '';
                        @endphp

                    <div class="delivery-details" style="background: #fff; padding: 20px 0px;">
                        <div style="margin-top: 30px;" class="customer-detail">
                            <strong>Delivery Details:</strong>
                            <div style="margin-top: 10px;">
                                <div style="font-size:16px;">
                                    <span class="set-min-width" style="display: inline-block;">Name:</span><span>{{$userData['name']}}</span>
                                </div>
                                <div style="font-size:16px; margin-top: 5px;">
                                    <span class="set-min-width" style="display: inline-block;">Address:</span>
                                    <span style="display: inline-block; max-width: 430px; vertical-align:text-top;">{{$shippingStreet}} {{$shippingCity}}, {{$stateName}} {{$shippingPostCode}}, US</span>
                                </div>
                                <div style="font-size:16px; margin-top: 5px;">
                                    <span class="set-min-width"
                                        style="display: inline-block;">Email:</span><span>{{$userData['email']}}</span>
                                </div>
                            </div>
                        </div>
                        <div style="margin-top: 30px;">
                            <p style="font-size: 16px;">We hope you enjoy shopping with MallShark! If you have any questions about your order, don’t hesitate to reach out to us at <a href="mailto:{{ env('CARE_EMAIL') }}" style="color: #073ba5;">{{ env('CARE_EMAIL') }}</a>.</p>
                        </div>
                        <div style="margin-top: 30px; font-size:16px;">
                            <p>Thank you,</p>
                            <p>The MallShark Team</p>
                        </div>

                        <div class="mail-footer" style="max-width: 500px;  margin: 60px auto 0">
                            <center>
                                <div style="margin-bottom: 30px;">
                                    <strong style="font-size: 18px;"> <a href="{{ env('FRONT_END_URL') }}/faqs/general"
                                            style="color: #183B56; text-decoration: none;">FAQs</a> | <a href="{{ env('FRONT_END_URL') }}/contact-us"
                                            style="color: #183B56; text-decoration: none;">Contact Us</a> | <a href="{{ env('FRONT_END_URL') }}/return-policy"
                                            style="color: #183B56; text-decoration: none;">Return Policy</a> </strong>
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