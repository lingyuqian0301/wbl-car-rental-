@extends('layouts.admin')

@section('title', 'Customer Documents')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">
            <i class="bi bi-file-earmark-text"></i> Customer Documents - {{ ucfirst(str_replace('_', ' ', $documentType)) }}
        </h1>
        <a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Customer
        </a>
    </div>

    <!-- Customer Info Summary -->
    <div class="card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <strong>Customer ID:</strong> #{{ $customer->id }}
                </div>
                <div class="col-md-3">
                    <strong>Name:</strong> {{ $customer->name }}
                </div>
                <div class="col-md-3">
                    <strong>Email:</strong> {{ $customer->email }}
                </div>
                <div class="col-md-3">
                    <strong>Phone:</strong> {{ $customer->phone ?? '-' }}
                </div>
            </div>
        </div>
    </div>

    <!-- Document Type Navigation -->
    <div class="card mb-3">
        <div class="card-body">
            <div class="btn-group w-100" role="group">
                <a href="{{ route('admin.customers.documents', [$customer, 'ic']) }}" 
                   class="btn {{ $documentType === 'ic' ? 'btn-danger' : 'btn-outline-danger' }}">
                    <i class="bi bi-card-text"></i> IC
                </a>
                <a href="{{ route('admin.customers.documents', [$customer, 'license']) }}" 
                   class="btn {{ $documentType === 'license' ? 'btn-danger' : 'btn-outline-danger' }}">
                    <i class="bi bi-card-heading"></i> License
                </a>
                <a href="{{ route('admin.customers.documents', [$customer, 'matric_card']) }}" 
                   class="btn {{ $documentType === 'matric_card' ? 'btn-danger' : 'btn-outline-danger' }}">
                    <i class="bi bi-person-badge"></i> Matric Card
                </a>
                <a href="{{ route('admin.customers.documents', [$customer, 'staff_card']) }}" 
                   class="btn {{ $documentType === 'staff_card' ? 'btn-danger' : 'btn-outline-danger' }}">
                    <i class="bi bi-briefcase"></i> Staff Card
                </a>
            </div>
        </div>
    </div>

    <!-- Customer Complete Details & Rental Record -->
    <div class="row g-3">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">Customer Complete Details</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <th width="40%">Customer ID:</th>
                            <td>#{{ $customer->id }}</td>
                        </tr>
                        <tr>
                            <th>Name:</th>
                            <td><strong>{{ $customer->name }}</strong></td>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <td>{{ $customer->email }}</td>
                        </tr>
                        <tr>
                            <th>Phone:</th>
                            <td>{{ $customer->phone ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Address:</th>
                            <td>{{ $customer->address ?? '-' }}</td>
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
                            <th>Status:</th>
                            <td>
                                @if($customer->is_blacklisted)
                                    <span class="badge bg-danger">Blacklisted</span>
                                @else
                                    <span class="badge bg-success">Active</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">Document: {{ ucfirst(str_replace('_', ' ', $documentType)) }}</h5>
                </div>
                <div class="card-body">
                    @php
                        $document = $customer->documents->where('document_type', $documentType)->first();
                    @endphp
                    @if($document)
                        <table class="table table-borderless mb-0">
                            <tr>
                                <th width="40%">Document Number:</th>
                                <td>{{ $document->document_number ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Expiry Date:</th>
                                <td>{{ $document->expiry_date ? $document->expiry_date->format('d M Y') : '-' }}</td>
                            </tr>
                            <tr>
                                <th>Document:</th>
                                <td>
                                    <a href="{{ Storage::url($document->document_path) }}" 
                                       target="_blank" 
                                       class="btn btn-sm btn-primary">
                                        <i class="bi bi-eye"></i> View Document
                                    </a>
                                </td>
                            </tr>
                        </table>
                    @else
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle"></i> No {{ str_replace('_', ' ', $documentType) }} document uploaded yet.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Complete Rental Record -->
    <div class="card mt-3">
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
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customer->bookings as $booking)
                            <tr>
                                <td>#{{ $booking->id }}</td>
                                <td>{{ $booking->vehicle->full_model }}</td>
                                <td>{{ $booking->start_date->format('d M Y') }}</td>
                                <td>{{ $booking->end_date->format('d M Y') }}</td>
                                <td>{{ $booking->duration_days }} days</td>
                                <td>RM {{ number_format($booking->total_price, 2) }}</td>
                                <td>
                                    <span class="badge {{ $booking->status === 'Confirmed' ? 'bg-success' : ($booking->status === 'Pending' ? 'bg-warning text-dark' : ($booking->status === 'Cancelled' ? 'bg-danger' : 'bg-info')) }}">
                                        {{ $booking->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    No rental records found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection










