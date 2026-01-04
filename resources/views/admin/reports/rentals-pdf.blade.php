<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rental Report</title>
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
        .summary {
            margin: 20px 0;
            padding: 15px;
            background: #f5f5f5;
            border-radius: 5px;
        }
        .summary-item {
            margin: 5px 0;
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
        <h1>Rental Report</h1>
        <p>Generated on: {{ date('d M Y H:i:s') }}</p>
    </div>

    <div class="summary">
        <h3>Summary</h3>
        <div class="summary-item"><strong>Total Bookings:</strong> {{ $summaries['totalBookings'] }}</div>
        <div class="summary-item"><strong>Total Revenue:</strong> RM {{ number_format($summaries['totalRevenue'], 2) }}</div>
        <div class="summary-item"><strong>Cancelled Bookings:</strong> {{ $summaries['cancelledBookings'] }}</div>
        <div class="summary-item"><strong>Most Frequent Vehicle:</strong> {{ $summaries['mostFrequentVehicle'] }}</div>
        <div class="summary-item"><strong>Most Frequent Car:</strong> {{ $summaries['mostFrequentCar'] }}</div>
        <div class="summary-item"><strong>Most Frequent Motorcycle:</strong> {{ $summaries['mostFrequentMotorcycle'] }}</div>
        <div class="summary-item"><strong>Peak Period:</strong> {{ $summaries['peakPeriod'] }}</div>
        <div class="summary-item"><strong>Most Active Faculty:</strong> {{ $summaries['mostActiveFaculty'] }} ({{ $summaries['facultyBookingCount'] }} bookings)</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Booking ID</th>
                <th>Customer Name</th>
                <th>Vehicle Brand & Model</th>
                <th>Plate No</th>
                <th>Booking Date</th>
                <th>Pickup Date</th>
                <th>Return Date</th>
                <th>Duration</th>
                <th>Payment Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bookings as $booking)
                @php
                    $vehicle = $booking->vehicle;
                    $totalPaid = $booking->payments()->where('payment_status', 'Verified')->sum('amount');
                @endphp
                <tr>
                    <td>#{{ $booking->bookingID ?? $booking->id }}</td>
                    <td>{{ $booking->user->name ?? 'Unknown' }}</td>
                    <td>{{ $vehicle ? (($vehicle->vehicle_brand ?? '') . ' ' . ($vehicle->vehicle_model ?? '')) : 'N/A' }}</td>
                    <td>{{ $vehicle ? ($vehicle->plate_number ?? $vehicle->plate_no ?? 'N/A') : 'N/A' }}</td>
                    <td>{{ $booking->rental_start_date ? \Carbon\Carbon::parse($booking->rental_start_date)->format('d M Y') : 'N/A' }}</td>
                    <td>{{ $booking->rental_start_date ? \Carbon\Carbon::parse($booking->rental_start_date)->format('d M Y') : 'N/A' }}</td>
                    <td>{{ $booking->rental_end_date ? \Carbon\Carbon::parse($booking->rental_end_date)->format('d M Y') : 'N/A' }}</td>
                    <td>{{ $booking->duration_days ?? 0 }} days</td>
                    <td>RM {{ number_format($totalPaid, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>This report was generated automatically by the Rental Management System</p>
    </div>
</body>
</html>






