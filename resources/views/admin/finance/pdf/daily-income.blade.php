<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Daily Income Report</title>
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
        <h1>Daily Income Report</h1>
        <p>Period: {{ \Carbon\Carbon::create($selectedYear, $selectedMonth, 1)->format('F Y') }}</p>
        <p>Generated: {{ \Carbon\Carbon::now()->format('d M Y H:i:s') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th class="text-right">Total Earnings</th>
                <th class="text-right">Total Expenses</th>
                <th class="text-right">Profit</th>
            </tr>
        </thead>
        <tbody>
            @foreach($days as $day)
                <tr>
                    <td>{{ $day['dateFormatted'] }}</td>
                    <td class="text-right">RM {{ number_format($day['totalEarnings'], 2) }}</td>
                    <td class="text-right">RM {{ number_format($day['totalExpenses'], 2) }}</td>
                    <td class="text-right {{ $day['profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                        RM {{ number_format($day['profit'], 2) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            @php
                $totalEarnings = collect($days)->sum('totalEarnings');
                $totalExpenses = collect($days)->sum('totalExpenses');
                $totalProfit = collect($days)->sum('profit');
            @endphp
            <tr style="background-color: #e9ecef; font-weight: bold;">
                <td>Total (Month)</td>
                <td class="text-right">RM {{ number_format($totalEarnings, 2) }}</td>
                <td class="text-right">RM {{ number_format($totalExpenses, 2) }}</td>
                <td class="text-right {{ $totalProfit >= 0 ? 'text-success' : 'text-danger' }}">
                    RM {{ number_format($totalProfit, 2) }}
                </td>
            </tr>
        </tfoot>
    </table>
</body>
</html>

