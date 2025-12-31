<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice #INV-{{ $booking->bookingID }}</title>
    <style>
        body { font-family: sans-serif; color: #333; }
        .container { width: 100%; max-width: 800px; margin: 0 auto; }
        .header { width: 100%; margin-bottom: 30px; }
        .logo { font-size: 24px; font-weight: bold; text-align: right; }
        .invoice-title { font-size: 36px; font-weight: bold; margin-bottom: 5px; }

        .info-table { width: 100%; margin-bottom: 30px; }
        .info-table td { vertical-align: top; width: 33%; }
        .label { font-size: 12px; font-weight: bold; margin-bottom: 3px; }

        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .items-table th { text-align: left; border-bottom: 1px solid #ddd; padding: 10px 0; }
        .items-table td { border-bottom: 1px solid #eee; padding: 10px 0; }

        .totals { text-align: right; margin-top: 20px; }
        .total-row { margin-bottom: 5px; }
        .grand-total { font-size: 18px; font-weight: bold; color: #6d28d9; border-top: 2px solid #6d28d9; padding-top: 10px; display: inline-block; }
    </style>
</head>
<body>
    <div class="container">
        <table class="header">
            <tr>
                <td>
                    <div class="invoice-title">INVOICE</div>
                    <div>#INV-{{ str_pad($booking->bookingID, 6, '0', STR_PAD_LEFT) }}</div>
                </td>
                <td class="logo">HASTA TRAVEL</td>
            </tr>
        </table>

        <table class="info-table">
            <tr>
                <td>
                    <div class="label">Issued</div>
                    <div>{{ now()->format('d M, Y') }}</div>
                </td>
                <td>
                    <div class="label">Billed To</div>
                    <div><strong>{{ $booking->customer->fullname }}</strong></div>
                    <div>{{ $booking->customer->email }}</div>
                </td>
                <td>
                    <div class="label">From</div>
                    <div><strong>Hasta Car Rental</strong></div>
                    <div>Johor Bahru, Malaysia</div>
                </td>
            </tr>
        </table>

        <table class="items-table">
            <thead>
                <tr>
                    <th>Vehicle</th>
                    <th>Dates</th>
                    <th style="text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <strong>{{ $booking->vehicle->brand }} {{ $booking->vehicle->model }}</strong><br>
                        <small>{{ $booking->vehicle->registration_number }}</small>
                    </td>
                    <td>
                        {{ \Carbon\Carbon::parse($booking->start_date)->format('d M') }} -
                        {{ \Carbon\Carbon::parse($booking->end_date)->format('d M, Y') }}
                    </td>
                    <td style="text-align: right;">RM {{ number_format($booking->total_price, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="totals">
            <div class="grand-total">
                Amount Paid: RM {{ number_format($booking->total_price, 2) }}
            </div>
        </div>

        <div style="margin-top: 50px; font-size: 12px; color: #777; border-top: 1px solid #eee; padding-top: 10px;">
            Thank you for choosing Hasta Travel! Computer-generated invoice.
        </div>
    </div>
       <div style="margin-top: 50px;">
    <div style="float: right; width: 200px; text-align: center;">
        <hr>
        <p><strong>Authorized Signature</strong></p>
        <p>{{ Auth::user()->name }}</p> 
        <p>Hasta Travel Management</p>
    </div>
</div>
</body>
</html>
