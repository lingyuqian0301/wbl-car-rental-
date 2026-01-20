@extends('layouts.admin')

@section('title', 'Other')

@push('styles')
<style>
    .pagination .page-link {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    .pagination .page-item:first-child .page-link,
    .pagination .page-item:last-child .page-link {
        padding: 0.35rem 0.6rem;
        font-size: 1rem;
        font-weight: 600;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-2">
    <!-- Dynamic Tabs -->
    <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ $activeTab === 'vehicle' ? 'active' : '' }}" 
               href="{{ route('admin.vehicles.others', ['tab' => 'vehicle']) }}">
                <i class="bi bi-car-front"></i> Vehicle
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ $activeTab === 'voucher' ? 'active' : '' }}" 
               href="{{ route('admin.vehicles.others', ['tab' => 'voucher']) }}">
                <i class="bi bi-ticket-perforated"></i> Voucher
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ $activeTab === 'reward' ? 'active' : '' }}" 
               href="{{ route('admin.vehicles.others', ['tab' => 'reward']) }}">
                <i class="bi bi-gift"></i> Reward
            </a>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content">
        @if($activeTab === 'vehicle')
            @include('admin.vehicles.vehicle-list')
        @elseif($activeTab === 'voucher')
            @include('admin.vouchers.index')
        @elseif($activeTab === 'reward')
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="bi bi-gift" style="font-size: 3rem; color: #dc2626;"></i>
                    <h4 class="mt-3">Reward Management</h4>
                    <p class="text-muted">Reward management functionality coming soon.</p>
                </div>
            </div>
        @endif
    </div>

    <!-- Add Category Modal -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="#">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Category Name</label>
                            <input type="text" name="name" class="form-control" required placeholder="e.g., Voucher, Reward">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Category description"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Add Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
