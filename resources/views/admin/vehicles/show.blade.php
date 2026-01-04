@extends('layouts.admin')

@section('title', 'Vehicle Details')

@push('styles')
<style>
    :root {
        --hasta-red: #b91c1c;
        --hasta-red-dark: #7f1d1d;
        --hasta-rose: #fee2e2;
    }
    .vehicle-info-text {
        font-size: 0.875rem;
    }
    .document-cell {
        min-height: 250px;
        transition: transform 0.2s;
        border: 1px solid #e5e7eb;
    }
    .document-cell:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .calendar-container {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .calendar-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid var(--hasta-rose);
    }
    .calendar-nav-btn {
        background: var(--hasta-red);
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 6px;
        cursor: pointer;
        transition: background 0.2s;
    }
    .calendar-nav-btn:hover {
        background: var(--hasta-red-dark);
        color: white;
    }
    .calendar-month-year {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--hasta-red-dark);
    }
    .calendar-weekdays {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 5px;
        margin-bottom: 10px;
    }
    .calendar-weekday {
        text-align: center;
        font-weight: 600;
        color: var(--hasta-red-dark);
        padding: 8px;
        background: var(--hasta-rose);
        border-radius: 6px;
        font-size: 0.875rem;
    }
    .calendar-days {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 5px;
    }
    .calendar-day {
        aspect-ratio: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        position: relative;
        border: 2px solid transparent;
    }
    .calendar-day.other-month {
        color: #d1d5db;
        background: #f9fafb;
    }
    .calendar-day.current-month {
        background: white;
        color: #374151;
        border-color: #e5e7eb;
    }
    .calendar-day.current-month:hover {
        background: #f3f4f6;
        border-color: var(--hasta-red);
    }
    .calendar-day.today {
        background: var(--hasta-rose);
        color: var(--hasta-red-dark);
        border-color: var(--hasta-red);
        font-weight: 700;
    }
    .calendar-day.booked {
        background: var(--hasta-red);
        color: white;
        border-color: var(--hasta-red-dark);
        font-weight: 600;
    }
    .calendar-day.booked:hover {
        background: var(--hasta-red-dark);
        border-color: var(--hasta-red);
    }
    .calendar-day.available {
        background: #d1fae5;
        color: #065f46;
        border-color: #10b981;
    }
    .calendar-legend {
        display: flex;
        justify-content: center;
        gap: 20px;
        margin-top: 20px;
        flex-wrap: wrap;
    }
    .legend-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.875rem;
    }
    .legend-color {
        width: 20px;
        height: 20px;
        border-radius: 4px;
        border: 2px solid;
    }
    .legend-booked {
        background: var(--hasta-red);
        border-color: var(--hasta-red-dark);
    }
    .legend-available {
        background: #d1fae5;
        border-color: #10b981;
    }
    .legend-today {
        background: var(--hasta-rose);
        border-color: var(--hasta-red);
    }
    .upload-date {
        font-size: 0.75rem;
        color: #6b7280;
        margin-top: 8px;
    }
</style>
@endpush

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h4 mb-1">{{ $vehicle->vehicle_brand }} {{ $vehicle->vehicle_model }}</h1>
            <div class="text-muted small">
                Plate: {{ $vehicle->plate_number ?? 'N/A' }} · Status: <span class="badge bg-secondary">{{ ucfirst($vehicle->availability_status ?? 'Unknown') }}</span>
            </div>
        </div>
        <div class="d-flex gap-2">
            @if($vehicle->car)
                <a href="{{ route('admin.vehicles.cars.edit', $vehicle->vehicleID) }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-pencil"></i> Edit
                </a>
            @elseif($vehicle->motorcycle)
                <a href="{{ route('admin.vehicles.motorcycles.edit', $vehicle->vehicleID) }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-pencil"></i> Edit
                </a>
            @endif
        <a href="{{ url()->previous() }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

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
        <!-- Car Info Tab - Contains Vehicle Info, Maintenance, and Documentation -->
        <div class="tab-pane fade show active" id="car-info" role="tabpanel">
            <div class="row g-3">
                <!-- Vehicle Basic Info -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-car-front"></i> Vehicle Information</h5>
                            @if($vehicle->car)
                                <a href="{{ route('admin.vehicles.cars.edit', $vehicle->vehicleID) }}" class="btn btn-sm btn-light">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                            @elseif($vehicle->motorcycle)
                                <a href="{{ route('admin.vehicles.motorcycles.edit', $vehicle->vehicleID) }}" class="btn btn-sm btn-light">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                            @endif
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                            <dl class="row mb-0">
                                        <dt class="col-5">Vehicle Brand:</dt>
                                        <dd class="col-7">{{ $vehicle->vehicle_brand ?? 'N/A' }}</dd>
                                        
                                        <dt class="col-5">Vehicle Model:</dt>
                                        <dd class="col-7">{{ $vehicle->vehicle_model ?? 'N/A' }}</dd>
                                        
                                        <dt class="col-5">Plate Number:</dt>
                                        <dd class="col-7">{{ $vehicle->plate_number ?? 'N/A' }}</dd>
                                        
                                        <dt class="col-5">Registration Date:</dt>
                                        <dd class="col-7">{{ $vehicle->created_date ? \Carbon\Carbon::parse($vehicle->created_date)->format('d M Y') : 'N/A' }}</dd>
                                        
                                        <dt class="col-5">Available Status:</dt>
                                <dd class="col-7">
                                            <span class="badge {{ $vehicle->availability_status === 'available' ? 'bg-success' : ($vehicle->availability_status === 'rented' ? 'bg-warning text-dark' : ($vehicle->availability_status === 'maintenance' ? 'bg-info' : 'bg-secondary')) }}">
                                                {{ ucfirst($vehicle->availability_status ?? 'Unknown') }}
                                            </span>
                                </dd>
                                        
                                        <dt class="col-5">Created Date:</dt>
                                        <dd class="col-7">{{ $vehicle->created_date ? \Carbon\Carbon::parse($vehicle->created_date)->format('d M Y') : 'N/A' }}</dd>
                                        
                                        <dt class="col-5">Manufacturing Year:</dt>
                                        <dd class="col-7">{{ $vehicle->manufacturing_year ?? 'N/A' }}</dd>
                                        
                                        <dt class="col-5">Color:</dt>
                                        <dd class="col-7">{{ $vehicle->color ?? 'N/A' }}</dd>
                                        
                                        <dt class="col-5">Engine Capacity:</dt>
                                        <dd class="col-7">{{ $vehicle->engineCapacity ? number_format($vehicle->engineCapacity, 2) . 'L' : 'N/A' }}</dd>
                                        
                                        <dt class="col-5">Vehicle Type:</dt>
                                        <dd class="col-7">{{ $vehicle->vehicleType ?? 'N/A' }}</dd>
                                        
                                        <dt class="col-5">Rental Price:</dt>
                                        <dd class="col-7">RM {{ number_format($vehicle->rental_price ?? 0, 2) }}</dd>
                                        
                                        <dt class="col-5">Is Active:</dt>
                                    <dd class="col-7">
                                            <span class="badge {{ ($vehicle->isActive ?? false) ? 'bg-success' : 'bg-secondary' }}">
                                                {{ ($vehicle->isActive ?? false) ? 'Active' : 'Inactive' }}
                                            </span>
                                    </dd>
                                    </dl>
                                </div>
                                <div class="col-md-6">
                                    @if($vehicle->car)
                                        <h6 class="fw-semibold mb-3">Car Details</h6>
                                        <dl class="row mb-0">
                                            <dt class="col-5">Seating Capacity:</dt>
                                            <dd class="col-7">{{ $vehicle->car->seating_capacity ?? 'N/A' }}</dd>
                                            
                                            <dt class="col-5">Transmission:</dt>
                                            <dd class="col-7">{{ $vehicle->car->transmission ?? 'N/A' }}</dd>
                                            
                                            <dt class="col-5">Model:</dt>
                                            <dd class="col-7">{{ $vehicle->car->model ?? 'N/A' }}</dd>
                                            
                                            <dt class="col-5">Car Type:</dt>
                                            <dd class="col-7">{{ $vehicle->car->car_type ?? 'N/A' }}</dd>
                                        </dl>
                                    @elseif($vehicle->motorcycle)
                                        <h6 class="fw-semibold mb-3">Motorcycle Details</h6>
                                        <dl class="row mb-0">
                                            <dt class="col-5">Motor Type:</dt>
                                            <dd class="col-7">{{ $vehicle->motorcycle->motor_type ?? 'N/A' }}</dd>
                                        </dl>
                                @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Maintenance List Section -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-tools"></i> Maintenance List</h5>
                            <button type="button" class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#addMaintenanceModal">
                                <i class="bi bi-plus-circle"></i> Add Maintenance
                            </button>
                        </div>
                        <div class="card-body">
                            <!-- Service History -->
                            <div class="mb-3">
                                <h6 class="fw-semibold"><i class="bi bi-clock-history"></i> Service History</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead style="background: var(--hasta-red); color: white;">
                                            <tr>
                                                <th>Date</th>
                                                <th>Service Type</th>
                                                <th>Mileage</th>
                                                <th>Cost</th>
                                                <th>Next Due</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($vehicle->maintenances as $maintenance)
                                                <tr>
                                                    <td>{{ $maintenance->service_date ? \Carbon\Carbon::parse($maintenance->service_date)->format('d M Y') : 'N/A' }}</td>
                                                    <td>{{ $maintenance->service_type ?? 'N/A' }}</td>
                                                    <td>{{ $maintenance->mileage ?? 'N/A' }}</td>
                                                    <td>RM {{ number_format($maintenance->cost ?? 0, 2) }}</td>
                                                    <td>
                                                        @if($maintenance->next_due_date)
                                                            @php
                                                                $nextDue = \Carbon\Carbon::parse($maintenance->next_due_date);
                                                                $isDue = $nextDue->isPast();
                                                            @endphp
                                                            <span class="{{ $isDue ? 'text-danger fw-bold' : '' }}">
                                                                {{ $nextDue->format('d M Y') }}
                                                            </span>
                                                            @if($isDue)
                                                                <span class="badge bg-danger">Due</span>
                                                            @endif
                                                        @else
                                                            N/A
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <form method="POST" action="{{ route('admin.vehicles.maintenance.destroy', $maintenance->maintenanceID) }}" 
                                                              onsubmit="return confirm('Are you sure?');" class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center text-muted py-2">No service history recorded yet.</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Service Remainder -->
                            <div class="mb-3">
                                <h6 class="fw-semibold"><i class="bi bi-calendar-check"></i> Service Reminder</h6>
                                @php
                                    $upcomingServices = $vehicle->maintenances()
                                        ->whereNotNull('next_due_date')
                                        ->where('next_due_date', '>=', \Carbon\Carbon::today())
                                        ->orderBy('next_due_date', 'asc')
                                        ->get();
                                @endphp
                                @if($upcomingServices->isNotEmpty())
                                <div class="alert alert-info mb-0">
                                        @foreach($upcomingServices->take(3) as $service)
                                            <div class="small">
                                                <strong>{{ $service->service_type }}:</strong> 
                                                {{ \Carbon\Carbon::parse($service->next_due_date)->format('d M Y') }}
                                </div>
                                        @endforeach
                            </div>
                                @else
                                    <div class="alert alert-info mb-0">
                                        <small>No upcoming service reminders.</small>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Documentation Section -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0"><i class="bi bi-file-earmark-text"></i> Documentation</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <!-- Insurance Cell -->
                                <div class="col-md-3">
                                    <div class="card document-cell h-100">
                                        <div class="card-body text-center">
                                            <i class="bi bi-shield-check fs-1 d-block mb-2" style="color: var(--hasta-red);"></i>
                                            <h6 class="fw-semibold">Insurance</h6>
                                            @php
                                                $insuranceDoc = $vehicle->documents->where('document_type', 'insurance')->first();
                                            @endphp
                                            @if($insuranceDoc && $insuranceDoc->fileURL)
                                                <div class="mb-2">
                                                    <img src="{{ asset('storage/' . $insuranceDoc->fileURL) }}" 
                                                         alt="Insurance" 
                                                         class="img-fluid mb-2" 
                                                         style="max-height: 150px; border-radius: 6px;">
                                                </div>
                                                <div class="upload-date">
                                                    <i class="bi bi-calendar3"></i> Uploaded: {{ $insuranceDoc->upload_date ? \Carbon\Carbon::parse($insuranceDoc->upload_date)->format('d M Y') : 'N/A' }}
                                                </div>
                                                <div class="d-flex gap-2 justify-content-center mt-2 flex-wrap">
                                                    <a href="{{ asset('storage/' . $insuranceDoc->fileURL) }}" 
                                                       target="_blank" 
                                                       class="btn btn-sm" style="background: var(--hasta-red); color: white; border: none;">
                                                        <i class="bi bi-eye"></i> View
                                                    </a>
                                                    <button type="button" class="btn btn-sm" style="background: var(--hasta-red); color: white; border: none;" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#uploadDocumentModal"
                                                            onclick="setDocumentType('insurance')">
                                                        <i class="bi bi-upload"></i> Upload
                                                    </button>
                                                    <form method="POST" action="{{ route('admin.vehicles.documents.destroy', $insuranceDoc->documentID) }}" 
                                                          onsubmit="return confirm('Are you sure?');" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                                            <i class="bi bi-trash"></i> Delete
                                                        </button>
                                                    </form>
                                                </div>
                                            @else
                                                <p class="small text-muted mb-2">No document uploaded</p>
                                                <button type="button" class="btn btn-sm" style="background: var(--hasta-red); color: white; border: none;" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#uploadDocumentModal"
                                                        onclick="setDocumentType('insurance')">
                                                    <i class="bi bi-upload"></i> Upload
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Grant Cell -->
                                <div class="col-md-3">
                                    <div class="card document-cell h-100">
                                        <div class="card-body text-center">
                                            <i class="bi bi-file-earmark-text fs-1 d-block mb-2" style="color: var(--hasta-red);"></i>
                                            <h6 class="fw-semibold">Grant</h6>
                                            @php
                                                $grantDoc = $vehicle->documents->where('document_type', 'grant')->first();
                                            @endphp
                                            @if($grantDoc && $grantDoc->fileURL)
                                                <div class="mb-2">
                                                    <img src="{{ asset('storage/' . $grantDoc->fileURL) }}" 
                                                         alt="Grant" 
                                                         class="img-fluid mb-2" 
                                                         style="max-height: 150px; border-radius: 6px;">
                                                </div>
                                                <div class="upload-date">
                                                    <i class="bi bi-calendar3"></i> Uploaded: {{ $grantDoc->upload_date ? \Carbon\Carbon::parse($grantDoc->upload_date)->format('d M Y') : 'N/A' }}
                                                </div>
                                                <div class="d-flex gap-2 justify-content-center mt-2 flex-wrap">
                                                    <a href="{{ asset('storage/' . $grantDoc->fileURL) }}" 
                                                       target="_blank" 
                                                       class="btn btn-sm" style="background: var(--hasta-red); color: white; border: none;">
                                                        <i class="bi bi-eye"></i> View
                                                    </a>
                                                    <button type="button" class="btn btn-sm" style="background: var(--hasta-red); color: white; border: none;" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#uploadDocumentModal"
                                                            onclick="setDocumentType('grant')">
                                                        <i class="bi bi-upload"></i> Upload
                                                    </button>
                                                    <form method="POST" action="{{ route('admin.vehicles.documents.destroy', $grantDoc->documentID) }}" 
                                                          onsubmit="return confirm('Are you sure?');" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                                            <i class="bi bi-trash"></i> Delete
                                                        </button>
                                                    </form>
                                                </div>
                                            @else
                                                <p class="small text-muted mb-2">No document uploaded</p>
                                                <button type="button" class="btn btn-sm" style="background: var(--hasta-red); color: white; border: none;" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#uploadDocumentModal"
                                                        onclick="setDocumentType('grant')">
                                                    <i class="bi bi-upload"></i> Upload
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Road Tax Cell -->
                                <div class="col-md-3">
                                    <div class="card document-cell h-100">
                                        <div class="card-body text-center">
                                            <i class="bi bi-receipt fs-1 d-block mb-2" style="color: var(--hasta-red);"></i>
                                            <h6 class="fw-semibold">Road Tax</h6>
                                            @php
                                                $roadtaxDoc = $vehicle->documents->where('document_type', 'roadtax')->first();
                                            @endphp
                                            @if($roadtaxDoc && $roadtaxDoc->fileURL)
                                                <div class="mb-2">
                                                    <img src="{{ asset('storage/' . $roadtaxDoc->fileURL) }}" 
                                                         alt="Road Tax" 
                                                         class="img-fluid mb-2" 
                                                         style="max-height: 150px; border-radius: 6px;">
                                                </div>
                                                <div class="upload-date">
                                                    <i class="bi bi-calendar3"></i> Uploaded: {{ $roadtaxDoc->upload_date ? \Carbon\Carbon::parse($roadtaxDoc->upload_date)->format('d M Y') : 'N/A' }}
                                                </div>
                                                <div class="d-flex gap-2 justify-content-center mt-2 flex-wrap">
                                                    <a href="{{ asset('storage/' . $roadtaxDoc->fileURL) }}" 
                                                       target="_blank" 
                                                       class="btn btn-sm" style="background: var(--hasta-red); color: white; border: none;">
                                                        <i class="bi bi-eye"></i> View
                                                    </a>
                                                    <button type="button" class="btn btn-sm" style="background: var(--hasta-red); color: white; border: none;" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#uploadDocumentModal"
                                                            onclick="setDocumentType('roadtax')">
                                                        <i class="bi bi-upload"></i> Upload
                                                    </button>
                                                    <form method="POST" action="{{ route('admin.vehicles.documents.destroy', $roadtaxDoc->documentID) }}" 
                                                          onsubmit="return confirm('Are you sure?');" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                                            <i class="bi bi-trash"></i> Delete
                                                        </button>
                                                    </form>
                                                </div>
                                            @else
                                                <p class="small text-muted mb-2">No document uploaded</p>
                                                <button type="button" class="btn btn-sm" style="background: var(--hasta-red); color: white; border: none;" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#uploadDocumentModal"
                                                        onclick="setDocumentType('roadtax')">
                                                    <i class="bi bi-upload"></i> Upload
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Contract Cell -->
                                <div class="col-md-3">
                                    <div class="card document-cell h-100">
                                        <div class="card-body text-center">
                                            <i class="bi bi-file-earmark-contract fs-1 d-block mb-2" style="color: var(--hasta-red);"></i>
                                            <h6 class="fw-semibold">Contract</h6>
                                            @php
                                                $contractDoc = $vehicle->documents->where('document_type', 'contract')->first();
                                            @endphp
                                            @if($contractDoc && $contractDoc->fileURL)
                                                <div class="mb-2">
                                                    <img src="{{ asset('storage/' . $contractDoc->fileURL) }}" 
                                                         alt="Contract" 
                                                         class="img-fluid mb-2" 
                                                         style="max-height: 150px; border-radius: 6px;">
                                                </div>
                                                <div class="upload-date">
                                                    <i class="bi bi-calendar3"></i> Uploaded: {{ $contractDoc->upload_date ? \Carbon\Carbon::parse($contractDoc->upload_date)->format('d M Y') : 'N/A' }}
                                                </div>
                                                <div class="d-flex gap-2 justify-content-center mt-2 flex-wrap">
                                                    <a href="{{ asset('storage/' . $contractDoc->fileURL) }}" 
                                                       target="_blank" 
                                                       class="btn btn-sm" style="background: var(--hasta-red); color: white; border: none;">
                                                        <i class="bi bi-eye"></i> View
                                                    </a>
                                                    <button type="button" class="btn btn-sm" style="background: var(--hasta-red); color: white; border: none;" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#uploadDocumentModal"
                                                            onclick="setDocumentType('contract')">
                                                        <i class="bi bi-upload"></i> Upload
                                                    </button>
                                                    <form method="POST" action="{{ route('admin.vehicles.documents.destroy', $contractDoc->documentID) }}" 
                                                          onsubmit="return confirm('Are you sure?');" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                                            <i class="bi bi-trash"></i> Delete
                                                        </button>
                                                    </form>
                                                </div>
                                            @else
                                                <p class="small text-muted mb-2">No document uploaded</p>
                                                <button type="button" class="btn btn-sm" style="background: var(--hasta-red); color: white; border: none;" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#uploadDocumentModal"
                                                        onclick="setDocumentType('contract')">
                                                    <i class="bi bi-upload"></i> Upload
                                                </button>
                                            @endif
                                        </div>
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
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="bi bi-person-circle"></i> Owner Information</h5>
                </div>
                <div class="card-body">
                    @if($vehicle->owner)
                    <div class="row">
                        <div class="col-md-6">
                                <dl class="row mb-0">
                                    <dt class="col-5">Owner ID:</dt>
                                    <dd class="col-7">{{ $vehicle->owner->ownerID ?? 'N/A' }}</dd>
                                    
                                    <dt class="col-5">Owner Name:</dt>
                                    <dd class="col-7">
                                        @php
                                            $ownerName = 'N/A';
                                            if ($vehicle->owner && $vehicle->owner->personDetails) {
                                                $ownerName = $vehicle->owner->personDetails->fullname ?? 'N/A';
                                            }
                                        @endphp
                                        {{ $ownerName }}
                                    </dd>
                                    
                                    <dt class="col-5">IC No:</dt>
                                    <dd class="col-7">{{ $vehicle->owner->ic_no ?? 'N/A' }}</dd>
                                    
                                    <dt class="col-5">Contact:</dt>
                                    <dd class="col-7">{{ $vehicle->owner->contact_number ?? 'N/A' }}</dd>
                                    
                                    <dt class="col-5">Email:</dt>
                                    <dd class="col-7">{{ $vehicle->owner->email ?? 'N/A' }}</dd>
                                    
                                    <dt class="col-5">Bank Name:</dt>
                                    <dd class="col-7">{{ $vehicle->owner->bankname ?? 'N/A' }}</dd>
                                    
                                    <dt class="col-5">Bank Account No:</dt>
                                    <dd class="col-7">{{ $vehicle->owner->bank_acc_number ?? 'N/A' }}</dd>
                                    
                                    <dt class="col-5">Registration Date:</dt>
                                    <dd class="col-7">{{ $vehicle->owner->registration_date ? \Carbon\Carbon::parse($vehicle->owner->registration_date)->format('d M Y') : 'N/A' }}</dd>
                            </dl>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> No owner information available for this vehicle.
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Booking History Tab -->
        <div class="tab-pane fade" id="booking-history" role="tabpanel">
            <div class="card">
                <div class="card-header bg-danger text-white">
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
                                    @php
                                        $customer = $booking->customer;
                                        $user = $customer->user ?? null;
                                    @endphp
                                    <tr>
                                        <td>#{{ $booking->bookingID }}</td>
                                        <td>{{ $user->name ?? 'N/A' }}</td>
                                        <td>{{ $booking->rental_start_date ? \Carbon\Carbon::parse($booking->rental_start_date)->format('d M Y') : 'N/A' }}</td>
                                        <td>{{ $booking->rental_end_date ? \Carbon\Carbon::parse($booking->rental_end_date)->format('d M Y') : 'N/A' }}</td>
                                        <td>{{ $booking->duration ?? 0 }} days</td>
                                        <td>
                                            <span class="badge {{ $booking->booking_status === 'Confirmed' ? 'bg-success' : ($booking->booking_status === 'Pending' ? 'bg-warning text-dark' : ($booking->booking_status === 'Cancelled' ? 'bg-danger' : 'bg-info')) }}">
                                                {{ $booking->booking_status ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td>RM {{ number_format(($booking->rental_amount ?? 0) + ($booking->deposit_amount ?? 0), 2) }}</td>
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
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="bi bi-calendar3"></i> Booking Calendar</h5>
                </div>
                <div class="card-body">
                    <div class="calendar-container">
                        <div id="calendar-view"></div>
                        <div class="calendar-legend">
                            <div class="legend-item">
                                <div class="legend-color legend-booked"></div>
                                <span>Booked</span>
                    </div>
                            <div class="legend-item">
                                <div class="legend-color legend-available"></div>
                                <span>Available</span>
                                </div>
                            <div class="legend-item">
                                <div class="legend-color legend-today"></div>
                                <span>Today</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Car Photos Tab -->
        <div class="tab-pane fade" id="photos" role="tabpanel">
            <div class="card">
                <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-images"></i> Car Photos</h5>
                    <button class="btn btn-sm" style="background: white; color: var(--hasta-red); border: none;" data-bs-toggle="modal" data-bs-target="#uploadPhotoModal">
                        <i class="bi bi-plus-circle"></i> Upload Photo
                    </button>
                </div>
                <div class="card-body">
                    <div class="row g-3" id="photoGallery">
                        @php
                            $photos = $vehicle->documents->where('document_type', 'photo');
                        @endphp
                        @forelse($photos as $photo)
                        <div class="col-md-4">
                            <div class="card">
                                    <div class="card-body text-center">
                                        @if($photo->fileURL)
                                            <img src="{{ asset('storage/' . $photo->fileURL) }}" 
                                                 alt="Vehicle Photo" 
                                                 class="img-fluid mb-2" 
                                                 style="max-height: 250px; width: 100%; object-fit: cover; border-radius: 6px;">
                                            <div class="upload-date">
                                                <i class="bi bi-calendar3"></i> Uploaded: {{ $photo->upload_date ? \Carbon\Carbon::parse($photo->upload_date)->format('d M Y') : 'N/A' }}
                                            </div>
                                            <div class="d-flex gap-2 justify-content-center mt-2">
                                                <a href="{{ asset('storage/' . $photo->fileURL) }}" 
                                                   target="_blank" 
                                                   class="btn btn-sm" style="background: var(--hasta-red); color: white; border: none;">
                                                    <i class="bi bi-eye"></i> View
                                                </a>
                                                <form method="POST" action="{{ route('admin.vehicles.documents.destroy', $photo->documentID) }}" 
                                                      onsubmit="return confirm('Are you sure?');" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="bi bi-trash"></i> Delete
                                                    </button>
                                                </form>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-center text-muted py-5">
                                <i class="bi bi-image fs-1 d-block mb-2"></i>
                                <p class="small mb-0">No photos uploaded yet.</p>
                                <p class="small">Click "Upload Photo" to add images.</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Location Tab -->
        <div class="tab-pane fade" id="location" role="tabpanel">
            <div class="card">
                <div class="card-header bg-danger text-white">
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

    <!-- Add Maintenance Modal -->
    <div class="modal fade" id="addMaintenanceModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Maintenance Record</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('admin.vehicles.maintenance.store', $vehicle->vehicleID) }}">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Service Date <span class="text-danger">*</span></label>
                            <input type="date" name="service_date" class="form-control" value="{{ old('service_date', date('Y-m-d')) }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Service Type <span class="text-danger">*</span></label>
                            <input type="text" name="service_type" class="form-control" value="{{ old('service_type') }}" required>
                            <small class="text-muted">e.g., Oil Change, Tire Replacement, Battery, General Service</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mileage</label>
                            <input type="number" name="mileage" class="form-control" value="{{ old('mileage') }}" min="0">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Cost (RM) <span class="text-danger">*</span></label>
                            <input type="number" name="cost" step="0.01" class="form-control" value="{{ old('cost') }}" min="0" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Next Due Date</label>
                            <input type="date" name="next_due_date" class="form-control" value="{{ old('next_due_date') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Service Center</label>
                            <input type="text" name="service_center" class="form-control" value="{{ old('service_center') }}">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Add Maintenance</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Upload Document Modal -->
    <div class="modal fade" id="uploadDocumentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Upload Document</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('admin.vehicles.documents.store', $vehicle->vehicleID) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Document Type <span class="text-danger">*</span></label>
                            <select name="document_type" id="document_type_select" class="form-select" required>
                                <option value="">Select Type</option>
                                <option value="insurance">Insurance</option>
                                <option value="grant">Grant</option>
                                <option value="roadtax">Road Tax</option>
                                <option value="contract">Contract</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">File <span class="text-danger">*</span></label>
                            <input type="file" name="file" class="form-control" accept="image/*" required>
                            <small class="text-muted">Supported formats: JPG, PNG, GIF. Max size: 5MB</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Upload Document</button>
                    </div>
                </form>
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
                <form method="POST" action="{{ route('admin.vehicles.photos.store', $vehicle->vehicleID) }}" enctype="multipart/form-data">
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

@push('scripts')
<script>
    function setDocumentType(type) {
        document.getElementById('document_type_select').value = type;
    }

    // Calendar View - Initialize on page load and when calendar tab is shown
    const bookedDates = @json($bookedDates ?? []);
    let currentDate = new Date();
    let currentMonth = currentDate.getMonth();
    let currentYear = currentDate.getFullYear();
    
    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 
                      'July', 'August', 'September', 'October', 'November', 'December'];
    const weekdays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    
    function renderCalendar() {
        const calendarContainer = document.getElementById('calendar-view');
        if (!calendarContainer) return;
            const firstDay = new Date(currentYear, currentMonth, 1);
            const lastDay = new Date(currentYear, currentMonth + 1, 0);
            const daysInMonth = lastDay.getDate();
            const startingDayOfWeek = firstDay.getDay();
            
            // Get previous month's last days
            const prevMonthLastDay = new Date(currentYear, currentMonth, 0).getDate();
            
            const today = new Date();
            const isCurrentMonth = currentMonth === today.getMonth() && currentYear === today.getFullYear();
            
            let calendarHTML = `
                <div class="calendar-header">
                    <button class="calendar-nav-btn" onclick="changeMonth(-1)">
                        <i class="bi bi-chevron-left"></i> Prev
                    </button>
                    <div class="calendar-month-year">${monthNames[currentMonth]} ${currentYear}</div>
                    <button class="calendar-nav-btn" onclick="changeMonth(1)">
                        Next <i class="bi bi-chevron-right"></i>
                    </button>
                </div>
                <div class="calendar-weekdays">
                    ${weekdays.map(day => `<div class="calendar-weekday">${day}</div>`).join('')}
                </div>
                <div class="calendar-days">
            `;
            
            // Previous month's days
            for (let i = startingDayOfWeek - 1; i >= 0; i--) {
                const day = prevMonthLastDay - i;
                calendarHTML += `<div class="calendar-day other-month">${day}</div>`;
            }
            
            // Current month's days
            for (let day = 1; day <= daysInMonth; day++) {
                const dateStr = `${currentYear}-${String(currentMonth + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                const isBooked = bookedDates.includes(dateStr);
                const isToday = isCurrentMonth && day === today.getDate();
                
                let classes = 'calendar-day current-month';
                if (isBooked) {
                    classes += ' booked';
                } else if (isToday) {
                    classes += ' today';
                } else {
                    classes += ' available';
                }
                
                calendarHTML += `
                    <div class="${classes}" 
                         title="${isBooked ? 'Booked on ' + dateStr : 'Available on ' + dateStr}">
                        ${day}
                    </div>
                `;
            }
            
            // Next month's days to fill the grid
            const totalCells = startingDayOfWeek + daysInMonth;
            const remainingCells = 42 - totalCells; // 6 rows * 7 days
            for (let day = 1; day <= remainingCells && day <= 14; day++) {
                calendarHTML += `<div class="calendar-day other-month">${day}</div>`;
            }
            
            calendarHTML += '</div>';
            calendarContainer.innerHTML = calendarHTML;
        }
        
    window.changeMonth = function(direction) {
        currentMonth += direction;
        if (currentMonth < 0) {
            currentMonth = 11;
            currentYear--;
        } else if (currentMonth > 11) {
            currentMonth = 0;
            currentYear++;
        }
        renderCalendar();
    };
    
    // Initialize calendar on page load
    document.addEventListener('DOMContentLoaded', function() {
        renderCalendar();
    });
    
    // Re-render calendar when calendar tab is shown
    document.addEventListener('shown.bs.tab', function(event) {
        if (event.target.getAttribute('data-bs-target') === '#calendar') {
            renderCalendar();
        }
    });
</script>
@endpush
