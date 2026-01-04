@extends('layouts.admin')

@section('title', 'Vehicle Details')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h4 mb-1">{{ $vehicle->full_model }}</h1>
            <div class="text-muted small">
                Plate: {{ $vehicle->plate_number ?? 'N/A' }} · Status: <span class="badge bg-secondary">{{ $vehicle->availability_status ?? 'Unknown' }}</span>
            </div>
        </div>
        <a href="{{ url()->previous() }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    <!-- Dynamic Tabs -->
    <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#car-info" type="button" role="tab">
                <i class="bi bi-info-circle"></i> Car Info
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#owner-info" type="button" role="tab">
                <i class="bi bi-person"></i> Owner Info
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#booking-history" type="button" role="tab">
                <i class="bi bi-clock-history"></i> Booking History
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#calendar" type="button" role="tab">
                <i class="bi bi-calendar3"></i> Calendar
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#photos" type="button" role="tab">
                <i class="bi bi-images"></i> Car Photos
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#location" type="button" role="tab">
                <i class="bi bi-geo-alt"></i> Location
            </button>
        </li>
    </ul>

    <div class="tab-content">
        <!-- Car Info Tab -->
        <div class="tab-pane fade show active" id="car-info" role="tabpanel">
            <div class="row g-3">
                <!-- Vehicle Basic Info -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0"><i class="bi bi-car-front"></i> Vehicle Information</h5>
                        </div>
                        <div class="card-body">
                            <dl class="row mb-0">
                                <dt class="col-5">Brand:</dt>
                                <dd class="col-7">{{ $vehicle->brand }}</dd>
                                <dt class="col-5">Model:</dt>
                                <dd class="col-7">{{ $vehicle->model }}</dd>
                                <dt class="col-5">Registration:</dt>
                                <dd class="col-7">{{ $vehicle->registration_number }}</dd>
                                <dt class="col-5">Daily Rate:</dt>
                                <dd class="col-7">RM {{ number_format($vehicle->daily_rate, 2) }}</dd>
                                <dt class="col-5">Status:</dt>
                                <dd class="col-7">
                                    <span class="badge bg-secondary">{{ $vehicle->status }}</span>
                                </dd>
                                @if($vehicle->description)
                                    <dt class="col-5">Description:</dt>
                                    <dd class="col-7">{{ $vehicle->description }}</dd>
                                @endif
                            </dl>
                        </div>
                    </div>
                </div>

                <!-- Maintenance List Section -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0"><i class="bi bi-tools"></i> Maintenance List</h5>
                        </div>
                        <div class="card-body">
                            <!-- Service History -->
                            <div class="mb-3">
                                <h6 class="fw-semibold"><i class="bi bi-clock-history"></i> Service History</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Date</th>
                                                <th>Service Type</th>
                                                <th>Mileage</th>
                                                <th>Workshop</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-2">No service history recorded yet.</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Service Types -->
                            <div class="mb-3">
                                <h6 class="fw-semibold"><i class="bi bi-list-check"></i> Services</h6>
                                <ul class="list-unstyled mb-0">
                                    <li><i class="bi bi-battery-charging text-success"></i> Battery</li>
                                    <li><i class="bi bi-speedometer2 text-primary"></i> Meter History</li>
                                    <li><i class="bi bi-wrench text-info"></i> Other Service</li>
                                </ul>
                            </div>

                            <!-- Service Remainder -->
                            <div class="mb-3">
                                <h6 class="fw-semibold"><i class="bi bi-calendar-check"></i> Service Remainder</h6>
                                <div class="alert alert-info mb-0">
                                    <small>Next service date will be displayed here based on database records.</small>
                                </div>
                            </div>

                            <!-- Renewal -->
                            <div>
                                <h6 class="fw-semibold"><i class="bi bi-arrow-repeat"></i> Renewal</h6>
                                <div class="small text-muted">
                                    <div>Road Tax: <span class="text-danger">Not set</span></div>
                                    <div>Insurance: <span class="text-danger">Not set</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Documentation Section -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="bi bi-file-earmark-text"></i> Documentation</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="border rounded p-3 text-center">
                                        <i class="bi bi-shield-check fs-1 text-primary"></i>
                                        <h6 class="mt-2">Insurance</h6>
                                        <p class="small text-muted mb-2">Insurance documents and expiry date</p>
                                        <button class="btn btn-sm btn-outline-primary">View/Upload</button>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="border rounded p-3 text-center">
                                        <i class="bi bi-file-earmark-text fs-1 text-success"></i>
                                        <h6 class="mt-2">Grant</h6>
                                        <p class="small text-muted mb-2">Vehicle grant documents</p>
                                        <button class="btn btn-sm btn-outline-success">View/Upload</button>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="border rounded p-3 text-center">
                                        <i class="bi bi-receipt fs-1 text-warning"></i>
                                        <h6 class="mt-2">Road Tax</h6>
                                        <p class="small text-muted mb-2">Road tax documents and expiry</p>
                                        <button class="btn btn-sm btn-outline-warning">View/Upload</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Owner Info Tab -->
        <div class="tab-pane fade" id="owner-info" role="tabpanel">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-person-circle"></i> Owner Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <dl class="row">
                                <dt class="col-4">Owner Name:</dt>
                                <dd class="col-8">-</dd>
                                <dt class="col-4">Contact:</dt>
                                <dd class="col-8">-</dd>
                                <dt class="col-4">Email:</dt>
                                <dd class="col-8">-</dd>
                                <dt class="col-4">Address:</dt>
                                <dd class="col-8">-</dd>
                            </dl>
                        </div>
                        <div class="col-md-6">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> Owner information will be displayed here once integrated with the owner management system.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Booking History Tab -->
        <div class="tab-pane fade" id="booking-history" role="tabpanel">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-calendar-check"></i> Booking History</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Customer</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Duration</th>
                                    <th>Status</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($vehicle->bookings as $booking)
                                    <tr>
                                        <td>#{{ $booking->id }}</td>
                                        <td>{{ $booking->user->name ?? 'N/A' }}</td>
                                        <td>{{ $booking->start_date->format('d M Y') }}</td>
                                        <td>{{ $booking->end_date->format('d M Y') }}</td>
                                        <td>{{ $booking->start_date->diffInDays($booking->end_date) }} days</td>
                                        <td>
                                            <span class="badge {{ $booking->status === 'Confirmed' ? 'bg-success' : ($booking->status === 'Pending' ? 'bg-warning text-dark' : ($booking->status === 'Cancelled' ? 'bg-danger' : 'bg-info')) }}">
                                                {{ $booking->status }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($booking->payment)
                                                RM {{ number_format($booking->payment->amount, 2) }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            <i class="bi bi-calendar-x fs-1 d-block mb-2"></i>
                                            No bookings recorded yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Calendar Tab -->
        <div class="tab-pane fade" id="calendar" role="tabpanel">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-calendar3"></i> Booking Calendar</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-3">
                        <i class="bi bi-info-circle"></i> Calendar showing booked dates for this vehicle. Dates highlighted in red indicate booked periods.
                    </div>
                    <div class="row">
                        <div class="col-md-8">
                            <div class="border rounded p-3 bg-light" style="min-height: 400px;">
                                <div class="text-center text-muted py-5">
                                    <i class="bi bi-calendar3 fs-1 d-block mb-2"></i>
                                    <p>Calendar view will be displayed here.</p>
                                    <p class="small">Booked dates: 
                                        @forelse($vehicle->bookings as $booking)
                                            <span class="badge bg-danger">{{ $booking->start_date->format('d M') }} - {{ $booking->end_date->format('d M Y') }}</span>
                                        @empty
                                            <span class="text-muted">No bookings</span>
                                        @endforelse
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <h6 class="fw-semibold mb-3">Upcoming Bookings</h6>
                            @forelse($vehicle->bookings->where('start_date', '>=', now())->take(5) as $booking)
                                <div class="card mb-2">
                                    <div class="card-body p-2">
                                        <small class="text-muted d-block">{{ $booking->start_date->format('d M Y') }} - {{ $booking->end_date->format('d M Y') }}</small>
                                        <small class="fw-semibold">{{ $booking->user->name ?? 'N/A' }}</small>
                                    </div>
                                </div>
                            @empty
                                <p class="text-muted small">No upcoming bookings.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Car Photos Tab -->
        <div class="tab-pane fade" id="photos" role="tabpanel">
            <div class="card">
                <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-images"></i> Car Photos</h5>
                    <button class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#uploadPhotoModal">
                        <i class="bi bi-plus-circle"></i> Upload Photo
                    </button>
                </div>
                <div class="card-body">
                    <div class="row g-3" id="photoGallery">
                        <!-- Photo items will be displayed here -->
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body text-center text-muted">
                                    <i class="bi bi-image fs-1 d-block mb-2"></i>
                                    <p class="small mb-0">No photos uploaded yet.</p>
                                    <p class="small">Click "Upload Photo" to add images.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Location Tab -->
        <div class="tab-pane fade" id="location" role="tabpanel">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="bi bi-geo-alt"></i> Realtime GPS Location</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-3">
                        <i class="bi bi-info-circle"></i> Real-time location tracking using GPS tracker installed in the vehicle.
                    </div>
                    <div class="row">
                        <div class="col-md-8">
                            <div class="ratio ratio-16x9 mb-3">
                                <div class="border rounded bg-light d-flex align-items-center justify-content-center">
                                    <div class="text-center text-muted">
                                        <i class="bi bi-map fs-1 d-block mb-2"></i>
                                        <p>Map will be displayed here</p>
                                        <p class="small">Integrate with GPS tracker API (Google Maps, OpenStreetMap, etc.)</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Location Details</h6>
                                </div>
                                <div class="card-body">
                                    <dl class="row mb-0 small">
                                        <dt class="col-5">Latitude:</dt>
                                        <dd class="col-7">-</dd>
                                        <dt class="col-5">Longitude:</dt>
                                        <dd class="col-7">-</dd>
                                        <dt class="col-5">Last Update:</dt>
                                        <dd class="col-7">-</dd>
                                        <dt class="col-5">Status:</dt>
                                        <dd class="col-7">
                                            <span class="badge bg-secondary">Offline</span>
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Photo Modal -->
    <div class="modal fade" id="uploadPhotoModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Upload Vehicle Photo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="#" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Select Photo</label>
                            <input type="file" name="photo" class="form-control" accept="image/*" required>
                            <small class="text-muted">Supported formats: JPG, PNG, GIF. Max size: 5MB</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Photo Type</label>
                            <select name="photo_type" class="form-select">
                                <option value="front">Front View</option>
                                <option value="side">Side View</option>
                                <option value="rear">Rear View</option>
                                <option value="interior">Interior</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description (Optional)</label>
                            <textarea name="description" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Upload Photo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    // Initialize tab functionality
    document.addEventListener('DOMContentLoaded', function() {
        // You can add custom JavaScript here for photo upload, calendar, etc.
    });
</script>
@endsection
