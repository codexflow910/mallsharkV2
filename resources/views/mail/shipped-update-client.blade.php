  <!DOCTYPE html>
  <html lang="en">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }

        img {
            border: 0;
            outline: none;
            text-decoration: none;
            display: block;
            max-width: 100%;
        }

        .vertical-item {
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            margin-bottom: 20px;
            background: #ffffff;
            padding: 15px;
        }

        .vertical-item strong {
            display: inline-block;
            width: 150px;
            font-weight: 600;
            color: #073ba5;
        }

        .vertical-item div {
            font-size: 15px;
            margin-bottom: 6px;
            line-height: 1.5;
        }

        .vertical-item a {
            color: #3498db;
            text-decoration: underline;
            font-weight: bold;
        }

        .mail-body {
            font-family: Arial, sans-serif;
            color: #333;
            background-color: #F9FBFE;
            padding: 1.5rem;
        }
    </style>
</head>

<body style="background:#F9FBFE;">
    <div class="mail-container">
        <div class="mail-body">
            <div style="border: 1px solid #b3b3b3; border-radius: 10px; overflow: hidden;">
                <div class="logo-section" style="background: #fff; padding: 26px 22px;">
                    <a href="{{ env('FRONT_END_URL') }}" target="_blank" style="text-align: center;display: block; display: inline-block;">
                        <img src="{{ URL::to('/assets/images/Mall-Shark-Logo.png') }}" alt="Mallshark" title="Mallshark" width="130px">
                    </a>

                    <div style="margin-top: 30px;">
                        <strong style="font-size: 17px;">Dear {{ $shippingAddress['full_name'] }},</strong>
                        <p style="margin: 30px 0 20px 0; font-size: 16px;">
                            We’re excited to let you know that your MallShark order is on its way! Here are the details for your order and delivery:
                        </p>
                    </div>
                    <div class="cancel-order-detail">
                        <div>
                            <strong>Order Summary:</strong>
                        </div>
                        @foreach($orderItems as $order)
                            @php
                                $customizations = $order['customizations'] ?? null;
                                if (is_string($customizations)) {
                                    $customizations = json_decode($customizations, true);
                                }
                                $customizations = is_array($customizations) ? $customizations : [];

                                $customType = $customizations['type'] ?? '';
                                $instructions = $customizations['instructions'] ?? '';
                                $customStyles = $customizations['styles'] ?? [];
                                $customText = $customizations['custom_text'] ?? '';
                                $customPrice = $customizations['price'] ?? 0;
                                $customQuantity = $customizations['product_quantity'] ?? 1;
                                $customImageUrl = $customizations['image_url'] ?? '';

                                if (is_array($customImageUrl)) {
                                    $customImageUrl = $customImageUrl[0] ?? '';
                                }
                                $customImageUrl = is_string($customImageUrl) ? $customImageUrl : '';

                                $customImageSrc = '';
                                if (!empty($customImageUrl) && $customType == 'image') {
                                    $customImageSrc = getOrderImage($customImageUrl);
                                }

                                $customPrice = is_numeric($customPrice) ? (float) $customPrice : 0;
                                $customQuantity = is_numeric($customQuantity) ? (int) $customQuantity : 1;
                            @endphp
                            <div class="vertical-item">
                                <div>
                                    <strong>Product Name:</strong><br>
                                    <a href="{{ env('FRONT_END_URL') }}/product/{{ $order['slug'] }}" style="color: #000000ff; text-decoration: none; font-weight: normal;">
                                        {{ $order['title'] }}
                                    </a>
                                    @if(isset($order['variation_info']))
                                        <span style="font-weight: normal;"> <small>({{ $order['variation_info'] }})</small></span>
                                    @endif
                                </div>
                                <div><strong>Unit Price:</strong>
                                    @if(array_key_exists('sold_price', $order) && !empty($order['sold_price']) && $order['sold_price'] != 0)
                                    ${{ $order['sold_price'] }}
                                    @else
                                    ${{ $order['price'] ?? 0 }}
                                    @endif</div>
                                <div><strong>Quantity:</strong> {{ $order['quantity'] }}</div>
                                <div><strong>Shipping Amount:</strong> @if(array_key_exists('shipping_amount', $order) && !empty($order['shipping_amount']) && $order['shipping_amount'] != 0)
                                    ${{ $order['shipping_amount'] }}
                                    @else
                                    0
                                    @endif</div>
                                <div>
                                    <strong>Track Product:</strong><br>
                                    <a href="{{ $order['tracking_id'] }}" target="_blank" style="color: #073ba5; text-decoration: none; font-weight: normal;">
                                        Click & Track
                                    </a>
                                </div>
                                @if (!empty($customStyles) || !empty($customText) || !empty($customPrice) || !empty($customImageSrc))
                                    <div style="margin-top:10px;">
                                        <strong>Customization:</strong>
                                    </div>

                                    @if (!empty($customStyles) && is_array($customStyles))
                                        <div>
                                            <strong>Personalization:</strong>
                                            {{ collect($customStyles)->map(fn($v, $k) => $k . ': ' . $v)->implode(', ') }}
                                        </div>
                                    @endif

                                    @if (!empty($customText))
                                        <div><strong>Custom Text:</strong> {{ is_array($customText) ? implode(', ', $customText) : $customText }}</div>
                                    @endif
                                    @if(!empty($instructions))
                                        Instructions: {{ $instructions }}<br>
                                    @endif
                                    @if ($customPrice > 0)
                                        <div>
                                            <strong>Personalization Price:</strong>
                                            ${{ number_format($customPrice, 2) }} x {{ $customQuantity }} = ${{ number_format($customPrice * $customQuantity, 2) }}
                                        </div>
                                    @endif

                                    @if (!empty($customImageSrc))
                                        <div>
                                            <strong>Customization Image:</strong>
                                            <div style="margin-top:8px;">
                                                <img src="{{ $customImageSrc }}" alt="Customization Image" style="max-width:140px; height:auto;" />
                                            </div>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        @endforeach
                    </div>
                    @php
                        $apartmentNo = $shippingAddress['apartment_no'] ?? '';
                        $shipping_street = $shippingAddress['street'];
                        $shipping_city = $shippingAddress['city'];
                        $shipping_country = $shippingAddress['country'];
                        $shipping_post_code = $shippingAddress['post_code'];
                        $shipping_state = $shippingAddress['state'];

                        $order_state = isset($shipping_state) ? $shipping_state : '';
                        $state_name = DB::table('states')->where('state_id', '=', $order_state)->select('state_name')->first();
                        $state_name = isset($state_name->state_name) ? $state_name->state_name : '';
                    @endphp
                    <div class="delivery-details" style="background: #fff; padding: 20px 0px;">
                        <div style="margin-top: 30px;" class="customer-detail">
                            <strong>Delivery Details:</strong>
                            <div style="margin-top: 10px;">
                                <div style="font-size:16px;">
                                    <span class="set-min-width" style="display: inline-block;">Name:</span><span>{{$shippingAddress['full_name']}}</span>
                                </div>
                                <div style="font-size:16px; margin-top: 5px;">
                                    <span class="set-min-width" style="display: inline-block;">Address:</span>
                                    <span style="display: inline-block; max-width: 430px; vertical-align:text-top;">{{$apartmentNo}} {{$shipping_street}} {{$shipping_city}}, {{$state_name}} {{$shipping_post_code}}, US</span>
                                </div>
                            </div>
                        </div>
                        <div style="margin-top: 30px;">
                            <p style="font-size: 16px;">We hope you enjoy shopping with MallShark! If you have any questions about your order, don’t hesitate to reach out to us at <a href="mailto:{{ env('CARE_EMAIL') }}" style="color: #073ba5;">{{ env('CARE_EMAIL') }}</a>.</p>
                        </div>
                        <div style="margin-top: 30px; font-size:16px;">
                            <p>Thank you,</p>
                            <p>Team MallShark</p>
                        </div>

                        <div class="mail-footer" style="max-width: 500px; margin: 60px auto 0;">
                            <center>
                                <div style="margin-bottom: 30px;">
                                    <strong style="font-size: 18px;">
                                        <a href="{{ env('FRONT_END_URL') }}/faqs/general" style="color: #183B56; text-decoration: none;">FAQs</a> |
                                        <a href="{{ env('FRONT_END_URL') }}/contact-us" style="color: #183B56; text-decoration: none;">Contact Us</a> |
                                        <a href="{{ env('FRONT_END_URL') }}/return-policy" style="color: #183B56; text-decoration: none;">Return Policy</a>
                                    </strong>
                                </div>

                                <div style="font-size:12px; margin: 10px 0;">
                                    Copyright © {{date('Y')}}
                                    <a href="https://mallshark.com" style="color: #073ba5; text-decoration: none;">Mallshark.com</a>, Inc. All Rights Reserved.
                                </div>
                            </center>
                        </div>
                    </div>
                </div>
                <img src="https://mallshark.com/assets/images/MallShark-Beta-new.png" alt="Mallshark logo" style="width:150px;">
                <div style="font-size:12px; margin: 10px 0;">
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
