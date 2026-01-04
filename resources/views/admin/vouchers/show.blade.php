@extends('layouts.admin')

@section('title', 'Voucher Details')

@section('content')
<div class="container-fluid py-2">
    <x-admin-page-header 
        title="Voucher Details" 
        description="View detailed information about the voucher"
    >
        <x-slot name="actions">
            <a href="{{ route('admin.vouchers.index') }}" class="btn btn-light text-danger pill-btn">
                <i class="bi bi-arrow-left me-1"></i> Back to List
            </a>
        </x-slot>
    </x-admin-page-header>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="bi bi-ticket-perforated"></i> Voucher Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4 fw-semibold">Voucher ID:</div>
                        <div class="col-md-8">#{{ $voucher->voucherID }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-semibold">Voucher Code:</div>
                        <div class="col-md-8"><code>{{ $voucher->voucher_code }}</code></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-semibold">Voucher Name:</div>
                        <div class="col-md-8">{{ $voucher->voucher_name ?? 'N/A' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-semibold">Description:</div>
                        <div class="col-md-8">{{ $voucher->description ?? 'N/A' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-semibold">Discount:</div>
                        <div class="col-md-8">
                            <strong>{{ $voucher->discount_display }}</strong>
                            <span class="badge bg-info ms-2">{{ ucfirst($voucher->discount_type) }}</span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-semibold">Restrictions:</div>
                        <div class="col-md-8">{{ $voucher->restrictions ?? 'None' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-semibold">Start Date:</div>
                        <div class="col-md-8">{{ $voucher->created_at?->format('d M Y') ?? 'N/A' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-semibold">Expiry Date:</div>
                        <div class="col-md-8">
                            @if($voucher->expiry_date)
                                {{ \Carbon\Carbon::parse($voucher->expiry_date)->format('d M Y') }}
                                @if(\Carbon\Carbon::parse($voucher->expiry_date)->isPast())
                                    <span class="badge bg-danger ms-2">Expired</span>
                                @endif
                            @else
                                <span class="text-muted">No expiry date</span>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-semibold">Total Vouchers:</div>
                        <div class="col-md-8">{{ $voucher->num_valid }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-semibold">Used Vouchers:</div>
                        <div class="col-md-8">{{ $voucher->num_applied }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-semibold">Available Vouchers:</div>
                        <div class="col-md-8"><strong>{{ $voucher->num_left }}</strong></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-semibold">Status:</div>
                        <div class="col-md-8">
                            <span class="badge {{ $voucher->is_active_status ? 'bg-success' : 'bg-secondary' }}">
                                {{ $voucher->active_status_text }}
                            </span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-semibold">Created Date:</div>
                        <div class="col-md-8">{{ $voucher->created_at?->format('d M Y H:i:s') ?? 'N/A' }}</div>
                    </div>
                </div>
            </div>

            @if($voucher->usages->count() > 0)
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-people"></i> Used By Customers ({{ $voucher->usages->count() }})</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Customer ID</th>
                                    <th>Customer Name</th>
                                    <th>Used Date</th>
                                    <th>Booking ID</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($voucher->usages as $usage)
                                    <tr>
                                        <td>#{{ $usage->customerID }}</td>
                                        <td>{{ $usage->customer->user->name ?? 'Unknown' }}</td>
                                        <td>{{ $usage->used_at?->format('d M Y H:i:s') ?? 'N/A' }}</td>
                                        <td>#{{ $usage->bookingID ?? 'N/A' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="bi bi-gear"></i> Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-primary" 
                                onclick="editVoucher({{ $voucher->voucherID }})"
                                data-bs-toggle="modal" data-bs-target="#editVoucherModal">
                            <i class="bi bi-pencil me-1"></i> Edit Voucher
                        </button>
                        @if($voucher->num_applied == 0)
                        <form action="{{ route('admin.vouchers.destroy', $voucher->voucherID) }}" 
                              method="POST" 
                              onsubmit="return confirm('Are you sure you want to delete this voucher?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="bi bi-trash me-1"></i> Delete Voucher
                            </button>
                        </form>
                        @else
                        <button type="button" class="btn btn-danger w-100" disabled title="Cannot delete voucher with usage history">
                            <i class="bi bi-trash me-1"></i> Delete Voucher
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal will be loaded via JavaScript -->
<script>
function editVoucher(voucherId) {
    fetch(`/admin/vouchers/edit-data/${voucherId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('edit_voucher_id').value = data.voucherID;
            document.getElementById('edit_voucher_code').value = data.voucher_code || '';
            document.getElementById('edit_voucher_name').value = data.voucher_name || '';
            document.getElementById('edit_description').value = data.description || '';
            document.getElementById('edit_discount_type').value = data.discount_type || 'percentage';
            document.getElementById('edit_discount_value').value = data.discount_value || '';
            document.getElementById('edit_expiry_date').value = data.expiry_date || '';
            document.getElementById('edit_num_valid').value = data.num_valid || '';
            document.getElementById('edit_restrictions').value = data.restrictions || '';
            document.getElementById('edit_isActive').checked = data.isActive || false;
        });
}
</script>
@endsection

