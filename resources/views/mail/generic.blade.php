<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $subject ?? 'Notification' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="background-color: #f8f9fa; font-family: Arial, sans-serif; padding: 20px;">

    <div style="max-width: 600px; margin: auto; background: #ffffff; padding: 20px;">
        <h6 style="color: #000;">Dear {{ $user['name'] ?? 'Customer' }},</h6>
        <h5 style="color: #000;">{{ $subject ?? 'Notification' }}</h5>
        <p style="color: #000;">{{ $message ?? 'Hello, this is a notification email.' }}</p>

        @if (!empty($url))
            <p>
                <a href="{{ $url }}" style="background-color: #007bff; color: #fff; padding: 9px 13px; border-radius: 5px; text-decoration: none; display: inline-block;">
                    View Details
                </a>
            </p>
        @endif

        <p style="margin-top: 20px; color: #000;">Best regards,<br><strong>Team Mallshark</strong></p>

        <div style="text-align: left; margin-top: 20px;">
            <a href="{{ env('FRONT_END_URL') }}">
                <img src="{{ URL::to('/assets/images/Mall-Shark-Logo.png') }}" alt="MallShark Logo" title="MallShark Logo" width="130px">
            </a>
        </div>
    </div>

</body>
</html>
