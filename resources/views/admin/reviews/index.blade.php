@extends('layouts.admin')

@section('title', 'Reviews')

@push('styles')
<style>
    .rating-stars {
        color: #ffc107;
        font-size: 1.2rem;
    }
    .review-comment {
        max-width: 400px;
        word-wrap: break-word;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-2">
    <x-admin-page-header 
        title="Reviews" 
        description="Manage customer reviews and ratings"
        :stats="[
            ['label' => 'Total Reviews', 'value' => $totalReviews, 'icon' => 'bi-star'],
            ['label' => 'Average Rating', 'value' => $averageRating . ' / 5', 'icon' => 'bi-star-fill'],
            ['label' => 'Reviews Today', 'value' => $reviewsToday, 'icon' => 'bi-calendar-day'],
            ['label' => 'This Month', 'value' => $reviewsThisMonth, 'icon' => 'bi-calendar-month']
        ]"
        :date="$today"
    />

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

    <!-- Search and Filters -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.bookings.reviews') }}" class="row g-3">
                <!-- Search -->
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Search</label>
                    <input type="text" name="search" value="{{ $search }}" 
                           class="form-control form-control-sm" 
                           placeholder="Booking ID, Customer Name, Plate No">
                </div>
                
                <!-- Rating Filter -->
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Rating</label>
                    <select name="filter_rating" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="5" {{ $filterRating == '5' ? 'selected' : '' }}>5 Stars</option>
                        <option value="4" {{ $filterRating == '4' ? 'selected' : '' }}>4 Stars</option>
                        <option value="3" {{ $filterRating == '3' ? 'selected' : '' }}>3 Stars</option>
                        <option value="2" {{ $filterRating == '2' ? 'selected' : '' }}>2 Stars</option>
                        <option value="1" {{ $filterRating == '1' ? 'selected' : '' }}>1 Star</option>
                    </select>
                </div>
                
                <!-- Date From -->
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Date From</label>
                    <input type="date" name="filter_date_from" value="{{ $filterDateFrom }}" 
                           class="form-control form-control-sm">
                </div>
                
                <!-- Date To -->
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Date To</label>
                    <input type="date" name="filter_date_to" value="{{ $filterDateTo }}" 
                           class="form-control form-control-sm">
                </div>
                
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-sm btn-danger">
                        <i class="bi bi-funnel"></i> Apply Filters
                    </button>
                    @if($search || $filterRating || $filterDateFrom || $filterDateTo)
                        <a href="{{ route('admin.bookings.reviews') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> Clear
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Reviews Table -->
    <div class="card">
        <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-star"></i> Reviews</h5>
            <span class="badge bg-light text-dark">{{ $reviews->total() }} total</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Booking ID</th>
                            <th>Customer Name</th>
                            <th>Vehicle Plate No</th>
                            <th>Rating</th>
                            <th>Comment</th>
                            <th>Review Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reviews as $review)
                            @php
                                $booking = $review->booking;
                                $customer = $booking->customer ?? null;
                                $user = $customer->user ?? null;
                                $vehicle = $booking->vehicle ?? null;
                            @endphp
                            <tr>
                                <td>
                                    <a href="{{ route('admin.bookings.reservations', ['search' => $review->bookingID, 'tab' => 'bookings']) }}" class="text-decoration-none fw-bold text-primary">
                                        #{{ $review->bookingID }}
                                    </a>
                                </td>
                                <td>{{ $user->name ?? 'N/A' }}</td>
                                <td>{{ $vehicle->plate_number ?? 'N/A' }}</td>
                                <td>
                                    <div class="rating-stars">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="bi bi-star{{ $i <= $review->rating ? '-fill' : '' }}"></i>
                                        @endfor
                                    </div>
                                    <small class="text-muted">({{ $review->rating }}/5)</small>
                                </td>
                                <td>
                                    <div class="review-comment">
                                        {{ $review->comment ?? 'No comment' }}
                                    </div>
                                </td>
                                <td>
                                    {{ $review->review_date ? \Carbon\Carbon::parse($review->review_date)->format('d M Y') : 'N/A' }}
                                </td>
                                <td>
                                    <a href="{{ route('admin.bookings.reservations', ['search' => $review->bookingID, 'tab' => 'bookings']) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i> View Booking
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    No reviews found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($reviews->hasPages())
            <div class="card-footer">
                {{ $reviews->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

