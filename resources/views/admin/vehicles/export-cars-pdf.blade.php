<!DOCTYPE html>
<html>
<head>
    <title>Cars Export</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Cars Export - {{ date('Y-m-d') }}</h1>
    <table>
        <thead>
            <tr>
                <th>Vehicle ID</th>
                <th>Brand</th>
                <th>Model</th>
                <th>Plate Number</th>
                <th>Seating</th>
                <th>Transmission</th>
                <th>Car Type</th>
                <th>Rental Price</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cars as $car)
                <tr>
                    <td>{{ $car->vehicleID }}</td>
                    <td>{{ $car->vehicle_brand ?? 'N/A' }}</td>
                    <td>{{ $car->vehicle_model ?? 'N/A' }}</td>
                    <td>{{ $car->plate_number ?? 'N/A' }}</td>
                    <td>{{ $car->seating_capacity ?? 'N/A' }}</td>
                    <td>{{ $car->transmission ?? 'N/A' }}</td>
                    <td>{{ $car->car_type ?? 'N/A' }}</td>
                    <td>RM {{ number_format($car->rental_price ?? 0, 2) }}</td>
                    <td>{{ ucfirst($car->availability_status ?? 'Unknown') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>




