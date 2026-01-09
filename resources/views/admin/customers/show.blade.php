@extends('layouts.admin')

@section('title', 'Customer Details')

@push('styles')
<style>
    :root {
        --hasta-red: #b91c1c;
        --hasta-red-dark: #7f1d1d;
        --hasta-rose: #fee2e2;
    }
    .grouping-box {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .grouping-box-header {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--hasta-red-dark);
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid var(--hasta-rose);
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
    .upload-date {
        font-size: 0.75rem;
        color: #6b7280;
        margin-top: 8px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-2">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h4 mb-1">{{ $customer->user->name ?? 'N/A' }}</h1>
            <div class="text-muted small">
                Customer ID: #{{ $customer->customerID }} · Status: 
                <span class="badge {{ ($customer->user->isActive ?? false) ? 'bg-success' : 'bg-secondary' }}">
                    {{ ($customer->user->isActive ?? false) ? 'Active' : 'Inactive' }}
                </span>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.customers.edit', $customer) }}" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-pencil"></i> Edit
            </a>
            <a href="{{ route('admin.manage.client') }}" class="btn btn-sm btn-outline-secondary">
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
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#detail" type="button" role="tab">
                <i class="bi bi-info-circle"></i> Detail
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#booking-history" type="button" role="tab">
                <i class="bi bi-clock-history"></i> Booking History
            </button>
        </li>
    </ul>

    <div class="tab-content">
        <!-- Detail Tab -->
        <div class="tab-pane fade show active" id="detail" role="tabpanel">
            <div class="row g-3">
                <!-- User Info Grouping Box -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-person-circle"></i> User Info</h5>
                        </div>
                        <div class="card-body">
                            <dl class="mb-0">
                                <dt>User ID:</dt>
                                <dd>{{ $customer->user->userID ?? 'N/A' }}</dd>
                                
                                <dt>Username:</dt>
                                <dd>{{ $customer->user->username ?? 'N/A' }}</dd>
                                
                                <dt>Email:</dt>
                                <dd>{{ $customer->user->email ?? 'N/A' }}</dd>
                                
                                <dt>Phone:</dt>
                                <dd>{{ $customer->user->phone ?? 'N/A' }}</dd>
                                
                                <dt>Name:</dt>
                                <dd>{{ $customer->user->name ?? 'N/A' }}</dd>
                                
                                <dt>Last Login:</dt>
                                <dd>{{ $customer->user->lastLogin ? \Carbon\Carbon::parse($customer->user->lastLogin)->format('d M Y H:i') : 'N/A' }}</dd>
                                
                                <dt>Date Registered:</dt>
                                <dd>{{ $customer->user->dateRegistered ? \Carbon\Carbon::parse($customer->user->dateRegistered)->format('d M Y') : 'N/A' }}</dd>
                                
                                <dt>Date of Birth:</dt>
                                <dd>{{ $customer->user->DOB ? \Carbon\Carbon::parse($customer->user->DOB)->format('d M Y') : 'N/A' }}</dd>
                                
                                <dt>Age:</dt>
                                <dd>
                                    @if($customer->user->DOB)
                                        {{ \Carbon\Carbon::parse($customer->user->DOB)->age }} years
                                    @else
                                        N/A
                                    @endif
                                </dd>
                                
                                <dt>Status:</dt>
                                <dd>
                                    <span class="badge {{ ($customer->user->isActive ?? false) ? 'bg-success' : 'bg-secondary' }}">
                                        {{ ($customer->user->isActive ?? false) ? 'Active' : 'Inactive' }}
                                    </span>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>

                <!-- Customer Detail Grouping Box -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-person-badge"></i> Customer Detail</h5>
                        </div>
                        <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <dl class="row mb-0">
                                    <dt class="col-5">Type:</dt>
                                    <dd class="col-7">
                                        @if($customer->local)
                                            Local
                                        @elseif($customer->international)
                                            International
                                        @else
                                            N/A
                                        @endif
                                        @if($customer->localStudent || $customer->internationalStudent)
                                            / Student
                                        @elseif($customer->localUtmStaff || $customer->internationalUtmStaff)
                                            / Staff
                                        @endif
                                    </dd>
                                    
                                    <dt class="col-5">{{ $customer->local ? 'IC No' : 'Passport No' }}:</dt>
                                    <dd class="col-7">
                                        {{ $customer->local->ic_no ?? ($customer->international->passport_no ?? 'N/A') }}
                                    </dd>
                                    
                                    <dt class="col-5">{{ $customer->local ? 'State of Origin' : 'Country of Origin' }}:</dt>
                                    <dd class="col-7">
                                        {{ $customer->local->stateOfOrigin ?? ($customer->international->countryOfOrigin ?? 'N/A') }}
                                    </dd>
                                    
                                    <dt class="col-5">Address:</dt>
                                    <dd class="col-7">{{ $customer->address ?? 'N/A' }}</dd>
                                    
                                    <dt class="col-5">License Expiry Date:</dt>
                                    <dd class="col-7">
                                        @if($customer->customer_license)
                                            {{ \Carbon\Carbon::parse($customer->customer_license)->format('d M Y') }}
                                        @else
                                            N/A
                                        @endif
                                    </dd>
                                    
                                    <dt class="col-5">Emergency Contact:</dt>
                                    <dd class="col-7">{{ $customer->emergency_contact ?? 'N/A' }}</dd>
                                    
                                    <dt class="col-5">Default Bank Name:</dt>
                                    <dd class="col-7">{{ $customer->default_bank_name ?? 'N/A' }}</dd>
                                    
                                    <dt class="col-5">Default Account No:</dt>
                                    <dd class="col-7">{{ $customer->default_account_no ?? 'N/A' }}</dd>
                                    
                                    <dt class="col-5">Booking Times:</dt>
                                    <dd class="col-7">{{ $customer->bookings->count() ?? 0 }}</dd>
                                </dl>
                            </div>
                            <div class="col-md-6">
                                @if($customer->localStudent || $customer->internationalStudent)
                                    <h6 class="fw-semibold mb-3">Student Details</h6>
                                    <dl class="row mb-0">
                                        <dt class="col-5">Matric Number:</dt>
                                        <dd class="col-7">
                                            {{ $customer->localStudent->matric_number ?? ($customer->internationalStudent->matric_number ?? 'N/A') }}
                                        </dd>
                                        
                                        <dt class="col-5">College:</dt>
                                        <dd class="col-7">
                                            {{ $customer->localStudent->studentDetails->college ?? ($customer->internationalStudent->studentDetails->college ?? 'N/A') }}
                                        </dd>
                                        
                                        <dt class="col-5">Faculty:</dt>
                                        <dd class="col-7">
                                            {{ $customer->localStudent->studentDetails->faculty ?? ($customer->internationalStudent->studentDetails->faculty ?? 'N/A') }}
                                        </dd>
                                        
                                        <dt class="col-5">Programme:</dt>
                                        <dd class="col-7">
                                            {{ $customer->localStudent->studentDetails->programme ?? ($customer->internationalStudent->studentDetails->programme ?? 'N/A') }}
                                        </dd>
                                        
                                        <dt class="col-5">Year of Study:</dt>
                                        <dd class="col-7">
                                            {{ $customer->localStudent->studentDetails->yearOfStudy ?? ($customer->internationalStudent->studentDetails->yearOfStudy ?? 'N/A') }}
                                        </dd>
                                    </dl>
                                @elseif($customer->localUtmStaff || $customer->internationalUtmStaff)
                                    <h6 class="fw-semibold mb-3">Staff Details</h6>
                                    <dl class="row mb-0">
                                        <dt class="col-5">Staff No:</dt>
                                        <dd class="col-7">
                                            {{ $customer->localUtmStaff->staffID ?? ($customer->internationalUtmStaff->staffID ?? 'N/A') }}
                                        </dd>
                                        
                                        <dt class="col-5">Position:</dt>
                                        <dd class="col-7">
                                            {{ $customer->localUtmStaff->staffDetails->position ?? ($customer->internationalUtmStaff->staffDetails->position ?? 'N/A') }}
                                        </dd>
                                        
                                        <dt class="col-5">College:</dt>
                                        <dd class="col-7">
                                            {{ $customer->localUtmStaff->staffDetails->college ?? ($customer->internationalUtmStaff->staffDetails->college ?? 'N/A') }}
                                        </dd>
                                    </dl>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <br>

                <!-- Documentation Grouping Box -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0"><i class="bi bi-file-earmark-text"></i> Documentation</h5>
                        </div>
                        <div class="card-body">
                        <div class="row g-3">
                            <!-- License Pic -->
                            <div class="col-md-6">
                                <div class="card document-cell h-100">
                                    <div class="card-body text-center">
                                        <i class="bi bi-card-text fs-1 d-block mb-2" style="color: var(--hasta-red);"></i>
                                        <h6 class="fw-semibold">License</h6>
                                        @php
                                            // Get license image from customer table
                                            $licenseImg = $customer->customer_license_img ?? null;
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
                                                        data-bs-target="#viewLicenseModal">
                                                    <i class="bi bi-eye"></i> View
                                                </button>
                                                <button type="button" 
                                                        class="btn btn-sm" 
                                                        style="background: white; color: var(--hasta-red); border: 1px solid var(--hasta-red);"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#uploadLicenseModal">
                                                    <i class="bi bi-upload"></i> Upload
                                                </button>
                                            </div>
                                            
                                            <!-- View License Modal -->
                                            <div class="modal fade" id="viewLicenseModal" tabindex="-1">
                                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">License Document</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body text-center" style="min-height: 400px;">
                                                            <img src="{{ asset('storage/' . $licenseImg) }}" 
                                                                 alt="License Document" 
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
                                            <button type="button" 
                                                    class="btn btn-sm" 
                                                    style="background: white; color: var(--hasta-red); border: 1px solid var(--hasta-red);"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#uploadLicenseModal">
                                                <i class="bi bi-upload"></i> Upload
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- IC/Passport Pic -->
                            <div class="col-md-6">
                                <div class="card document-cell h-100">
                                    <div class="card-body text-center">
                                        <i class="bi bi-person-badge fs-1 d-block mb-2" style="color: var(--hasta-red);"></i>
                                        <h6 class="fw-semibold">{{ $customer->local ? 'IC' : 'Passport' }}</h6>
                                        @php
                                            // Get IC image from local table if local
                                            $icImg = null;
                                            if ($customer->local && isset($customer->local->ic_img)) {
                                                $icImg = $customer->local->ic_img;
                                            }
                                        @endphp
                                        @if($icImg)
                                            <div class="mb-2">
                                                <img src="{{ asset('storage/' . $icImg) }}" 
                                                     alt="{{ $customer->local ? 'IC' : 'Passport' }}" 
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
                                                        data-bs-target="#viewIcModal">
                                                    <i class="bi bi-eye"></i> View
                                                </button>
                                                <button type="button" 
                                                        class="btn btn-sm" 
                                                        style="background: white; color: var(--hasta-red); border: 1px solid var(--hasta-red);"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#uploadIcModal">
                                                    <i class="bi bi-upload"></i> Upload
                                                </button>
                                            </div>
                                            
                                            <!-- View IC Modal -->
                                            <div class="modal fade" id="viewIcModal" tabindex="-1">
                                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">{{ $customer->local ? 'IC' : 'Passport' }} Document</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body text-center" style="min-height: 400px;">
                                                            <img src="{{ asset('storage/' . $icImg) }}" 
                                                                 alt="{{ $customer->local ? 'IC' : 'Passport' }} Document" 
                                                                 class="img-fluid" 
                                                                 style="max-height: 70vh; width: auto; border-radius: 6px;"
                                                                 onerror="this.parentElement.innerHTML='<p class=\'text-muted\'>Image not found</p>';">
                                                        </div>
                                                        <div class="modal-footer">
                                                            <a href="{{ asset('storage/' . $icImg) }}" 
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
                                            <p class="small text-muted mb-2">No {{ $customer->local ? 'IC' : 'Passport' }} image uploaded</p>
                                            <button type="button" 
                                                    class="btn btn-sm" 
                                                    style="background: white; color: var(--hasta-red); border: 1px solid var(--hasta-red);"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#uploadIcModal">
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

        <!-- Upload License Modal -->
        <div class="modal fade" id="uploadLicenseModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Upload License</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST" action="{{ route('admin.customers.upload-license', $customer->customerID) }}" enctype="multipart/form-data">
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

        <!-- Upload IC/Passport Modal -->
        <div class="modal fade" id="uploadIcModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Upload {{ $customer->local ? 'IC' : 'Passport' }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST" action="{{ route('admin.customers.upload-ic', $customer->customerID) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">{{ $customer->local ? 'IC' : 'Passport' }} Image <span class="text-danger">*</span></label>
                                <input type="file" name="ic_img" class="form-control" accept="image/*,.pdf" required>
                                <small class="text-muted">Supported formats: JPG, PNG, GIF, PDF. Max size: 5MB</small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger">Upload {{ $customer->local ? 'IC' : 'Passport' }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Booking History Tab -->
        <div class="tab-pane fade" id="booking-history" role="tabpanel">
            <x-admin-page-header 
                title="{{ $customer->user->name ?? 'Customer' }}" 
                description="Booking history and payment information"
                :stats="[
                    ['label' => 'Username', 'value' => $customer->user->username ?? 'N/A', 'icon' => 'bi-person'],
                    ['label' => 'Total Bookings', 'value' => $totalBookings ?? 0, 'icon' => 'bi-calendar-check'],
                    ['label' => 'Outstanding Amount', 'value' => 'RM ' . number_format($totalOutstanding ?? 0, 2), 'icon' => 'bi-currency-dollar'],
                    ['label' => 'Wallet Amount', 'value' => 'RM ' . number_format($totalWalletAmount ?? 0, 2), 'icon' => 'bi-wallet']
                ]"
            />

            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="bi bi-calendar-check"></i> Booking History</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Booking ID</th>
                                    <th>Booking Date</th>
                                    <th>Plate No</th>
                                    <th>Payment Amount</th>
                                    <th>Status Deposit</th>
                                    <th>Booking Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($customer->bookings as $booking)
                                    @php
                                        $totalPaid = $booking->payments->where('payment_status', 'Verified')->sum('total_amount');
                                        $depositStatus = $booking->deposit_customer_choice ?? ($booking->deposit_refund_status === 'refunded' ? 'refunded' : 'hold');
                                    @endphp
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.bookings.reservations', ['search' => $booking->bookingID]) }}" class="text-decoration-none fw-bold text-primary">
                                                #{{ $booking->bookingID }}
                                            </a>
                                        </td>
                                        <td>
                                            {{ $booking->lastUpdateDate ? \Carbon\Carbon::parse($booking->lastUpdateDate)->format('d M Y') : 'N/A' }}
                                        </td>
                                        <td>
                                            {{ $booking->vehicle->plate_number ?? 'N/A' }}
                                        </td>
                                        <td>
                                            <strong>RM {{ number_format($totalPaid, 2) }}</strong>
                                        </td>
                                        <td>
                                            @if($depositStatus)
                                                <span class="badge {{ $depositStatus === 'hold' ? 'bg-info' : 'bg-warning text-dark' }}">
                                                    {{ ucfirst($depositStatus) }}
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge {{ $booking->booking_status === 'Confirmed' ? 'bg-success' : ($booking->booking_status === 'Pending' ? 'bg-warning text-dark' : ($booking->booking_status === 'Cancelled' ? 'bg-danger' : 'bg-info')) }}">
                                                {{ $booking->booking_status ?? 'N/A' }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            <i class="bi bi-calendar-x fs-1 d-block mb-2"></i>
                                            No booking history recorded yet.
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
</div>
@endsection
