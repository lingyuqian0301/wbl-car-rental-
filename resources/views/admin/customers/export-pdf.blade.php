<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Customers Export</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
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
            background-color: #b91c1c;
            color: white;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            color: #b91c1c;
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Customer List Export</h1>
        <p>Generated on: {{ date('d M Y H:i:s') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Customer ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Matric No</th>
                <th>Address</th>
                <th>State/Country</th>
                <th>IC/Passport</th>
                <th>Emergency Contact</th>
                <th>License</th>
                <th>College</th>
                <th>Faculty</th>
                <th>Programme</th>
                <th>Year of Study</th>
                <th>Booking Count</th>
                <th>Latest Booking</th>
                <th>Status</th>
                <th>Is Active</th>
            </tr>
        </thead>
        <tbody>
            @foreach($customers as $customer)
                <tr>
                    <td>#{{ $customer->customerID }}</td>
                    <td>{{ $customer->user->name ?? 'N/A' }}</td>
                    <td>{{ $customer->user->email ?? 'N/A' }}</td>
                    <td>{{ $customer->user->phone ?? $customer->phone_number ?? 'N/A' }}</td>
                    <td>{{ $customer->studentDetail->matric_number ?? $customer->localStudent->matric_number ?? 'N/A' }}</td>
                    <td>{{ $customer->address ?? 'N/A' }}</td>
                    <td>{{ $customer->local->stateOfOrigin ?? $customer->international->countryOfOrigin ?? 'N/A' }}</td>
                    <td>{{ $customer->local->ic_no ?? $customer->international->passport_no ?? 'N/A' }}</td>
                    <td>{{ $customer->emergency_contact ?? 'N/A' }}</td>
                    <td>{{ $customer->customer_license ?? 'N/A' }}</td>
                    <td>{{ $customer->studentDetail->college ?? 'N/A' }}</td>
                    <td>{{ $customer->studentDetail->faculty ?? 'N/A' }}</td>
                    <td>{{ $customer->studentDetail->programme ?? 'N/A' }}</td>
                    <td>{{ $customer->studentDetail->yearOfStudy ?? 'N/A' }}</td>
                    <td>{{ $customer->bookings_count ?? 0 }}</td>
                    <td>{{ $customer->bookings->first()?->rental_start_date?->format('d M Y') ?? 'N/A' }}</td>
                    <td>{{ $customer->customer_status ?? 'active' }}</td>
                    <td>{{ ($customer->user->isActive ?? false) ? 'Yes' : 'No' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>

