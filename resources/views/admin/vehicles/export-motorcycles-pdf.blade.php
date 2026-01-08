<!DOCTYPE html>
<html>
<head>
    <title>Motorcycles Export</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Motorcycles Export - {{ date('Y-m-d') }}</h1>
    <table>
        <thead>
            <tr>
                <th>Vehicle ID</th>
                <th>Brand</th>
                <th>Model</th>
                <th>Plate Number</th>
                <th>Motor Type</th>
                <th>Rental Price</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($motorcycles as $motorcycle)
                <tr>
                    <td>{{ $motorcycle->vehicleID }}</td>
                    <td>{{ $motorcycle->vehicle_brand ?? 'N/A' }}</td>
                    <td>{{ $motorcycle->vehicle_model ?? 'N/A' }}</td>
                    <td>{{ $motorcycle->plate_number ?? 'N/A' }}</td>
                    <td>{{ $motorcycle->motor_type ?? 'N/A' }}</td>
                    <td>RM {{ number_format($motorcycle->rental_price ?? 0, 2) }}</td>
                    <td>{{ ucfirst($motorcycle->availability_status ?? 'Unknown') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>



