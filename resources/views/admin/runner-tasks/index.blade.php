@extends('layouts.admin')

@section('title', 'Runner Tasks')

@push('styles')
<style>
    .filter-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        padding: 15px;
        margin-bottom: 25px;
    }
    .runner-task-table {
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
    .filter-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 10px;
        align-items: end;
    }
    .filter-row > div {
        min-width: 0;
    }
    .filter-row .form-label {
        font-size: 0.75rem;
        margin-bottom: 4px;
    }
    .filter-row .form-control,
    .filter-row .form-select {
        font-size: 0.85rem;
        padding: 4px 8px;
    }
    .filter-row .btn {
        font-size: 0.85rem;
        padding: 4px 12px;
    }
    .reservation-info-text {
        font-size: 0.85rem;
        line-height: 1.6;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-2">
    <x-admin-page-header 
        title="Runner Tasks" 
        description="Manage runner assignments for bookings requiring pickup/return outside HASTA HQ Office"
        :stats="[
            ['label' => 'Total Bookings', 'value' => $totalBookings, 'icon' => 'bi-calendar-check'],
            ['label' => 'Assigned', 'value' => $assignedCount, 'icon' => 'bi-person-check'],
            ['label' => 'Unassigned', 'value' => $unassignedCount, 'icon' => 'bi-person-x'],
            ['label' => 'Today', 'value' => $today->format('d M Y'), 'icon' => 'bi-calendar-day']
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
    <div class="filter-card">
        <form method="GET" action="{{ route('admin.runner.tasks') }}" class="row g-3">
            <!-- Search -->
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Search (Booking ID / Customer Name)</label>
                <input type="text" name="search" class="form-control form-control-sm" 
                       value="{{ $search }}" placeholder="Enter booking ID or customer name">
            </div>
            
            <!-- Sort -->
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Sort By</label>
                <select name="sort" class="form-select form-select-sm">
                    <option value="booking_desc" {{ $sort === 'booking_desc' ? 'selected' : '' }}>Booking ID (Desc)</option>
                    <option value="pickup_desc" {{ $sort === 'pickup_desc' ? 'selected' : '' }}>Pickup Date (Desc)</option>
                    <option value="pickup_asc" {{ $sort === 'pickup_asc' ? 'selected' : '' }}>Pickup Date (Asc)</option>
                </select>
            </div>
            
            <!-- Booking Status Filter -->
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Booking Status</label>
                <select name="filter_booking_status" class="form-select form-select-sm">
                    <option value="">All</option>
                    <option value="done" {{ $filterBookingStatus === 'done' ? 'selected' : '' }}>Done</option>
                    <option value="current" {{ $filterBookingStatus === 'current' ? 'selected' : '' }}>Current</option>
                    <option value="upcoming" {{ $filterBookingStatus === 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                </select>
            </div>
            
            <!-- Assigned Filter -->
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Assigned Status</label>
                <select name="filter_assigned" class="form-select form-select-sm">
                    <option value="">All</option>
                    <option value="assigned" {{ $filterAssigned === 'assigned' ? 'selected' : '' }}>Assigned</option>
                    <option value="unassigned" {{ $filterAssigned === 'unassigned' ? 'selected' : '' }}>Unassigned</option>
                </select>
            </div>
            
            <!-- Runner Filter -->
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Runner</label>
                <select name="filter_runner" class="form-select form-select-sm">
                    <option value="">All Runners</option>
                    @foreach($runners as $runner)
                        <option value="{{ $runner->userID }}" {{ $filterRunner == $runner->userID ? 'selected' : '' }}>
                            {{ $runner->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <!-- Buttons -->
            <div class="col-md-1">
                <label class="form-label small fw-semibold">&nbsp;</label>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-danger btn-sm flex-fill">
                        <i class="bi bi-funnel"></i> Filter
                    </button>
                    @if($search || $filterBookingStatus || $filterAssigned || $filterRunner)
                    <a href="{{ route('admin.runner.tasks') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-x-circle"></i>
                    </a>
                    @endif
                </div>
            </div>
        </form>
    </div>
    <br>

    <!-- Runner Tasks Table -->
    <div class="runner-task-table">
        <div class="table-header">
            <i class="bi bi-truck"></i> Runner Tasks ({{ $bookings->total() }})
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Pickup Detail</th>
                        <th>Return Detail</th>
                        <th>Assigned Status</th>
                        <th>Runner</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookings as $booking)
                        @php
                            $pickupDateTime = $booking->rental_start_date ? \Carbon\Carbon::parse($booking->rental_start_date) : null;
                            $returnDateTime = $booking->rental_end_date ? \Carbon\Carbon::parse($booking->rental_end_date) : null;
                            $pickupLocation = $booking->pickup_point ?? null;
                            $returnLocation = $booking->return_point ?? null;
                            $isPickupAtOffice = ($pickupLocation === 'HASTA HQ Office');
                            $isReturnAtOffice = ($returnLocation === 'HASTA HQ Office');
                            $assignedStatus = $booking->staff_served ? 'assigned' : 'unassigned';
                        @endphp
                        <tr>
                            <td>
                                <a href="{{ route('admin.bookings.reservations.show', ['booking' => $booking->bookingID]) }}" 
                                   class="text-decoration-none fw-bold text-primary">
                                    #{{ $booking->bookingID }}
                                </a>
                            </td>
                            <td>
                                <div class="reservation-info-text">
                                    @if($pickupDateTime)
                                        <div><strong>Date:</strong> {{ $pickupDateTime->format('d M Y') }}</div>
                                        <div><strong>Time:</strong> {{ $pickupDateTime->format('H:i') }}</div>
                                        <div><strong>Location:</strong> {{ $isPickupAtOffice ? '-' : ($pickupLocation ?? 'N/A') }}</div>
                                    @else
                                        <div>N/A</div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="reservation-info-text">
                                    @if($returnDateTime)
                                        <div><strong>Date:</strong> {{ $returnDateTime->format('d M Y') }}</div>
                                        <div><strong>Time:</strong> {{ $returnDateTime->format('H:i') }}</div>
                                        <div><strong>Location:</strong> {{ $isReturnAtOffice ? '-' : ($returnLocation ?? 'N/A') }}</div>
                                    @else
                                        <div>N/A</div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="badge {{ $assignedStatus === 'assigned' ? 'bg-success' : 'bg-warning text-dark' }}">
                                    {{ ucfirst($assignedStatus) }}
                                </span>
                            </td>
                            <td>
                                <select class="form-select form-select-sm runner-select" 
                                        data-booking-id="{{ $booking->bookingID }}"
                                        data-current-runner="{{ $booking->staff_served }}">
                                    <option value="">Unassigned</option>
                                    @foreach($runners as $runnerOption)
                                        <option value="{{ $runnerOption->userID }}" {{ $booking->staff_served == $runnerOption->userID ? 'selected' : '' }}>
                                            {{ $runnerOption->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                <p class="mt-3 mb-0">No runner tasks found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($bookings->hasPages())
            <div class="p-3 border-top">
                {{ $bookings->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Success/Error Notification Toast (same style as booking detail page) -->
<div id="notificationToast" class="position-fixed top-0 end-0 p-3" style="z-index: 9999; display: none;">
    <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header" id="toastHeader">
            <i class="bi bi-check-circle me-2" id="toastIcon"></i>
            <strong class="me-auto" id="toastTitle">Notification</strong>
            <button type="button" class="btn-close" onclick="hideNotification()"></button>
        </div>
        <div class="toast-body" id="toastMessage">
            Message here
        </div>
    </div>
</div>

@push('scripts')
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

    // Update Runner
    document.querySelectorAll('.runner-select').forEach(select => {
        select.addEventListener('change', function() {
            let bookingId = this.dataset.bookingId;
            let newRunnerId = this.value;
            let oldRunnerId = this.dataset.currentRunner;
            
            if (oldRunnerId == newRunnerId) {
                return;
            }

            fetch(`/admin/runner/tasks/${bookingId}/update-runner`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    runner_id: newRunnerId || null
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.dataset.currentRunner = newRunnerId;
                    // Update assigned status badge
                    const row = this.closest('tr');
                    const statusCell = row.querySelector('td:nth-child(4)');
                    if (statusCell) {
                        const isAssigned = newRunnerId && newRunnerId !== '';
                        statusCell.innerHTML = `
                            <span class="badge ${isAssigned ? 'bg-success' : 'bg-warning text-dark'}">
                                ${isAssigned ? 'Assigned' : 'Unassigned'}
                            </span>
                        `;
                    }
                    // Show success message
                    showNotification('Runner assigned successfully.', true);
                } else {
                    showNotification(data.message || 'Failed to update runner.', false);
                    this.value = oldRunnerId || '';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('An error occurred while updating runner.', false);
                this.value = oldRunnerId || '';
            });
        });
    });

    // Show notification (same style as booking detail page)
    function showNotification(message, isSuccess = true) {
        const toast = document.getElementById('notificationToast');
        const header = document.getElementById('toastHeader');
        const icon = document.getElementById('toastIcon');
        const title = document.getElementById('toastTitle');
        const body = document.getElementById('toastMessage');

        header.className = 'toast-header ' + (isSuccess ? 'bg-success text-white' : 'bg-danger text-white');
        icon.className = 'bi me-2 ' + (isSuccess ? 'bi-check-circle' : 'bi-x-circle');
        title.textContent = isSuccess ? 'Success' : 'Error';
        body.textContent = message;
        toast.style.display = 'block';

        setTimeout(() => {
            hideNotification();
        }, 4000);
    }

    function hideNotification() {
        document.getElementById('notificationToast').style.display = 'none';
    }
</script>
@endpush
@endsection

