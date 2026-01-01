<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $invoiceData->invoice_number ?? $booking->bookingID }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 14px; color: #333; }
        .invoice-box { max-width: 800px; margin: auto; padding: 30px; border: 1px solid #eee; }
        .header { display: flex; justify-content: space-between; margin-bottom: 20px; }
        .logo { font-size: 24px; font-weight: bold; color: #dc2626; }
        .invoice-details { text-align: right; }
        
        table { width: 100%; line-height: inherit; text-align: left; border-collapse: collapse; }
        table td { padding: 10px; vertical-align: top; }
        table tr.heading td { background: #eee; border-bottom: 1px solid #ddd; font-weight: bold; }
        table tr.item td { border-bottom: 1px solid #eee; }
        table tr.total td { border-top: 2px solid #333; font-weight: bold; }
        
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <div class="invoice-box">
        <table cellpadding="0" cellspacing="0">
            <tr class="top">
                <td colspan="3">
                    <table>
                        <tr>
                            <td class="title">
                                <span class="logo">HASTA TRAVEL</span>
                            </td>
                            <td class="text-right">
                                Invoice #: {{ $invoiceData->invoice_number ?? 'INV-'.$booking->bookingID }}<br>
                                Date: {{ \Carbon\Carbon::parse($invoiceData->issue_date ?? now())->format('d M, Y') }}<br>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr class="information">
                <td colspan="3">
                    <table>
                        <tr>
                            <td>
                                <strong>Billed To:</strong><br>
                                {{ $booking->customer->fullname ?? 'Customer' }}<br>
                                {{ $booking->customer->email ?? '' }}
                            </td>
                            <td class="text-right">
                                <strong>From:</strong><br>
                                Hasta Car Rental<br>
                                Johor Bahru, Malaysia
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr class="heading">
                <td>Vehicle</td>
                <td>Dates</td>
                <td class="text-right">Total</td>
            </tr>

            <tr class="item">
                <td>
                    {{-- FIXED: Using correct DB columns --}}
                    @if($booking->vehicle)
                        {{ $booking->vehicle->vehicle_brand }} {{ $booking->vehicle->vehicle_model }}
                        <br><small>{{ $booking->vehicle->vehicle_number ?? '' }}</small>
                    @else
                        <em>Vehicle details unavailable</em>
                    @endif
                </td>
                <td>
                    {{ \Carbon\Carbon::parse($booking->start_date)->format('d M') }} - 
                    {{ \Carbon\Carbon::parse($booking->end_date)->format('d M, Y') }}
                </td>
                <td class="text-right">
                    RM {{ number_format($booking->total_amount, 2) }}
                </td>
            </tr>

            <tr class="total">
                <td colspan="2"></td>
                <td class="text-right">
                    Total: RM {{ number_format($booking->total_amount, 2) }}
                </td>
            </tr>
        </table>
        
        <br>
        <p style="text-align: center; font-size: 12px; color: #777;">
            Thank you for choosing Hasta Travel! This is a computer-generated invoice.
        </p>
    </div>
</body>
</html>