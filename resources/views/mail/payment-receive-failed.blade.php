<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Webhook Failure Alert</title>
</head>
<body style="background:#f9f9f9; font-family: Arial, sans-serif; color:#333; padding:20px;">
    <div style="max-width:600px; margin:auto; background:#fff; padding:20px; border-radius:8px; box-shadow:0 2px 6px rgba(0,0,0,0.1);">
        <h2 style="color:#d9534f;">⚠️ Webhook Processing Failed</h2>
        <p><strong>Service:</strong> Stripe</p>
        <p><strong>Error Message:</strong> {{ $errorMessage }}</p>
        <hr>
        <h3>Webhook Payload:</h3>
        <pre style="background:#f4f4f4; padding:10px; border:1px solid #ddd; font-size:12px; overflow-x:auto;">
{{ json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}
        </pre>
        <p style="font-size:12px; color:#666;">This email was generated automatically. Please investigate the issue.</p>
    </div>
</body>
</html>
