<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - Booking #{{ $booking->id }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.6;
        }
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: #fff;
        }
        .header {
            border-bottom: 3px solid #800020;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #800020;
            margin-bottom: 5px;
        }
        .company-reg {
            font-size: 10px;
            color: #666;
            margin-bottom: 15px;
        }
        .invoice-title {
            font-size: 28px;
            font-weight: bold;
            color: #800020;
            text-align: right;
            margin-top: 10px;
        }
        .invoice-info {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }
        .invoice-info-left,
        .invoice-info-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        .invoice-info-right {
            text-align: right;
        }
        .info-label {
            font-weight: bold;
            color: #800020;
            margin-bottom: 5px;
        }
        .info-value {
            margin-bottom: 10px;
        }
        .billing-section {
            margin-bottom: 30px;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #800020;
            border-bottom: 2px solid #800020;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th {
            background-color: #800020;
            color: #fff;
            padding: 10px;
            text-align: left;
            font-weight: bold;
        }
        .items-table td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        .items-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .totals-section {
            margin-top: 20px;
            margin-left: auto;
            width: 300px;
        }
        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }
        .totals-table td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }
        .totals-table td:first-child {
            font-weight: bold;
            text-align: right;
            padding-right: 20px;
        }
        .totals-table .total-row {
            font-size: 14px;
            font-weight: bold;
            background-color: #f0f0f0;
        }
        .totals-table .balance-row {
            font-size: 14px;
            font-weight: bold;
            color: #800020;
            background-color: #ffe6e6;
        }
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 2px solid #800020;
            text-align: center;
            color: #666;
            font-size: 11px;
        }
        .footer-message {
            font-size: 14px;
            color: #800020;
            font-weight: bold;
            margin-top: 10px;
        }
        .payment-info {
            background-color: #f9f9f9;
            padding: 15px;
            border-left: 4px solid #800020;
            margin-top: 20px;
        }
        .payment-info-title {
            font-weight: bold;
            color: #800020;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Header -->
        <div class="header">
            <div class="company-name">HASTA TRAVEL & TOURS SDN. BHD.</div>
            <div class="company-reg">Registration No: 1359376-T</div>
            <div class="invoice-title">INVOICE</div>
        </div>

        <!-- Invoice Information -->
        <div class="invoice-info">
            <div class="invoice-info-left">
                <div class="info-label">Bill To:</div>
                <div class="info-value">
                    {{ $booking->user->name }}<br>
                    {{ $booking->user->email }}
                </div>
            </div>
            <div class="invoice-info-right">
                <div class="info-label">Invoice Details:</div>
                <div class="info-value">
                    Invoice #: {{ $booking->id }}<br>
                    Date: {{ $invoiceDate->format('d M Y') }}<br>
                    Booking ID: #{{ $booking->id }}
                </div>
            </div>
        </div>

        <!-- Billing Section -->
        <div class="billing-section">
            <div class="section-title">Car Rental Details</div>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th class="text-center">Duration</th>
                        <th class="text-right">Daily Rate</th>
                        <th class="text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <strong>{{ $booking->vehicle->full_model }}</strong><br>
                            Registration: {{ $booking->vehicle->registration_number }}<br>
                            <small>Rental Period: {{ $booking->start_date->format('d M Y') }} to {{ $booking->end_date->format('d M Y') }}</small>
                        </td>
                        <td class="text-center">{{ $booking->duration_days }} days</td>
                        <td class="text-right">RM {{ number_format($booking->vehicle->daily_rate, 2) }}</td>
                        <td class="text-right">RM {{ number_format($booking->total_price, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Payment Summary -->
        <div class="totals-section">
            <table class="totals-table">
                <tr>
                    <td>Subtotal:</td>
                    <td class="text-right">RM {{ number_format($booking->total_price, 2) }}</td>
                </tr>
                @if($depositPaid > 0)
                <tr>
                    <td>Deposit Paid:</td>
                    <td class="text-right">RM {{ number_format($depositPaid, 2) }}</td>
                </tr>
                @endif
                @if($fullPaymentPaid > 0)
                <tr>
                    <td>Full Payment Paid:</td>
                    <td class="text-right">RM {{ number_format($fullPaymentPaid, 2) }}</td>
                </tr>
                @endif
                <tr class="total-row">
                    <td>Total Paid:</td>
                    <td class="text-right">RM {{ number_format($totalPaid, 2) }}</td>
                </tr>
                @if($balanceDue > 0)
                <tr class="balance-row">
                    <td>Balance Due:</td>
                    <td class="text-right">RM {{ number_format($balanceDue, 2) }}</td>
                </tr>
                @else
                <tr class="total-row">
                    <td>Status:</td>
                    <td class="text-right" style="color: #28a745;">Fully Paid</td>
                </tr>
                @endif
            </table>
        </div>

        <!-- Payment Information -->
        @if($booking->payments->where('status', 'Verified')->count() > 0)
        <div class="payment-info">
            <div class="payment-info-title">Payment History:</div>
            @foreach($booking->payments->where('status', 'Verified') as $payment)
                <div style="margin-bottom: 8px;">
                    <strong>{{ $payment->payment_type }}</strong> - 
                    RM {{ number_format($payment->amount, 2) }} 
                    ({{ $payment->payment_method }}) 
                    - {{ $payment->payment_date->format('d M Y') }}
                </div>
            @endforeach
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <div>HASTA TRAVEL & TOURS SDN. BHD.</div>
            <div>Registration No: 1359376-T</div>
            <div class="footer-message">Thank you for choosing Hasta Travel.</div>
            <div style="margin-top: 10px;">
                This is a computer-generated invoice. No signature required.
            </div>
        </div>
    </div>
</body>
</html>

