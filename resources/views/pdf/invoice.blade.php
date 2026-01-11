<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $invoiceData->invoice_number ?? 'INV-'.str_pad($booking->bookingID, 6, '0', STR_PAD_LEFT) }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.6;
            background: #f5f5f5;
        }
        .invoice-container {
            max-width: 900px;
            margin: 20px auto;
            background: white;
            padding: 40px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }

        /* HEADER SECTION */
        .header-section {
            border-bottom: 3px solid #800020;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .company-info {
            margin-bottom: 15px;
        }
        .company-name {
            font-size: 32px;
            font-weight: bold;
            color: #800020;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 5px;
        }
        .company-details {
            font-size: 11px;
            color: #666;
            line-height: 1.8;
        }
        .invoice-header {
            text-align: right;
            margin-top: 10px;
        }
        .invoice-title {
            font-size: 36px;
            font-weight: bold;
            color: #800020;
            margin-bottom: 10px;
        }
        .invoice-meta {
            font-size: 12px;
            color: #555;
            line-height: 2;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 11px;
            margin-top: 5px;
        }
        .status-paid { background: #28a745; color: white; }
        .status-pending { background: #ffc107; color: #333; }
        .status-confirmed { background: #17a2b8; color: white; }

        /* TWO COLUMN LAYOUT */
        .two-column {
            display: table;
            width: 100%;
            margin-bottom: 25px;
        }
        .column {
            display: table-cell;
            vertical-align: top;
            width: 50%;
            padding: 15px;
        }
        .column-left {
            border-right: 1px solid #eee;
            padding-right: 25px;
        }
        .column-right {
            padding-left: 25px;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #800020;
            text-transform: uppercase;
            margin-bottom: 12px;
            padding-bottom: 5px;
            border-bottom: 2px solid #800020;
        }
        .info-row {
            margin-bottom: 8px;
            font-size: 11px;
        }
        .info-label {
            font-weight: bold;
            color: #555;
            display: inline-block;
            width: 120px;
        }
        .info-value {
            color: #333;
        }

        /* VEHICLE SPECIFICATIONS */
        .vehicle-specs {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin-top: 10px;
        }
        .spec-grid {
            display: table;
            width: 100%;
        }
        .spec-item {
            display: table-cell;
            width: 50%;
            padding: 5px 0;
            font-size: 11px;
        }

        /* ITEMS TABLE */
        .items-section {
            margin: 30px 0;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .items-table thead {
            background: #800020;
            color: white;
        }
        .items-table th {
            padding: 12px 10px;
            text-align: left;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .items-table td {
            padding: 10px;
            border-bottom: 1px solid #eee;
            font-size: 11px;
        }
        .items-table tbody tr:hover {
            background: #f9f9f9;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .item-description {
            color: #333;
            font-weight: 500;
        }
        .item-details {
            font-size: 10px;
            color: #777;
            margin-top: 3px;
            font-style: italic;
        }
        .discount-row {
            background: #e8f5e9;
            color: #2e7d32;
            font-weight: bold;
        }
        .subtotal-row {
            background: #f5f5f5;
            font-weight: bold;
        }
        .total-row {
            background: #800020;
            color: white;
            font-weight: bold;
            font-size: 13px;
        }
        .total-row td {
            padding: 15px 10px;
        }

        /* PAYMENT HISTORY */
        .payments-section {
            margin: 30px 0;
        }
        .payments-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .payments-table th {
            background: #f5f5f5;
            padding: 10px;
            text-align: left;
            font-size: 11px;
            font-weight: bold;
            border-bottom: 2px solid #ddd;
        }
        .payments-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #eee;
            font-size: 11px;
        }
        .payment-status {
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }
        .status-verified { background: #28a745; color: white; }
        .status-pending-payment { background: #ffc107; color: #333; }
        .status-rejected { background: #dc3545; color: white; }

        /* SUMMARY BOX */
        .summary-box {
            background: #f9f9f9;
            border: 2px solid #800020;
            border-radius: 5px;
            padding: 20px;
            margin: 30px 0;
        }
        .summary-row {
            display: table;
            width: 100%;
            margin-bottom: 10px;
            font-size: 12px;
        }
        .summary-label {
            display: table-cell;
            font-weight: bold;
            color: #555;
            width: 60%;
        }
        .summary-value {
            display: table-cell;
            text-align: right;
            color: #333;
            font-weight: 600;
        }
        .summary-total {
            border-top: 2px solid #800020;
            padding-top: 15px;
            margin-top: 15px;
            font-size: 16px;
            font-weight: bold;
        }
        .summary-total .summary-label {
            color: #800020;
        }
        .summary-total .summary-value {
            color: #800020;
            font-size: 18px;
        }

        /* FOOTER */
        .footer-section {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #eee;
            text-align: center;
            font-size: 10px;
            color: #777;
        }
        .terms-section {
            text-align: left;
            margin-top: 20px;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 5px;
        }
        .terms-title {
            font-weight: bold;
            color: #800020;
            margin-bottom: 10px;
        }
        .terms-list {
            list-style: none;
            padding-left: 0;
        }
        .terms-list li {
            margin-bottom: 5px;
            padding-left: 20px;
            position: relative;
        }
        .terms-list li:before {
            content: "•";
            position: absolute;
            left: 0;
            color: #800020;
            font-weight: bold;
        }

        /* UTILITY CLASSES */
        .mb-10 { margin-bottom: 10px; }
        .mb-15 { margin-bottom: 15px; }
        .mt-20 { margin-top: 20px; }
        .text-muted { color: #777; }
        .text-success { color: #28a745; }
        .text-danger { color: #dc3545; }
        .text-warning { color: #ffc107; }
    </style>
</head>
<body>
    <div class="invoice-container">
        {{-- HEADER SECTION --}}
        <div class="header-section">
            <div class="company-info">
                <div class="company-name">HASTA TRAVEL & TOURS</div>
                <div class="company-details">
                    <strong>Registration:</strong> 1359376-T<br>
                    <strong>Address:</strong> HASTA HQ Office, Johor Bahru, Malaysia<br>
                    <strong>Email:</strong> support@hastatravel.com<br>
                    <strong>Phone:</strong> +60 12-345 6789
                </div>
            </div>
            <div class="invoice-header">
                <div class="invoice-title">INVOICE</div>
                <div class="invoice-meta">
                    <strong>Invoice No:</strong> {{ $invoiceData->invoice_number ?? 'INV-'.date('Ymd').'-'.$booking->bookingID }}<br>
                    <strong>Booking ID:</strong> #{{ str_pad($booking->bookingID, 6, '0', STR_PAD_LEFT) }}<br>
                    <strong>Issue Date:</strong> {{ ($invoiceData->issue_date ?? now())->format('d M Y') }}<br>
                    <strong>Status:</strong>
                    <span class="status-badge
                        @if($booking->booking_status == 'Confirmed') status-confirmed
                        @elseif($booking->booking_status == 'Pending') status-pending
                        @else status-paid @endif">
                        {{ strtoupper($booking->booking_status ?? 'PENDING') }}
                    </span>
                </div>
            </div>
        </div>

        {{-- CUSTOMER & RENTAL DETAILS --}}
        <div class="two-column">
            <div class="column column-left">
                <div class="section-title">Billed To</div>
                <div class="info-row">
                    <span class="info-label">Name:</span>
                    <span class="info-value">{{ $user->name ?? $customer->user->name ?? 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span class="info-value">{{ $user->email ?? $customer->user->email ?? 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Phone:</span>
                    <span class="info-value">{{ $customer->phone_number ?? 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Address:</span>
                    <span class="info-value">{{ $customer->address ?? 'N/A' }}</span>
                </div>
                @if($localstudent)
                    <div class="info-row">
                        <span class="info-label">IC Number:</span>
                        <span class="info-value">{{ $localCustomer->ic_no ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">State:</span>
                        <span class="info-value">{{ $localCustomer->stateOfOrigin ?? 'N/A' }}</span>
                    </div>
                @endif
                @if($internationalCustomer)
                    <div class="info-row">
                        <span class="info-label">Passport:</span>
                        <span class="info-value">{{ $internationalCustomer->passport_no ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Country:</span>
                        <span class="info-value">{{ $internationalCustomer->countryOfOrigin ?? 'N/A' }}</span>
                    </div>
                @endif
                @if($customer->customer_license)
                    <div class="info-row">
                        <span class="info-label">License Exp:</span>
                        <span class="info-value">{{ \Carbon\Carbon::parse($customer->customer_license)->format('d M Y') }}</span>
                    </div>
                @endif
            </div>

            <div class="column column-right">
                <div class="section-title">Rental Details</div>
                <div class="info-row">
                    <span class="info-label">Vehicle:</span>
                    <span class="info-value"><strong>{{ $vehicle->vehicle_brand ?? 'N/A' }} {{ $vehicle->vehicle_model ?? '' }}</strong></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Plate Number:</span>
                    <span class="info-value">{{ $vehicle->plate_number ?? 'N/A' }}</span>
                </div>

                <div class="vehicle-specs">
                    <div class="spec-grid">
                        <div class="spec-item">
                            <strong>Type:</strong> {{ $vehicle->vehicleType ?? 'N/A' }}<br>
                            <strong>Color:</strong> {{ $vehicle->color ?? 'N/A' }}<br>
                            <strong>Year:</strong> {{ $vehicle->manufacturing_year ?? 'N/A' }}
                        </div>
                        <div class="spec-item">
                            <strong>Engine:</strong> {{ $vehicle->engineCapacity ?? 'N/A' }}L<br>
                @if(isset($vehicle->car) && $vehicle->car)
                            <strong>Seats:</strong> {{ $vehicle->car->seating_capacity ?? 'N/A' }}<br>
                            <strong>Transmission:</strong> {{ ucfirst($vehicle->car->transmission ?? 'N/A') }}
                        @elseif(isset($vehicle->motorcycle) && $vehicle->motorcycle)
                            <strong>Type:</strong> {{ ucfirst($vehicle->motorcycle->motor_type ?? 'N/A') }}
                        @endif
                        </div>
                    </div>
                </div>

                <div class="info-row mt-20">
                    <span class="info-label">Pickup Date:</span>
                    <span class="info-value"><strong>{{ \Carbon\Carbon::parse($booking->rental_start_date)->format('d M Y, h:i A') }}</strong></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Pickup Location:</span>
                    <span class="info-value">{{ $booking->pickup_point ?? 'HASTA HQ Office' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Return Date:</span>
                    <span class="info-value"><strong>{{ \Carbon\Carbon::parse($booking->rental_end_date)->format('d M Y, h:i A') }}</strong></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Return Location:</span>
                    <span class="info-value">{{ $booking->return_point ?? 'HASTA HQ Office' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Duration:</span>
                    <span class="info-value"><strong>{{ $booking->duration ?? 1 }} Day(s)</strong></span>
                </div>
            </div>
        </div>

        {{-- ITEMS BREAKDOWN --}}
        <div class="items-section">
            <div class="section-title">Invoice Items</div>
            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width: 50%;">Description</th>
                        <th style="width: 15%;" class="text-center">Duration</th>
                        <th style="width: 15%;" class="text-right">Rate</th>
                        <th style="width: 20%;" class="text-right">Amount (RM)</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Vehicle Rental --}}
                    <tr>
                        <td>
                            <div class="item-description">Vehicle Rental</div>
                            <div class="item-details">{{ $vehicle->vehicle_brand }} {{ $vehicle->vehicle_model }} ({{ $vehicle->plate_number }})</div>
                        </td>
                        <td class="text-center">{{ $booking->duration ?? 1 }} day(s)</td>
                        <td class="text-right">RM {{ number_format($dailyRate, 2) }}</td>
                        <td class="text-right"><strong>RM {{ number_format($rentalBase, 2) }}</strong></td>
                    </tr>

                    {{-- Add-ons Breakdown --}}
                    @if(!empty($addonsBreakdown))
                        @foreach($addonsBreakdown as $addon)
                            <tr>
                                <td>
                                    <div class="item-description">{{ $addon['name'] }}</div>
                                    <div class="item-details">Additional Equipment</div>
                                </td>
<!-- {{-- Fix: Use Null Coalescing Operator (??) to prevent crash --}} -->
<td class="text-center">{{ $addon['duration'] ?? $booking->duration }} day(s)</td>
<td class="text-right">
    RM {{ number_format($addon['daily_price'] ?? 0, 2) }}/day
</td>
<td class="text-right">
    RM {{ number_format($addon['total'] ?? (($addon['daily_price'] ?? 0) * ($addon['duration'] ?? $booking->duration)), 2) }}
</td>
                            </tr>
                        @endforeach
                    @endif

                    {{-- Pickup Surcharge --}}
                    @if($pickupSurcharge > 0)
                        <tr>
                            <td>
                                <div class="item-description">Pickup Location Surcharge</div>
                                <div class="item-details">{{ $booking->pickup_point ?? 'N/A' }}</div>
                            </td>
                            <td class="text-center">-</td>
                            <td class="text-right">-</td>
                            <td class="text-right">RM {{ number_format($pickupSurcharge, 2) }}</td>
                        </tr>
                    @endif

                    {{-- Subtotal Before Discount --}}
                    <tr class="subtotal-row">
                        <td colspan="3" class="text-right"><strong>Subtotal:</strong></td>
                        <td class="text-right"><strong>RM {{ number_format($baseAmount, 2) }}</strong></td>
                    </tr>

                    {{-- Voucher Discount --}}
                    @if($voucher && $discountAmount > 0)
                        <tr class="discount-row">
                            <td>
                                <div class="item-description">Voucher Discount</div>
                                <div class="item-details">
                                    @if($voucher->discount_type == 'PERCENT')
                                        {{ $voucher->discount_amount }}% Off (Loyalty Reward)
                                    @else
                                        Flat Discount (Loyalty Reward)
                                    @endif
                                </div>
                            </td>
                            <td class="text-center">-</td>
                            <td class="text-right">-</td>
                            <td class="text-right"><strong>- RM {{ number_format($discountAmount, 2) }}</strong></td>
                        </tr>
                    @endif

                    {{-- Subtotal After Discount --}}
                    @if($discountAmount > 0)
                        <tr class="subtotal-row">
                            <td colspan="3" class="text-right"><strong>Subtotal After Discount:</strong></td>
                            <td class="text-right"><strong>RM {{ number_format($subtotalAfterDiscount, 2) }}</strong></td>
                        </tr>
                    @endif

                    {{-- Security Deposit --}}
                    <tr>
                        <td>
                            <div class="item-description">Security Deposit</div>
                            <div class="item-details">Refundable upon vehicle return</div>
                        </td>
                        <td class="text-center">-</td>
                        <td class="text-right">-</td>
                        <td class="text-right">RM {{ number_format($depositAmount, 2) }}</td>
                    </tr>

                    {{-- Total Amount Due --}}
                    <tr class="total-row">
                        <td colspan="3" class="text-right">TOTAL AMOUNT DUE:</td>
                        <td class="text-right">RM {{ number_format($finalTotal, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- PAYMENT HISTORY --}}
        @if($allPayments->count() > 0)
        <div class="payments-section">
            <div class="section-title">Payment History</div>
            <table class="payments-table">
                <thead>
                    <tr>
                        <th style="width: 15%;">Date</th>
                        <th style="width: 20%;">Payment Type</th>
                        <th style="width: 15%;">Method</th>
                        <th style="width: 15%;" class="text-right">Amount (RM)</th>
                        <th style="width: 15%;">Status</th>
                        <th style="width: 20%;">Verified By</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($allPayments as $payment)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}</td>
                            <td>
                                @if($payment->total_amount == $depositAmount)
                                    Deposit
                                @elseif($payment->total_amount == $finalTotal)
                                    Full Payment
                                @else
                                    Balance Payment
                                @endif
                            </td>
                            <td>{{ $payment->payment_bank_name ?? 'Bank Transfer' }}</td>
                            <td class="text-right"><strong>RM {{ number_format($payment->total_amount ?? 0, 2) }}</strong></td>
                            <td>
                                <span class="payment-status
                                    @if($payment->payment_status == 'Verified') status-verified
                                    @elseif($payment->payment_status == 'Pending') status-pending-payment
                                    @else status-rejected @endif">
                                    {{ strtoupper($payment->payment_status ?? 'PENDING') }}
                                </span>
                            </td>
                            <td>
                                @if($payment->payment_status == 'Verified' && $payment->verified_by)
                                    Staff #{{ $payment->verified_by }}
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        {{-- FINANCIAL SUMMARY --}}
        <div class="summary-box">
            <div class="section-title" style="border-bottom: none; margin-bottom: 15px;">Financial Summary</div>

            <div class="summary-row">
                <div class="summary-label">Total Amount Due:</div>
                <div class="summary-value">RM {{ number_format($finalTotal, 2) }}</div>
            </div>

            <div class="summary-row">
                <div class="summary-label">Total Paid:</div>
                <div class="summary-value text-success">RM {{ number_format($totalPaid, 2) }}</div>
            </div>

            @if($discountAmount > 0)
            <div class="summary-row">
                <div class="summary-label">Discount Applied:</div>
                <div class="summary-value text-success">- RM {{ number_format($discountAmount, 2) }}</div>
            </div>
            @endif

            <div class="summary-row summary-total">
                <div class="summary-label">Outstanding Balance:</div>
                <div class="summary-value {{ $outstandingBalance > 0 ? 'text-danger' : 'text-success' }}">
                    RM {{ number_format($outstandingBalance, 2) }}
                </div>
            </div>
        </div>

        {{-- TERMS AND CONDITIONS --}}
        <div class="terms-section">
            <div class="terms-title">Terms & Conditions</div>
            <ul class="terms-list">
                <li>Security deposit is refundable upon vehicle return in good condition.</li>
                <li>Late return fees may apply if vehicle is returned after the scheduled return date/time.</li>
                <li>Customer is responsible for any damages or violations during the rental period.</li>
                <li>Fuel should be returned at the same level as received, or refueling charges will apply.</li>
                <li>All payments must be verified before vehicle pickup.</li>
                <li>Booking cancellation policy applies as per company terms.</li>
            </ul>
        </div>

        {{-- FOOTER --}}
        <div class="footer-section">
            <p><strong>Thank you for choosing Hasta Travel & Tours!</strong></p>
            <p>For inquiries, please contact us at support@hastatravel.com or +60 12-345 6789</p>
            <p style="margin-top: 15px; color: #999;">This is a computer-generated invoice. No signature required.</p>
            <p style="color: #999;">Generated on {{ now()->format('d M Y, h:i A') }}</p>
        </div>
    </div>
</body>
</html>
