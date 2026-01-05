<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Customers Export</title>
    <style>
        @page {
            margin: 15mm;
            size: A4;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            color: #000;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 9px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 4px;
            text-align: left;
            word-wrap: break-word;
        }
        th {
            background-color: #f2f2f2;
            color: #000;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .no-data {
            text-align: center;
            padding: 20px;
            color: #666;
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
                <th style="width: 5%;">ID</th>
                <th style="width: 12%;">Name</th>
                <th style="width: 10%;">Email</th>
                <th style="width: 8%;">Phone</th>
                <th style="width: 8%;">Matric/Staff No</th>
                <th style="width: 10%;">Address</th>
                <th style="width: 7%;">State/Country</th>
                <th style="width: 8%;">IC/Passport</th>
                <th style="width: 8%;">College</th>
                <th style="width: 8%;">Faculty</th>
                <th style="width: 5%;">Bookings</th>
                <th style="width: 6%;">Status</th>
                <th style="width: 5%;">Active</th>
            </tr>
        </thead>
        <tbody>
            @forelse($customers as $customer)
                <tr>
                    <td>#{{ $customer->customerID }}</td>
                    <td>{{ $customer->user->name ?? 'N/A' }}</td>
                    <td>{{ $customer->user->email ?? 'N/A' }}</td>
                    <td>{{ $customer->user->phone ?? $customer->phone_number ?? 'N/A' }}</td>
                    @php
                        $localStudentDetails = $customer->localStudent->studentDetails ?? null;
                        $internationalStudentDetails = $customer->internationalStudent->studentDetails ?? null;
                    @endphp
                    <td>{{ $customer->localStudent->matric_number ?? ($customer->internationalStudent->matric_number ?? ($customer->localUtmStaff->staffID ?? ($customer->internationalUtmStaff->staffID ?? 'N/A'))) }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($customer->address ?? 'N/A', 30) }}</td>
                    <td>{{ $customer->local->stateOfOrigin ?? $customer->international->countryOfOrigin ?? 'N/A' }}</td>
                    <td>{{ $customer->local->ic_no ?? $customer->international->passport_no ?? 'N/A' }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($localStudentDetails->college ?? ($internationalStudentDetails->college ?? 'N/A'), 20) }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($localStudentDetails->faculty ?? ($internationalStudentDetails->faculty ?? 'N/A'), 20) }}</td>
                    <td>{{ $customer->bookings_count ?? 0 }}</td>
                    <td>{{ $customer->customer_status ?? 'active' }}</td>
                    <td>{{ ($customer->user->isActive ?? false) ? 'Yes' : 'No' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="13" class="no-data">No customers found</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
