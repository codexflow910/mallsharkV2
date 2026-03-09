<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Store Performance Report</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 30px;
        }

        h1 {
            color: #1a1a2e;
            font-size: 22px;
            margin-bottom: 5px;
        }

        h2 {
            color: #16213e;
            font-size: 16px;
            margin-top: 25px;
            border-bottom: 2px solid #0f3460;
            padding-bottom: 5px;
        }

        .date-range {
            color: #666;
            font-size: 13px;
            margin-bottom: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th {
            background-color: #0f3460;
            color: #fff;
            padding: 8px 12px;
            text-align: left;
            font-size: 11px;
        }

        td {
            padding: 7px 12px;
            border-bottom: 1px solid #ddd;
            font-size: 11px;
        }

        tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .metric-value {
            font-weight: bold;
            color: #0f3460;
        }

        .no-data {
            color: #999;
            font-style: italic;
        }

        .footer {
            margin-top: 30px;
            font-size: 10px;
            color: #999;
            text-align: center;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>

<body>
    <h1>Store Performance Report</h1>
    <div class="date-range">Date Range: {{ $dateRange }}</div>
    <div class="date-range">Generated: {{ now()->format('F j, Y g:i A') }}</div>

    {{-- Summary Metrics --}}
    <h2>Summary Metrics</h2>
    <table>
        <thead>
            <tr>
                <th>Metric</th>
                <th>Value</th>
                <th>Details</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Cancellation Rate</td>
                <td class="metric-value">{{ $summary['cancellation_rate']['rate'] }}%</td>
                <td>{{ $summary['cancellation_rate']['cancelled_orders'] }} cancelled out of {{ $summary['cancellation_rate']['total_orders'] }} orders</td>
            </tr>
            <tr>
                <td>Delivered Timely</td>
                <td class="metric-value">{{ $summary['delivered_timely']['rate'] }}%</td>
                <td>{{ $summary['delivered_timely']['on_time_orders'] }} on-time out of {{ $summary['delivered_timely']['total_delivered_orders'] }} delivered</td>
            </tr>
            <tr>
                <td>Return Rate</td>
                <td class="metric-value">{{ $summary['return_rate']['rate'] }}%</td>
                <td>{{ $summary['return_rate']['returned_orders'] }} returned out of {{ $summary['return_rate']['total_orders'] }} orders</td>
            </tr>
            <tr>
                <td>Returning Customers</td>
                <td class="metric-value">{{ $summary['returning_customers']['rate'] }}%</td>
                <td>Customers who ordered more than once</td>
            </tr>
            <tr>
                <td>Conversion Rate</td>
                <td class="metric-value">{{ $summary['conversion_rate']['rate'] }}%</td>
                <td>Orders placed / total visitors</td>
            </tr>
            <tr>
                <td>Total Visitors</td>
                <td class="metric-value">{{ number_format($summary['total_visitors']) }}</td>
                <td>Product views from search analytics</td>
            </tr>
            <tr>
                <td>Unique Visitors</td>
                <td class="metric-value">{{ number_format($summary['unique_visitors']) }}</td>
                <td>Distinct users (orders + cart interactions)</td>
            </tr>
            <tr>
                <td>Abandoned Carts</td>
                <td class="metric-value">{{ number_format($summary['abandoned_carts']) }}</td>
                <td>Users with items left in cart</td>
            </tr>
        </tbody>
    </table>

    {{-- Orders Trend --}}
    <h2>Orders Trend</h2>
    @if(count($graphs['orders_trend']) > 0)
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Orders</th>
                </tr>
            </thead>
            <tbody>
                @foreach($graphs['orders_trend'] as $row)
                    <tr>
                        <td>{{ $row['date'] }}</td>
                        <td class="metric-value">{{ number_format($row['count']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="no-data">No data available for this period.</p>
    @endif

    {{-- Revenue Trend --}}
    <h2>Revenue Trend</h2>
    @if(count($graphs['revenue_trend']) > 0)
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Revenue ($)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($graphs['revenue_trend'] as $row)
                    <tr>
                        <td>{{ $row['date'] }}</td>
                        <td class="metric-value">${{ number_format($row['revenue'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="no-data">No data available for this period.</p>
    @endif

    {{-- Cancellation Trend --}}
    <h2>Cancellation Trend</h2>
    @if(count($graphs['cancellation_trend']) > 0)
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Cancellations</th>
                </tr>
            </thead>
            <tbody>
                @foreach($graphs['cancellation_trend'] as $row)
                    <tr>
                        <td>{{ $row['date'] }}</td>
                        <td class="metric-value">{{ number_format($row['count']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="no-data">No data available for this period.</p>
    @endif

    {{-- Return Trend --}}
    <h2>Return Trend</h2>
    @if(count($graphs['return_trend']) > 0)
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Returns</th>
                </tr>
            </thead>
            <tbody>
                @foreach($graphs['return_trend'] as $row)
                    <tr>
                        <td>{{ $row['date'] }}</td>
                        <td class="metric-value">{{ number_format($row['count']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="no-data">No data available for this period.</p>
    @endif

    <div class="footer">
        This report was automatically generated by Shopzit Seller Center.
    </div>
</body>

</html>
