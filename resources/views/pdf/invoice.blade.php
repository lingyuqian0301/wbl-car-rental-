<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $invoiceData->invoice_number ?? $booking->bookingID }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 14px; color: #333; line-height: 1.6; }
        .invoice-box { max-width: 800px; margin: auto; padding: 30px; border: 1px solid #eee; }

        /* Layout */
        .header-table, .info-table, .items-table { width: 100%; border-collapse: collapse; }
        .header-table { margin-bottom: 30px; }
        .header-table td { vertical-align: top; }

        /* Branding */
        .logo { font-size: 26px; font-weight: bold; color: #800020; /* Hasta Maroon */ text-transform: uppercase; }
        .invoice-title { font-size: 24px; font-weight: bold; color: #333; text-align: right; }

        /* Sections */
        .section-title { margin-top: 20px; margin-bottom: 10px; font-weight: bold; color: #555; text-transform: uppercase; font-size: 12px; border-bottom: 2px solid #eee; padding-bottom: 5px; }

        /* Info Tables */
        .info-table td { padding: 5px 0; vertical-align: top; }
        .label { font-weight: bold; color: #777; width: 120px; display: inline-block; }

        /* Items Table (Financials) */
        .items-table { margin-top: 20px; }
        .items-table th { background: #f9f9f9; padding: 12px; border-bottom: 2px solid #ddd; text-align: left; font-weight: bold; color: #555; }
        .items-table td { padding: 12px; border-bottom: 1px solid #eee; }
        .items-table .text-right { text-align: right; }

        /* Totals */
        .total-row td { border-top: 2px solid #333; font-weight: bold; font-size: 16px; color: #000; background-color: #fcfcfc; }
        .subtotal-row td { color: #555; }
        .discount-row td { color: #198754; } /* Green for discount */

        .footer { margin-top: 40px; text-align: center; font-size: 12px; color: #aaa; }
    </style>
</head>
<body>
    <div class="invoice-box">

        <table class="header-table">
            <tr>
                <td>
                    <div class="logo">Hasta Travel</div>
                    <small>Johor Bahru, Johor, Malaysia</small><br>
                    <small>Email: support@hastatravel.com</small>
                </td>
                <td class="text-right">
                    <div class="invoice-title">INVOICE</div>
                    <strong>No:</strong> #{{ $invoiceData->invoice_number ?? 'INV-'.str_pad($booking->bookingID, 6, '0', STR_PAD_LEFT) }}<br>
                    <strong>Date:</strong> {{ \Carbon\Carbon::parse($invoiceData->issue_date ?? now())->format('d M, Y') }}<br>
                    <strong>Status:</strong> <span style="color: green; font-weight:bold;">PAID</span>
                </td>
            </tr>
        </table>

        <table class="info-table">
            <tr>
                <td width="50%">
                    <div class="section-title">Billed To</div>
                    <strong>{{ $booking->customer->fullname ?? 'Guest' }}</strong><br>
                    <span class="label">Phone:</span> {{ $booking->customer->phone_number ?? 'N/A' }}<br>
                    <span class="label">Email:</span> {{ $booking->customer->email ?? 'N/A' }}
                </td>
                <td width="50%">
                    <div class="section-title">Vehicle & Trip Details</div>
                    <strong>{{ $booking->vehicle->vehicle_brand ?? 'Car' }} {{ $booking->vehicle->vehicle_model ?? '' }}</strong><br>
                    <span class="label">Plate No:</span> {{ $booking->vehicle->vehicle_number ?? $booking->vehicle->plate_number ?? '-' }}<br>
                    <span class="label">Duration:</span> {{ \Carbon\Carbon::parse($booking->start_date)->diffInDays(\Carbon\Carbon::parse($booking->end_date)) + 1 }} Days<br>
                    <span class="label">Dates:</span> {{ \Carbon\Carbon::parse($booking->start_date)->format('d/m/y') }} - {{ \Carbon\Carbon::parse($booking->end_date)->format('d/m/y') }}
                </td>
            </tr>
        </table>

        <div class="section-title">Payment Summary</div>
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
                        Rental Charges<br>
                        <small style="color:#777;">
                            ({{ \Carbon\Carbon::parse($booking->start_date)->diffInDays(\Carbon\Carbon::parse($booking->end_date)) + 1 }} Days x RM {{ number_format($booking->vehicle->price_per_day ?? 0, 2) }})
                        </small>
                    </td>
                    <td class="text-right">
                        {{ number_format($rentalAmount ?? $booking->rental_amount, 2) }}
                    </td>
                </tr>

                <tr>
                    <td>
                        Security Deposit <small>(Refundable)</small>
                    </td>
                    <td class="text-right">
                        {{ number_format($depositAmount ?? $booking->deposit_amount, 2) }}
                    </td>
                </tr>

                @if(isset($voucher) && $voucher)
                <tr class="discount-row">
                    <td>
                        Voucher Applied <small>({{ $voucher->code ?? 'DISCOUNT' }})</small>
                    </td>
                    <td class="text-right">
                        - {{ number_format($voucher->discount_amount ?? 0, 2) }}
                    </td>
                </tr>
                @endif

                <tr class="total-row">
                    <td class="text-right">Total Amount Paid</td>
                    <td class="text-right">
                        RM {{ number_format($totalPaid ?? $booking->total_amount, 2) }}
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="footer">
            <p>Thank you for choosing Hasta Travel! We hope you have a safe journey.</p>
            <p>This is a computer-generated invoice and requires no signature.</p>
        </div>
    </div>
</body>
</html>
