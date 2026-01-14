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
                <a href="{{ route('admin.vehicles.cars.edit', $vehicle->vehicleID) }}" class="btn btn-sm btn-danger">
                    <i class="bi bi-pencil"></i> Edit Car
                </a>
            @elseif($vehicle->motorcycle)
                <a href="{{ route('admin.vehicles.motorcycles.edit', $vehicle->vehicleID) }}" class="btn btn-sm btn-danger">
                    <i class="bi bi-pencil"></i> Edit Motorcycle
                </a>
            @else
                <a href="{{ route('admin.vehicles.others.edit', $vehicle->vehicleID) }}" class="btn btn-sm btn-danger">
                    <i class="bi bi-pencil"></i> Edit Vehicle
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
            <button class="nav-link {{ ($activeTab ?? 'car-info') === 'car-info' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#car-info" type="button" role="tab">
                <i class="bi bi-info-circle"></i> Car Info
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ ($activeTab ?? '') === 'owner-info' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#owner-info" type="button" role="tab">
                <i class="bi bi-person"></i> Owner Info
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <a href="{{ route('admin.vehicles.maintenance', $vehicle->vehicleID) }}" class="nav-link {{ ($activeTab ?? '') === 'maintenance' ? 'active' : '' }}">
                <i class="bi bi-tools"></i> Maintenance
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a href="{{ route('admin.vehicles.fuel', $vehicle->vehicleID) }}" class="nav-link {{ ($activeTab ?? '') === 'fuel' ? 'active' : '' }}">
                <i class="bi bi-fuel-pump"></i> Fuel and Wash
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ ($activeTab ?? '') === 'booking-history' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#booking-history" type="button" role="tab">
                <i class="bi bi-clock-history"></i> Booking History
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ ($activeTab ?? '') === 'photos' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#photos" type="button" role="tab">
                <i class="bi bi-images"></i> Car Photos
            </button>
        </li>
    </ul>

    <div class="tab-content">
        <!-- Car Info Tab - Contains Vehicle Info, Maintenance, and Documentation -->
        <div class="tab-pane fade {{ ($activeTab ?? 'car-info') === 'car-info' ? 'show active' : '' }}" id="car-info" role="tabpanel">
            <div class="row g-3">
                <!-- Vehicle Basic Info -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-car-front"></i> Vehicle Information</h5>
                            @if($vehicle->car)
                                <a href="{{ route('admin.vehicles.cars.edit', $vehicle->vehicleID) }}" class="btn btn-sm btn-light">
                                    <i class="bi bi-pencil"></i> Edit Car
                                </a>
                            @elseif($vehicle->motorcycle)
                                <a href="{{ route('admin.vehicles.motorcycles.edit', $vehicle->vehicleID) }}" class="btn btn-sm btn-light">
                                    <i class="bi bi-pencil"></i> Edit Motorcycle
                                </a>
                            @else
                                <a href="{{ route('admin.vehicles.others.edit', $vehicle->vehicleID) }}" class="btn btn-sm btn-light">
                                    <i class="bi bi-pencil"></i> Edit Vehicle
                                </a>
                            @endif
                        </div>
                        <div class="card-body">
                            <dl class="mb-0">
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Vehicle Brand:</dt>
                                    <dd class="d-inline ms-2">{{ $vehicle->vehicle_brand ?? 'N/A' }}</dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Vehicle Model:</dt>
                                    <dd class="d-inline ms-2">{{ $vehicle->vehicle_model ?? 'N/A' }}</dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Plate Number:</dt>
                                    <dd class="d-inline ms-2">{{ $vehicle->plate_number ?? 'N/A' }}</dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Registration Date:</dt>
                                    <dd class="d-inline ms-2">{{ $vehicle->created_date ? \Carbon\Carbon::parse($vehicle->created_date)->format('d M Y') : 'N/A' }}</dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Available Status:</dt>
                                    <dd class="d-inline ms-2">
                                        <span class="badge {{ $vehicle->availability_status === 'available' ? 'bg-success' : ($vehicle->availability_status === 'rented' ? 'bg-warning text-dark' : ($vehicle->availability_status === 'maintenance' ? 'bg-info' : 'bg-secondary')) }}">
                                            {{ ucfirst($vehicle->availability_status ?? 'Unknown') }}
                                        </span>
                                    </dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Created Date:</dt>
                                    <dd class="d-inline ms-2">{{ $vehicle->created_date ? \Carbon\Carbon::parse($vehicle->created_date)->format('d M Y') : 'N/A' }}</dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Manufacturing Year:</dt>
                                    <dd class="d-inline ms-2">{{ $vehicle->manufacturing_year ?? 'N/A' }}</dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Color:</dt>
                                    <dd class="d-inline ms-2">{{ $vehicle->color ?? 'N/A' }}</dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Engine Capacity:</dt>
                                    <dd class="d-inline ms-2">{{ $vehicle->engineCapacity ? number_format($vehicle->engineCapacity, 2) . 'L' : 'N/A' }}</dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Vehicle Type:</dt>
                                    <dd class="d-inline ms-2">{{ $vehicle->vehicleType ?? 'N/A' }}</dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Rental Price:</dt>
                                    <dd class="d-inline ms-2">RM {{ number_format($vehicle->rental_price ?? 0, 2) }}</dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Is Active:</dt>
                                    <dd class="d-inline ms-2">
                                        <span class="badge {{ ($vehicle->isActive ?? false) ? 'bg-success' : 'bg-secondary' }}">
                                            {{ ($vehicle->isActive ?? false) ? 'Active' : 'Inactive' }}
                                        </span>
                                    </dd>
                                </div>
                                @if($vehicle->car)
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Seating Capacity:</dt>
                                    <dd class="d-inline ms-2">{{ $vehicle->car->seating_capacity ?? 'N/A' }}</dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Transmission:</dt>
                                    <dd class="d-inline ms-2">{{ $vehicle->car->transmission ?? 'N/A' }}</dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Car Type:</dt>
                                    <dd class="d-inline ms-2">{{ $vehicle->car->car_type ?? 'N/A' }}</dd>
                                </div>
                                @elseif($vehicle->motorcycle)
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Motor Type:</dt>
                                    <dd class="d-inline ms-2">{{ $vehicle->motorcycle->motor_type ?? 'N/A' }}</dd>
                                </div>
                                @endif
                            </dl>
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
                                                @php
                                                    $isPdf = strtolower(pathinfo($insuranceDoc->fileURL, PATHINFO_EXTENSION)) === 'pdf';
                                                @endphp
                                                <div class="mb-2">
                                                    @if($isPdf)
                                                        <div class="d-flex align-items-center justify-content-center" style="min-height: 150px; background: #f5f5f5; border-radius: 6px;">
                                                            <i class="bi bi-file-earmark-pdf" style="font-size: 4rem; color: var(--hasta-red);"></i>
                                                        </div>
                                                    @else
                                                        <img src="{{ getFileUrl($insuranceDoc->fileURL, true) }}" 
                                                             alt="Insurance" 
                                                             class="img-fluid mb-2" 
                                                             style="max-height: 150px; border-radius: 6px;">
                                                    @endif
                                                </div>
                                                <div class="upload-date">
                                                    <i class="bi bi-calendar3"></i> Uploaded: {{ $insuranceDoc->upload_date ? \Carbon\Carbon::parse($insuranceDoc->upload_date)->format('d M Y') : 'N/A' }}
                                                </div>
                                                <div class="d-flex gap-2 justify-content-center mt-2 flex-wrap">
                                                    <button type="button" 
                                                            class="btn btn-sm" 
                                                            style="background: white; color: var(--hasta-red); border: 1px solid var(--hasta-red);"
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#viewDocumentModal{{ $insuranceDoc->documentID }}">
                                                        <i class="bi bi-eye"></i> View
                                                    </button>
                                                    <button type="button" class="btn btn-sm" style="background: white; color: var(--hasta-red); border: 1px solid var(--hasta-red);" 
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
                                                
                                                <!-- View Document Modal for Insurance -->
                                                <div class="modal fade" id="viewDocumentModal{{ $insuranceDoc->documentID }}" tabindex="-1">
                                                    <div class="modal-dialog modal-lg modal-dialog-centered">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Insurance Document</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body text-center" style="min-height: 400px;">
                                                                @if($isPdf)
                                                                    <iframe src="{{ getFileUrl($insuranceDoc->fileURL) }}" 
                                                                            style="width: 100%; height: 70vh; border: none; border-radius: 6px;"
                                                                            onerror="this.parentElement.innerHTML='<p class=\'text-muted\'>PDF not found</p>';">
                                                                    </iframe>
                                                                @else
                                                                    <img src="{{ getFileUrl($insuranceDoc->fileURL, true) }}" 
                                                                         alt="Insurance Document" 
                                                                         class="img-fluid" 
                                                                         style="max-height: 70vh; width: auto; border-radius: 6px;"
                                                                         onerror="this.parentElement.innerHTML='<p class=\'text-muted\'>Image not found</p>';">
                                                                @endif
                                                            </div>
                                                            <div class="modal-footer">
                                                                <a href="{{ getFileUrl($insuranceDoc->fileURL) }}" 
                                                                   target="_blank" 
                                                                   class="btn btn-primary">
                                                                    <i class="bi bi-download"></i> Open in New Tab
                                                                </a>
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                <p class="small text-muted mb-2">No document uploaded</p>
                                                <button type="button" class="btn btn-sm" style="background: white; color: var(--hasta-red); border: 1px solid var(--hasta-red);" 
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
                                                @php
                                                    $isPdf = strtolower(pathinfo($grantDoc->fileURL, PATHINFO_EXTENSION)) === 'pdf';
                                                @endphp
                                                <div class="mb-2">
                                                    @if($isPdf)
                                                        <div class="d-flex align-items-center justify-content-center" style="min-height: 150px; background: #f5f5f5; border-radius: 6px;">
                                                            <i class="bi bi-file-earmark-pdf" style="font-size: 4rem; color: var(--hasta-red);"></i>
                                                        </div>
                                                    @else
                                                        <img src="{{ getFileUrl($grantDoc->fileURL, true) }}" 
                                                             alt="Grant" 
                                                             class="img-fluid mb-2" 
                                                             style="max-height: 150px; border-radius: 6px;">
                                                    @endif
                                                </div>
                                                <div class="upload-date">
                                                    <i class="bi bi-calendar3"></i> Uploaded: {{ $grantDoc->upload_date ? \Carbon\Carbon::parse($grantDoc->upload_date)->format('d M Y') : 'N/A' }}
                                                </div>
                                                <div class="d-flex gap-2 justify-content-center mt-2 flex-wrap">
                                                    <button type="button" 
                                                            class="btn btn-sm" 
                                                            style="background: white; color: var(--hasta-red); border: 1px solid var(--hasta-red);"
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#viewDocumentModal{{ $grantDoc->documentID }}">
                                                        <i class="bi bi-eye"></i> View
                                                    </button>
                                                    <button type="button" class="btn btn-sm" style="background: white; color: var(--hasta-red); border: 1px solid var(--hasta-red);" 
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
                                                
                                                <!-- View Document Modal for Grant -->
                                                <div class="modal fade" id="viewDocumentModal{{ $grantDoc->documentID }}" tabindex="-1">
                                                    <div class="modal-dialog modal-lg modal-dialog-centered">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Grant Document</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body text-center" style="min-height: 400px;">
                                                                @if($isPdf)
                                                                    <iframe src="{{ getFileUrl($grantDoc->fileURL) }}" 
                                                                            style="width: 100%; height: 70vh; border: none; border-radius: 6px;"
                                                                            onerror="this.parentElement.innerHTML='<p class=\'text-muted\'>PDF not found</p>';">
                                                                    </iframe>
                                                                @else
                                                                    <img src="{{ getFileUrl($grantDoc->fileURL, true) }}" 
                                                                         alt="Grant Document" 
                                                                         class="img-fluid" 
                                                                         style="max-height: 70vh; width: auto; border-radius: 6px;"
                                                                         onerror="this.parentElement.innerHTML='<p class=\'text-muted\'>Image not found</p>';">
                                                                @endif
                                                            </div>
                                                            <div class="modal-footer">
                                                                <a href="{{ asset('storage/' . $grantDoc->fileURL) }}" 
                                                                   target="_blank" 
                                                                   class="btn btn-primary">
                                                                    <i class="bi bi-download"></i> Open in New Tab
                                                                </a>
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                <p class="small text-muted mb-2">No document uploaded</p>
                                                <button type="button" class="btn btn-sm" style="background: white; color: var(--hasta-red); border: 1px solid var(--hasta-red);" 
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
                                                @php
                                                    $isPdf = strtolower(pathinfo($roadtaxDoc->fileURL, PATHINFO_EXTENSION)) === 'pdf';
                                                @endphp
                                                <div class="mb-2">
                                                    @if($isPdf)
                                                        <div class="d-flex align-items-center justify-content-center" style="min-height: 150px; background: #f5f5f5; border-radius: 6px;">
                                                            <i class="bi bi-file-earmark-pdf" style="font-size: 4rem; color: var(--hasta-red);"></i>
                                                        </div>
                                                    @else
                                                        <img src="{{ asset('storage/' . $roadtaxDoc->fileURL) }}" 
                                                             alt="Road Tax" 
                                                             class="img-fluid mb-2" 
                                                             style="max-height: 150px; border-radius: 6px;">
                                                    @endif
                                                </div>
                                                <div class="upload-date">
                                                    <i class="bi bi-calendar3"></i> Uploaded: {{ $roadtaxDoc->upload_date ? \Carbon\Carbon::parse($roadtaxDoc->upload_date)->format('d M Y') : 'N/A' }}
                                                </div>
                                                <div class="d-flex gap-2 justify-content-center mt-2 flex-wrap">
                                                    @php
                                                        $isPdf = strtolower(pathinfo($roadtaxDoc->fileURL, PATHINFO_EXTENSION)) === 'pdf';
                                                    @endphp
                                                    <button type="button" 
                                                            class="btn btn-sm" 
                                                            style="background: white; color: var(--hasta-red); border: 1px solid var(--hasta-red);"
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#viewDocumentModal{{ $roadtaxDoc->documentID }}">
                                                        <i class="bi bi-eye"></i> View
                                                    </button>
                                                    <button type="button" class="btn btn-sm" style="background: white; color: var(--hasta-red); border: 1px solid var(--hasta-red);" 
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
                                                
                                                <!-- View Document Modal for Road Tax -->
                                                <div class="modal fade" id="viewDocumentModal{{ $roadtaxDoc->documentID }}" tabindex="-1">
                                                    <div class="modal-dialog modal-lg modal-dialog-centered">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Road Tax Document</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body text-center" style="min-height: 400px;">
                                                                @if($isPdf)
                                                                    <iframe src="{{ asset('storage/' . $roadtaxDoc->fileURL) }}" 
                                                                            style="width: 100%; height: 70vh; border: none; border-radius: 6px;"
                                                                            onerror="this.parentElement.innerHTML='<p class=\'text-muted\'>PDF not found</p>';">
                                                                    </iframe>
                                                                @else
                                                                    <img src="{{ asset('storage/' . $roadtaxDoc->fileURL) }}" 
                                                                         alt="Road Tax Document" 
                                                                         class="img-fluid" 
                                                                         style="max-height: 70vh; width: auto; border-radius: 6px;"
                                                                         onerror="this.parentElement.innerHTML='<p class=\'text-muted\'>Image not found</p>';">
                                                                @endif
                                                            </div>
                                                            <div class="modal-footer">
                                                                <a href="{{ asset('storage/' . $roadtaxDoc->fileURL) }}" 
                                                                   target="_blank" 
                                                                   class="btn btn-primary">
                                                                    <i class="bi bi-download"></i> Open in New Tab
                                                                </a>
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                <p class="small text-muted mb-2">No document uploaded</p>
                                                <button type="button" class="btn btn-sm" style="background: white; color: var(--hasta-red); border: 1px solid var(--hasta-red);" 
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
                                                @php
                                                    $isPdf = strtolower(pathinfo($contractDoc->fileURL, PATHINFO_EXTENSION)) === 'pdf';
                                                @endphp
                                                <div class="mb-2">
                                                    @if($isPdf)
                                                        <div class="d-flex align-items-center justify-content-center" style="min-height: 150px; background: #f5f5f5; border-radius: 6px;">
                                                            <i class="bi bi-file-earmark-pdf" style="font-size: 4rem; color: var(--hasta-red);"></i>
                                                        </div>
                                                    @else
                                                        <img src="{{ asset('storage/' . $contractDoc->fileURL) }}" 
                                                             alt="Contract" 
                                                             class="img-fluid mb-2" 
                                                             style="max-height: 150px; border-radius: 6px;">
                                                    @endif
                                                </div>
                                                <div class="upload-date">
                                                    <i class="bi bi-calendar3"></i> Uploaded: {{ $contractDoc->upload_date ? \Carbon\Carbon::parse($contractDoc->upload_date)->format('d M Y') : 'N/A' }}
                                                </div>
                                                <div class="d-flex gap-2 justify-content-center mt-2 flex-wrap">
                                                    @php
                                                        $isPdf = strtolower(pathinfo($contractDoc->fileURL, PATHINFO_EXTENSION)) === 'pdf';
                                                    @endphp
                                                    <button type="button" 
                                                            class="btn btn-sm" 
                                                            style="background: white; color: var(--hasta-red); border: 1px solid var(--hasta-red);"
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#viewDocumentModal{{ $contractDoc->documentID }}">
                                                        <i class="bi bi-eye"></i> View
                                                    </button>
                                                    <button type="button" class="btn btn-sm" style="background: white; color: var(--hasta-red); border: 1px solid var(--hasta-red);" 
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
                                                
                                                <!-- View Document Modal for Contract -->
                                                <div class="modal fade" id="viewDocumentModal{{ $contractDoc->documentID }}" tabindex="-1">
                                                    <div class="modal-dialog modal-lg modal-dialog-centered">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Contract Document</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body text-center" style="min-height: 400px;">
                                                                @php
                                                                    $isPdfContract = strtolower(pathinfo($contractDoc->fileURL, PATHINFO_EXTENSION)) === 'pdf';
                                                                @endphp
                                                                @if($isPdfContract)
                                                                    <iframe src="{{ asset('storage/' . $contractDoc->fileURL) }}" 
                                                                            style="width: 100%; height: 70vh; border: none; border-radius: 6px;"
                                                                            onerror="this.parentElement.innerHTML='<p class=\'text-muted\'>PDF not found</p>';">
                                                                    </iframe>
                                                                @else
                                                                    <img src="{{ asset('storage/' . $contractDoc->fileURL) }}" 
                                                                         alt="Contract Document" 
                                                                         class="img-fluid" 
                                                                         style="max-height: 70vh; width: auto; border-radius: 6px;"
                                                                         onerror="this.parentElement.innerHTML='<p class=\'text-muted\'>Image not found</p>';">
                                                                @endif
                                                            </div>
                                                            <div class="modal-footer">
                                                                <a href="{{ asset('storage/' . $contractDoc->fileURL) }}" 
                                                                   target="_blank" 
                                                                   class="btn btn-primary">
                                                                    <i class="bi bi-download"></i> Open in New Tab
                                                                </a>
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                <p class="small text-muted mb-2">No document uploaded</p>
                                                <button type="button" class="btn btn-sm" style="background: white; color: var(--hasta-red); border: 1px solid var(--hasta-red);" 
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
        <div class="tab-pane fade {{ ($activeTab ?? '') === 'owner-info' ? 'show active' : '' }}" id="owner-info" role="tabpanel">
            <div class="row g-3">
                <!-- Owner Information Card -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-person-circle"></i> Owner Information</h5>
                            <button type="button" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#editOwnerModal">
                                <i class="bi bi-pencil"></i> Edit Owner
                            </button>
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
                                        </dl>
                                    </div>
                                    <div class="col-md-6">
                                        <dl class="row mb-0">
                                            <dt class="col-5">Registration Date:</dt>
                                            <dd class="col-7">
                                                @if($vehicle->owner->registration_date)
                                                    @try
                                                        {{ \Carbon\Carbon::parse($vehicle->owner->registration_date)->format('d M Y') }}
                                                    @catch(\Exception $e)
                                                        {{ $vehicle->owner->registration_date }}
                                                    @endtry
                                                @else
                                                    N/A
                                                @endif
                                            </dd>
                                            
                                            <dt class="col-5">Leasing Price:</dt>
                                            <dd class="col-7">RM {{ number_format($vehicle->owner->leasing_price ?? 0, 2) }}</dd>
                                            
                                            <dt class="col-5">Leasing Due Date:</dt>
                                            <dd class="col-7">
                                                @if($vehicle->owner->leasing_due_date)
                                                    @try
                                                        {{ \Carbon\Carbon::parse($vehicle->owner->leasing_due_date)->format('d M Y') }}
                                                    @catch(\Exception $e)
                                                        {{ $vehicle->owner->leasing_due_date }}
                                                    @endtry
                                                @else
                                                    N/A
                                                @endif
                                            </dd>
                                            
                                            <dt class="col-5">License Expiry Date:</dt>
                                            <dd class="col-7">
                                                @if($vehicle->owner->license_expirydate)
                                                    @try
                                                        {{ \Carbon\Carbon::parse($vehicle->owner->license_expirydate)->format('d M Y') }}
                                                    @catch(\Exception $e)
                                                        {{ $vehicle->owner->license_expirydate }}
                                                    @endtry
                                                @else
                                                    N/A
                                                @endif
                                            </dd>
                                            
                                            <dt class="col-5">Status:</dt>
                                            <dd class="col-7">
                                                <span class="badge {{ ($vehicle->owner->isActive ?? false) ? 'bg-success' : 'bg-secondary' }}">
                                                    {{ ($vehicle->owner->isActive ?? false) ? 'Active' : 'Inactive' }}
                                                </span>
                                            </dd>
                                        </dl>
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i> No owner information available for this vehicle.
                                    <button type="button" class="btn btn-sm btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#editOwnerModal">
                                        <i class="bi bi-plus-circle"></i> Add Owner Information
                                    </button>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Documentation Section -->
                @if($vehicle->owner)
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0"><i class="bi bi-file-earmark-text"></i> Documentation</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <!-- License Image -->
                                <div class="col-md-6">
                                    <div class="card document-cell h-100">
                                        <div class="card-body text-center">
                                            <i class="bi bi-card-text fs-1 d-block mb-2" style="color: var(--hasta-red);"></i>
                                            <h6 class="fw-semibold">License</h6>
                                            @php
                                                $licenseImg = $vehicle->owner->license_img ?? null;
                                            @endphp
                                            @if($licenseImg)
                                                <div class="mb-2">
                                                    <img src="{{ asset('storage/' . $licenseImg) }}" 
                                                         alt="License" 
                                                         class="img-fluid mb-2" 
                                                         style="max-height: 150px; border-radius: 6px;"
                                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                                    <p class="text-muted small" style="display:none;">Image not found</p>
                                                </div>
                                                <div class="d-flex gap-2 justify-content-center mt-2 flex-wrap">
                                                    <button type="button" 
                                                            class="btn btn-sm" 
                                                            style="background: white; color: var(--hasta-red); border: 1px solid var(--hasta-red);"
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#viewOwnerLicenseModal">
                                                        <i class="bi bi-eye"></i> View
                                                    </button>
                                                    <button type="button" class="btn btn-sm" style="background: white; color: var(--hasta-red); border: 1px solid var(--hasta-red);" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#uploadOwnerLicenseModal">
                                                        <i class="bi bi-upload"></i> Upload
                                                    </button>
                                                </div>
                                                
                                                <!-- View License Modal -->
                                                <div class="modal fade" id="viewOwnerLicenseModal" tabindex="-1">
                                                    <div class="modal-dialog modal-lg modal-dialog-centered">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Owner License</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body text-center" style="min-height: 400px;">
                                                                <img src="{{ asset('storage/' . $licenseImg) }}" 
                                                                     alt="Owner License" 
                                                                     class="img-fluid" 
                                                                     style="max-height: 70vh; width: auto; border-radius: 6px;"
                                                                     onerror="this.parentElement.innerHTML='<p class=\'text-muted\'>Image not found</p>';">
                                                            </div>
                                                            <div class="modal-footer">
                                                                <a href="{{ asset('storage/' . $licenseImg) }}" 
                                                                   target="_blank" 
                                                                   class="btn btn-primary">
                                                                    <i class="bi bi-download"></i> Open in New Tab
                                                                </a>
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                <p class="small text-muted mb-2">No license image uploaded</p>
                                                <button type="button" class="btn btn-sm" style="background: white; color: var(--hasta-red); border: 1px solid var(--hasta-red);" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#uploadOwnerLicenseModal">
                                                    <i class="bi bi-upload"></i> Upload
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- IC Image -->
                                <div class="col-md-6">
                                    <div class="card document-cell h-100">
                                        <div class="card-body text-center">
                                            <i class="bi bi-person-badge fs-1 d-block mb-2" style="color: var(--hasta-red);"></i>
                                            <h6 class="fw-semibold">IC</h6>
                                            @php
                                                $icImg = null;
                                                if ($vehicle->owner) {
                                                    $icImg = $vehicle->owner->ic_img ?? null;
                                                }
                                            @endphp
                                            @if($icImg)
                                                <div class="mb-2">
                                                    <img src="{{ getFileUrl($icImg) }}" 
                                                         alt="IC" 
                                                         class="img-fluid mb-2" 
                                                         style="max-height: 150px; border-radius: 6px;"
                                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                                    <p class="text-muted small" style="display:none;">Image not found</p>
                                                </div>
                                                <div class="d-flex gap-2 justify-content-center mt-2 flex-wrap">
                                                    <button type="button" 
                                                            class="btn btn-sm" 
                                                            style="background: white; color: var(--hasta-red); border: 1px solid var(--hasta-red);"
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#viewOwnerIcModal">
                                                        <i class="bi bi-eye"></i> View
                                                    </button>
                                                    <button type="button" class="btn btn-sm" style="background: white; color: var(--hasta-red); border: 1px solid var(--hasta-red);" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#uploadOwnerIcModal">
                                                        <i class="bi bi-upload"></i> Upload
                                                    </button>
                                                </div>
                                                
                                                <!-- View IC Modal -->
                                                <div class="modal fade" id="viewOwnerIcModal" tabindex="-1">
                                                    <div class="modal-dialog modal-lg modal-dialog-centered">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Owner IC</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body text-center" style="min-height: 400px;">
                                                                <img src="{{ getFileUrl($icImg) }}" 
                                                                     alt="Owner IC" 
                                                                     class="img-fluid" 
                                                                     style="max-height: 70vh; width: auto; border-radius: 6px;"
                                                                     onerror="this.parentElement.innerHTML='<p class=\'text-muted\'>Image not found</p>';">
                                                            </div>
                                                            <div class="modal-footer">
                                                                <a href="{{ getFileUrl($icImg) }}" 
                                                                   target="_blank" 
                                                                   class="btn btn-primary">
                                                                    <i class="bi bi-download"></i> Open in New Tab
                                                                </a>
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                <p class="small text-muted mb-2">No IC image uploaded</p>
                                                <button type="button" class="btn btn-sm" style="background: white; color: var(--hasta-red); border: 1px solid var(--hasta-red);" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#uploadOwnerIcModal">
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
                @endif
            </div>
        </div>

        <!-- Booking History Tab -->
        <div class="tab-pane fade {{ ($activeTab ?? '') === 'booking-history' ? 'show active' : '' }}" id="booking-history" role="tabpanel">
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
                                    <th>Review Form</th>
                                    <th>Vehicle Condition Form</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($vehicle->bookings as $booking)
                                    @php
                                        $customer = $booking->customer;
                                        $user = $customer->user ?? null;
                                        $review = $booking->review;
                                        // Assuming there's a vehicle condition form - using bookingID for now
                                    @endphp
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.bookings.reservations', ['search' => $booking->bookingID]) }}" class="text-decoration-none fw-bold text-primary">
                                                #{{ $booking->bookingID }}
                                            </a>
                                        </td>
                                        <td>{{ $user->name ?? 'N/A' }}</td>
                                        <td>{{ $booking->rental_start_date ? \Carbon\Carbon::parse($booking->rental_start_date)->format('d M Y') : 'N/A' }}</td>
                                        <td>{{ $booking->rental_end_date ? \Carbon\Carbon::parse($booking->rental_end_date)->format('d M Y') : 'N/A' }}</td>
                                        <td>{{ $booking->duration ?? 0 }} days</td>
                                        <td>
                                            <span class="badge {{ $booking->booking_status === 'Confirmed' ? 'bg-success' : ($booking->booking_status === 'Pending' ? 'bg-warning text-dark' : ($booking->booking_status === 'Cancelled' ? 'bg-danger' : 'bg-info')) }}">
                                                {{ $booking->booking_status ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($review)
                                                <a href="{{ route('bookings.show', $booking->bookingID) }}#review" target="_blank" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-star"></i> View Review
                                                </a>
                                            @else
                                                <span class="text-muted small">Not submitted</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('bookings.show', $booking->bookingID) }}#vehicle-condition" target="_blank" class="btn btn-sm btn-outline-info">
                                                <i class="bi bi-clipboard-check"></i> View Form
                                            </a>
                                        </td>
                                        <td>RM {{ number_format(($booking->rental_amount ?? 0) + ($booking->deposit_amount ?? 0), 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-4">
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

        <!-- Car Photos Tab -->
        <div class="tab-pane fade {{ ($activeTab ?? '') === 'photos' ? 'show active' : '' }}" id="photos" role="tabpanel">
            <div class="card">
                <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-images"></i> Car Photos</h5>
                    <button class="btn btn-sm" style="background: white; border: 2px solid #dc3545;" data-bs-toggle="modal" data-bs-target="#uploadPhotoModal">
                        <i class="bi bi-cloud-arrow-up-fill" style="color: #dc3545;"></i> <span style="color: #dc3545; font-weight: 600;">Upload</span>
                    </button>
                </div>
                <div class="card-body">
                    <div class="row g-3" id="photoGallery">
                        @php
                            // Use carImages from car_img table if available, otherwise fallback to VehicleDocument
                            $photos = isset($carImages) && $carImages->count() > 0 ? $carImages : (isset($vehiclePhotos) ? $vehiclePhotos : $vehicle->documents->where('document_type', 'photo'));
                            $isCarImg = isset($carImages) && $carImages->count() > 0;
                        @endphp
                        @forelse($photos as $photo)
                        <div class="col-md-4">
                            <div class="card">
                                    <div class="card-body text-center">
                                        @php
                                            // Get image URL: if car_img table, use documentID (Google Drive URL), otherwise use fileURL
                                            $imageUrl = $isCarImg ? ($photo->documentID ?? '') : ($photo->fileURL ?? '');
                                            $photoId = $isCarImg ? ($photo->imgID ?? $photo->documentID ?? '') : ($photo->documentID ?? '');
                                        @endphp
                                        @if($imageUrl)
                                            <img src="{{ getFileUrl($imageUrl, true) }}" 
                                                 alt="Vehicle Photo" 
                                                 class="img-fluid mb-2" 
                                                 style="max-height: 250px; width: 100%; object-fit: cover; border-radius: 6px;">
                                            @if($isCarImg)
                                                <div class="mb-2">
                                                    <span class="badge bg-primary">{{ ucfirst($photo->imageType ?? 'other') }}</span>
                                                    @if($photo->img_description)
                                                        <p class="small text-muted mt-1 mb-0">{{ $photo->img_description }}</p>
                                                    @endif
                                                </div>
                                            @else
                                                <div class="upload-date">
                                                    <i class="bi bi-calendar3"></i> Uploaded: {{ $photo->upload_date ? \Carbon\Carbon::parse($photo->upload_date)->format('d M Y') : 'N/A' }}
                                                </div>
                                            @endif
                                            <div class="d-flex gap-2 justify-content-center mt-2">
                                                <button type="button" 
                                                        class="btn btn-sm" 
                                                        style="background: white; color: var(--hasta-red); border: 1px solid var(--hasta-red);"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#viewImageModal{{ $photoId }}">
                                                    <i class="bi bi-eye"></i> View
                                                </button>
                                                @if($isCarImg)
                                                    @php
                                                        $photoImgId = is_object($photo) ? ($photo->imgID ?? null) : ($photo['imgID'] ?? null);
                                                    @endphp
                                                    @if($photoImgId)
                                                    <form method="POST" action="{{ route('admin.vehicles.photos.destroy', ['imgId' => $photoImgId]) }}" 
                                                          onsubmit="return confirm('Are you sure?');" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                                            <i class="bi bi-trash"></i> Delete
                                                        </button>
                                                    </form>
                                                    @endif
                                                @else
                                                    <form method="POST" action="{{ route('admin.vehicles.documents.destroy', $photo->documentID) }}" 
                                                          onsubmit="return confirm('Are you sure?');" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                                            <i class="bi bi-trash"></i> Delete
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                            
                                            <!-- View Image Modal -->
                            <div class="modal fade" id="viewImageModal{{ $photoId }}" tabindex="-1">
                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Vehicle Photo{{ $isCarImg && $photo->imageType ? ' - ' . ucfirst($photo->imageType) : '' }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body text-center">
                                            <img src="{{ getFileUrl($imageUrl, true) }}" 
                                                 alt="Vehicle Photo" 
                                                 class="img-fluid" 
                                                 style="max-height: 70vh; width: auto; border-radius: 6px;"
                                                 onerror="this.parentElement.innerHTML='<p class=\'text-muted\'>Image not found</p>';">
                                            @if($isCarImg && $photo->img_description)
                                                <p class="text-muted mt-3">{{ $photo->img_description }}</p>
                                            @endif
                                        </div>
                                        <div class="modal-footer">
                                            <a href="{{ getFileUrl($imageUrl) }}" 
                                               target="_blank" 
                                               class="btn btn-primary">
                                                <i class="bi bi-download"></i> Open in New Tab
                                            </a>
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
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
                            <input type="file" name="file" class="form-control" accept="image/*,.pdf" required>
                            <small class="text-muted">Supported formats: JPG, PNG, GIF, PDF. Max size: 5MB</small>
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

    <!-- Upload Owner License Modal -->
    <div class="modal fade" id="uploadOwnerLicenseModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Upload Owner License</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('admin.vehicles.owner.upload-license', $vehicle->vehicleID) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">License Image <span class="text-danger">*</span></label>
                            <input type="file" name="license_img" class="form-control" accept="image/*,.pdf" required>
                            <small class="text-muted">Supported formats: JPG, PNG, GIF, PDF. Max size: 5MB</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Upload License</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Upload Owner IC Modal -->
    <div class="modal fade" id="uploadOwnerIcModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Upload Owner IC</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('admin.vehicles.owner.upload-ic', $vehicle->vehicleID) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">IC Image <span class="text-danger">*</span></label>
                            <input type="file" name="ic_img" class="form-control" accept="image/*,.pdf" required>
                            <small class="text-muted">Supported formats: JPG, PNG, GIF, PDF. Max size: 5MB</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Upload IC</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Owner Modal -->
    <div class="modal fade" id="editOwnerModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="bi bi-pencil"></i> {{ $vehicle->owner ? 'Edit' : 'Add' }} Owner Information</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('admin.vehicles.owner.update', $vehicle->vehicleID) }}">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">IC Number <span class="text-danger">*</span></label>
                                <input type="text" name="ic_no" class="form-control @error('ic_no') is-invalid @enderror" 
                                       value="{{ old('ic_no', $vehicle->owner->ic_no ?? '') }}" required>
                                @error('ic_no')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Owner Name</label>
                                <input type="text" name="owner_name" class="form-control @error('owner_name') is-invalid @enderror" 
                                       value="{{ old('owner_name', ($vehicle->owner && $vehicle->owner->personDetails) ? $vehicle->owner->personDetails->fullname : '') }}">
                                @error('owner_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Contact Number</label>
                                <input type="text" name="contact_number" class="form-control @error('contact_number') is-invalid @enderror" 
                                       value="{{ old('contact_number', $vehicle->owner->contact_number ?? '') }}">
                                @error('contact_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                                       value="{{ old('email', $vehicle->owner->email ?? '') }}">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Bank Name</label>
                                <input type="text" name="bankname" class="form-control @error('bankname') is-invalid @enderror" 
                                       value="{{ old('bankname', $vehicle->owner->bankname ?? '') }}">
                                @error('bankname')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Bank Account Number</label>
                                <input type="text" name="bank_acc_number" class="form-control @error('bank_acc_number') is-invalid @enderror" 
                                       value="{{ old('bank_acc_number', $vehicle->owner->bank_acc_number ?? '') }}">
                                @error('bank_acc_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Registration Date</label>
                                @php
                                    $registrationDateValue = '';
                                    if ($vehicle->owner && $vehicle->owner->registration_date) {
                                        try {
                                            $registrationDateValue = \Carbon\Carbon::parse($vehicle->owner->registration_date)->format('Y-m-d');
                                        } catch (\Exception $e) {
                                            $registrationDateValue = '';
                                        }
                                    }
                                @endphp
                                <input type="date" name="registration_date" class="form-control @error('registration_date') is-invalid @enderror" 
                                       value="{{ old('registration_date', $registrationDateValue) }}">
                                @error('registration_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Leasing Price (RM)</label>
                                <input type="number" step="0.01" name="leasing_price" class="form-control @error('leasing_price') is-invalid @enderror" 
                                       value="{{ old('leasing_price', $vehicle->owner->leasing_price ?? '') }}">
                                @error('leasing_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Leasing Due Date</label>
                                @php
                                    $leasingDueDateValue = '';
                                    if ($vehicle->owner && $vehicle->owner->leasing_due_date) {
                                        try {
                                            $leasingDueDateValue = \Carbon\Carbon::parse($vehicle->owner->leasing_due_date)->format('Y-m-d');
                                        } catch (\Exception $e) {
                                            $leasingDueDateValue = '';
                                        }
                                    }
                                @endphp
                                <input type="date" name="leasing_due_date" class="form-control @error('leasing_due_date') is-invalid @enderror" 
                                       value="{{ old('leasing_due_date', $leasingDueDateValue) }}">
                                @error('leasing_due_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" name="isActive" id="isActiveOwner" 
                                           {{ old('isActive', $vehicle->owner->isActive ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="isActiveOwner">
                                        Is Active
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Save Owner Information</button>
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
    
</script>
@endpush
