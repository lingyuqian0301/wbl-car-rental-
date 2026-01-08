@extends('layouts.admin')

@php
    use Illuminate\Support\Facades\Storage;
@endphp

@section('title', 'Top Bar Calendar')

@section('content')
    <style>
        .calendar-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .calendar-header {
            padding: 20px;
            border-bottom: 2px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .unread-badge {
            background: #dc2626;
            color: white;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-left: 10px;
        }

        .calendar-controls {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .calendar-filters {
            padding: 15px 20px;
            background: #f8f9fa;
            border-bottom: 1px solid #e0e0e0;
        }

        .calendar-grid {
            padding: 20px;
        }

        .calendar-month-view {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 1px;
            background: #e0e0e0;
        }

        .calendar-day-header {
            background: var(--admin-red);
            color: white;
            padding: 10px;
            text-align: center;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .calendar-day-cell {
            background: white;
            min-height: 120px;
            padding: 8px;
            position: relative;
            cursor: pointer;
            transition: all 0.2s;
        }

        .calendar-day-cell:hover {
            background: #f8f9fa;
            z-index: 10;
        }

        .calendar-day-cell.other-month {
            background: #f5f5f5;
            color: #999;
        }

        .calendar-day-cell.today {
            border: 2px solid var(--admin-red);
        }

        .calendar-day-number {
            font-weight: 600;
            margin-bottom: 5px;
        }

        .booking-item {
            padding: 5px;
            margin: 3px 0;
            border-radius: 4px;
            font-size: 0.75rem;
            cursor: pointer;
            position: relative;
        }

        /* Unread - Light Green (Deposit Only) */
        .booking-item.unread.deposit-only {
            background: #86efac;
            color: #166534;
            font-weight: 600;
        }

        /* Unread - Dark Green (Full Payment or Balance) */
        .booking-item.unread.fully-paid,
        .booking-item.unread.balance-paid {
            background: #16a34a;
            color: white;
            font-weight: 600;
        }

        /* Read - Light Orange (Deposit Only) */
        .booking-item.read.deposit-only {
            background: #fed7aa;
            color: #9a3412;
        }

        /* Read - Dark Orange (Full Payment or Balance) */
        .booking-item.read.fully-paid,
        .booking-item.read.balance-paid {
            background: #ea580c;
            color: white;
        }

        /* Completed - Red */
        .booking-item.completed {
            background: #dc2626;
            color: white;
            font-weight: 600;
        }

        .booking-floating-box {
            position: absolute;
            top: 100%;
            left: 0;
            background: white;
            border: 2px solid var(--admin-red);
            border-radius: 8px;
            padding: 15px;
            min-width: 350px;
            max-width: 450px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.15);
            z-index: 1000;
            display: none;
        }

        .booking-item:hover .booking-floating-box,
        .booking-floating-box.sticky {
            display: block;
        }

        .booking-detail-row {
            padding: 6px 0;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
        }

        .booking-detail-row:last-child {
            border-bottom: none;
        }

        .booking-detail-label {
            font-weight: 600;
            color: #666;
            font-size: 0.85rem;
        }

        .booking-detail-value {
            color: #333;
            font-size: 0.9rem;
            text-align: right;
        }

        .booking-actions {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #eee;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .staff-tick {
            display: inline-block;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            margin-right: 5px;
            position: relative;
            cursor: pointer;
        }

        .staff-tick.staff1 { background: #3b82f6; }
        .staff-tick.staff2 { background: #10b981; }
        .staff-tick.admin1 { background: #8b5cf6; }
        .staff-tick.admin2 { background: #6b7280; }

        .staff-tick::after {
            content: '✓';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-size: 12px;
            font-weight: bold;
        }

        .staff-name-tooltip {
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            background: #333;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.75rem;
            white-space: nowrap;
            margin-bottom: 5px;
            display: none;
            z-index: 1001;
        }

        .staff-tick:hover .staff-name-tooltip {
            display: block;
        }

        .staff-name-tooltip::after {
            content: '';
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            border: 5px solid transparent;
            border-top-color: #333;
        }
    </style>

    <!-- Receipt Modal -->
    <div class="modal fade" id="receiptModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Payment Receipt</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="receiptImage" src="" alt="Receipt" class="img-fluid" style="max-height: 600px;">
                </div>
                <div class="modal-footer">
                    <a id="receiptDownloadLink" href="" target="_blank" class="btn btn-primary">
                        <i class="bi bi-download"></i> Download
                    </a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="calendar-container">
        <div class="calendar-header">
            <h4 class="mb-0">
                <i class="bi bi-calendar-event"></i> Top Bar Calendar
                @if(count($unreadBookings) > 0)
                    <span class="unread-badge">
                        <i class="bi bi-exclamation-circle"></i> {{ count($unreadBookings) }} Unread
                    </span>
                @endif
            </h4>
            <div class="calendar-controls">
                <div class="btn-group" role="group">
                    <a href="{{ route('admin.topbar-calendar.index', ['view' => 'month', 'vehicle_id' => $selectedVehicle, 'date' => $currentDate]) }}" 
                       class="btn btn-sm {{ $currentView === 'month' ? 'btn-danger' : 'btn-outline-danger' }}">
                        Month
                    </a>
                    <a href="{{ route('admin.topbar-calendar.index', ['view' => 'week', 'vehicle_id' => $selectedVehicle, 'date' => $currentDate]) }}" 
                       class="btn btn-sm {{ $currentView === 'week' ? 'btn-danger' : 'btn-outline-danger' }}">
                        Week
                    </a>
                    <a href="{{ route('admin.topbar-calendar.index', ['view' => 'day', 'vehicle_id' => $selectedVehicle, 'date' => $currentDate]) }}" 
                       class="btn btn-sm {{ $currentView === 'day' ? 'btn-danger' : 'btn-outline-danger' }}">
                        Day
                    </a>
                </div>
                <div class="btn-group ms-2" role="group">
                    <a href="{{ route('admin.topbar-calendar.index', ['view' => $currentView, 'vehicle_id' => $selectedVehicle, 'date' => \Carbon\Carbon::parse($currentDate)->subMonth()->format('Y-m-d')]) }}" 
                       class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                    <button class="btn btn-sm btn-outline-secondary" onclick="document.getElementById('datePicker').showPicker()">
                        {{ \Carbon\Carbon::parse($currentDate)->format('M Y') }}
                    </button>
                    <input type="month" id="datePicker" value="{{ \Carbon\Carbon::parse($currentDate)->format('Y-m') }}" 
                           style="display: none;" 
                           onchange="window.location.href='{{ route('admin.topbar-calendar.index', ['view' => $currentView, 'vehicle_id' => $selectedVehicle]) }}&date=' + this.value + '-01'">
                    <a href="{{ route('admin.topbar-calendar.index', ['view' => $currentView, 'vehicle_id' => $selectedVehicle, 'date' => \Carbon\Carbon::parse($currentDate)->addMonth()->format('Y-m-d')]) }}" 
                       class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="calendar-filters">
            <form method="GET" class="row g-2 align-items-end">
                <input type="hidden" name="view" value="{{ $currentView }}">
                <input type="hidden" name="date" value="{{ $currentDate }}">
                <div class="col-md-4">
                    <label class="form-label small">Filter by Vehicle</label>
                    <select name="vehicle_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="all" {{ $selectedVehicle === 'all' ? 'selected' : '' }}>All Vehicles</option>
                        <optgroup label="Cars">
                            @foreach($cars as $car)
                                <option value="car_{{ $car->vehicleID }}" {{ $selectedVehicle == 'car_' . $car->vehicleID ? 'selected' : '' }}>
                                    {{ $car->full_model }} ({{ $car->plate_no ?? $car->plate_number ?? 'N/A' }})
                                </option>
                            @endforeach
                        </optgroup>
                        <optgroup label="Motorcycles">
                            @foreach($motorcycles as $motorcycle)
                                <option value="motorcycle_{{ $motorcycle->vehicleID }}" {{ $selectedVehicle == 'motorcycle_' . $motorcycle->vehicleID ? 'selected' : '' }}>
                                    {{ $motorcycle->full_model }} ({{ $motorcycle->plate_no ?? $motorcycle->plate_number ?? 'N/A' }})
                                </option>
                            @endforeach
                        </optgroup>
                    </select>
                </div>
            </form>
        </div>

        <div class="calendar-grid">
            @if($currentView === 'month')
                <div class="calendar-month-view">
                    <!-- Day Headers -->
                    <div class="calendar-day-header">Sun</div>
                    <div class="calendar-day-header">Mon</div>
                    <div class="calendar-day-header">Tue</div>
                    <div class="calendar-day-header">Wed</div>
                    <div class="calendar-day-header">Thu</div>
                    <div class="calendar-day-header">Fri</div>
                    <div class="calendar-day-header">Sat</div>

                    @php
                        $startOfMonth = \Carbon\Carbon::parse($currentDate)->startOfMonth();
                        $endOfMonth = \Carbon\Carbon::parse($currentDate)->endOfMonth();
                        $startOfCalendar = $startOfMonth->copy()->startOfWeek();
                        $endOfCalendar = $endOfMonth->copy()->endOfWeek();
                        $currentDay = $startOfCalendar->copy();
                    @endphp

                    @while($currentDay->lte($endOfCalendar))
                        @php
                            $dateKey = $currentDay->format('Y-m-d');
                            $isToday = $currentDay->isToday();
                            $isOtherMonth = !$currentDay->isSameMonth($startOfMonth);
                            $dayBookings = $bookingsByDate[$dateKey] ?? [];
                            $cellClass = 'calendar-day-cell';
                            if ($isOtherMonth) $cellClass .= ' other-month';
                            if ($isToday) $cellClass .= ' today';
                        @endphp
                        <div class="{{ $cellClass }}" data-date="{{ $dateKey }}">
                            <div class="calendar-day-number">{{ $currentDay->format('j') }}</div>
                            @foreach($dayBookings as $booking)
                                @php
                                    $isUnread = in_array($booking->id, $unreadBookings);
                                    $paymentStatus = $booking->payment_status;
                                    $isCompleted = $booking->booking_status === 'Completed';
                                    
                                    $totalPaid = $booking->payments()->where('payment_status', 'Verified')->sum('amount');
                                    $hasBalancePayment = $booking->payments()->where('payment_status', 'Verified')->where('payment_type', 'Balance')->exists();
                                    $hasFullPayment = $booking->payments()->where('payment_status', 'Verified')->where('payment_type', 'Full Payment')->exists();
                                    $isDepositOnly = $totalPaid > 0 && $totalPaid < $booking->total_price && !$hasFullPayment && !$hasBalancePayment;
                                    
                                    // Determine color class
                                    $colorClass = '';
                                    if ($isCompleted) {
                                        $colorClass = 'completed';
                                    } elseif ($isUnread) {
                                        // Unread: light green (deposit only) or dark green (full/balance)
                                        $colorClass = $isDepositOnly ? 'unread deposit-only' : 'unread fully-paid';
                                    } else {
                                        // Read: light orange (deposit only) or dark orange (full/balance)
                                        $colorClass = $isDepositOnly ? 'read deposit-only' : 'read fully-paid';
                                    }
                                    
                                    // Use confirmed_by instead of servedBy (booking_served_by table doesn't exist)
                                    $confirmedBy = $booking->confirmedByUser ?? null;
                                    $servedByRecords = collect();
                                    if ($confirmedBy) {
                                        $servedByRecords = collect([['servedByUser' => ['name' => $confirmedBy->name]]]);
                                    }
                                    
                                    $hasReceipt = $booking->payments()->whereNotNull('proof_of_payment')->exists();
                                    $firstPaymentWithReceipt = $booking->payments()->whereNotNull('proof_of_payment')->first();
                                    $latestPayment = $booking->payments()->orderBy('payment_date', 'desc')->first();
                                @endphp
                                <div class="booking-item {{ $colorClass }}"
                                     data-booking-id="{{ $booking->id }}"
                                     onmouseenter="showBookingBox({{ $booking->id }}, event)"
                                     onclick="event.stopPropagation(); toggleBookingBox({{ $booking->id }})">
                                    <div>
                                        <strong>{{ $booking->user->name }}</strong>
                                        @if($isUnread)
                                            <i class="bi bi-circle-fill" style="font-size: 0.5rem; margin-left: 5px;"></i>
                                        @endif
                                    </div>
                                    <div class="small">{{ $booking->vehicle->registration_number }}</div>
                                    
                                    @if($booking->status === 'Confirmed' || $booking->status === 'Completed')
                                        <div class="mt-1">
                                            @php
                                                $staffCount = 1;
                                                $adminCount = 1;
                                            @endphp
                                            @if($booking->confirmedByUser)
                                                @php
                                                    $confirmedUser = $booking->confirmedByUser;
                                                    $tickClass = '';
                                                    if ($confirmedUser->role === 'staff') {
                                                        $tickClass = 'staff' . min($staffCount++, 2);
                                                    } elseif ($confirmedUser->role === 'admin') {
                                                        $tickClass = 'admin' . min($adminCount++, 2);
                                                    }
                                                @endphp
                                                @if($tickClass)
                                                    <span class="staff-tick {{ $tickClass }}" 
                                                          data-staff-name="{{ $confirmedUser->name }}">
                                                        <span class="staff-name-tooltip">{{ $confirmedUser->name }} (Confirmed)</span>
                                                    </span>
                                                @endif
                                            @endif
                                            @if($booking->completedByUser)
                                                @php
                                                    $completedUser = $booking->completedByUser;
                                                    $tickClass = '';
                                                    if ($completedUser->role === 'staff') {
                                                        $tickClass = 'staff' . min($staffCount++, 2);
                                                    } elseif ($completedUser->role === 'admin') {
                                                        $tickClass = 'admin' . min($adminCount++, 2);
                                                    }
                                                @endphp
                                                @if($tickClass)
                                                    <span class="staff-tick {{ $tickClass }}" 
                                                          data-staff-name="{{ $completedUser->name }}">
                                                        <span class="staff-name-tooltip">{{ $completedUser->name }} (Completed)</span>
                                                    </span>
                                                @endif
                                            @endif
                                        </div>
                                    @endif

                                    <!-- Floating Booking Details Box -->
                                    <div class="booking-floating-box" id="booking-box-{{ $booking->id }}" onclick="event.stopPropagation()">
                                        <div class="booking-detail-row">
                                            <span class="booking-detail-label">Customer Name:</span>
                                            <span class="booking-detail-value">{{ $booking->user->name }}</span>
                                        </div>
                                        <div class="booking-detail-row">
                                            <span class="booking-detail-label">Plate Number:</span>
                                            <span class="booking-detail-value">{{ $booking->vehicle->registration_number }}</span>
                                        </div>
                                        <div class="booking-detail-row">
                                            <span class="booking-detail-label">Rental Date:</span>
                                            <span class="booking-detail-value">{{ $booking->start_date->format('d M Y') }}</span>
                                        </div>
                                        <div class="booking-detail-row">
                                            <span class="booking-detail-label">Rental Time:</span>
                                            <span class="booking-detail-value">{{ $booking->pickup_time ?? 'Not set' }}</span>
                                        </div>
                                        <div class="booking-detail-row">
                                            <span class="booking-detail-label">Return Date:</span>
                                            <span class="booking-detail-value">{{ $booking->end_date->format('d M Y') }}</span>
                                        </div>
                                        <div class="booking-detail-row">
                                            <span class="booking-detail-label">Return Time:</span>
                                            <span class="booking-detail-value">{{ $booking->return_time ?? 'Not set' }}</span>
                                        </div>
                                        <div class="booking-detail-row">
                                            <span class="booking-detail-label">Pickup Location:</span>
                                            <span class="booking-detail-value">{{ $booking->pickup_location ?? 'Not set' }}</span>
                                        </div>
                                        <div class="booking-detail-row">
                                            <span class="booking-detail-label">Return Location:</span>
                                            <span class="booking-detail-value">{{ $booking->return_location ?? 'Not set' }}</span>
                                        </div>
                                        <div class="booking-detail-row">
                                            <span class="booking-detail-label">Payment Status:</span>
                                            <span class="booking-detail-value">
                                                @if($paymentStatus === 'fully_paid')
                                                    <span class="badge bg-success">Fully</span>
                                                @elseif($paymentStatus === 'deposit_only')
                                                    <span class="badge bg-warning text-dark">Deposit</span>
                                                @else
                                                    <span class="badge bg-secondary">Unpaid</span>
                                                @endif
                                            </span>
                                        </div>
                                        @if($latestPayment && $latestPayment->payment_method)
                                            <div class="booking-detail-row">
                                                <span class="booking-detail-label">Payment Method:</span>
                                                <span class="booking-detail-value">
                                                    <span class="badge bg-info">{{ $latestPayment->payment_method }}</span>
                                                </span>
                                            </div>
                                        @endif
                                        
                                        @if($booking->booking_status === 'Pending')
                                            <div class="booking-actions">
                                                <button class="btn btn-sm btn-success" 
                                                        onclick="confirmBooking({{ $booking->id }})">
                                                    <i class="bi bi-check-circle"></i> Confirm
                                                </button>
                                                @if($hasReceipt && $firstPaymentWithReceipt)
                                                    <button class="btn btn-sm btn-primary" 
                                                            onclick="showReceipt('{{ Storage::url($firstPaymentWithReceipt->proof_of_payment) }}')">
                                                        <i class="bi bi-receipt"></i> Receipt
                                                    </button>
                                                @endif
                                            </div>
                                        @elseif($booking->booking_status === 'Confirmed')
                                            <div class="booking-actions">
                                                @if($isDepositOnly)
                                                    <button class="btn btn-sm btn-warning" 
                                                            onclick="sendBalanceReminder({{ $booking->id }})">
                                                        <i class="bi bi-envelope"></i> Reminding
                                                    </button>
                                                @endif
                                                <button class="btn btn-sm btn-danger" 
                                                        onclick="completeBooking({{ $booking->id }})">
                                                    <i class="bi bi-check-all"></i> Complete
                                                </button>
                                                @if($hasReceipt && $firstPaymentWithReceipt)
                                                    <button class="btn btn-sm btn-primary" 
                                                            onclick="showReceipt('{{ Storage::url($firstPaymentWithReceipt->proof_of_payment) }}')">
                                                        <i class="bi bi-receipt"></i> Receipt
                                                    </button>
                                                @endif
                                            </div>
                                            @if($booking->confirmedByUser)
                                                <div class="booking-detail-row mt-2">
                                                    <span class="booking-detail-label">Confirmed by:</span>
                                                    <span class="booking-detail-value">{{ $booking->confirmedByUser->name }}</span>
                                                </div>
                                            @endif
                                        @elseif($booking->booking_status === 'Completed')
                                            <div class="booking-actions">
                                                @if($hasReceipt && $firstPaymentWithReceipt)
                                                    <button class="btn btn-sm btn-primary" 
                                                            onclick="showReceipt('{{ Storage::url($firstPaymentWithReceipt->proof_of_payment) }}')">
                                                        <i class="bi bi-receipt"></i> Receipt
                                                    </button>
                                                @endif
                                            </div>
                                            @if($booking->confirmedByUser)
                                                <div class="booking-detail-row mt-2">
                                                    <span class="booking-detail-label">Confirmed by:</span>
                                                    <span class="booking-detail-value">{{ $booking->confirmedByUser->name }}</span>
                                                </div>
                                            @endif
                                            @if($booking->completedByUser)
                                                <div class="booking-detail-row">
                                                    <span class="booking-detail-label">Completed by:</span>
                                                    <span class="booking-detail-value">{{ $booking->completedByUser->name }}</span>
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @php $currentDay->addDay(); @endphp
                    @endwhile
                </div>
            @elseif($currentView === 'week')
                <div class="alert alert-info">
                    Week view will be implemented here.
                </div>
            @else
                <div class="alert alert-info">
                    Day view will be implemented here.
                </div>
            @endif
        </div>
    </div>

    <script>
        let stickyBoxes = {};

        function showBookingBox(bookingId, event) {
            const box = document.getElementById('booking-box-' + bookingId);
            if (box && !stickyBoxes[bookingId]) {
                box.style.display = 'block';
            }
        }

        function toggleBookingBox(bookingId) {
            const box = document.getElementById('booking-box-' + bookingId);
            if (box) {
                if (stickyBoxes[bookingId]) {
                    box.classList.remove('sticky');
                    delete stickyBoxes[bookingId];
                } else {
                    // Close all other sticky boxes
                    Object.keys(stickyBoxes).forEach(id => {
                        const otherBox = document.getElementById('booking-box-' + id);
                        if (otherBox) {
                            otherBox.classList.remove('sticky');
                        }
                        delete stickyBoxes[id];
                    });
                    box.classList.add('sticky');
                    stickyBoxes[bookingId] = true;
                }
            }
        }

        function confirmBooking(bookingId) {
            if (confirm('Confirm this booking? This will change the status from Pending to Confirmed.')) {
                fetch(`/admin/topbar-calendar/bookings/${bookingId}/confirm`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + (data.message || 'Failed to confirm booking'));
                    }
                })
                .catch(error => {
                    alert('Error confirming booking');
                    console.error(error);
                });
            }
        }

        function completeBooking(bookingId) {
            if (confirm('Mark this booking as completed?')) {
                fetch(`/admin/topbar-calendar/bookings/${bookingId}/complete`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + (data.message || 'Failed to complete booking'));
                    }
                })
                .catch(error => {
                    alert('Error completing booking');
                    console.error(error);
                });
            }
        }

        function sendBalanceReminder(bookingId) {
            if (confirm('Send balance reminder email to customer?')) {
                fetch(`/admin/topbar-calendar/bookings/${bookingId}/send-balance-reminder`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Balance reminder email sent successfully!');
                    } else {
                        alert('Error: ' + (data.message || 'Failed to send email'));
                    }
                })
                .catch(error => {
                    alert('Error sending email');
                    console.error(error);
                });
            }
        }

        // Close floating boxes when clicking outside
        document.addEventListener('click', function(event) {
            if (!event.target.closest('.booking-item') && !event.target.closest('.booking-floating-box')) {
                Object.keys(stickyBoxes).forEach(id => {
                    const box = document.getElementById('booking-box-' + id);
                    if (box) {
                        box.classList.remove('sticky');
                    }
                    delete stickyBoxes[id];
                });
            }
        });

        function showReceipt(receiptUrl) {
            document.getElementById('receiptImage').src = receiptUrl;
            document.getElementById('receiptDownloadLink').href = receiptUrl;
            new bootstrap.Modal(document.getElementById('receiptModal')).show();
        }
    </script>
@endsection











