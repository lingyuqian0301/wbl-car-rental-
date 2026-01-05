@extends('layouts.admin')

@section('title', 'Leasing Management')

@section('content')
<div class="container-fluid py-2">
    <!-- Dynamic Tabs -->
    <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ $activeTab === 'owner' ? 'active' : '' }}" 
               href="{{ route('admin.leasing.index', ['tab' => 'owner']) }}">
                <i class="bi bi-building me-1"></i> Owner
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ $activeTab === 'vehicle' ? 'active' : '' }}" 
               href="{{ route('admin.leasing.index', ['tab' => 'vehicle']) }}">
                <i class="bi bi-car-front me-1"></i> Vehicle
            </a>
        </li>
    </ul>

    <!-- Owner Tab Content -->
    @if($activeTab === 'owner')
        <x-admin-page-header 
            title="Owner Leasing" 
            description="Manage owner leasing information"
            :stats="[
                ['label' => 'Total Owners', 'value' => $totalOwners ?? 0, 'icon' => 'bi-building'],
                ['label' => 'Active Owners', 'value' => $activeOwners ?? 0, 'icon' => 'bi-check-circle'],
                ['label' => 'Total Cars', 'value' => $totalCars ?? 0, 'icon' => 'bi-car-front']
            ]"
            :date="$today ?? null"
        >
            <x-slot name="actions">
                <a href="{{ route('admin.leasing.owner.create') }}" class="btn btn-light text-danger pill-btn">
                    <i class="bi bi-plus-circle me-1"></i> Add New Owner
                </a>
            </x-slot>
        </x-admin-page-header>

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

        <div class="card">
            <div class="card-body">
                @if(isset($owners) && $owners->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Owner ID</th>
                                    <th>IC No</th>
                                    <th>Contact Number</th>
                                    <th>Email</th>
                                    <th>Registration Date</th>
                                    <th>Leasing Price</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($owners as $owner)
                                    <tr>
                                        <td>#{{ $owner->ownerID }}</td>
                                        <td>{{ $owner->ic_no ?? 'N/A' }}</td>
                                        <td>{{ $owner->contact_number ?? 'N/A' }}</td>
                                        <td>{{ $owner->email ?? 'N/A' }}</td>
                                        <td>{{ $owner->registration_date ? \Carbon\Carbon::parse($owner->registration_date)->format('d M Y') : 'N/A' }}</td>
                                        <td>RM {{ number_format($owner->leasing_price ?? 0, 2) }}</td>
                                        <td>
                                            <span class="badge {{ ($owner->isActive ?? false) ? 'bg-success' : 'bg-secondary' }}">
                                                {{ ($owner->isActive ?? false) ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('admin.leasing.owner.show', $owner) }}" class="btn btn-outline-primary">
                                                    <i class="bi bi-eye"></i> View
                                                </a>
                                                <a href="{{ route('admin.leasing.owner.edit', $owner) }}" class="btn btn-outline-secondary">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </a>
                                                <form action="{{ route('admin.leasing.owner.destroy', $owner) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this owner?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger">
                                                        <i class="bi bi-trash"></i> Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $owners->links() }}
                    </div>
                @else
                    <div class="alert alert-info">
                        No owners found. <a href="{{ route('admin.leasing.owner.create') }}">Create your first owner</a>.
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Vehicle Tab Content -->
    @if($activeTab === 'vehicle')
        <x-admin-page-header 
            title="Vehicle Leasing" 
            description="Manage vehicle leasing bookings (more than 15 days)"
            :stats="[
                ['label' => 'Total Bookings', 'value' => $totalBookings ?? 0, 'icon' => 'bi-calendar'],
                ['label' => 'Ongoing', 'value' => $ongoingBookings ?? 0, 'icon' => 'bi-clock-history'],
                ['label' => 'Total Revenue', 'value' => 'RM ' . number_format($totalRevenue ?? 0, 2), 'icon' => 'bi-cash-stack'],
                ['label' => 'Total Paid', 'value' => 'RM ' . number_format($totalPaid ?? 0, 2), 'icon' => 'bi-wallet']
            ]"
            :date="$today ?? null"
        >
            <x-slot name="actions">
                <a href="{{ route('admin.bookings.reservations') }}" class="btn btn-light text-danger pill-btn">
                    <i class="bi bi-plus-circle me-1"></i> Create New
                </a>
            </x-slot>
        </x-admin-page-header>

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

        <div class="card">
            <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Vehicle Leasing Bookings</h5>
                <span class="badge bg-light text-dark">{{ $bookings->total() ?? 0 }} total</span>
            </div>
            <div class="card-body p-0">
                @if(isset($bookings) && $bookings->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Booking ID</th>
                                    <th>Customer Name</th>
                                    <th>Vehicle Plate No</th>
                                    <th>Payment Price</th>
                                    <th>Invoice No</th>
                                    <th>Payment Receipt Pic</th>
                                    <th>Payment Status</th>
                                    <th>Pickup Detail</th>
                                    <th>Return Detail</th>
                                    <th>Booking Status</th>
                                    <th>Duration</th>
                                    <th>Served By</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($bookings as $booking)
                                    @php
                                        $customer = $booking->customer;
                                        $user = $customer->user ?? null;
                                        $vehicle = $booking->vehicle;
                                        $latestPayment = $booking->payments()->orderBy('payment_date', 'desc')->first();
                                        $invoice = $booking->invoice;
                                        
                                        // Calculate payment status
                                        $totalPaid = $booking->payments()->where('payment_status', 'Verified')->sum('total_amount');
                                        $totalAmount = ($booking->deposit_amount ?? 0) + ($booking->rental_amount ?? 0);
                                        $paymentStatus = 'Deposit';
                                        if ($totalPaid >= $totalAmount) {
                                            $paymentStatus = 'Full';
                                        } elseif ($totalPaid > 0) {
                                            $paymentStatus = 'Deposit';
                                        } else {
                                            $paymentStatus = 'Unpaid';
                                        }
                                        
                                        // Check if refunded
                                        if ($booking->booking_status === 'Cancelled' || $booking->booking_status === 'Refunding') {
                                            $refundedPayments = $booking->payments()->where('payment_status', 'Refunded')->sum('total_amount');
                                            if ($refundedPayments > 0) {
                                                $paymentStatus = 'Refunded';
                                            }
                                        }
                                        
                                        $staffServed = $booking->staff_served ? \App\Models\User::find($booking->staff_served) : null;
                                    @endphp
                                    <tr>
                                        <td><strong>#{{ $booking->bookingID }}</strong></td>
                                        <td>{{ $user->name ?? 'Unknown' }}</td>
                                        <td>
                                            <strong>{{ $vehicle->plate_number ?? 'N/A' }}</strong>
                                            <div class="reservation-info-text">
                                                <div><strong>Model:</strong> {{ $vehicle->vehicle_model ?? 'N/A' }}</div>
                                                <div><strong>Brand:</strong> {{ $vehicle->vehicle_brand ?? 'N/A' }}</div>
                                            </div>
                                        </td>
                                        <td>
                                            <strong>RM {{ number_format($totalAmount, 2) }}</strong>
                                            <div class="reservation-info-text">
                                                <div>Paid: RM {{ number_format($totalPaid, 2) }}</div>
                                            </div>
                                        </td>
                                        <td>
                                            @if($invoice)
                                                <strong>{{ $invoice->invoice_number ?? 'N/A' }}</strong>
                                                <div class="reservation-info-text">
                                                    <div>
                                                        <a href="{{ route('invoices.generate', $booking->bookingID) }}" 
                                                           target="_blank" class="text-primary">
                                                            <i class="bi bi-file-pdf"></i> View Invoice
                                                        </a>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-muted">No invoice</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($latestPayment && $latestPayment->transaction_reference)
                                                <a href="{{ asset('storage/' . $latestPayment->transaction_reference) }}" 
                                                   target="_blank" class="text-primary">
                                                    <i class="bi bi-image"></i> View Receipt
                                                </a>
                                                <div class="reservation-info-text">
                                                    <div><strong>Payment ID:</strong> {{ $latestPayment->paymentID ?? 'N/A' }}</div>
                                                    <div><strong>Date:</strong> {{ $latestPayment->payment_date ? \Carbon\Carbon::parse($latestPayment->payment_date)->format('d M Y') : 'N/A' }}</div>
                                                    <div><strong>Time:</strong> {{ $latestPayment->payment_date ? \Carbon\Carbon::parse($latestPayment->payment_date)->format('H:i') : 'N/A' }}</div>
                                                </div>
                                            @else
                                                <span class="text-muted">No receipt</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge {{ $paymentStatus === 'Full' ? 'bg-success' : ($paymentStatus === 'Deposit' ? 'bg-warning text-dark' : ($paymentStatus === 'Refunded' ? 'bg-info' : 'bg-secondary')) }}">
                                                {{ $paymentStatus }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="reservation-info-text">
                                                <div><strong>Date:</strong> {{ $booking->rental_start_date ? \Carbon\Carbon::parse($booking->rental_start_date)->format('d M Y') : 'N/A' }}</div>
                                                <div><strong>Time:</strong> {{ $booking->pickup_time ?? 'N/A' }}</div>
                                                <div><strong>Location:</strong> {{ $booking->pickup_point ?? 'N/A' }}</div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="reservation-info-text">
                                                <div><strong>Date:</strong> {{ $booking->rental_end_date ? \Carbon\Carbon::parse($booking->rental_end_date)->format('d M Y') : 'N/A' }}</div>
                                                <div><strong>Time:</strong> {{ $booking->return_time ?? 'N/A' }}</div>
                                                <div><strong>Location:</strong> {{ $booking->return_point ?? 'N/A' }}</div>
                                            </div>
                                        </td>
                                        <td>
                                            <select class="form-select form-select-sm booking-status-select" 
                                                    data-booking-id="{{ $booking->bookingID }}"
                                                    data-current-status="{{ $booking->booking_status }}">
                                                @foreach($bookingStatuses as $status)
                                                    <option value="{{ $status }}" {{ $booking->booking_status === $status ? 'selected' : '' }}>
                                                        {{ $status }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            {{ $booking->duration ?? 0 }} days
                                        </td>
                                        <td>
                                            <select class="form-select form-select-sm served-by-select" 
                                                    data-booking-id="{{ $booking->bookingID }}"
                                                    data-current-served="{{ $booking->staff_served }}">
                                                <option value="">Not Assigned</option>
                                                @foreach($staffUsers as $staff)
                                                    <option value="{{ $staff->userID }}" {{ $booking->staff_served == $staff->userID ? 'selected' : '' }}>
                                                        {{ $staff->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            @php
                                                $vehicle = $booking->vehicle;
                                                $contractDoc = $vehicle->documents->where('document_type', 'contract')->first() ?? null;
                                            @endphp
                                            <div class="d-flex gap-1">
                                                <a href="{{ route('admin.bookings.reservations') }}?search={{ $booking->bookingID }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </a>
                                                @if($contractDoc && $contractDoc->fileURL)
                                                    <a href="{{ asset('storage/' . $contractDoc->fileURL) }}" target="_blank" class="btn btn-sm btn-outline-info" title="View Contract">
                                                        <i class="bi bi-file-pdf"></i> View Detail
                                                    </a>
                                                @else
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" disabled title="No contract available">
                                                        <i class="bi bi-file-pdf"></i> View Detail
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3 p-3">
                        {{ $bookings->links() }}
                    </div>
                @else
                    <div class="alert alert-info m-3">
                        No vehicle leasing bookings found (bookings longer than 15 days).
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>

@push('styles')
<style>
    .reservation-info-text {
        font-size: 0.75rem;
        color: #6b7280;
        line-height: 1.4;
    }
    .reservation-info-text div {
        margin-bottom: 2px;
    }
</style>
@endpush

@push('scripts')
<script>
    // Booking status update
    document.querySelectorAll('.booking-status-select').forEach(select => {
        select.addEventListener('change', function() {
            let bookingId = this.dataset.bookingId;
            let newStatus = this.value;
            let oldStatus = this.dataset.currentStatus;
            
            if (oldStatus == newStatus) {
                return;
            }
            
            fetch(`/admin/bookings/reservations/${bookingId}/update-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    booking_status: newStatus
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.dataset.currentStatus = newStatus;
                    location.reload();
                } else {
                    alert('Failed to update booking status.');
                    this.value = oldStatus;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating booking status.');
                this.value = oldStatus;
            });
        });
    });

    // Update Served By
    document.querySelectorAll('.served-by-select').forEach(select => {
        select.addEventListener('change', function() {
            let bookingId = this.dataset.bookingId;
            let newServedBy = this.value;
            let oldServedBy = this.dataset.currentServed;
            
            if (oldServedBy == newServedBy) {
                return;
            }
            
            fetch(`/admin/bookings/reservations/${bookingId}/update-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    staff_served: newServedBy || null
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.dataset.currentServed = newServedBy;
                    location.reload();
                } else {
                    alert('Failed to update served by.');
                    this.value = oldServedBy;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating served by.');
                this.value = oldServedBy;
            });
        });
    });
</script>
@endpush
@endsection
