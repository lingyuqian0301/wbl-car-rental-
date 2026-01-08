<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Staff Tasks Report - {{ $staff->user->name ?? 'Staff' }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
        }
        .info {
            margin-bottom: 20px;
        }
        .info p {
            margin: 5px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        table th {
            background-color: #b91c1c;
            color: white;
        }
        .total-row {
            font-weight: bold;
            background-color: #f5f5f5;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Staff Tasks Report</h1>
        <p>{{ $staff->user->name ?? 'Staff' }} ({{ $staff->user->username ?? 'N/A' }})</p>
        <p>Period: {{ date('F Y', mktime(0, 0, 0, $filterMonth, 1, $filterYear)) }}</p>
    </div>

    <div class="info">
        <p><strong>Staff ID:</strong> #{{ $staff->staffID }}</p>
        <p><strong>Total Tasks:</strong> {{ $tasks->count() }}</p>
        <p><strong>Total Commission:</strong> RM {{ number_format($totalCommission, 2) }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Task Date</th>
                <th>Task Type</th>
                <th>Description</th>
                <th>Commission Amount (RM)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tasks as $task)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($task['task_date'])->format('d M Y') }}</td>
                    <td>{{ $task['task_type'] }}</td>
                    <td>{{ $task['description'] }}</td>
                    <td>{{ number_format($task['commission_amount'], 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center;">No tasks found for the selected period.</td>
                </tr>
            @endforelse
        </tbody>
        @if($tasks->count() > 0)
        <tfoot>
            <tr class="total-row">
                <td colspan="3" style="text-align: right;">Total Commission:</td>
                <td>RM {{ number_format($totalCommission, 2) }}</td>
            </tr>
        </tfoot>
        @endif
    </table>

    <div style="margin-top: 30px; text-align: center; font-size: 10px; color: #666;">
        <p>Generated on {{ date('d M Y H:i:s') }}</p>
    </div>
</body>
</html>


