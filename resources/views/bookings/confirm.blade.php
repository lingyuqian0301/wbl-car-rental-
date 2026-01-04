<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Booking Confirmation - HASTA Travel</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }

        .header p {
            font-size: 1.1em;
            opacity: 0.9;
        }

        .content {
            padding: 40px 30px;
        }

        .section {
            margin-bottom: 35px;
        }

        .section h2 {
            font-size: 1.3em;
            color: #333;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 3px solid #dc2626;
            display: inline-block;
        }

        .section-content {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin-top: 10px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e0e0e0;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: #666;
        }

        .info-value {
            color: #333;
            font-weight: 500;
        }

        .price-summary {
            background: #fee2e2;
            border: 2px solid #dc2626;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .price-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #fecdd3;
            font-size: 1em;
        }

        .price-row:last-child {
            border-bottom: none;
        }

        .price-label {
            color: #666;
            font-weight: 500;
        }

        .price-value {
            color: #333;
            font-weight: 600;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding-top: 15px;
            border-top: 2px solid #dc2626;
            margin-top: 15px;
            font-size: 1.3em;
        }

        .total-label {
            color: #333;
            font-weight: bold;
        }

        .total-value {
            color: #dc2626;
            font-weight: bold;
        }

        .actions {
            display: flex;
            gap: 15px;
            margin-top: 40px;
        }

        .btn {
            flex: 1;
            padding: 15px 30px;
            font-size: 1.1em;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            text-align: center;
            display: inline-block;
        }

        .btn-cancel {
            background-color: #d0d0d0;
            color: #333;
        }

        .btn-cancel:hover {
            background-color: #b0b0b0;
            transform: translateY(-2px);
        }

        .btn-confirm {
            background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(220, 38, 38, 0.3);
        }

        .btn-confirm:hover {
            box-shadow: 0 6px 20px rgba(220, 38, 38, 0.4);
            transform: translateY(-2px);
        }

        /* Loading Overlay Styles */
        #loadingOverlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(4px);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }

        #loadingOverlay.active {
            display: flex;
        }

        .loading-content {
            background: white;
            border-radius: 15px;
            padding: 50px 40px;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .spinner {
            width: 60px;
            height: 60px;
            margin: 0 auto 30px;
            border: 6px solid #f0f0f0;
            border-top: 6px solid #dc2626;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .loading-text h2 {
            color: #333;
            font-size: 1.5em;
            margin-bottom: 10px;
        }

        .loading-text p {
            color: #666;
            font-size: 1em;
        }

        .dots {
            display: inline-block;
            animation: dots 1.5s steps(4, end) infinite;
        }

        @keyframes dots {
            0%, 20% { content: '.'; }
            40% { content: '..'; }
            60% { content: '...'; }
            80%, 100% { content: ''; }
        }

        .addon-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            color: #333;
        }

        .addon-name {
            font-weight: 500;
        }

        .addon-price {
            color: #dc2626;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>Booking Confirmation</h1>
            <p>Please review your booking details before confirming</p>
        </div>

        <!-- Content -->
        <div class="content">
            @if(session('error'))
                <div style="background-color: #fee2e2; border: 1px solid #dc2626; color: #dc2626; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <strong>Error:</strong> {{ session('error') }}
                </div>
            @endif
            
            @if ($errors->any())
                <div style="background-color: #fee2e2; border: 1px solid #dc2626; color: #dc2626; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <ul style="list-style-type: none;">
                        @foreach ($errors->all() as $error)
                            <li>• {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            {{-- Debug: Show form action URL --}}
            @if(config('app.debug'))
                <div style="background-color: #e0f2fe; border: 1px solid #0284c7; color: #0369a1; padding: 10px; border-radius: 8px; margin-bottom: 20px; font-size: 12px;">
                    <strong>Debug Info:</strong><br>
                    Current URL: {{ request()->url() }}<br>
                    Form Action (route helper): {{ route('booking.finalize') }}<br>
                    Vehicle ID: {{ $vehicle->vehicleID ?? 'N/A' }}<br>
                    Start Date: {{ $bookingData['rental_start_date'] ?? $bookingData['start_date'] ?? 'EMPTY' }}<br>
                    End Date: {{ $bookingData['rental_end_date'] ?? $bookingData['end_date'] ?? 'EMPTY' }}<br>
                    Total Amount: {{ $bookingData['rental_amount'] ?? $bookingData['total_amount'] ?? 'EMPTY' }}
                </div>
            @endif
            <!-- Customer Information -->
            <div class="section">
                <h2>Customer Information</h2>
                <div class="section-content">
                    <div class="info-row">
                        <span class="info-label">Name:</span>
                        <span class="info-value">{{ auth()->user()->name }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Email:</span>
                        <span class="info-value">{{ auth()->user()->email }}</span>
                    </div>
                </div>
            </div>

            <!-- Vehicle Details -->
            <div class="section">
                <h2>Vehicle Details</h2>
                <div class="section-content">
                    <div class="info-row">
                        <span class="info-label">Vehicle:</span>
                        <span class="info-value">{{ $vehicle->vehicle_brand }} {{ $vehicle->vehicle_model }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Type:</span>
                        <span class="info-value">{{ $vehicle->vehicle_type }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Color:</span>
                        <span class="info-value">{{ $vehicle->color }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Plate Number:</span>
                        <span class="info-value">{{ $vehicle->plate_number }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Daily Rate:</span>
                        <span class="info-value" style="color: #dc2626; font-weight: bold;">RM {{ number_format($vehicle->rental_price, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Rental Period -->
            <div class="section">
                <h2>Rental Period</h2>
                <div class="section-content">
                    <div class="info-row">
                        <span class="info-label">Pick-up Date:</span>
                        <span class="info-value">{{ date('M d, Y', strtotime($bookingData['rental_start_date'] ?? $bookingData['start_date'] ?? '')) }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Return Date:</span>
                        <span class="info-value">{{ date('M d, Y', strtotime($bookingData['rental_end_date'] ?? $bookingData['end_date'] ?? '')) }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Duration:</span>
                        <span class="info-value">{{ $bookingData['duration'] ?? $bookingData['duration_days'] ?? 0 }} day(s)</span>
                    </div>
                </div>
            </div>

            <!-- Rental Locations -->
            <div class="section">
                <h2>Rental Locations</h2>
                <div class="section-content">
                    <div class="info-row">
                        <span class="info-label">Pick-up Location:</span>
                        <span class="info-value">{{ $bookingData['pickup_point'] }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Return Location:</span>
                        <span class="info-value">{{ $bookingData['return_point'] }}</span>
                    </div>
                </div>
            </div>

            <!-- Add-ons -->
            @if(count($addons) > 0)
            <div class="section">
                <h2>Add-ons Selected</h2>
                <div class="section-content">
                    @foreach($addons as $addon)
                    <div class="addon-item">
                        <span class="addon-name">{{ $addon['name'] }}</span>
                        <span class="addon-price">RM {{ number_format($addon['total'], 2) }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Price Summary -->
            <div class="price-summary">
                <h2 style="border-bottom: 2px solid #dc2626; padding-bottom: 10px; margin-bottom: 20px;">Price Summary</h2>
                @php
                    $duration = $bookingData['duration'] ?? $bookingData['duration_days'] ?? 1;
                    $addonsCharge = 0;
                    foreach($addons as $addon) {
                        $addonsCharge += $addon['total'] ?? 0;
                    }
                    $vehicleTotal = $vehicle->rental_price * $duration;
                    $totalAmount = $bookingData['rental_amount'] ?? $bookingData['total_amount'] ?? ($vehicleTotal + $addonsCharge);
                @endphp
                <div class="price-row">
                    <span class="price-label">Vehicle (RM {{ $vehicle->rental_price }} × {{ $duration }} days)</span>
                    <span class="price-value">RM {{ number_format($vehicleTotal, 2) }}</span>
                </div>
                @if(count($addons) > 0)
                <div class="price-row">
                    <span class="price-label">Add-ons Total</span>
                    <span class="price-value">RM {{ number_format($addonsCharge, 2) }}</span>
                </div>
                @endif
                <div class="total-row">
                    <span class="total-label">Total Amount</span>
                    <span class="total-value">RM {{ number_format($totalAmount, 2) }}</span>
                </div>
            </div>

            <!-- Action Buttons -->
         <div class="actions">
    <button class="btn btn-cancel" type="button" onclick="history.back()">Cancel</button>

    <form method="post" action="{{ route('booking.finalize') }}" style="flex: 1;" id="confirmForm" enctype="application/x-www-form-urlencoded">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <input type="hidden" name="vehicle_id" value="{{ $vehicle->vehicleID ?? $vehicle->id ?? 0 }}">
        <input type="hidden" name="start_date" value="{{ $bookingData['rental_start_date'] ?? $bookingData['start_date'] ?? now()->format('Y-m-d') }}">
        <input type="hidden" name="end_date" value="{{ $bookingData['rental_end_date'] ?? $bookingData['end_date'] ?? now()->addDay()->format('Y-m-d') }}">
        <input type="hidden" name="pickup_point" value="{{ $bookingData['pickup_point'] ?? 'Not specified' }}">
        <input type="hidden" name="return_point" value="{{ $bookingData['return_point'] ?? 'Not specified' }}">
        <input type="hidden" name="total_amount" value="{{ $bookingData['rental_amount'] ?? $bookingData['total_amount'] ?? 0 }}">

        @if(isset($addons))
            @foreach($addons as $index => $addon)
                <input type="hidden" name="addons[{{ $index }}][name]" value="{{ $addon['name'] }}">
                <input type="hidden" name="addons[{{ $index }}][price]" value="{{ $addon['total'] }}">
            @endforeach
        @endif
        <button type="submit" class="btn btn-confirm" style="width: 100%; margin: 0;" id="submitBtn">Confirm Booking</button>
    </form>
</div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay">
        <div class="loading-content">
            <div class="spinner"></div>
            <div class="loading-text">
                <h2>Processing Your Booking<span class="dots">...</span></h2>
                <p>Please wait while we save your reservation</p>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('confirmForm').addEventListener('submit', function(e) {
            // Show loading overlay when form is submitted
            document.getElementById('loadingOverlay').classList.add('active');
            document.getElementById('submitBtn').disabled = true;
            // Form submits naturally as POST
        });
    </script>
</body>
</html>
