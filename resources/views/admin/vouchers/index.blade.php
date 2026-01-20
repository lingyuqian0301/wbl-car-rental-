@extends('layouts.admin')

@section('title', 'Voucher Management')

@push('styles')
<style>
    .voucher-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        padding: 15px;
        margin-bottom: 25px;
    }
    .voucher-table {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    .table-header {
        background: var(--admin-red);
        color: white;
        padding: 15px 20px;
        font-weight: 600;
    }
    .table thead th {
        background: var(--admin-red-light);
        color: var(--admin-red-dark);
        font-weight: 600;
        border-bottom: 2px solid var(--admin-red);
        padding: 12px;
        font-size: 0.9rem;
    }
    .table tbody td {
        padding: 12px;
        vertical-align: middle;
    }
    .badge-status {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 500;
    }
    .btn-used-count {
        cursor: pointer;
        text-decoration: underline;
        color: var(--admin-red);
    }
    .btn-used-count:hover {
        color: var(--admin-red-dark);
    }
</style>
@endpush

@if(!isset($showHeader) || $showHeader)
@section('content')
<div class="container-fluid py-2">
    <x-admin-page-header 
        title="Voucher Management" 
        description="Manage vouchers and track usage"
        :stats="[
            ['label' => 'Total Vouchers', 'value' => $totalVouchers, 'icon' => 'bi-ticket-perforated'],
            ['label' => 'Active Vouchers', 'value' => $activeVouchers, 'icon' => 'bi-check-circle'],
            ['label' => 'Total Used', 'value' => $totalUsed, 'icon' => 'bi-arrow-repeat'],
            ['label' => 'Total Applied', 'value' => $totalApplied, 'icon' => 'bi-calendar-check']
        ]"
        :date="$today"
    >
        <x-slot name="actions">
            <button type="button" class="btn btn-light text-danger pill-btn" data-bs-toggle="modal" data-bs-target="#addVoucherModal">
                <i class="bi bi-plus-circle me-1"></i> Add Voucher
            </button>
        </x-slot>
    </x-admin-page-header>
@else
<div class="container-fluid py-2">
@endif

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

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Filters -->
    <div class="voucher-card">
        <form method="GET" action="{{ route('admin.vouchers.index') }}" class="row g-2">
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" 
                       class="form-control form-control-sm" 
                       placeholder="Voucher ID, Code, or Name">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Sort By</label>
                <select name="sort_by" class="form-select form-select-sm">
                    <option value="latest" {{ request('sort_by') === 'latest' ? 'selected' : '' }}>Latest</option>
                    <option value="oldest" {{ request('sort_by') === 'oldest' ? 'selected' : '' }}>Oldest</option>
                    <option value="code_asc" {{ request('sort_by') === 'code_asc' ? 'selected' : '' }}>Code (A-Z)</option>
                    <option value="code_desc" {{ request('sort_by') === 'code_desc' ? 'selected' : '' }}>Code (Z-A)</option>
                    <option value="expiry_asc" {{ request('sort_by') === 'expiry_asc' ? 'selected' : '' }}>Expiry (Asc)</option>
                    <option value="expiry_desc" {{ request('sort_by') === 'expiry_desc' ? 'selected' : '' }}>Expiry (Desc)</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">&nbsp;</label>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-danger btn-sm flex-fill">
                        <i class="bi bi-funnel"></i> Filter
                    </button>
                    @if(request()->has('search') || request()->has('status') || request()->has('sort_by'))
                    <a href="{{ route('admin.vouchers.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-x-circle"></i>
                    </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <!-- Vouchers Table -->
    <div class="voucher-table">
        <div class="table-header">
            <i class="bi bi-ticket-perforated"></i> Vouchers ({{ $vouchers->total() }})
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Voucher ID</th>
                        <th>Loyalty Card ID</th>
                        <th>Discount Type</th>
                        <th>Discount Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vouchers as $voucher)
                        @php
                            $isActive = $voucher->voucher_isActive ?? false;
                            
                            $statusText = $isActive ? 'Active' : 'Inactive';
                            $statusClass = $isActive ? 'bg-success' : 'bg-secondary';
                            
                            $discountAmount = $voucher->discount_amount ?? 0;
                            $discountType = $voucher->discount_type ?? 'N/A';
                        @endphp
                        <tr>
                            <td><strong>#{{ $voucher->voucherID }}</strong></td>
                            <td>{{ $voucher->loyaltyCardID ?? 'N/A' }}</td>
                            <td>{{ $discountType }}</td>
                            <td><strong>RM {{ number_format($discountAmount, 2) }}</strong></td>
                            <td>
                                <span class="badge badge-status {{ $statusClass }}">
                                    {{ $statusText }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-primary" 
                                            onclick="editVoucher({{ $voucher->voucherID }})"
                                            data-bs-toggle="modal" data-bs-target="#editVoucherModal">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    @if($voucher->num_applied == 0)
                                    <form action="{{ route('admin.vouchers.destroy', $voucher->voucherID) }}" 
                                          method="POST" class="d-inline" 
                                          onsubmit="return confirm('Are you sure you want to delete this voucher?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox"></i> No vouchers found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <!-- Pagination -->
        @if($vouchers->hasPages())
        <div class="p-3 border-top">
            {{ $vouchers->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Add Voucher Modal -->
<div class="modal fade" id="addVoucherModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Add New Voucher</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.vouchers.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Loyalty Card ID</label>
                            <input type="number" name="loyaltyCardID" class="form-control" 
                                   placeholder="Optional" value="{{ old('loyaltyCardID') }}">
                            @error('loyaltyCardID')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Discount Type <span class="text-danger">*</span></label>
                            <select name="discount_type" class="form-select" required>
                                <option value="">Select type</option>
                                <option value="percentage" {{ old('discount_type') === 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                                <option value="flat" {{ old('discount_type') === 'flat' ? 'selected' : '' }}>Flat Amount (RM)</option>
                            </select>
                            @error('discount_type')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Discount Amount <span class="text-danger">*</span></label>
                            <input type="number" name="discount_amount" class="form-control" required 
                                   step="0.01" min="0" placeholder="e.g., 10.00" value="{{ old('discount_amount') }}">
                            @error('discount_amount')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="voucher_isActive" class="form-select" required>
                                <option value="1" {{ old('voucher_isActive', '1') == '1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('voucher_isActive') == '0' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('voucher_isActive')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Create Voucher</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Voucher Modal -->
<div class="modal fade" id="editVoucherModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-pencil"></i> Edit Voucher</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="editVoucherForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Loyalty Card ID</label>
                            <input type="number" name="loyaltyCardID" id="edit_loyaltyCardID" class="form-control">
                            @error('loyaltyCardID')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Discount Type <span class="text-danger">*</span></label>
                            <select name="discount_type" id="edit_discount_type" class="form-select" required>
                                <option value="">Select type</option>
                                <option value="percentage">Percentage (%)</option>
                                <option value="flat">Flat Amount (RM)</option>
                            </select>
                            @error('discount_type')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Discount Amount <span class="text-danger">*</span></label>
                            <input type="number" name="discount_amount" id="edit_discount_amount" class="form-control" 
                                   required step="0.01" min="0">
                            @error('discount_amount')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="voucher_isActive" id="edit_voucher_isActive" class="form-select" required>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                            @error('voucher_isActive')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Voucher</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Used Customers Modal -->
<div class="modal fade" id="usedCustomersModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="bi bi-people"></i> Customers Who Used This Voucher</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="usedCustomersList">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@if(!isset($showHeader) || $showHeader)
@endsection
@endif

@push('scripts')
<script>
    let vouchersData = @json($vouchers->items());

    function editVoucher(voucherID) {
        // Always fetch voucher data via AJAX to get latest data
        fetch(`/admin/vouchers/${voucherID}/edit-data`)
            .then(response => response.json())
            .then(data => {
                populateEditForm(data);
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error loading voucher data');
            });
    }

    function populateEditForm(voucher) {
        document.getElementById('editVoucherForm').action = `/admin/vouchers/${voucher.voucherID}`;
        document.getElementById('edit_loyaltyCardID').value = voucher.loyaltyCardID || '';
        document.getElementById('edit_discount_type').value = voucher.discount_type || '';
        document.getElementById('edit_discount_amount').value = voucher.discount_amount || voucher.discount_value || '';
        document.getElementById('edit_voucher_isActive').value = voucher.voucher_isActive ?? (voucher.isActive ? '1' : '0');
    }

    function showUsedCustomers(voucherID) {
        const modal = new bootstrap.Modal(document.getElementById('usedCustomersModal'));
        modal.show();
        
        document.getElementById('usedCustomersList').innerHTML = 
            '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
        
        fetch(`/admin/vouchers/${voucherID}/used-customers`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    let html = '';
                    if (data.customers.length === 0) {
                        html = '<div class="text-center py-4 text-muted"><i class="bi bi-inbox"></i> No customers have used this voucher yet.</div>';
                    } else {
                        html = '<div class="table-responsive"><table class="table table-hover"><thead><tr><th>Customer Name</th><th>Customer ID</th><th>Used At</th></tr></thead><tbody>';
                        data.customers.forEach(customer => {
                            html += `<tr><td>${customer.customer_name}</td><td>#${customer.customer_id}</td><td>${customer.used_at}</td></tr>`;
                        });
                        html += '</tbody></table></div>';
                        html += `<div class="mt-3"><strong>Total: ${data.total} customer(s)</strong></div>`;
                    }
                    document.getElementById('usedCustomersList').innerHTML = html;
                } else {
                    document.getElementById('usedCustomersList').innerHTML = 
                        '<div class="alert alert-danger">Error loading customers.</div>';
                }
            })
            .catch(error => {
                document.getElementById('usedCustomersList').innerHTML = 
                    '<div class="alert alert-danger">Error loading customers.</div>';
            });
    }
</script>
@endpush

