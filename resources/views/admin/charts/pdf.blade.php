<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Charts Report</title>
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
        .chart-section {
            margin: 30px 0;
            page-break-inside: avoid;
        }
        .chart-title {
            font-size: 1.2rem;
            font-weight: bold;
            margin-bottom: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
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
    </style>
</head>
<body>
    <div class="header">
        <h1>Charts & Analytics Report</h1>
        <p>Generated on: {{ date('d M Y H:i:s') }}</p>
        <p>Chart Type: {{ ucfirst($activeTab) }}</p>
    </div>

    @if($activeTab === 'weekly')
    <div class="chart-section">
        <div class="chart-title">Weekly Rental Line Chart</div>
        <p><strong>Week:</strong> {{ $selectedWeek }}</p>
        <p><strong>Data Attribute:</strong> Number of bookings per day</p>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Number of Bookings</th>
                </tr>
            </thead>
            <tbody>
                @foreach($weeklyData as $data)
                    <tr>
                        <td>{{ $data['date'] }}</td>
                        <td>{{ $data['count'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if($activeTab === 'monthly')
    <div class="chart-section">
        <div class="chart-title">Monthly Rental Line Chart</div>
        <p><strong>Month:</strong> {{ $selectedMonth }}</p>
        <p><strong>Data Attribute:</strong> Number of bookings per day</p>
        <table>
            <thead>
                <tr>
                    <th>Day</th>
                    <th>Number of Bookings</th>
                </tr>
            </thead>
            <tbody>
                @foreach($monthlyData as $data)
                    <tr>
                        <td>{{ $data['date'] }}</td>
                        <td>{{ $data['count'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if($activeTab === 'faculty')
    <div class="chart-section">
        <div class="chart-title">Faculty Rental Pie Chart</div>
        <p><strong>Month:</strong> {{ $facultyMonth }}, <strong>Year:</strong> {{ $facultyYear }}</p>
        <p><strong>Data Attribute:</strong> Number of bookings by faculty</p>
        <table>
            <thead>
                <tr>
                    <th>Faculty</th>
                    <th>Number of Bookings</th>
                </tr>
            </thead>
            <tbody>
                @foreach($facultyData as $faculty => $count)
                    <tr>
                        <td>{{ $faculty }}</td>
                        <td>{{ $count }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if($activeTab === 'brand')
    <div class="chart-section">
        <div class="chart-title">Car Brand Rental Pie Chart</div>
        <p><strong>Month:</strong> {{ $brandMonth }}, <strong>Vehicle Type:</strong> {{ ucfirst($brandVehicleType) }}</p>
        <p><strong>Data Attribute:</strong> Number of bookings by brand and model</p>
        <table>
            <thead>
                <tr>
                    <th>Brand - Model</th>
                    <th>Number of Bookings</th>
                </tr>
            </thead>
            <tbody>
                @foreach($brandData as $brand => $count)
                    <tr>
                        <td>{{ $brand }}</td>
                        <td>{{ $count }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if($activeTab === 'comparison')
    <div class="chart-section">
        <div class="chart-title">Comparison Bar Chart (Latest 4 Months)</div>
        <p><strong>Data Attribute:</strong> Total, Car, and Motorcycle rentals comparison</p>
        <table>
            <thead>
                <tr>
                    <th>Month</th>
                    <th>Total</th>
                    <th>Cars</th>
                    <th>Motorcycles</th>
                </tr>
            </thead>
            <tbody>
                @foreach($comparisonData as $data)
                    <tr>
                        <td>{{ $data['month'] }}</td>
                        <td>{{ $data['total'] }}</td>
                        <td>{{ $data['cars'] }}</td>
                        <td>{{ $data['motorcycles'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</body>
</html>









