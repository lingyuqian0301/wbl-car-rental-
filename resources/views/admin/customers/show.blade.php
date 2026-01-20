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
    .reservation-info-text {
        font-size: 0.75rem;
        color: #6b7280;
        margin-top: 4px;
    }
    .table th {
        font-weight: 600;
        font-size: 0.875rem;
        color: var(--hasta-red-dark);
    }
    .table td {
        vertical-align: middle;
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
            <button class="nav-link {{ ($activeTab ?? 'detail') === 'detail' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#detail" type="button" role="tab">
                <i class="bi bi-info-circle"></i> Detail
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ ($activeTab ?? '') === 'booking-history' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#booking-history" type="button" role="tab">
                <i class="bi bi-clock-history"></i> Booking History
            </button>
        </li>
    </ul>

    <div class="tab-content">
        <!-- Detail Tab -->
        <div class="tab-pane fade {{ ($activeTab ?? 'detail') === 'detail' ? 'show active' : '' }}" id="detail" role="tabpanel">
            <div class="row g-3">
                <!-- User Info Grouping Box -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-person-circle"></i> User Info</h5>
                        </div>
                        <div class="card-body">
                            <dl class="mb-0">
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Customer ID:</dt>
                                    <dd class="d-inline ms-2">{{ $customer->customerID ?? 'N/A' }}</dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">User ID:</dt>
                                    <dd class="d-inline ms-2">{{ $customer->user->userID ?? 'N/A' }}</dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Username:</dt>
                                    <dd class="d-inline ms-2">{{ $customer->user->username ?? 'N/A' }}</dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Email:</dt>
                                    <dd class="d-inline ms-2">{{ $customer->user->email ?? 'N/A' }}</dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Phone:</dt>
                                    <dd class="d-inline ms-2">{{ $customer->user->phone ?? 'N/A' }}</dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Name:</dt>
                                    <dd class="d-inline ms-2">{{ $customer->user->name ?? 'N/A' }}</dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Last Login:</dt>
                                    <dd class="d-inline ms-2">{{ $customer->user->lastLogin ? \Carbon\Carbon::parse($customer->user->lastLogin)->format('d M Y H:i') : 'N/A' }}</dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Date Registered:</dt>
                                    <dd class="d-inline ms-2">{{ $customer->user->dateRegistered ? \Carbon\Carbon::parse($customer->user->dateRegistered)->format('d M Y') : 'N/A' }}</dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Date of Birth:</dt>
                                    <dd class="d-inline ms-2">{{ $customer->user->DOB ? \Carbon\Carbon::parse($customer->user->DOB)->format('d M Y') : 'N/A' }}</dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Age:</dt>
                                    <dd class="d-inline ms-2">
                                        @if($customer->user->DOB)
                                            {{ \Carbon\Carbon::parse($customer->user->DOB)->age }} years
                                        @else
                                            N/A
                                        @endif
                                    </dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Status:</dt>
                                    <dd class="d-inline ms-2">
                                        <span class="badge {{ ($customer->user->isActive ?? false) ? 'bg-success' : 'bg-secondary' }}">
                                            {{ ($customer->user->isActive ?? false) ? 'Active' : 'Inactive' }}
                                        </span>
                                    </dd>
                                </div>
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
                            <dl class="mb-0">
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Customer ID:</dt>
                                    <dd class="d-inline ms-2">{{ $customer->customerID ?? 'N/A' }}</dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Type:</dt>
                                    <dd class="d-inline ms-2">
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
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">{{ $customer->local ? 'IC No' : 'Passport No' }}:</dt>
                                    <dd class="d-inline ms-2">
                                        {{ $customer->local->ic_no ?? ($customer->international->passport_no ?? 'N/A') }}
                                    </dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">{{ $customer->local ? 'State of Origin' : 'Country of Origin' }}:</dt>
                                    <dd class="d-inline ms-2">
                                        {{ $customer->local->stateOfOrigin ?? ($customer->international->countryOfOrigin ?? 'N/A') }}
                                    </dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Address:</dt>
                                    <dd class="d-inline ms-2">{{ $customer->address ?? 'N/A' }}</dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">License Expiry Date:</dt>
                                    <dd class="d-inline ms-2">
                                        @if($customer->customer_license)
                                            {{ \Carbon\Carbon::parse($customer->customer_license)->format('d M Y') }}
                                        @else
                                            N/A
                                        @endif
                                    </dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Emergency Contact:</dt>
                                    <dd class="d-inline ms-2">{{ $customer->emergency_contact ?? 'N/A' }}</dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Default Bank Name:</dt>
                                    <dd class="d-inline ms-2">{{ $customer->default_bank_name ?? 'N/A' }}</dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Default Account No:</dt>
                                    <dd class="d-inline ms-2">{{ $customer->default_account_no ?? 'N/A' }}</dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="d-inline fw-semibold">Booking Times:</dt>
                                    <dd class="d-inline ms-2">{{ $customer->bookings->count() ?? 0 }}</dd>
                                </div>
                                @if($customer->localStudent || $customer->internationalStudent)
                                <div class="mt-3 pt-3 border-top">
                                    <h6 class="fw-semibold mb-3">Student Details</h6>
                                    <div class="mb-2">
                                        <dt class="d-inline fw-semibold">Matric Number:</dt>
                                        <dd class="d-inline ms-2">
                                            {{ $customer->localStudent->matric_number ?? ($customer->internationalStudent->matric_number ?? 'N/A') }}
                                        </dd>
                                    </div>
                                    <div class="mb-2">
                                        <dt class="d-inline fw-semibold">College:</dt>
                                        <dd class="d-inline ms-2">
                                            {{ $customer->localStudent->studentDetails->college ?? ($customer->internationalStudent->studentDetails->college ?? 'N/A') }}
                                        </dd>
                                    </div>
                                    <div class="mb-2">
                                        <dt class="d-inline fw-semibold">Faculty:</dt>
                                        <dd class="d-inline ms-2">
                                            {{ $customer->localStudent->studentDetails->faculty ?? ($customer->internationalStudent->studentDetails->faculty ?? 'N/A') }}
                                        </dd>
                                    </div>
                                    <div class="mb-2">
                                        <dt class="d-inline fw-semibold">Programme:</dt>
                                        <dd class="d-inline ms-2">
                                            {{ $customer->localStudent->studentDetails->programme ?? ($customer->internationalStudent->studentDetails->programme ?? 'N/A') }}
                                        </dd>
                                    </div>
                                    <div class="mb-2">
                                        <dt class="d-inline fw-semibold">Year of Study:</dt>
                                        <dd class="d-inline ms-2">
                                            {{ $customer->localStudent->studentDetails->yearOfStudy ?? ($customer->internationalStudent->studentDetails->yearOfStudy ?? 'N/A') }}
                                        </dd>
                                    </div>
                                </div>
                                @elseif($customer->localUtmStaff || $customer->internationalUtmStaff)
                                <div class="mt-3 pt-3 border-top">
                                    <h6 class="fw-semibold mb-3">Staff Details</h6>
                                    <div class="mb-2">
                                        <dt class="d-inline fw-semibold">Staff No:</dt>
                                        <dd class="d-inline ms-2">
                                            {{ $customer->localUtmStaff->staffID ?? ($customer->internationalUtmStaff->staffID ?? 'N/A') }}
                                        </dd>
                                    </div>
                                    <div class="mb-2">
                                        <dt class="d-inline fw-semibold">Position:</dt>
                                        <dd class="d-inline ms-2">
                                            {{ $customer->localUtmStaff->staffDetails->position ?? ($customer->internationalUtmStaff->staffDetails->position ?? 'N/A') }}
                                        </dd>
                                    </div>
                                    <div class="mb-2">
                                        <dt class="d-inline fw-semibold">College:</dt>
                                        <dd class="d-inline ms-2">
                                            {{ $customer->localUtmStaff->staffDetails->college ?? ($customer->internationalUtmStaff->staffDetails->college ?? 'N/A') }}
                                        </dd>
                                    </div>
                                </div>
                                @endif
                            </dl>
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
                                                <img src="{{ getFileUrl($licenseImg) }}" 
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
                                                            <img src="{{ getFileUrl($licenseImg) }}" 
                                                                 alt="License Document" 
                                                                 class="img-fluid" 
                                                                 style="max-height: 70vh; width: auto; border-radius: 6px;"
                                                                 onerror="this.parentElement.innerHTML='<p class=\'text-muted\'>Image not found</p>';">
                                                        </div>
                                                        <div class="modal-footer">
                                                            <a href="{{ getFileUrl($licenseImg) }}" 
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
                                            // Get IC image from local table for local customers, otherwise from customer table for international
                                            if ($customer->local) {
                                                $icImg = $customer->local->ic_img ?? null;
                                            } else {
                                                $icImg = $customer->customer_ic_img ?? null;
                                            }
                                        @endphp
                                        @if($icImg)
                                            <div class="mb-2">
                                                <img src="{{ getFileUrl($icImg) }}" 
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
                                                            <img src="{{ getFileUrl($icImg) }}" 
                                                                 alt="{{ $customer->local ? 'IC' : 'Passport' }} Document" 
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
        <div class="tab-pane fade {{ ($activeTab ?? '') === 'booking-history' ? 'show active' : '' }}" id="booking-history" role="tabpanel">
            <!-- Header Box (similar to car detail page) -->
            <div class="grouping-box mb-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h1 class="h4 mb-1">{{ $customer->user->name ?? 'Customer' }}</h1>
                        <div class="text-muted small mb-2">
                            <div class="mb-1">
                                <i class="bi bi-person"></i> Username: <strong>{{ $customer->user->username ?? 'N/A' }}</strong>
                            </div>
                            <div>
                                <i class="bi bi-calendar-check"></i> Number of Bookings: <strong>{{ $totalBookings ?? 0 }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Booking List -->
            <div class="card">
                <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-calendar-check"></i> Booking History</h5>
                    <span class="badge bg-light text-dark">{{ $totalBookings ?? 0 }} total</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Booking ID</th>
                                    <th>Vehicle</th>
                                    <th>Plate No</th>
                                    <th>Duration</th>
                                    <th>Pickup Date</th>
                                    <th>Return Date</th>
                                    <th>Total Amount</th>
                                    <th>Paid</th>
                                    <th>Outstanding</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $sortedBookings = $customer->bookings->sortByDesc(function($booking) {
                                        return $booking->rental_start_date ? \Carbon\Carbon::parse($booking->rental_start_date)->timestamp : 0;
                                    });
                                @endphp
                                @forelse($sortedBookings as $booking)
                                    @php
                                        $pickupDate = $booking->rental_start_date ?? null;
                                        $returnDate = $booking->rental_end_date ?? null;
                                        $duration = $pickupDate && $returnDate ? \Carbon\Carbon::parse($pickupDate)->diffInDays(\Carbon\Carbon::parse($returnDate)) + 1 : ($booking->duration ?? 'N/A');
                                        $bookingDate = $booking->lastUpdateDate ?? $booking->created_at ?? null;
                                        
                                        // Calculate total payment amount
                                        $totalPaid = $booking->payments ? $booking->payments->where('payment_status', 'Verified')->sum('total_amount') : 0;
                                        $totalAmount = ($booking->deposit_amount ?? 0) + ($booking->rental_amount ?? 0);
                                        $outstandingBalance = max(0, $totalAmount - $totalPaid);
                                        
                                        $vehicle = $booking->vehicle;
                                    @endphp
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.bookings.reservations.show', $booking->bookingID) }}" class="text-decoration-none fw-bold text-primary">
                                                #{{ $booking->bookingID }}
                                            </a>
                                        </td>
                                        <td>
                                            @if($vehicle)
                                                <div class="fw-semibold">{{ $vehicle->vehicle_brand ?? 'N/A' }} {{ $vehicle->vehicle_model ?? '' }}</div>
                                                <div class="text-muted small">{{ $vehicle->vehicleType ?? 'N/A' }}</div>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            <strong>{{ $vehicle->plate_number ?? ($vehicle->plate_no ?? 'N/A') }}</strong>
                                        </td>
                                        <td>
                                            @if($duration !== 'N/A')
                                                {{ $duration }} {{ $duration == 1 ? 'day' : 'days' }}
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>
                                            @if($pickupDate)
                                                <div>{{ \Carbon\Carbon::parse($pickupDate)->format('d M Y') }}</div>
                                                <div class="text-muted small">{{ \Carbon\Carbon::parse($pickupDate)->format('h:i A') }}</div>
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>
                                            @if($returnDate)
                                                <div>{{ \Carbon\Carbon::parse($returnDate)->format('d M Y') }}</div>
                                                <div class="text-muted small">{{ \Carbon\Carbon::parse($returnDate)->format('h:i A') }}</div>
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>
                                            <strong>RM {{ number_format($totalAmount, 2) }}</strong>
                                        </td>
                                        <td>
                                            <span class="text-success fw-semibold">RM {{ number_format($totalPaid, 2) }}</span>
                                        </td>
                                        <td>
                                            @if($outstandingBalance > 0)
                                                <span class="text-danger fw-semibold">RM {{ number_format($outstandingBalance, 2) }}</span>
                                            @else
                                                <span class="text-success">RM 0.00</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge {{ $booking->booking_status === 'Confirmed' ? 'bg-success' : ($booking->booking_status === 'Pending' ? 'bg-warning text-dark' : ($booking->booking_status === 'Cancelled' ? 'bg-danger' : ($booking->booking_status === 'Completed' ? 'bg-info' : 'bg-secondary'))) }}">
                                                {{ $booking->booking_status ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('admin.bookings.reservations.show', $booking->bookingID) }}" 
                                                   class="btn btn-outline-primary" 
                                                   title="View Booking Details">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="11" class="text-center text-muted py-4">
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
