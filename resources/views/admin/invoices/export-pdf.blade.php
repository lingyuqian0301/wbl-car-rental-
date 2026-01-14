<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoices Export</title>
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
        <h1>Invoices Report</h1>
        <p>Generated on: {{ date('d M Y H:i:s') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Invoice ID</th>
                <th>Booking ID</th>
                <th>Customer Name</th>
                <th>Vehicle Plate</th>
                <th>Issue Date</th>
                <th>Pickup Date</th>
                <th>Total Amount</th>
                <th>Total Paid</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bookings as $booking)
                @php
                    $invoice = $booking->invoice;
                    $customer = $booking->customer;
                    $user = $customer->user ?? null;
                    $vehicle = $booking->vehicle ?? null;
                    $totalPaid = $booking->payments()->where('payment_status', 'Verified')->sum('total_amount');
                    $depositAmount = $booking->deposit_amount ?? 0;
                    $rentalAmount = $booking->rental_amount ?? 0;
                    $additionalCharges = $booking->additionalCharges;
                    $additionalChargesTotal = $additionalCharges ? ($additionalCharges->total_extra_charge ?? 0) : 0;
                    $totalPaymentAmount = $depositAmount + $rentalAmount + $additionalChargesTotal;
                @endphp
                <tr>
                    <td>{{ $invoice->invoice_number ?? 'N/A' }}</td>
                    <td>#{{ $booking->bookingID }}</td>
                    <td>{{ $user->name ?? 'N/A' }}</td>
                    <td>{{ $vehicle->plate_number ?? 'N/A' }}</td>
                    <td>{{ $invoice->issue_date ? \Carbon\Carbon::parse($invoice->issue_date)->format('Y-m-d') : 'N/A' }}</td>
                    <td>{{ $booking->rental_start_date ? \Carbon\Carbon::parse($booking->rental_start_date)->format('Y-m-d') : 'N/A' }}</td>
                    <td>RM {{ number_format($totalPaymentAmount, 2) }}</td>
                    <td>RM {{ number_format($totalPaid, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Total Invoices: {{ $bookings->count() }}</p>
    </div>
</body>
</html>

