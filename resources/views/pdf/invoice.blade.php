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
        .label-wide { font-weight: bold; color: #777; display: inline-block; width: 100px; }

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
        {{-- HEADER SECTION --}}
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

        {{-- DETAILS SECTION --}}
        <table class="info-table">
            <tr>
                {{-- LEFT: Customer Details --}}
                <td>
                    <div style="font-weight: bold; margin-bottom: 5px; text-transform: uppercase; color: #555;">Billed To:</div>
                    <strong>{{ $booking->customer->user->name ?? $booking->customer->fullname ?? 'Valued Customer' }}</strong><br>
                    
                    <span class="label">Phone:</span> {{ $booking->customer->phone_number ?? 'N/A' }}<br>
                    <span class="label">Email:</span> {{ $booking->customer->user->email ?? $booking->customer->email ?? 'N/A' }}<br>
                    <span class="label">Address:</span> {{ $booking->customer->address ?? 'N/A' }}
                </td>

                {{-- RIGHT: Rental Details (Updated with Time & Location) --}}
                <td class="text-right">
                    <div style="font-weight: bold; margin-bottom: 5px; text-transform: uppercase; color: #555;">Rental Details:</div>
                    <strong>{{ $booking->vehicle->vehicle_brand ?? 'Vehicle' }} {{ $booking->vehicle->vehicle_model ?? '' }}</strong><br>
                    <small>Plate: {{ $booking->vehicle->plate_number ?? '-' }}</small><br>

                    <div style="margin-top: 8px; font-size: 13px;">
                        {{-- START DATE & TIME --}}
                        <div>
                            <span class="label-wide">Pickup:</span> 
                            {{ \Carbon\Carbon::parse($booking->rental_start_date)->format('d M Y, h:i A') }}
                        </div>
                        <div style="margin-bottom: 4px;">
                            <span class="label-wide">Location:</span> 
                            {{ $booking->pickup_point ?? 'HQ' }}
                        </div>

                        {{-- RETURN DATE & TIME --}}
                        <div>
                            <span class="label-wide">Return:</span> 
                            {{ \Carbon\Carbon::parse($booking->rental_end_date)->format('d M Y, h:i A') }}
                        </div>
                        <div style="margin-bottom: 4px;">
                            <span class="label-wide">Location:</span> 
                            {{ $booking->return_point ?? 'HQ' }}
                        </div>

                        {{-- DURATION --}}
                        <div>
                            <span class="label-wide">Duration:</span> 
                            {{ $booking->duration }} Days
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        {{-- FINANCIAL TABLE --}}
        <table class="items-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="text-right">Amount (RM)</th>
                </tr>
            </thead>
            <tbody>
                @php
                    // Recalculate basic totals based on DB columns
                    $rental = $booking->rental_amount ?? 0;
                    $deposit = $booking->deposit_amount ?? 0; 
                    // Note: Your SQL table shows 'deposit_amount' is NULL in some rows, 
                    // so we default to 0 to avoid errors.
                    
                    $totalRequired = $rental + $deposit;
                    
                    // You passed $totalPaid from controller
                    $totalPaid = $totalPaid ?? 0; 
                    $outstandingBalance = max(0, $totalRequired - $totalPaid);
                @endphp

                {{-- Row 1: Vehicle Rental --}}
                <tr>
                    <td>
                        Vehicle Rental Charges
                        <br><small style="color:#777;">{{ $booking->duration }} Days x RM {{ number_format(($rental / max($booking->duration, 1)), 2) }} / day</small>
                    </td>
                    <td class="text-right">{{ number_format($rental, 2) }}</td>
                </tr>

                {{-- Row 2: Security Deposit (If Applicable) --}}
                @if($deposit > 0)
                <tr>
                    <td>Security Deposit (Refundable)</td>
                    <td class="text-right">{{ number_format($deposit, 2) }}</td>
                </tr>
                @endif

                {{-- Row 3: Add-ons (If Applicable) --}}
                @if(!empty($booking->addOns_item))
                <tr>
                    <td>Add-ons: {{ ucwords(str_replace('_', ' ', $booking->addOns_item)) }}</td>
                    <td class="text-right"><small>Included in Rental</small></td>
                </tr>
                @endif

                {{-- Subtotals & Payments --}}
                <tr>
                    <td><strong>Total Amount Required</strong></td>
                    <td class="text-right"><strong>{{ number_format($totalRequired, 2) }}</strong></td>
                </tr>

                <tr>
                    <td>Total Paid to Date</td>
                    <td class="text-right" style="color: green;">- {{ number_format($totalPaid, 2) }}</td>
                </tr>

                {{-- Vouchers --}}
                @if(isset($voucher) && $voucher)
                <tr>
                    <td class="discount-text">
                        Voucher Discount ({{ $voucher->code ?? 'LOYALTY' }})
                    </td>
                    <td class="text-right discount-text">
                        - {{ number_format($voucher->discount_amount ?? 0, 2) }}
                    </td>
                </tr>
                @endif

                {{-- Final Total --}}
                <tr class="total-row">
                    <td class="text-right">Outstanding Balance</td>
                    <td class="text-right" style="color: {{ $outstandingBalance > 0 ? '#d9534f' : 'green' }};">
                        RM {{ number_format($outstandingBalance, 2) }}
                    </td>
                </tr>
            </tbody>
        </table>

        <br>
        <p style="text-align: center; font-size: 12px; color: #aaa; margin-top: 30px;">
            Thank you for choosing Hasta Travel! <br>
            Please retain this invoice for your records.
        </p>
    </div>
</body>
</html>