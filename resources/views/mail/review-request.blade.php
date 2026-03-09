<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Write a review</title>
</head>
<body style="font-family: Arial, sans-serif; background:#f7f7f7; padding:20px;">
    <div style="max-width:600px;margin:0 auto;background:#fff;padding:20px;border-radius:6px;">
        <h4 style="margin-top:0">Thanks for your purchase!</h4>

        <p>Hi {{ $buyer->name ?? 'Customer' }},</p>

        <p>We'd love to hear your feedback. Please take a moment to write a review for the products below.</p>

        @foreach ($products as $item)
            <div style="display:flex;gap:12px;align-items:center;margin:16px 0;padding:12px;border:1px solid #eee;border-radius:6px;">
                
                @if(!empty($item['image']))
                
                
                <img 
                    src="{{ asset('uploads/orders/' . str_replace(' ', '%20', $item['image'])) }}"
                    alt="{{ $item['product']->title }}"
                    style="width:90px;height:90px;object-fit:contain;border-radius:4px;"
                />
                @endif

                <div style="margin-left:12px;">
                    <strong>{{ $item['product']->title }}</strong><br />
                    <span>Price: {{ number_format($item['product']->price, 2) }}</span>
                </div>
            </div>

            <p>
                <a 
                    href="{{ $item['reviewUrl'] }}" 
                    style="background:#007bff;color:#fff;padding:10px 14px;border-radius:6px;text-decoration:none;display:inline-block;"
                >
                    Rate & Review
                </a>
            </p>

            <hr />
        @endforeach

        <p style="color:#777;margin-top:18px;">
            Thanks,<br />
            <strong>Team MallShark</strong>
        </p>

        <small style="color:#999">
            If you think this is a mistake or you do not want review emails, please
            <a href="{{ url('/') }}">contact us</a>.
        </small>
    </div>
</body>
</html>
