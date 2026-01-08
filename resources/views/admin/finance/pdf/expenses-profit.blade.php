<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Expenses and Profit Report</title>
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
        <h1>Expenses and Profit Report</h1>
        <p>Period: {{ $selectedYear }}{{ $selectedMonth ? ' - ' . \Carbon\Carbon::create($selectedYear, $selectedMonth, 1)->format('F') : '' }}</p>
        @if($vehicleType !== 'all')
            <p>Vehicle Type: {{ ucfirst($vehicleType) }}</p>
        @endif
        <p>Generated: {{ \Carbon\Carbon::now()->format('d M Y H:i:s') }}</p>
    </div>

    <div class="summary">
        <h3>Summary</h3>
        <div class="summary-row">
            <strong>Total Vehicles:</strong>
            <span>{{ $totalVehicles }}</span>
        </div>
        <div class="summary-row">
            <strong>Total Profit:</strong>
            <span class="{{ $totalProfit >= 0 ? 'text-success' : 'text-danger' }}">RM {{ number_format($totalProfit, 2) }}</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Vehicle ID</th>
                <th>Vehicle</th>
                <th>Plate Number</th>
                <th class="text-right">Owner Leasing Price</th>
                <th class="text-right">Expenses (Maintenance)</th>
                <th class="text-right">Expenses (Staff)</th>
                <th class="text-right">Profit</th>
            </tr>
        </thead>
        <tbody>
            @forelse($vehicles as $vehicle)
                <tr>
                    <td>#{{ $vehicle['vehicleID'] }}</td>
                    <td>{{ $vehicle['vehicle'] }}</td>
                    <td>{{ $vehicle['plate_number'] }}</td>
                    <td class="text-right">RM {{ number_format($vehicle['leasing_price'], 2) }}</td>
                    <td class="text-right">RM {{ number_format($vehicle['maintenance_expenses'], 2) }}</td>
                    <td class="text-right">RM {{ number_format($vehicle['staff_expenses'], 2) }}</td>
                    <td class="text-right {{ $vehicle['profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                        RM {{ number_format($vehicle['profit'], 2) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">No vehicles found</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background-color: #e9ecef; font-weight: bold;">
                <td colspan="6" class="text-right">Total Profit:</td>
                <td class="text-right {{ $totalProfit >= 0 ? 'text-success' : 'text-danger' }}">
                    RM {{ number_format($totalProfit, 2) }}
                </td>
            </tr>
        </tfoot>
    </table>
</body>
</html>

