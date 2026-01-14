<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Deposits Export</title>
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
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Deposits Report</h1>
        <p>Generated on: {{ date('d M Y H:i:s') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Booking ID</th>
                <th>Customer Name</th>
                <th>Vehicle Plate</th>
                <th>Deposit Amount</th>
                <th>Vehicle Condition Form</th>
                <th>Customer Choice</th>
                <th>Refund Amount</th>
                <th>Refund Status</th>
                <th>Handled By</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bookings as $booking)
                @php
                    $customer = $booking->customer;
                    $user = $customer->user ?? null;
                    $vehicle = $booking->vehicle ?? null;
                    $hasReturnForm = $booking->vehicleConditionForms && $booking->vehicleConditionForms->where('form_type', 'RETURN')->first();
                    $handledBy = $booking->deposit_handled_by ? \App\Models\User::find($booking->deposit_handled_by) : null;
                @endphp
                <tr>
                    <td>#{{ $booking->bookingID }}</td>
                    <td>{{ $user->name ?? 'N/A' }}</td>
                    <td>{{ $vehicle->plate_number ?? 'N/A' }}</td>
                    <td>RM {{ number_format($booking->deposit_amount ?? 0, 2) }}</td>
                    <td>{{ $hasReturnForm ? 'Submitted' : 'Pending' }}</td>
                    <td>{{ $booking->deposit_customer_choice ? ucfirst(str_replace('_', ' ', $booking->deposit_customer_choice)) : 'N/A' }}</td>
                    <td>RM {{ number_format($booking->deposit_refund_amount ?? 0, 2) }}</td>
                    <td>{{ ucfirst($booking->deposit_refund_status ?? 'pending') }}</td>
                    <td>{{ $handledBy->name ?? 'N/A' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Total Deposits: {{ $bookings->count() }}</p>
    </div>
</body>
</html>

