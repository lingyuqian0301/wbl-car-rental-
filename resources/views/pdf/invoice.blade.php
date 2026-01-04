<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $invoiceData->invoice_number ?? $booking->bookingID }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 14px; color: #333; line-height: 1.5; }
        .invoice-box { max-width: 800px; margin: auto; padding: 30px; border: 1px solid #eee; }

        /* HEADER & LOGO */
        .header-table { width: 100%; margin-bottom: 20px; }
        .logo { font-size: 26px; font-weight: bold; color: #800020; text-transform: uppercase; } /* Maroon */
        .invoice-title { font-size: 24px; font-weight: bold; text-align: right; color: #333; }

        /* CUSTOMER INFO & DATES */
        .info-table { width: 100%; margin-bottom: 20px; border-top: 1px solid #eee; padding-top: 10px; }
        .info-table td { vertical-align: top; width: 50%; }
        .label { font-weight: bold; color: #777; display: inline-block; width: 80px; }

        /* FINANCIAL TABLE */
        .items-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .items-table th { background: #f9f9f9; padding: 10px; border-bottom: 2px solid #ddd; text-align: left; }
        .items-table td { padding: 10px; border-bottom: 1px solid #eee; }
        .text-right { text-align: right; }

        /* TOTALS */
        .total-row td { border-top: 2px solid #333; font-weight: bold; font-size: 16px; background-color: #fafafa; }
        .discount-text { color: green; }
    </style>
</head>
<body>
    <div class="invoice-box">
        <table class="header-table">
            <tr>
                <td>
                    <div class="logo">HASTA TRAVEL</div>
                    <small>Johor Bahru, Malaysia</small><br>
                    <small>support@hastatravel.com</small>
                </td>
                <td class="text-right">
                    <div class="invoice-title">INVOICE</div>
                    <strong>No:</strong> #{{ $invoiceData->invoice_number ?? 'INV-'.str_pad($booking->bookingID, 6, '0', STR_PAD_LEFT) }}<br>
                    <strong>Date:</strong> {{ now()->format('d M, Y') }}<br>
                    <strong>Status:</strong> <span style="color: green; font-weight:bold;">PAID</span>
                </td>
            </tr>
        </table>

        <table class="info-table">
            <tr>
                <td>
                    <div style="font-weight: bold; margin-bottom: 5px; text-transform: uppercase; color: #555;">Billed To:</div>
                    <strong>{{ $booking->customer->user->name ?? $booking->customer->fullname ?? 'Valued Customer' }}</strong><br>

                    <span class="label">Phone:</span> {{ $booking->customer->phone_number ?? 'N/A' }}<br>

                    <span class="label">Email:</span> {{ $booking->customer->user->email ?? $booking->customer->email ?? 'N/A' }}
                </td>

                <td class="text-right">
                    <div style="font-weight: bold; margin-bottom: 5px; text-transform: uppercase; color: #555;">Rental Details:</div>
                    <strong>{{ $booking->vehicle->vehicle_brand ?? 'Vehicle' }} {{ $booking->vehicle->vehicle_model ?? '' }}</strong><br>
                    <small>Plate: {{ $booking->vehicle->vehicle_number ?? $booking->vehicle->plate_number ?? '-' }}</small><br>

                    <div style="margin-top: 5px;">
                        <span class="label">Start:</span> {{ \Carbon\Carbon::parse($booking->start_date)->format('d M Y') }}<br>
                        <span class="label">Return:</span> {{ \Carbon\Carbon::parse($booking->end_date)->format('d M Y') }}<br>
                        <span class="label">Duration:</span> {{ \Carbon\Carbon::parse($booking->start_date)->diffInDays(\Carbon\Carbon::parse($booking->end_date)) + 1 }} Days
                    </div>
                </td>
            </tr>
        </table>

        <table class="items-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="text-right">Amount (RM)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        Car Rental Charges
                        <br><small style="color:#777;">Rate per day: RM {{ number_format($booking->vehicle->price_per_day ?? 0, 2) }}</small>
                    </td>
                    <td class="text-right">{{ number_format($rentalAmount, 2) }}</td>
                </tr>

                <tr>
                    <td>Security Deposit <small>(Refundable)</small></td>
                    <td class="text-right">{{ number_format($depositAmount, 2) }}</td>
                </tr>

                @if(isset($voucher) && $voucher)
                <tr>
                    <td class="discount-text">
                        Voucher Discount ({{ $voucher->code ?? 'PROMO' }})
                    </td>
                    <td class="text-right discount-text">
                        - {{ number_format($voucher->discount_amount ?? 0, 2) }}
                    </td>
                </tr>
                @endif

                <tr class="total-row">
                    <td class="text-right">Total Paid</td>
                    <td class="text-right">RM {{ number_format($totalPaid, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <br>
        <p style="text-align: center; font-size: 12px; color: #aaa;">
            Thank you for choosing Hasta Travel! Safe travels.
        </p>
    </div>
</body>
</html>
