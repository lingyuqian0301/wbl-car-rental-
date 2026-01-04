@extends('layouts.admin')

@section('title', 'Customer Management')

@push('styles')
<style>
    .customer-info-text {
        font-size: 0.75rem;
        color: #6b7280;
        line-height: 1.4;
    }
    .customer-info-text div {
        margin-bottom: 2px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-2">
    <!-- Header -->
    <x-admin-page-header 
        title="Customer Management" 
        description="Manage all customer information"
        :stats="[
            ['label' => 'Total Customers', 'value' => $totalCustomers, 'icon' => 'bi-people'],
            ['label' => 'With Bookings', 'value' => $customersWithBookings, 'icon' => 'bi-calendar-check'],
            ['label' => 'New Today', 'value' => $totalCustomersToday, 'icon' => 'bi-person-plus']
        ]"
        :date="$today"
    />

    <!-- Success/Error Messages -->
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

    <!-- Action Buttons - Right Top Corner -->
    <div class="d-flex justify-content-end mb-3">
        <div class="d-flex gap-2">
            <a href="{{ route('admin.customers.create') }}" class="btn btn-sm btn-light text-danger">
                <i class="bi bi-plus-circle me-1"></i> Create
            </a>
            <div class="btn-group">
                <button type="button" class="btn btn-sm btn-light dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="bi bi-download me-1"></i> Export
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="{{ route('admin.customers.export-pdf', request()->query()) }}">
                        <i class="bi bi-file-pdf me-2"></i> Export PDF
                    </a></li>
                    <li><a class="dropdown-item" href="{{ route('admin.customers.export-excel', request()->query()) }}">
                        <i class="bi bi-file-excel me-2"></i> Export Excel
                    </a></li>
                </ul>
            </div>
            <button class="btn btn-sm btn-light text-danger" onclick="removeSelected()">
                <i class="bi bi-trash me-1"></i> Remove
            </button>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.manage.client') }}" class="row g-3">
                <!-- Search -->
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Search</label>
                    <input type="text" name="search" value="{{ $search }}" 
                           class="form-control form-control-sm" 
                           placeholder="ID, Name, Email, Phone, Matric No">
                </div>
                
                <!-- Sort -->
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Sort By</label>
                    <select name="sort_by" class="form-select form-select-sm">
                        <option value="name_asc" {{ $sortBy === 'name_asc' ? 'selected' : '' }}>Name (A-Z)</option>
                        <option value="name_desc" {{ $sortBy === 'name_desc' ? 'selected' : '' }}>Name (Z-A)</option>
                        <option value="latest_booking" {{ $sortBy === 'latest_booking' ? 'selected' : '' }}>Latest Booking</option>
                        <option value="highest_rental" {{ $sortBy === 'highest_rental' ? 'selected' : '' }}>Highest Rental Time</option>
                    </select>
                </div>
                
                <!-- Faculty Filter -->
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Faculty</label>
                    <select name="faculty" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach($faculties as $faculty)
                            <option value="{{ $faculty }}" {{ $faculty === request('faculty') ? 'selected' : '' }}>
                                {{ $faculty }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <!-- College Filter -->
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">College</label>
                    <select name="college" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach($colleges as $college)
                            <option value="{{ $college }}" {{ $college === request('college') ? 'selected' : '' }}>
                                {{ $college }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Booking Count Filter -->
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Booking Count</label>
                    <input type="number" name="booking_count" value="{{ $bookingCount }}" 
                           class="form-control form-control-sm" 
                           placeholder="Min bookings">
                </div>
                
                <!-- Customer Status Filter -->
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Customer Status</label>
                    <select name="customer_status" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="active" {{ $customerStatus === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="blacklisted" {{ $customerStatus === 'blacklisted' ? 'selected' : '' }}>Blacklisted</option>
                        <option value="deleted" {{ $customerStatus === 'deleted' ? 'selected' : '' }}>Deleted</option>
                    </select>
                </div>
                
                <!-- Customer Nation Filter -->
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Customer Nation</label>
                    <select name="customer_nation" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="local" {{ $customerNation === 'local' ? 'selected' : '' }}>Local</option>
                        <option value="international" {{ $customerNation === 'international' ? 'selected' : '' }}>International</option>
                    </select>
                </div>
                
                <!-- Customer Type Filter -->
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Customer Type</label>
                    <select name="customer_type" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="student" {{ $customerType === 'student' ? 'selected' : '' }}>Student</option>
                        <option value="staff" {{ $customerType === 'staff' ? 'selected' : '' }}>Staff</option>
                    </select>
                </div>
                
                <!-- Filter Button -->
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-sm btn-danger w-100">
                        <i class="bi bi-funnel"></i> Filter
                    </button>
                </div>
                
                <!-- Clear Button -->
                @if($search || $faculty || $college || $bookingCount || $customerStatus || $customerNation || $customerType)
                <div class="col-md-2 d-flex align-items-end">
                    <a href="{{ route('admin.manage.client') }}" class="btn btn-sm btn-outline-secondary w-100">
                        <i class="bi bi-x-circle"></i> Clear
                    </a>
                </div>
                @endif
            </form>
        </div>
    </div>

    <!-- Customer List -->
    <div class="card">
        <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Customers</h5>
            <span class="badge bg-light text-dark">{{ $customers->total() }} total</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="30">
                                <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                            </th>
                            <th>Customer ID</th>
                            <th>Name</th>
                            <th>No of Booking Time</th>
                            <th>Edit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $customer)
                            @php
                                $user = $customer->user;
                                $latestBooking = $customer->bookings->first();
                                $latestBookingDate = $latestBooking ? ($latestBooking->rental_start_date ?? null) : null;
                                
                                // Get customer details
                                $phone = $user->phone ?? $customer->phone_number ?? 'N/A';
                                $address = $customer->address ?? 'N/A';
                                $stateCountry = $customer->local->stateOfOrigin ?? $customer->international->countryOfOrigin ?? 'N/A';
                                $license = $customer->customer_license ?? 'N/A';
                                $icPassport = $customer->local->ic_no ?? $customer->international->passport_no ?? 'N/A';
                                $emergencyContact = $customer->emergency_contact ?? 'N/A';
                                $college = $customer->studentDetail->college ?? 'N/A';
                                $faculty = $customer->studentDetail->faculty ?? 'N/A';
                                $programme = $customer->studentDetail->programme ?? 'N/A';
                                $yearOfStudy = $customer->studentDetail->yearOfStudy ?? 'N/A';
                            @endphp
                            <tr class="{{ $customer->customer_status === 'blacklist' ? 'table-danger' : ($customer->customer_status === 'deleted' ? 'table-secondary' : '') }}">
                                <td>
                                    <input type="checkbox" class="customer-checkbox" value="{{ $customer->customerID }}">
                                </td>
                                <td>
                                    <strong>#{{ $customer->customerID }}</strong>
                                    <div class="customer-info-text">
                                        <div>
                                            <span class="badge {{ ($user->isActive ?? false) ? 'bg-success' : 'bg-secondary' }} badge-sm">
                                                {{ ($user->isActive ?? false) ? 'Active' : 'Inactive' }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <strong>{{ $user->name ?? 'Unknown' }}</strong>
                                    <div class="customer-info-text">
                                        <div><strong>Phone:</strong> {{ $phone }}</div>
                                        <div><strong>Address:</strong> {{ $address }}</div>
                                        <div><strong>State/Country:</strong> {{ $stateCountry }}</div>
                                        <div><strong>License:</strong> {{ $license }}</div>
                                        <div><strong>IC/Passport:</strong> {{ $icPassport }}</div>
                                        <div><strong>Emergency Contact:</strong> {{ $emergencyContact }}</div>
                                        <div><strong>College:</strong> {{ $college }}</div>
                                        <div><strong>Faculty:</strong> {{ $faculty }}</div>
                                        <div><strong>Programme:</strong> {{ $programme }}</div>
                                        <div><strong>Year of Study:</strong> {{ $yearOfStudy }}</div>
                                    </div>
                                </td>
                                <td>
                                    <strong>{{ $customer->bookings_count ?? 0 }}</strong>
                                    @if($latestBookingDate)
                                        <div class="customer-info-text">
                                            <div>Latest: {{ \Carbon\Carbon::parse($latestBookingDate)->format('d M Y') }}</div>
                                        </div>
                                    @else
                                        <div class="customer-info-text">
                                            <div>No bookings yet</div>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.customers.edit', $customer) }}" 
                                       class="btn btn-sm btn-outline-primary" title="Edit Customer">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    No customers found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($customers->hasPages())
            <div class="card-footer">
                {{ $customers->links() }}
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    function toggleSelectAll() {
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.customer-checkbox');
        checkboxes.forEach(cb => cb.checked = selectAll.checked);
    }

    function removeSelected() {
        const selected = Array.from(document.querySelectorAll('.customer-checkbox:checked')).map(cb => cb.value);
        if (selected.length === 0) {
            alert('Please select at least one customer.');
            return;
        }
        if (confirm(`Are you sure you want to delete ${selected.length} customer(s)?\n\nNote: Customers with existing bookings cannot be deleted.`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route('admin.customers.delete-selected') }}';
            form.innerHTML = `
                @csrf
                ${selected.map(id => `<input type="hidden" name="selected_customers[]" value="${id}">`).join('')}
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }
</script>
@endpush
@endsection
