<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Store Setup Notification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
            background: #f8f9fa;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: auto;
            background: #ffffff;
            border-radius: 8px;
            padding: 20px;
            border: 1px solid #ddd;
        }
        h2 {
            color: #0056b3;
            margin-bottom: 10px;
        }
        h3 {
            margin-top: 20px;
            color: #333;
        }
        p {
            margin: 5px 0;
        }
        .step-progress {
            margin: 20px 0;
            padding: 20px;
            background: #f1f5f9;
            border-radius: 6px;
            border-left: 4px solid #3b82f6;
        }

        .completed-steps {
            color: #10b981;
            margin-bottom: 15px;
        }
        .remaining-steps {
            color: #f59e0b;
            margin-bottom: 15px;
        }
        .step-item {
            margin: 8px 0;
            padding: 5px 0;
        }
        .footer {
            margin-top: 20px;
            font-size: 13px;
            color: #777;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>{{ $details['step'] ?? 'Store Update' }}</h2>
        <p>{{ $details['message'] ?? '' }}</p>
        <div class="step-progress">
            <h3 style="margin-top: 0; color: #1f2937;">Store Setup Progress</h3>
            @if(!empty($details['remainingSteps']) && count($details['remainingSteps']) > 0)
            <div class="remaining-steps">
                <h4 style="color: #f59e0b; margin-bottom: 10px;">⏳ Remaining Steps:</h4>
                @foreach($details['remainingSteps'] as $remainingStep)
                <div class="step-item">• {{ $remainingStep }}</div>
                @endforeach
            </div>
            @endif
            @if(!empty($details['completedSteps']) && count($details['completedSteps']) > 0)
            <div class="completed-steps">
                <h4 style="color: #10b981; margin-bottom: 10px;">✅ Completed Steps:</h4>
                @foreach($details['completedSteps'] as $completedStep)
                <div class="step-item">• {{ $completedStep }}</div>
                @endforeach
            </div>
            @endif

        </div>

        @if(!empty($details['storeName']))
            <h3>Store Details</h3>
            <p><strong>Name:</strong> {{ $details['storeName'] }}</p>
            <p><strong>ID:</strong> {{ $details['storeId'] ?? 'N/A' }}</p>
            @if(!empty($details['vendorEmail']))
            <p><strong>Vendor Email:</strong> {{ $details['vendorEmail'] }}</p>
            @endif
            @if(!empty($details['vendorName']))
            <p><strong>Vendor Name:</strong> {{ $details['vendorName'] }}</p>
            @endif
        @endif

        @if(!empty($details['product']))
            <h3>Product Details</h3>
            <p><strong>Title:</strong> {{ $details['product']->title ?? $details['product']->name ?? 'N/A' }}</p>
            <p><strong>Product ID:</strong> {{ $details['product']->id ?? $details['product']->product_id ?? 'N/A' }}</p>
        @endif

        @if(!empty($details['store']))
            <h3>Store Details</h3>
            <p><strong>Name:</strong> {{ $details['store']->name ?? $details['store']->store_name ?? 'N/A' }}</p>
            <p><strong>ID:</strong> {{ $details['store']->id ?? $details['store']->store_id ?? 'N/A' }}</p>
        @endif

        <div class="footer">
            <p>This is an automated notification from <strong>MallShark</strong>.com</p>
        </div>
    </div>
</body>
</html>
