<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rental Agreement</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            line-height: 1.5;
            color: #000;
            padding: 20px;
            font-size: 11px;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
        }

        /* Header Section */
        .header-section {
            margin-bottom: 20px;
            border-bottom: 1px solid #000;
            padding-bottom: 10px;
        }

        .company-name {
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 5px;
        }

        .company-details {
            text-align: center;
            font-size: 10px;
            line-height: 1.4;
            margin-bottom: 10px;
        }

        .agreement-title {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            margin-top: 10px;
            margin-bottom: 10px;
        }

        /* Invoice Section */
        .invoice-section {
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .invoice-label {
            font-weight: bold;
        }

        .invoice-number {
            font-weight: bold;
            font-size: 12px;
            text-align: right; /* Ensure alignment in PDF */
        }

        /* Usage Details Table */
        .usage-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .usage-table th,
        .usage-table td {
            border: 1px solid #000;
            padding: 6px;
            text-align: left;
            font-size: 10px;
        }

        .usage-table th {
            background: #e5e5e5;
            font-weight: bold;
        }

        /* Customer Details Section */
        .section-title {
            font-weight: bold;
            font-size: 11px;
            margin-top: 10px;
            margin-bottom: 8px;
            background: #e5e5e5;
            padding: 4px;
        }

        .details-table {
            width: 100%;
            margin-bottom: 15px;
            font-size: 10px;
        }

        .details-table td {
            padding: 4px 6px;
            border-bottom: 1px solid #ccc;
        }

        .details-table td:first-child {
            font-weight: bold;
            width: 40%;
        }

        /* Pricing Summary */
        .pricing-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .pricing-table th,
        .pricing-table td {
            border: 1px solid #000;
            padding: 6px;
            text-align: right;
            font-size: 10px;
        }

        .pricing-table th {
            background: #e5e5e5;
            font-weight: bold;
            text-align: left;
        }

        .pricing-table td:first-child {
            text-align: left;
        }

        /* Terms Section */
        .terms-section {
            margin-top: 15px;
        }

        .terms-list {
            list-style-position: inside;
            margin-left: 0;
            font-size: 10px;
            line-height: 1.5;
        }

        .terms-list li {
            margin-bottom: 6px;
            text-align: justify;
        }

        /* Excess Fee Table */
        .excess-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 15px;
        }

        .excess-table th,
        .excess-table td {
            border: 1px solid #000;
            padding: 6px;
            text-align: left;
            font-size: 10px;
        }

        .excess-table th {
            background: #e5e5e5;
            font-weight: bold;
        }

        /* Signature Section */
        .signature-section {
            margin-top: 30px;
            border-top: 1px solid #000;
            padding-top: 15px;
        }

        .signature-boxes {
            width: 100%;
            margin-top: 40px;
        }

        /* Using table for layout consistency in PDF */
        .signature-table {
            width: 100%;
            margin-top: 40px;
        }
        
        .signature-table td {
            vertical-align: top;
            width: 45%;
            text-align: center;
            font-size: 10px;
        }

        .signature-line {
            border-bottom: 1px solid #000;
            margin-bottom: 5px;
            height: 40px;
            width: 80%;
            margin-left: auto;
            margin-right: auto;
        }

        .signature-name {
            margin-top: 10px;
            font-weight: bold;
        }

        .signature-date {
            margin-top: 5px;
        }

        .page-break {
            page-break-after: always;
        }

        @media print {
            body {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-section">
            <div class="company-name">HASTA TRAVEL & TOURS SDN. BHD.</div>
            <div class="company-details">
                <div>Registration No: 1359376-T</div>
                <div>Address: HASTA HQ Office, Johor Bahru, Malaysia</div>
                <div>Phone: +60 12-345 6789 | Email: support@hastatravel.com</div>
            </div>
            <div class="agreement-title">RENTAL AGREEMENT FORM</div>
        </div>

        <div class="invoice-section">
            <div class="invoice-label">Booking Reference:</div>
            <div class="invoice-number">#{{ str_pad($booking->bookingID, 6, '0', STR_PAD_LEFT) }}</div>
        </div>

        <div style="margin-bottom: 15px;">
            <div class="section-title">USAGE DETAILS</div>
            <table class="usage-table">
                <tr>
                    <td style="font-weight: bold;">Pick-up Date & Time:</td>
                    <td>{{ \Carbon\Carbon::parse($booking->rental_start_date)->format('d M Y, h:i A') }}</td>
                    <td style="font-weight: bold;">Pick-up Location:</td>
                    <td>{{ $booking->pickup_point ?? 'HASTA HQ Office' }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">Return Date & Time:</td>
                    <td>{{ \Carbon\Carbon::parse($booking->rental_end_date)->format('d M Y, h:i A') }}</td>
                    <td style="font-weight: bold;">Return Location:</td>
                    <td>{{ $booking->return_point ?? 'HASTA HQ Office' }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">Duration:</td>
                    <td>{{ $booking->duration ?? 1 }} day(s)</td>
                    <td></td>
                    <td></td>
                </tr>
            </table>
        </div>

        <div class="section-title">CUSTOMER DETAILS</div>
        <table class="details-table">
            <tr>
                <td>Full Name:</td>
                <td>{{ $customer->user->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Identity Card / Passport No.:</td>
                <td>{{ $identityNo ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Phone Number:</td>
                <td>{{ $phone ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Email Address:</td>
                <td>{{ $customer->user->email ?? 'N/A' }}</td>
            </tr>
        </table>

        <div class="section-title">CAR INFORMATION</div>
        <table class="details-table">
            <tr>
                <td>Vehicle:</td>
                <td>{{ $vehicle->vehicle_brand ?? '' }} {{ $vehicle->vehicle_model ?? '' }}</td>
            </tr>
            <tr>
                <td>Vehicle Type:</td>
                <td>{{ $vehicle->vehicleType ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Plate Number:</td>
                <td>{{ $vehicle->plate_number ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Color:</td>
                <td>{{ $vehicle->color ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Seating Capacity:</td>
                <td>
                    @if($vehicle->car)
                        {{ $vehicle->car->seating_capacity }} person(s)
                    @elseif($vehicle->motorcycle)
                        2 person(s)
                    @else
                        N/A
                    @endif
                </td>
            </tr>
            <tr>
                <td>Transmission:</td>
                <td>
                    @if($vehicle->car)
                        {{ ucfirst($vehicle->car->transmission) }}
                    @elseif($vehicle->motorcycle)
                        {{ ucfirst($vehicle->motorcycle->motor_type) }}
                    @else
                        N/A
                    @endif
                </td>
            </tr>
        </table>

        <div class="section-title">VEHICLE PRICE & PAYMENT SUMMARY</div>
        <table class="pricing-table">
            <tr>
                <th>Description</th>
                <th>Amount (RM)</th>
            </tr>
            <tr>
                <td>Daily Rental Rate</td>
                <td>{{ number_format($vehicle->rental_price ?? 0, 2) }}</td>
            </tr>
            <tr>
                <td>Number of Days</td>
                <td>{{ $booking->duration ?? 1 }}</td>
            </tr>
            @if(($booking->pickup_surcharge ?? 0) > 0)
            <tr>
                <td>Pickup Location Surcharge @if($booking->pickup_custom_location)(Others: {{ $booking->pickup_custom_location }})@endif</td>
                <td>{{ number_format($booking->pickup_surcharge, 2) }}</td>
            </tr>
            @endif
            @if(($booking->return_surcharge ?? 0) > 0)
            <tr>
                <td>Return Location Surcharge @if($booking->return_custom_location)(Others: {{ $booking->return_custom_location }})@endif</td>
                <td>{{ number_format($booking->return_surcharge, 2) }}</td>
            </tr>
            @endif
            <tr>
                <td>Total Rental Amount</td>
                <td>{{ number_format($booking->rental_amount ?? 0, 2) }}</td>
            </tr>
            <tr>
                <td>Security Deposit (Refundable)</td>
                <td>{{ number_format($booking->deposit_amount ?? 50, 2) }}</td>
            </tr>
            <tr style="font-weight: bold; background: #e5e5e5;">
                <td>Total Payable</td>
                <td>{{ number_format(($booking->rental_amount ?? 0) + ($booking->deposit_amount ?? 50), 2) }}</td>
            </tr>
        </table>

        <div class="terms-section">
            <div class="section-title">RENTAL AGREEMENT - TERMS & CONDITIONS</div>
            
            <div style="margin: 10px 0; font-weight: bold; font-size: 10px;">1. RENTAL RATES</div>
            <ol class="terms-list">
                <li>The rental rate quoted is per day for the vehicle specified above.</li>
                <li>Rental period is calculated on a 24-hour basis from pick-up to return time.</li>
                <li>Any partial day exceeding the booked period will be charged at the full daily rate.</li>
            </ol>

            <div style="margin: 10px 0; font-weight: bold; font-size: 10px;">2. SECURITY DEPOSIT</div>
            <ol class="terms-list">
                <li>A refundable security deposit of RM {{ number_format($booking->deposit_amount ?? 50, 2) }} is required at the time of pick-up.</li>
                <li>The deposit will be refunded upon safe return of the vehicle in original condition.</li>
                <li>Any damage or violations will be deducted from the deposit.</li>
            </ol>

            <div style="margin: 10px 0; font-weight: bold; font-size: 10px;">3. CANCELLATION POLICY</div>
            <ol class="terms-list">
                <li>Cancellations made 24 hours or more before the rental start date shall receive a full refund.</li>
                <li>Cancellations made less than 24 hours before the rental start date shall forfeit 20% of the total rental amount.</li>
                <li>No-shows will be charged the full rental amount.</li>
            </ol>

            <div style="margin: 10px 0; font-weight: bold; font-size: 10px;">4. EXCESS FEE</div>
            <ol class="terms-list">
                <li>The renter is responsible for any damage to the vehicle during the rental period.</li>
                <li>Excess fee charges will apply based on the nature and extent of damage.</li>
                <li>Please refer to the Excess Fee Schedule below.</li>
            </ol>

            <div style="margin: 10px 0; font-weight: bold; font-size: 10px;">5. FUEL POLICY</div>
            <ol class="terms-list">
                <li>The vehicle is provided with a full tank of fuel.</li>
                <li>The renter must return the vehicle with a full tank of fuel.</li>
                <li>Refueling charges will apply if the vehicle is returned with less fuel than provided.</li>
            </ol>

            <div style="margin: 10px 0; font-weight: bold; font-size: 10px;">6. VEHICLE CONDITION & MAINTENANCE</div>
            <ol class="terms-list">
                <li>The renter agrees to maintain the vehicle in good condition throughout the rental period.</li>
                <li>The vehicle must be returned clean, inside and out.</li>
                <li>Any cleaning required will be charged at RM 50.00 per hour.</li>
                <li>The renter is responsible for all regular maintenance such as refueling.</li>
            </ol>

            <div class="page-break"></div>

            <div style="margin: 10px 0; font-weight: bold; font-size: 10px;">7. DRIVER REQUIREMENTS</div>
            <ol class="terms-list">
                <li>The renter must have a valid driving license and must be at least 23 years old.</li>
                <li>Only the person named in this agreement is authorized to drive the vehicle.</li>
                <li>Unauthorized drivers are strictly prohibited.</li>
            </ol>

            <div style="margin: 10px 0; font-weight: bold; font-size: 10px;">8. INSURANCE & LIABILITY</div>
            <ol class="terms-list">
                <li>The rental includes comprehensive insurance coverage.</li>
                <li>The renter is responsible for the deductible amount in case of accident or damage.</li>
                <li>The renter assumes full responsibility for any consequences arising from the use of the vehicle.</li>
            </ol>

            <div style="margin: 10px 0; font-weight: bold; font-size: 10px;">9. TRAFFIC VIOLATIONS & FINES</div>
            <ol class="terms-list">
                <li>The renter is responsible for all traffic violations and parking fines incurred during the rental period.</li>
                <li>Amounts will be recovered from the security deposit or billed directly to the renter.</li>
            </ol>

            <div style="margin: 10px 0; font-weight: bold; font-size: 10px;">10. LATE RETURNS</div>
            <ol class="terms-list">
                <li>The renter agrees to return the vehicle on the agreed date and time.</li>
                <li>Late returns will be charged at the daily rental rate.</li>
                <li>Any delay beyond 2 hours from the return time will be charged for a full day.</li>
            </ol>

            <div style="margin: 10px 0; font-weight: bold; font-size: 10px;">11. SMOKING & PETS</div>
            <ol class="terms-list">
                <li>Smoking is strictly prohibited inside the vehicle.</li>
                <li>Pets are not allowed inside the vehicle without prior written approval.</li>
                <li>Violations will result in additional charges of RM 100.00 per violation.</li>
            </ol>

            <div style="margin: 10px 0; font-weight: bold; font-size: 10px;">12. EQUIPMENT & ACCESSORIES</div>
            <ol class="terms-list">
                <li>All equipment and accessories provided with the vehicle must be returned in the same condition.</li>
                <li>Loss or damage to equipment will result in replacement charges.</li>
            </ol>
        </div>

        <div style="margin-top: 15px;">
            <div class="section-title">EXCESS FEE SCHEDULE</div>
            <table class="excess-table">
                <tr>
                    <th>Damage Category</th>
                    <th>Excess Amount (RM)</th>
                </tr>
                <tr>
                    <td>Minor Scratches & Dents</td>
                    <td>100 - 300</td>
                </tr>
                <tr>
                    <td>Broken Window or Mirror</td>
                    <td>300 - 500</td>
                </tr>
                <tr>
                    <td>Significant Bodywork Damage</td>
                    <td>500 - 1,500</td>
                </tr>
                <tr>
                    <td>Major Accident Damage</td>
                    <td>As quoted by service center</td>
                </tr>
                <tr>
                    <td>Interior Damage (Seats, Dashboard)</td>
                    <td>200 - 800</td>
                </tr>
                <tr>
                    <td>Loss or Damage to Accessories</td>
                    <td>As quoted</td>
                </tr>
            </table>
        </div>

        <div class="signature-section">
            <div style="font-weight: bold; margin-bottom: 10px;">AGREEMENT ACCEPTANCE</div>
            <p style="font-size: 10px; line-height: 1.4; margin-bottom: 20px;">
                By signing below, the renter confirms that they have read, understood, and agree to all the terms and conditions outlined in this rental agreement. The renter acknowledges receipt of the vehicle in good condition and agrees to be fully responsible for the vehicle during the rental period.
            </p>

            <table class="signature-table">
                <tr>
                    <td>
                        <div class="signature-line"></div>
                        <div class="signature-name">{{ strtoupper($customer->user->name ?? 'RENTER NAME') }}</div>
                        <div class="signature-date">Renter Signature & Date: {{ now()->format('d M Y') }}</div>
                    </td>
                    <td></td> <td>
                        <div class="signature-line"></div>
                        <div class="signature-name">AUTHORIZED REPRESENTATIVE</div>
                        <div class="signature-date">HASTA Travel Authority & Date: {{ now()->format('d M Y') }}</div>
                    </td>
                </tr>
            </table>
        </div>

        <div style="margin-top: 40px; padding-top: 15px; border-top: 1px solid #ccc; text-align: center; font-size: 9px;">
            <p>This is an electronically generated document. Booking Reference: #{{ str_pad($booking->bookingID, 6, '0', STR_PAD_LEFT) }}</p>
            <p>For inquiries, please contact HASTA Travel at support@hastatravel.com or call +60 12-345 6789</p>
        </div>
    </div>
</body>
</html>