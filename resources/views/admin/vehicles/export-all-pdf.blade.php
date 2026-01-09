<!DOCTYPE html>
<html>
<head>
    <title>Vehicles Export</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Vehicles Export - {{ date('Y-m-d') }}</h1>
    <table>
        <thead>
            <tr>
                <th>Vehicle ID</th>
                <th>Brand</th>
                <th>Model</th>
                <th>Plate Number</th>
                <th>Type</th>
                <th>Created Date</th>
                <th>Manufacturing Year</th>
                <th>Engine Capacity</th>
                <th>Rental Price</th>
                <th>Is Active</th>
            </tr>
        </thead>
        <tbody>
            @foreach($vehicles as $vehicle)
                @php
                    $vehicleType = 'Other';
                    if ($vehicle->car) {
                        $vehicleType = 'Car';
                    } elseif ($vehicle->motorcycle) {
                        $vehicleType = 'Motorcycle';
                    }
                @endphp
                <tr>
                    <td>{{ $vehicle->vehicleID }}</td>
                    <td>{{ $vehicle->vehicle_brand ?? 'N/A' }}</td>
                    <td>{{ $vehicle->vehicle_model ?? 'N/A' }}</td>
                    <td>{{ $vehicle->plate_number ?? 'N/A' }}</td>
                    <td>{{ $vehicleType }}</td>
                    <td>{{ $vehicle->created_date ? \Carbon\Carbon::parse($vehicle->created_date)->format('Y-m-d') : 'N/A' }}</td>
                    <td>{{ $vehicle->manufacturing_year ?? 'N/A' }}</td>
                    <td>{{ $vehicle->engineCapacity ? number_format($vehicle->engineCapacity, 2) . 'L' : 'N/A' }}</td>
                    <td>RM {{ number_format($vehicle->rental_price ?? 0, 2) }}</td>
                    <td>{{ ($vehicle->isActive ?? false) ? 'Yes' : 'No' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>







