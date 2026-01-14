<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payments Export</title>
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
        <h1>Payments Report</h1>
        <p>Generated on: {{ date('d M Y H:i:s') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Payment ID</th>
                <th>Booking ID</th>
                <th>Customer Name</th>
                <th>Vehicle Plate</th>
                <th>Bank Name</th>
                <th>Account No</th>
                <th>Payment Date</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Verified</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payments as $payment)
                @php
                    $booking = $payment->booking;
                    $customer = $booking->customer ?? null;
                    $user = $customer->user ?? null;
                    $vehicle = $booking->vehicle ?? null;
                @endphp
                <tr>
                    <td>{{ $payment->paymentID }}</td>
                    <td>#{{ $payment->bookingID }}</td>
                    <td>{{ $user->name ?? 'N/A' }}</td>
                    <td>{{ $vehicle->plate_number ?? 'N/A' }}</td>
                    <td>{{ $payment->payment_bank_name ?? 'N/A' }}</td>
                    <td>{{ $payment->payment_bank_account_no ?? 'N/A' }}</td>
                    <td>{{ $payment->payment_date ? $payment->payment_date->format('Y-m-d H:i') : 'N/A' }}</td>
                    <td>RM {{ number_format($payment->total_amount ?? 0, 2) }}</td>
                    <td>{{ $payment->payment_status ?? 'N/A' }}</td>
                    <td>{{ $payment->payment_isVerify ? 'Yes' : 'No' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Total Payments: {{ $payments->count() }}</p>
    </div>
</body>
</html>

