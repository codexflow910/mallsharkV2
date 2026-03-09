<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
</head>

<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 30px;">

    <div style="max-width: 700px; margin: 0 auto; background: #fff; padding: 20px; border-radius: 6px;">

        {{-- Top Logo --}}
        <a href="{{ env('FRONT_END_URL') }}" target="_blank" style="text-align: center; display: block; margin-bottom: 20px;">
            <img src="{{ URL::to('/assets/images/Mall-Shark-Logo.png') }}"
                alt="MallShark logo"
                title="Mallshark logo"
                width="130px">
        </a>

        <p style="font-size: 16px; color: #333;"><strong>Dear {{ $store->user->name }}</strong>,</p>
        <p style="margin: 8px 0; font-size: 16px; color:#555;">
            Store: <strong>{{ $store->store_name }}</strong>
        </p>
        <p style="font-size: 16px; color: #333;">We’ve detected that some of your products are running low on stock.
            To avoid missing out on potential sales, please consider restocking them promptly.</p>

        {{-- Products Table --}}
        <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse; margin-top: 20px;">
            <thead>
                <tr style="background-color: #073ba5; color: #fff;">
                    <th style="padding: 12px; text-align: left;">Product </th>
                    <th style="padding: 12px; text-align: left;">Image </th>
                    <th style="padding: 12px; text-align: left;">Current Stock</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($products as $item)
                @php
                $variationParts = [];
                if (isset($item['variation'])) {
                if (!empty($item['variation']['attribute1'])) {
                $variationParts[] = $item['variation']['attribute1'] . ': ' . $item['variation']['option1'];
                }
                if (!empty($item['variation']['attribute2'])) {
                $variationParts[] = $item['variation']['attribute2'] . ': ' . $item['variation']['option2'];
                }
                if (!empty($item['variation']['attribute3'])) {
                $variationParts[] = $item['variation']['attribute3'] . ': ' . $item['variation']['option3'];
                }
                }
                @endphp
                <tr style="border-bottom: 1px solid #ddd;">
                    <td style="padding: 10px; font-size: 16px;">
                        <a href="{{ env('FRONT_END_URL') }}/product/{{ $item['slug'] }}" style="color: #073ba5; text-decoration: none;">
                            {{ $item['title'] }}
                        </a>
                        @if(count($variationParts))
                        <br>
                        <small style="color: #555; display: block;">
                            {!! implode('<br>', $variationParts) !!}
                        </small>
                        @endif
                    </td>
                    <td style="text-align:left; padding: 10px;">
                        <img src="{{ $item['image'] }}" 
                            alt="{{ $item['title'] }}" 
                            style="width: 80px; height: 80px; object-fit: contain; border: 1px solid #ddd; border-radius: 6px;">
                    </td>
                    <td style="padding: 10px; font-size: 16px; color: #c00; text-align:center">
                        {{ $item['current_stock'] }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Thank you note --}}
        <div style="margin-top: 30px; font-size: 16px;">
            <p>Thank you,</p>
            <p>The MallShark Team</p>
        </div>

        {{-- Footer --}}
        <div class="mail-footer" style="max-width: 500px; margin: 60px auto 0;">
            <center>
                <div style="margin-bottom: 30px;">
                    <strong style="font-size: 18px;">
                        <a href="{{ env('FRONT_END_URL') }}/faqs/general" style="color: #183B56; text-decoration: none;">FAQs</a> |
                        <a href="{{ env('FRONT_END_URL') }}/contact-us" style="color: #183B56; text-decoration: none;">Contact Us</a>
                    </strong>
                </div>
                
                <div style="font-size: 12px; margin: 10px 0; color: #666;">
                    Copyright © {{ date('Y') }} , Inc. All Rights Reserved.
                </div>
            </center>
        </div>

    </div>
</body>

</html>