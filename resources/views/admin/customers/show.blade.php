@extends('layouts.admin')

@section('title', 'Customer Details')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0"><i class="bi bi-person-circle"></i> Customer Details</h1>
        <div>
            <a href="{{ route('admin.customers.edit', $customer) }}" class="btn btn-sm btn-info">
                <i class="bi bi-pencil"></i> Edit
            </a>
            <a href="{{ route('admin.customers.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#info" type="button" role="tab">
                <i class="bi bi-info-circle"></i> Customer Info
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#blacklist" type="button" role="tab">
                <i class="bi bi-shield-exclamation"></i> Blacklist
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#documents" type="button" role="tab">
                <i class="bi bi-file-earmark-text"></i> Documents
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#rental-record" type="button" role="tab">
                <i class="bi bi-calendar-check"></i> Complete Rental Record
            </button>
        </li>
    </ul>

    <div class="tab-content">
        <!-- Customer Info Tab -->
        <div class="tab-pane fade show active" id="info" role="tabpanel">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0">Personal Information</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless mb-0">
                                <tr>
                                    <th width="40%">Customer ID:</th>
                                    <td>#{{ $customer->customerID }}</td>
                                </tr>
                                <tr>
                                    <th>Full Name:</th>
                                    <td><strong>{{ $customer->fullname }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Matric Number:</th>
                                    <td>{{ $customer->matric_number ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>IC Number:</th>
                                    <td>{{ $customer->ic_number ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Email:</th>
                                    <td>{{ $customer->email ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Phone:</th>
                                    <td>{{ $customer->phone ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Faculty:</th>
                                    <td>{{ $customer->faculty ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>College:</th>
                                    <td>{{ $customer->college ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Customer Type:</th>
                                    <td>{{ $customer->customer_type ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Registration Date:</th>
                                    <td>{{ $customer->registration_date ? \Carbon\Carbon::parse($customer->registration_date)->format('d M Y') : '-' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0">Additional Information</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless mb-0">
                                <tr>
                                    <th width="40%">Emergency Contact:</th>
                                    <td>{{ $customer->emergency_contact ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Country:</th>
                                    <td>{{ $customer->country ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Customer License:</th>
                                    <td>{{ $customer->customer_license ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Number of Bookings:</th>
                                    <td><span class="badge bg-info">{{ $customer->bookings->count() }}</span></td>
                                </tr>
                                <tr>
                                    <th>Latest Booking:</th>
                                    <td>
                                        @if($customer->bookings->count() > 0)
                                            @php
                                                $latestBooking = $customer->bookings->sortByDesc(function($booking) {
                                                    return $booking->rental_start_date ?? $booking->start_date ?? null;
                                                })->first();
                                                $bookingDate = $latestBooking->rental_start_date ?? $latestBooking->start_date ?? null;
                                            @endphp
                                            @if($bookingDate)
                                                {{ \Carbon\Carbon::parse($bookingDate)->format('d M Y') }}
                                            @else
                                                <span class="text-muted">No rental</span>
                                            @endif
                                        @else
                                            <span class="text-muted">No rental</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        @if($customer->customer_status === 'blacklist')
                                            <span class="badge bg-danger">Blacklisted</span>
                                        @elseif($customer->customer_status === 'deleted')
                                            <span class="badge bg-secondary">Deleted</span>
                                        @else
                                            <span class="badge bg-success">Active</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Blacklist Tab -->
        <div class="tab-pane fade" id="blacklist" role="tabpanel">
            <div class="card">
                <div class="card-header bg-{{ $customer->customer_status === 'blacklist' ? 'danger' : ($customer->customer_status === 'deleted' ? 'secondary' : 'success') }} text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-shield-exclamation"></i> 
                        @if($customer->customer_status === 'blacklist')
                            Blacklisted Customer
                        @elseif($customer->customer_status === 'deleted')
                            Deleted Customer
                        @else
                            Active Customer
                        @endif
                    </h5>
                </div>
                <div class="card-body">
                    @if($customer->customer_status === 'blacklist')
                        <div class="alert alert-danger">
                            <strong>This customer is blacklisted.</strong>
                        </div>
                        <form action="{{ route('admin.customers.toggle-blacklist', $customer) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-circle"></i> Remove from Blacklist
                            </button>
                        </form>
                    @elseif($customer->customer_status === 'deleted')
                        <div class="alert alert-secondary">
                            <strong>This customer has been deleted.</strong>
                        </div>
                    @else
                        <div class="alert alert-success">
                            <strong>This customer is active.</strong>
                        </div>
                        <form action="{{ route('admin.customers.toggle-blacklist', $customer) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-danger">
                                <i class="bi bi-x-circle"></i> Blacklist Customer
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <!-- Documents Tab -->
        <div class="tab-pane fade" id="documents" role="tabpanel">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="bi bi-file-earmark-text"></i> Customer Documents</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <a href="{{ route('admin.customers.documents', [$customer, 'ic']) }}" class="card text-decoration-none">
                                <div class="card-body text-center">
                                    <i class="bi bi-card-text fs-1 text-primary"></i>
                                    <h6 class="mt-2">IC</h6>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('admin.customers.documents', [$customer, 'license']) }}" class="card text-decoration-none">
                                <div class="card-body text-center">
                                    <i class="bi bi-card-heading fs-1 text-success"></i>
                                    <h6 class="mt-2">License</h6>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('admin.customers.documents', [$customer, 'matric_card']) }}" class="card text-decoration-none">
                                <div class="card-body text-center">
                                    <i class="bi bi-person-badge fs-1 text-info"></i>
                                    <h6 class="mt-2">Matric Card</h6>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('admin.customers.documents', [$customer, 'staff_card']) }}" class="card text-decoration-none">
                                <div class="card-body text-center">
                                    <i class="bi bi-briefcase fs-1 text-warning"></i>
                                    <h6 class="mt-2">Staff Card</h6>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Complete Rental Record Tab -->
        <div class="tab-pane fade" id="rental-record" role="tabpanel">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="bi bi-calendar-check"></i> Complete Rental Record</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Booking ID</th>
                                    <th>Vehicle</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Duration</th>
                                    <th>Total Price</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($customer->bookings as $booking)
                                    @php
                                        $vehicle = $booking->vehicle;
                                        $startDate = $booking->rental_start_date ?? $booking->start_date ?? null;
                                        $endDate = $booking->rental_end_date ?? $booking->end_date ?? null;
                                    @endphp
                                    <tr>
                                        <td>#{{ $booking->bookingID ?? $booking->id }}</td>
                                        <td>
                                            @if($vehicle)
                                                {{ $vehicle->full_model ?? ($vehicle->vehicle_brand ?? '') . ' ' . ($vehicle->vehicle_model ?? '') }}
                                            @else
                                                <span class="text-muted">Vehicle not found</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($startDate)
                                                {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }}
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($endDate)
                                                {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>{{ $booking->duration_days ?? 0 }} days</td>
                                        <td>RM {{ number_format($booking->total_price, 2) }}</td>
                                        <td>
                                            <span class="badge {{ $booking->booking_status === 'Confirmed' ? 'bg-success' : ($booking->booking_status === 'Pending' ? 'bg-warning text-dark' : ($booking->booking_status === 'Cancelled' ? 'bg-danger' : 'bg-info')) }}">
                                                {{ $booking->booking_status }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($vehicle)
                                                <a href="{{ route('admin.vehicles.show', $booking->vehicleID ?? $booking->vehicle_id ?? '') }}" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    View
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">
                                            No rental records found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection






