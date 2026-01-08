<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Weekly Income Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #dc3545;
            padding-bottom: 10px;
        }
        .header h1 {
            color: #dc3545;
            margin: 0;
        }
        .summary {
            margin: 20px 0;
            padding: 15px;
            background: #f5f5f5;
            border-radius: 5px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin: 5px 0;
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
        .text-success {
            color: #28a745;
        }
        .text-danger {
            color: #dc3545;
        }
        .text-right {
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Weekly Income Report</h1>
        <p>Week: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</p>
        <p>Generated: {{ \Carbon\Carbon::now()->format('d M Y H:i:s') }}</p>
    </div>

    <div class="summary">
        <h3>Week Summary</h3>
        <div class="summary-row">
            <strong>Total Rentals:</strong>
            <span>{{ $weekTotalRentals }}</span>
        </div>
        <div class="summary-row">
            <strong>Total Expenses:</strong>
            <span class="text-danger">RM {{ number_format($weekTotalExpenses, 2) }}</span>
        </div>
        <div class="summary-row">
            <strong>Total Earnings:</strong>
            <span class="text-success">RM {{ number_format($weekTotalEarnings, 2) }}</span>
        </div>
        <div class="summary-row">
            <strong>Total Profit:</strong>
            <span class="{{ $weekTotalProfit >= 0 ? 'text-success' : 'text-danger' }}">RM {{ number_format($weekTotalProfit, 2) }}</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th class="text-right">Total Rentals</th>
                <th class="text-right">Total Expenses</th>
                <th class="text-right">Total Earnings</th>
                <th class="text-right">Profit</th>
            </tr>
        </thead>
        <tbody>
            @foreach($week as $day)
                <tr>
                    <td>{{ $day['dateFormatted'] }}</td>
                    <td class="text-right">{{ $day['totalRentals'] }}</td>
                    <td class="text-right">RM {{ number_format($day['totalExpenses'], 2) }}</td>
                    <td class="text-right">RM {{ number_format($day['totalEarnings'], 2) }}</td>
                    <td class="text-right {{ $day['profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                        RM {{ number_format($day['profit'], 2) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #e9ecef; font-weight: bold;">
                <td>Total (Week)</td>
                <td class="text-right">{{ $weekTotalRentals }}</td>
                <td class="text-right">RM {{ number_format($weekTotalExpenses, 2) }}</td>
                <td class="text-right">RM {{ number_format($weekTotalEarnings, 2) }}</td>
                <td class="text-right {{ $weekTotalProfit >= 0 ? 'text-success' : 'text-danger' }}">
                    RM {{ number_format($weekTotalProfit, 2) }}
                </td>
            </tr>
        </tfoot>
    </table>
</body>
</html>

