<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Store Setup Confirmation</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333; line-height: 1.6;">
    <h2 style="color: #2d3748;">Dear {{ $vendorName }},</h2>

    <p>Congratulations! 🎉 Your store <strong>{{ $storeName }}</strong> has been successfully set up on our platform.</p>

    <h3 style="color: #2b6cb0;">🔑 Login Details:</h3>
    <ul>
        <li><strong>Email:</strong> {{ $vendorEmail }}</li>
        <li><strong>Store Dashboard:</strong> 
            <a href="{{ $dashboardUrl }}" target="_blank">{{ $dashboardUrl }}</a>
        </li>
    </ul>

    <p>For a smooth start, we recommend:</p>
    <ol>
        <li>Updating your store profile and logo</li>
        <li>Adding your first products</li>
        <li>Reviewing store policies and payment details</li>
    </ol>

    <p>If you need any assistance, our support team is always here to help you.</p>

    <br>
    <p>Best regards,</p>
    <p><strong>The Mindblaze Team</strong></p>
</body>
</html>
