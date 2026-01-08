<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Finance Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .summary {
            margin: 20px 0;
            padding: 15px;
            background: #f5f5f5;
            border-radius: 5px;
        }
        .summary-item {
            margin: 10px 0;
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        .summary-value {
            font-size: 1.2rem;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #dc3545;
            color: white;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Finance Report</h1>
        <p>Period: {{ $dateFrom }} to {{ $dateTo }}</p>
        @if($vehicleType !== 'all')
            <p>Vehicle Type: {{ ucfirst($vehicleType) }}</p>
        @endif
        @if($vehicleBrand)
            <p>Brand: {{ $vehicleBrand }}</p>
        @endif
        @if($vehicleModel)
            <p>Model: {{ $vehicleModel }}</p>
        @endif
    </div>

    <div class="summary">
        <h3>Financial Summary</h3>
        <div class="summary-item">
            <strong>Total Earnings:</strong> <span class="summary-value text-success">RM {{ number_format($totalEarnings, 2) }}</span>
            <div style="margin-left: 20px;">
                <div>Deposit: RM {{ number_format($depositEarnings, 2) }}</div>
                <div>Balance Payment: RM {{ number_format($balanceEarnings, 2) }}</div>
                <div>Full Payment: RM {{ number_format($fullPaymentEarnings, 2) }}</div>
            </div>
        </div>
        <div class="summary-item">
            <strong>Total Expenses:</strong> <span class="summary-value text-danger">RM {{ number_format($totalExpenses, 2) }}</span>
            <div style="margin-left: 20px;">
                <div>Maintenance: RM {{ number_format($maintenanceExpenses, 2) }}</div>
            </div>
        </div>
        <div class="summary-item">
            <strong>Net Profit:</strong> <span class="summary-value {{ $totalProfit >= 0 ? 'text-success' : 'text-danger' }}">RM {{ number_format($totalProfit, 2) }}</span>
        </div>
    </div>

    <h3>Earnings List</h3>
    <table>
        <thead>
            <tr>
                <th>Payment ID</th>
                <th>Booking ID</th>
                <th>Customer Name</th>
                <th>Vehicle</th>
                <th>Payment Type</th>
                <th>Amount</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($earningsList as $earning)
                <tr>
                    <td>#{{ $earning['payment_id'] }}</td>
                    <td>#{{ $earning['booking_id'] ?? 'N/A' }}</td>
                    <td>{{ $earning['customer_name'] }}</td>
                    <td>{{ $earning['vehicle'] }}</td>
                    <td>{{ $earning['payment_type'] }}</td>
                    <td>RM {{ number_format($earning['amount'], 2) }}</td>
                    <td>{{ \Carbon\Carbon::parse($earning['payment_date'])->format('d M Y') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h3>Expenses List</h3>
    <table>
        <thead>
            <tr>
                <th>Type</th>
                <th>Description</th>
                <th>Amount</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($expensesList as $expense)
                <tr>
                    <td>{{ $expense['type'] }}</td>
                    <td>{{ $expense['description'] }}</td>
                    <td>RM {{ number_format($expense['amount'], 2) }}</td>
                    <td>{{ \Carbon\Carbon::parse($expense['date'])->format('d M Y') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>









