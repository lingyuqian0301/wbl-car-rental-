@extends('layouts.staff')

@section('title', 'Customer Management')

@push('styles')
<style>
    .customer-tooltip {
        position: relative;
        cursor: pointer;
    }
    .customer-tooltip-content {
        visibility: hidden;
        position: absolute;
        z-index: 1000;
        background-color: #333;
        color: white;
        padding: 12px;
        border-radius: 8px;
        font-size: 0.85rem;
        width: 300px;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%);
        margin-bottom: 10px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        opacity: 0;
        transition: opacity 0.3s;
    }
    .customer-tooltip-content::after {
        content: "";
        position: absolute;
        top: 100%;
        left: 50%;
        transform: translateX(-50%);
        border: 6px solid transparent;
        border-top-color: #333;
    }
    .customer-tooltip:hover .customer-tooltip-content {
        visibility: visible;
        opacity: 1;
    }
    .tooltip-row {
        display: flex;
        justify-content: space-between;
        padding: 4px 0;
        border-bottom: 1px solid rgba(255,255,255,0.1);
    }
    .tooltip-row:last-child {
        border-bottom: none;
    }
    .tooltip-label {
        font-weight: 600;
        margin-right: 10px;
    }
</style>
@endpush

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0"><i class="bi bi-people"></i> Customer Management</h1>
        <div>
            <a href="{{ route('staff.customers.create') }}" class="btn btn-sm btn-staff">
                <i class="bi bi-plus-circle"></i> Create Customer
            </a>
            <button class="btn btn-sm btn-outline-staff" onclick="exportReport()">
                <i class="bi bi-download"></i> Export Report
            </button>
            <button class="btn btn-sm btn-outline-staff" onclick="removeSelected()">
                <i class="bi bi-trash"></i> Remove Selected
            </button>
        </div>
    </div>

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

    <!-- Filters -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2">
                <div class="col-md-3">
                    <label class="form-label small">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           class="form-control form-control-sm" 
                           placeholder="ID, Name, Email, Phone, Matric">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Faculty</label>
                    <select name="faculty" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach($faculties as $faculty)
                            <option value="{{ $faculty }}" {{ request('faculty') == $faculty ? 'selected' : '' }}>
                                {{ $faculty }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">College</label>
                    <select name="college" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach($colleges as $college)
                            <option value="{{ $college }}" {{ request('college') == $college ? 'selected' : '' }}>
                                {{ $college }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Booking Count</label>
                    <input type="number" name="booking_count" value="{{ request('booking_count') }}" 
                           class="form-control form-control-sm" 
                           placeholder="Min bookings">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Booking Date From</label>
                    <input type="date" name="booking_date_from" value="{{ request('booking_date_from') }}" 
                           class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Booking Date To</label>
                    <input type="date" name="booking_date_to" value="{{ request('booking_date_to') }}" 
                           class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Blacklist Status</label>
                    <select name="blacklist_status" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="active" {{ request('blacklist_status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="blacklisted" {{ request('blacklist_status') == 'blacklisted' ? 'selected' : '' }}>Blacklisted</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Customer ID</label>
                    <input type="number" name="customer_id" value="{{ request('customer_id') }}" 
                           class="form-control form-control-sm" 
                           placeholder="Customer ID">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Customer Name</label>
                    <input type="text" name="customer_name" value="{{ request('customer_name') }}" 
                           class="form-control form-control-sm" 
                           placeholder="Customer Name">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Vehicle ID</label>
                    <input type="number" name="vehicle_id" value="{{ request('vehicle_id') }}" 
                           class="form-control form-control-sm" 
                           placeholder="Vehicle ID">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Vehicle Brand</label>
                    <input type="text" name="vehicle_brand" value="{{ request('vehicle_brand') }}" 
                           class="form-control form-control-sm" 
                           placeholder="Brand">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Vehicle Model</label>
                    <input type="text" name="vehicle_model" value="{{ request('vehicle_model') }}" 
                           class="form-control form-control-sm" 
                           placeholder="Model">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Plate No</label>
                    <input type="text" name="plate_no" value="{{ request('plate_no') }}" 
                           class="form-control form-control-sm" 
                           placeholder="Plate No">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Sort By</label>
                    <select name="sort_by" class="form-select form-select-sm">
                        <option value="name_asc" {{ request('sort_by') == 'name_asc' ? 'selected' : '' }}>Name (A-Z)</option>
                        <option value="name_desc" {{ request('sort_by') == 'name_desc' ? 'selected' : '' }}>Name (Z-A)</option>
                        <option value="latest_booking" {{ request('sort_by') == 'latest_booking' ? 'selected' : '' }}>Latest Booking</option>
                        <option value="highest_rental" {{ request('sort_by') == 'highest_rental' ? 'selected' : '' }}>Highest Rental</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-sm btn-staff w-100">
                        <i class="bi bi-funnel"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Customer List -->
    <div class="card">
        <div class="card-header bg-staff text-white d-flex justify-content-between align-items-center">
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
                            <th>Bookings</th>
                            <th>Latest Rental</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $customer)
                            <tr class="{{ $customer->customer_status === 'blacklist' ? 'table-danger' : ($customer->customer_status === 'deleted' ? 'table-secondary' : '') }}">
                                <td>
                                    <input type="checkbox" class="customer-checkbox" value="{{ $customer->customerID }}">
                                </td>
                                <td>#{{ $customer->customerID }}</td>
                                <td>
                                    <div class="customer-tooltip">
                                        <strong>{{ $customer->fullname }}</strong>
                                        <div class="customer-tooltip-content">
                                            <div class="tooltip-row">
                                                <span class="tooltip-label">Customer ID:</span>
                                                <span>#{{ $customer->customerID }}</span>
                                            </div>
                                            <div class="tooltip-row">
                                                <span class="tooltip-label">Full Name:</span>
                                                <span>{{ $customer->fullname }}</span>
                                            </div>
                                            @if($customer->matric_number)
                                            <div class="tooltip-row">
                                                <span class="tooltip-label">Matric No:</span>
                                                <span>{{ $customer->matric_number }}</span>
                                            </div>
                                            @endif
                                            @if($customer->ic_number)
                                            <div class="tooltip-row">
                                                <span class="tooltip-label">IC Number:</span>
                                                <span>{{ $customer->ic_number }}</span>
                                            </div>
                                            @endif
                                            @if($customer->phone)
                                            <div class="tooltip-row">
                                                <span class="tooltip-label">Phone:</span>
                                                <span>{{ $customer->phone }}</span>
                                            </div>
                                            @endif
                                            @if($customer->email)
                                            <div class="tooltip-row">
                                                <span class="tooltip-label">Email:</span>
                                                <span>{{ $customer->email }}</span>
                                            </div>
                                            @endif
                                            @if($customer->faculty)
                                            <div class="tooltip-row">
                                                <span class="tooltip-label">Faculty:</span>
                                                <span>{{ $customer->faculty }}</span>
                                            </div>
                                            @endif
                                            @if($customer->college)
                                            <div class="tooltip-row">
                                                <span class="tooltip-label">College:</span>
                                                <span>{{ $customer->college }}</span>
                                            </div>
                                            @endif
                                            @if($customer->customer_type)
                                            <div class="tooltip-row">
                                                <span class="tooltip-label">Customer Type:</span>
                                                <span>{{ $customer->customer_type }}</span>
                                            </div>
                                            @endif
                                            @if($customer->registration_date)
                                            <div class="tooltip-row">
                                                <span class="tooltip-label">Registration Date:</span>
                                                <span>{{ \Carbon\Carbon::parse($customer->registration_date)->format('d M Y') }}</span>
                                            </div>
                                            @endif
                                            @if($customer->emergency_contact)
                                            <div class="tooltip-row">
                                                <span class="tooltip-label">Emergency Contact:</span>
                                                <span>{{ $customer->emergency_contact }}</span>
                                            </div>
                                            @endif
                                            @if($customer->country)
                                            <div class="tooltip-row">
                                                <span class="tooltip-label">Country:</span>
                                                <span>{{ $customer->country }}</span>
                                            </div>
                                            @endif
                                            @if($customer->customer_license)
                                            <div class="tooltip-row">
                                                <span class="tooltip-label">License:</span>
                                                <span>{{ $customer->customer_license }}</span>
                                            </div>
                                            @endif
                                            <div class="tooltip-row">
                                                <span class="tooltip-label">Total Bookings:</span>
                                                <span>{{ $customer->bookings_count ?? 0 }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $customer->bookings_count ?? 0 }}</span>
                                </td>
                                <td>
                                    @if($customer->bookings && $customer->bookings->count() > 0)
                                        @php
                                            $latestBooking = $customer->bookings->first();
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
                                <td>
                                    @if($customer->customer_status === 'blacklist')
                                        <span class="badge bg-danger">Blacklisted</span>
                                    @elseif($customer->customer_status === 'deleted')
                                        <span class="badge bg-secondary">Deleted</span>
                                    @else
                                        <span class="badge bg-success">Active</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('staff.customers.show', $customer) }}" 
                                           class="btn btn-outline-primary" title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('staff.customers.edit', $customer) }}" 
                                           class="btn btn-outline-info" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-outline-{{ $customer->customer_status === 'blacklist' ? 'success' : 'danger' }}"
                                                onclick="toggleBlacklist({{ $customer->customerID }}, '{{ $customer->customer_status === 'blacklist' ? 'false' : 'true' }}')"
                                                title="{{ $customer->customer_status === 'blacklist' ? 'Remove from Blacklist' : 'Blacklist' }}">
                                            <i class="bi bi-{{ $customer->customer_status === 'blacklist' ? 'check-circle' : 'x-circle' }}"></i>
                                        </button>
                                        <button type="button" 
                                                class="btn btn-outline-danger"
                                                onclick="deleteCustomer({{ $customer->customerID }})"
                                                title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
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

    <!-- Blacklist Modal -->
    <div class="modal fade" id="blacklistModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="blacklistModalTitle">Blacklist Customer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="blacklistForm" method="POST">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" id="blacklistCustomerId" name="customer_id">
                        <div class="mb-3">
                            <label class="form-label">Reason</label>
                            <textarea name="blacklist_reason" class="form-control" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-staff">Confirm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Customer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this customer? This action cannot be undone.</p>
                    <p class="text-danger"><strong>Note:</strong> Customers with existing bookings cannot be deleted.</p>
                </div>
                <div class="modal-footer">
                    <form id="deleteForm" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleSelectAll() {
            const selectAll = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.customer-checkbox');
            checkboxes.forEach(cb => cb.checked = selectAll.checked);
        }

        function toggleBlacklist(customerId, isBlacklisting) {
            const modal = new bootstrap.Modal(document.getElementById('blacklistModal'));
            document.getElementById('blacklistModalTitle').textContent = isBlacklisting === 'true' ? 'Blacklist Customer' : 'Remove from Blacklist';
            document.getElementById('blacklistForm').action = `/staff/customers/${customerId}/toggle-blacklist`;
            document.getElementById('blacklistCustomerId').value = customerId;
            const textarea = document.querySelector('#blacklistForm textarea[name="blacklist_reason"]');
            if (isBlacklisting === 'false') {
                textarea.required = false;
            } else {
                textarea.required = true;
            }
            modal.show();
        }

        function deleteCustomer(customerId) {
            const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            document.getElementById('deleteForm').action = `/staff/customers/${customerId}`;
            modal.show();
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
                form.action = '{{ route('staff.customers.delete-selected') }}';
                form.innerHTML = `
                    @csrf
                    ${selected.map(id => `<input type="hidden" name="selected_customers[]" value="${id}">`).join('')}
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function exportReport() {
            const params = new URLSearchParams(window.location.search);
            window.location.href = `/staff/customers/export?${params.toString()}`;
        }
    </script>
@endsection








