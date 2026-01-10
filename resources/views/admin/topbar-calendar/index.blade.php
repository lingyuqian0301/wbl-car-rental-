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

        .date-label {
            font-size: 0.65rem;
            font-weight: 600;
            text-transform: uppercase;
            padding: 2px 6px;
            border-radius: 3px;
            margin-top: 4px;
            display: inline-block;
        }

        .date-label.pickup-label {
            background: #3b82f6;
            color: white;
        }

        .date-label.return-label {
            background: #8b5cf6;
            color: white;
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
            border-left: 3px solid #16a34a;
        }

        /* Unread - Dark Green (Full Payment or Balance) */
        .booking-item.unread.fully-paid,
        .booking-item.unread.balance-paid {
            background: #16a34a;
            color: white;
            font-weight: 600;
            border-left: 3px solid #15803d;
        }

        /* Read - Light Red (Deposit Only) */
        .booking-item.read.deposit-only {
            background: #fca5a5;
            color: #991b1b;
        }

        /* Read - Dark Red (Full Payment or Balance) */
        .booking-item.read.fully-paid,
        .booking-item.read.balance-paid {
            background: #dc2626;
            color: white;
        }

        /* Completed - Red */
        .booking-item.completed {
            background: #dc2626;
            color: white;
            font-weight: 600;
        }

        .booking-floating-box {
            position: fixed;
            background: white;
            border: 2px solid var(--admin-red);
            border-radius: 8px;
            padding: 15px;
            min-width: 350px;
            max-width: 400px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.15);
            z-index: 1000;
            display: none;
            cursor: default;
        }

        .booking-item:hover .booking-floating-box:not(.sticky),
        .booking-floating-box.sticky {
            display: block;
        }

        .booking-floating-box.sticky {
            cursor: default;
        }

        .floating-box-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding-bottom: 10px;
            margin-bottom: 10px;
            border-bottom: 2px solid var(--admin-red);
        }

        .floating-box-close {
            background: none;
            border: none;
            font-size: 1.2rem;
            color: #666;
            cursor: pointer;
            padding: 0;
            margin-left: 10px;
            line-height: 1;
        }

        .floating-box-close:hover {
            color: var(--admin-red);
        }

        .floating-box-buttons {
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid #eee;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .floating-box-buttons .btn {
            flex: 1;
            min-width: 100px;
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
                        <div class="{{ $cellClass }}" data-date="{{ $dateKey }}" 
                             onmouseleave="handleCellMouseLeave('{{ $dateKey }}')">
                            <div class="calendar-day-number">{{ $currentDay->format('j') }}</div>
                            @foreach($dayBookings as $booking)
                                @php
                                    // Determine if this is pickup or return date for this cell
                                    $pickupDateForCompare = $booking->rental_start_date ? \Carbon\Carbon::parse($booking->rental_start_date)->format('Y-m-d') : '';
                                    $returnDateForCompare = $booking->rental_end_date ? \Carbon\Carbon::parse($booking->rental_end_date)->format('Y-m-d') : '';
                                    $isPickupDateCell = $currentDay->format('Y-m-d') === $pickupDateForCompare;
                                    $isReturnDateCell = $currentDay->format('Y-m-d') === $returnDateForCompare;
                                    
                                    // Check unread status based on which date type this cell represents
                                    $isUnread = false;
                                    if ($isPickupDateCell) {
                                        $isUnread = in_array($booking->bookingID, $unreadPickups ?? []);
                                    } elseif ($isReturnDateCell) {
                                        $isUnread = in_array($booking->bookingID, $unreadReturns ?? []);
                                    } else {
                                        // For dates in between, use general unread status
                                        $isUnread = in_array($booking->bookingID, $unreadBookings);
                                    }
                                    
                                    $paymentStatus = $booking->payment_status;
                                    $isCompleted = $booking->booking_status === 'Completed';
                                    
                                    $totalPaid = $booking->payments()->where('payment_status', 'Verified')->sum('total_amount');
                                    $totalRequired = ($booking->rental_amount ?? 0) + ($booking->deposit_amount ?? 0);
                                    $hasFullPayment = $totalPaid >= $totalRequired;
                                    $hasBalancePayment = $totalPaid > 0 && $totalPaid < $totalRequired;
                                    $isDepositOnly = $totalPaid > 0 && $totalPaid < ($booking->deposit_amount ?? 0);
                                    
                                    // Determine color class
                                    $colorClass = '';
                                    if ($isCompleted) {
                                        $colorClass = 'completed';
                                    } elseif ($isUnread) {
                                        // Unread: light green (deposit only) or dark green (full/balance)
                                        $colorClass = $isDepositOnly ? 'unread deposit-only' : 'unread fully-paid';
                                    } else {
                                        // Read: light red (deposit only) or dark red (full/balance)
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
                                    
                                    // Determine the date type for this cell
                                    $dateTypeForCell = $isPickupDateCell ? 'pickup' : ($isReturnDateCell ? 'return' : 'rental');
                                @endphp
                                <div class="booking-item {{ $colorClass }}"
                                     data-booking-id="{{ $booking->bookingID }}"
                                     data-date-type="{{ $dateTypeForCell }}"
                                     data-is-unread="{{ $isUnread ? 'true' : 'false' }}"
                                     onmouseenter="showBookingBox('{{ $booking->bookingID }}_{{ $dateTypeForCell }}', event)"
                                     onmouseleave="hideBookingBox('{{ $booking->bookingID }}_{{ $dateTypeForCell }}')"
                                     onclick="event.stopPropagation(); toggleBookingBox('{{ $booking->bookingID }}_{{ $dateTypeForCell }}')">
                                    <div>
                                        @if($isPickupDateCell)
                                            <span class="date-label pickup-label">Pickup</span>
                                        @elseif($isReturnDateCell)
                                            <span class="date-label return-label">Return</span>
                                        @endif
                                    </div>
                                    <div>
                                        <strong>{{ $booking->customer && $booking->customer->user ? $booking->customer->user->name : 'N/A' }}</strong>
                                        @if($isUnread)
                                            <i class="bi bi-circle-fill" style="font-size: 0.5rem; margin-left: 5px;"></i>
                                        @endif
                                    </div>
                                    <div class="small">{{ $booking->vehicle->plate_number ?? ($booking->vehicle->plate_no ?? 'N/A') }}</div>
                                    
                                    @if($booking->booking_status === 'Confirmed' || $booking->booking_status === 'Done')
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
                                    <div class="booking-floating-box" 
                                         id="booking-box-{{ $booking->bookingID }}_{{ $dateTypeForCell }}" 
                                         data-booking-id="{{ $booking->bookingID }}"
                                         data-date-type="{{ $dateTypeForCell }}"
                                         data-is-unread="{{ $isUnread ? 'true' : 'false' }}"
                                         onmouseenter="keepBookingBoxOpen('{{ $booking->bookingID }}_{{ $dateTypeForCell }}')"
                                         onmouseleave="hideBookingBox('{{ $booking->bookingID }}_{{ $dateTypeForCell }}')">
                                        @php
                                            $eventType = $isPickupDateCell ? 'Pickup' : ($isReturnDateCell ? 'Return' : 'Rental');
                                            $pickupDateTime = $booking->rental_start_date ? \Carbon\Carbon::parse($booking->rental_start_date) : null;
                                            $returnDateTime = $booking->rental_end_date ? \Carbon\Carbon::parse($booking->rental_end_date) : null;
                                        @endphp
                                        <div class="floating-box-header">
                                            <div>
                                                <strong style="color: var(--admin-red); font-size: 1.1rem;">{{ $eventType }}</strong>
                                                <div style="font-size: 0.85rem; color: #666; margin-top: 3px;">
                                                    @if($isPickupDateCell && $pickupDateTime)
                                                        {{ $pickupDateTime->format('d M Y H:i') }}
                                                    @elseif($isReturnDateCell && $returnDateTime)
                                                        {{ $returnDateTime->format('d M Y H:i') }}
                                                    @else
                                                        {{ $pickupDateTime ? $pickupDateTime->format('d M Y') : 'N/A' }} - {{ $returnDateTime ? $returnDateTime->format('d M Y') : 'N/A' }}
                                                    @endif
                                                </div>
                                            </div>
                                            <button type="button" class="floating-box-close" onclick="event.stopPropagation(); closeAndMarkRead('{{ $booking->bookingID }}_{{ $dateTypeForCell }}', {{ $isUnread ? 'true' : 'false' }}, '{{ $dateTypeForCell }}')" title="Close">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </div>
                                        <div class="booking-detail-row">
                                            <span class="booking-detail-label">Booking ID:</span>
                                            <span class="booking-detail-value"><strong>#{{ $booking->bookingID }}</strong></span>
                                        </div>
                                        <div class="booking-detail-row">
                                            <span class="booking-detail-label">Booking Status:</span>
                                            <span class="booking-detail-value">
                                                <span class="badge {{ $booking->booking_status === 'Confirmed' ? 'bg-success' : ($booking->booking_status === 'Pending' ? 'bg-warning text-dark' : ($booking->booking_status === 'Cancelled' ? 'bg-danger' : 'bg-secondary')) }}">
                                                    {{ $booking->booking_status ?? 'N/A' }}
                                                </span>
                                            </span>
                                        </div>
                                        <div class="booking-detail-row">
                                            <span class="booking-detail-label">Customer Name:</span>
                                            <span class="booking-detail-value">{{ $booking->customer && $booking->customer->user ? $booking->customer->user->name : 'N/A' }}</span>
                                        </div>
                                        <div class="booking-detail-row">
                                            <span class="booking-detail-label">Plate Number:</span>
                                            <span class="booking-detail-value">{{ $booking->vehicle->plate_number ?? ($booking->vehicle->plate_no ?? 'N/A') }}</span>
                                        </div>
                                        @if($isPickupDateCell)
                                            <div class="booking-detail-row">
                                                <span class="booking-detail-label">Pickup Location:</span>
                                                <span class="booking-detail-value">{{ $booking->pickup_point ?? 'Not set' }}</span>
                                            </div>
                                        @elseif($isReturnDateCell)
                                            <div class="booking-detail-row">
                                                <span class="booking-detail-label">Return Location:</span>
                                                <span class="booking-detail-value">{{ $booking->return_point ?? 'Not set' }}</span>
                                            </div>
                                        @endif
                                        <div class="booking-detail-row">
                                            <span class="booking-detail-label">Duration:</span>
                                            <span class="booking-detail-value">{{ $booking->duration ?? 0 }} days</span>
                                        </div>
                                        <div class="booking-detail-row">
                                            <span class="booking-detail-label">Payment Status:</span>
                                            <span class="booking-detail-value">
                                                @if($hasFullPayment)
                                                    <span class="badge bg-success">Fully Paid</span>
                                                @elseif($hasBalancePayment)
                                                    <span class="badge bg-warning text-dark">Balance Pending</span>
                                                @elseif($isDepositOnly)
                                                    <span class="badge bg-info">Deposit Only</span>
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
                                        
                                        <!-- Action Buttons -->
                                        <div class="floating-box-buttons" onclick="event.stopPropagation()">
                                            @if($hasReceipt && $firstPaymentWithReceipt)
                                                <button class="btn btn-sm btn-info" 
                                                        onclick="event.stopPropagation(); showReceipt('{{ getFileUrl($firstPaymentWithReceipt->transaction_reference ?? $firstPaymentWithReceipt->proof_of_payment ?? '') }}')">
                                                    <i class="bi bi-receipt"></i> Receipt
                                                </button>
                                            @endif
                                            <button class="btn btn-sm btn-primary" 
                                                    onclick="event.stopPropagation(); goToBookingDetail({{ $booking->bookingID }})">
                                                <i class="bi bi-eye"></i> Booking Detail
                                            </button>
                                            @if($isUnread)
                                                <button class="btn btn-sm btn-success mark-read-btn" 
                                                        id="mark-read-btn-{{ $booking->bookingID }}_{{ $dateTypeForCell }}"
                                                        onclick="event.stopPropagation(); closeAndMarkRead('{{ $booking->bookingID }}_{{ $dateTypeForCell }}', true, '{{ $dateTypeForCell }}')">
                                                    <i class="bi bi-check-lg"></i> Mark as Read
                                                </button>
                                            @endif
                                        </div>
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
        let hoveredBoxes = {};
        let hideTimeouts = {};

        // boxId format: "bookingId_dateType" (e.g., "123_pickup" or "123_return")
        function showBookingBox(boxId, event) {
            const box = document.getElementById('booking-box-' + boxId);
            if (!box) return;
            
            // Clear any pending hide timeout
            if (hideTimeouts[boxId]) {
                clearTimeout(hideTimeouts[boxId]);
                delete hideTimeouts[boxId];
            }
            
            if (!stickyBoxes[boxId]) {
                box.style.display = 'block';
                hoveredBoxes[boxId] = true;
                // Position the box after it's displayed (using requestAnimationFrame for accurate measurements)
                requestAnimationFrame(() => {
                    positionBookingBox(box, event);
                });
            }
        }

        function hideBookingBox(boxId) {
            hoveredBoxes[boxId] = false;
            
            // Set a small delay before hiding to allow moving to the box
            if (hideTimeouts[boxId]) {
                clearTimeout(hideTimeouts[boxId]);
            }
            
            hideTimeouts[boxId] = setTimeout(() => {
                const box = document.getElementById('booking-box-' + boxId);
                
                // Check if mouse is still over box
                const isMouseOverBox = box && (box.matches(':hover') || box.querySelector(':hover'));
                
                if (box && !stickyBoxes[boxId] && !hoveredBoxes[boxId] && !isMouseOverBox) {
                    box.style.display = 'none';
                }
                delete hideTimeouts[boxId];
            }, 200);
        }

        function keepBookingBoxOpen(boxId) {
            hoveredBoxes[boxId] = true;
            if (hideTimeouts[boxId]) {
                clearTimeout(hideTimeouts[boxId]);
                delete hideTimeouts[boxId];
            }
        }

        function positionBookingBox(box, event) {
            if (!box) return;
            
            const viewportWidth = window.innerWidth;
            const viewportHeight = window.innerHeight;
            const margin = 15;
            const scrollY = window.scrollY || window.pageYOffset;
            const scrollX = window.scrollX || window.pageXOffset;
            
            // Get the booking item element
            const bookingItem = box.closest('.booking-item');
            if (!bookingItem) return;
            
            const itemRect = bookingItem.getBoundingClientRect();
            
            // Reset position and styles first
            box.style.top = 'auto';
            box.style.left = 'auto';
            box.style.right = 'auto';
            box.style.bottom = 'auto';
            box.style.maxHeight = '';
            box.style.overflowY = '';
            
            // Get measurements after display
            const boxRect = box.getBoundingClientRect();
            const boxWidth = boxRect.width;
            const boxHeight = boxRect.height;
            
            // Calculate initial position (below the item)
            let top = itemRect.bottom + 5;
            let left = itemRect.left;
            
            // Check if box goes off the right edge of viewport
            if (left + boxWidth > viewportWidth - margin) {
                left = viewportWidth - boxWidth - margin;
            }
            
            // Check if box goes off the left edge of viewport
            if (left < margin) {
                left = margin;
            }
            
            // Check if box goes off the bottom edge of viewport
            if (top + boxHeight > viewportHeight - margin) {
                // Try to show above the item instead
                const topAbove = itemRect.top - boxHeight - 5;
                
                if (topAbove >= margin) {
                    // Can fit above
                    top = topAbove;
                } else {
                    // Can't fit above, constrain height and show below or center
                    const spaceBelow = viewportHeight - itemRect.bottom - margin;
                    const spaceAbove = itemRect.top - margin;
                    
                    if (spaceBelow >= spaceAbove && spaceBelow > 150) {
                        // Show below with constrained height
                        top = itemRect.bottom + 5;
                        box.style.maxHeight = (spaceBelow - 10) + 'px';
                        box.style.overflowY = 'auto';
                    } else if (spaceAbove > 150) {
                        // Show above with constrained height
                        top = margin;
                        box.style.maxHeight = (spaceAbove - 10) + 'px';
                        box.style.overflowY = 'auto';
                    } else {
                        // Center vertically with constrained height
                        top = margin;
                        box.style.maxHeight = (viewportHeight - margin * 2) + 'px';
                        box.style.overflowY = 'auto';
                    }
                }
            }
            
            // Apply position (using fixed positioning)
            box.style.top = top + 'px';
            box.style.left = left + 'px';
        }

        function toggleBookingBox(boxId) {
            const box = document.getElementById('booking-box-' + boxId);
            if (box) {
                if (stickyBoxes[boxId]) {
                    box.classList.remove('sticky');
                    box.style.display = 'none';
                    delete stickyBoxes[boxId];
                } else {
                    // Close all other sticky boxes
                    Object.keys(stickyBoxes).forEach(id => {
                        const otherBox = document.getElementById('booking-box-' + id);
                        if (otherBox) {
                            otherBox.classList.remove('sticky');
                            otherBox.style.display = 'none';
                        }
                        delete stickyBoxes[id];
                    });
                    box.classList.add('sticky');
                    box.style.display = 'block';
                    stickyBoxes[boxId] = true;
                    // Reposition when made sticky
                    const bookingItem = box.closest('.booking-item');
                    if (bookingItem) {
                        const fakeEvent = { clientX: 0, clientY: 0 };
                        positionBookingBox(box, fakeEvent);
                    }
                }
            }
        }

        function handleCellMouseLeave(dateKey) {
            // Hide all non-sticky boxes in this cell
            const cell = document.querySelector(`[data-date="${dateKey}"]`);
            if (cell) {
                const bookingItems = cell.querySelectorAll('.booking-item');
                bookingItems.forEach(item => {
                    const bookingId = item.dataset.bookingId;
                    const dateType = item.dataset.dateType;
                    const boxId = bookingId + '_' + dateType;
                    if (boxId && !stickyBoxes[boxId]) {
                        hideBookingBox(boxId);
                    }
                });
            }
        }

        function navigateToBooking(bookingId, isUnread, event) {
            // Prevent event bubbling
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            if (isUnread) {
                // Mark as read first, then navigate
                fetch(`/admin/topbar-calendar/bookings/${bookingId}/mark-as-read`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => response.json())
                .then(data => {
                    // Navigate to booking detail
                    window.location.href = `/admin/bookings/reservations/${bookingId}?tab=booking-detail`;
                })
                .catch(error => {
                    console.error('Error marking as read:', error);
                    // Navigate even if mark as read fails
                    window.location.href = `/admin/bookings/reservations/${bookingId}?tab=booking-detail`;
                });
            } else {
                // Navigate directly
                window.location.href = `/admin/bookings/reservations/${bookingId}?tab=booking-detail`;
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

        // Close floating boxes when clicking outside (but not sticky ones on hover)
        document.addEventListener('click', function(event) {
            if (!event.target.closest('.booking-item') && !event.target.closest('.booking-floating-box')) {
                Object.keys(stickyBoxes).forEach(id => {
                    if (!hoveredBoxes[id]) {
                        const box = document.getElementById('booking-box-' + id);
                        if (box) {
                            box.classList.remove('sticky');
                            box.style.display = 'none';
                        }
                        delete stickyBoxes[id];
                    }
                });
            }
        });

        // Close boxes when mouse leaves the calendar day cell or floating box
        document.addEventListener('mouseout', function(event) {
            const relatedTarget = event.relatedTarget;
            
            // Check if leaving a calendar day cell
            if (event.target.classList.contains('calendar-day-cell')) {
                if (!relatedTarget || !event.target.contains(relatedTarget)) {
                    const bookingItems = event.target.querySelectorAll('.booking-item');
                    bookingItems.forEach(item => {
                        const bookingId = item.dataset.bookingId;
                        const dateType = item.dataset.dateType;
                        const boxId = bookingId + '_' + dateType;
                        if (boxId && !stickyBoxes[boxId]) {
                            hideBookingBox(boxId);
                        }
                    });
                }
            }
            
            // Check if leaving a floating box (but not moving to its parent booking item)
            if (event.target.classList.contains('booking-floating-box')) {
                const box = event.target;
                const bookingId = box.dataset.bookingId;
                const dateType = box.dataset.dateType;
                const boxId = bookingId + '_' + dateType;
                
                // If not moving to booking item or staying in box, hide it (unless sticky)
                if (!stickyBoxes[boxId] && (!relatedTarget || !box.contains(relatedTarget))) {
                    hideBookingBox(boxId);
                }
            }
        }, true);

        function showReceipt(receiptUrl) {
            document.getElementById('receiptImage').src = receiptUrl;
            document.getElementById('receiptDownloadLink').href = receiptUrl;
            new bootstrap.Modal(document.getElementById('receiptModal')).show();
        }

        function closeBookingBox(boxId) {
            const box = document.getElementById('booking-box-' + boxId);
            if (box) {
                box.classList.remove('sticky');
                box.style.display = 'none';
                delete stickyBoxes[boxId];
                delete hoveredBoxes[boxId];
            }
        }

        function goToBookingDetail(bookingId) {
            window.location.href = `/admin/bookings/reservations/${bookingId}?tab=booking-detail`;
        }

        // boxId format: "bookingId_dateType", dateType is 'pickup' or 'return'
        function closeAndMarkRead(boxId, isUnread, dateType) {
            // Extract bookingId from boxId (format: "123_pickup" or "123_return")
            const parts = boxId.split('_');
            const bookingId = parts[0];
            dateType = dateType || parts[1] || 'pickup';
            
            if (isUnread) {
                // Mark as read, then close
                fetch(`/admin/topbar-calendar/bookings/${bookingId}/mark-as-read`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ date_type: dateType })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update the booking item appearance for this specific date type
                        updateBookingItemToRead(boxId, dateType);
                    }
                    // Close the box regardless
                    closeBookingBox(boxId);
                })
                .catch(error => {
                    console.error('Error marking as read:', error);
                    // Still close the box even if API call fails
                    closeBookingBox(boxId);
                });
            } else {
                // Just close the box
                closeBookingBox(boxId);
            }
        }

        // boxId format: "bookingId_dateType"
        function updateBookingItemToRead(boxId, dateType) {
            // Extract bookingId and dateType from boxId
            const parts = boxId.split('_');
            const bookingId = parts[0];
            dateType = dateType || parts[1] || 'pickup';
            
            // Find the specific booking item for this date type
            const bookingItems = document.querySelectorAll(`.booking-item[data-booking-id="${bookingId}"][data-date-type="${dateType}"]`);
            
            bookingItems.forEach(bookingItem => {
                // Update color classes
                bookingItem.classList.remove('unread');
                bookingItem.classList.add('read');
                
                // Update data attribute
                bookingItem.dataset.isUnread = 'false';
                
                // Remove unread indicator
                const unreadIndicator = bookingItem.querySelector('.bi-circle-fill');
                if (unreadIndicator) {
                    unreadIndicator.remove();
                }
            });
            
            // Hide the "Mark as Read" button
            const markReadBtn = document.getElementById('mark-read-btn-' + boxId);
            if (markReadBtn) {
                markReadBtn.style.display = 'none';
            }
            
            // Update the floating box data attribute
            const box = document.getElementById('booking-box-' + boxId);
            if (box) {
                box.dataset.isUnread = 'false';
            }
            
            // Update unread badge count
            const unreadBadge = document.querySelector('.unread-badge');
            if (unreadBadge) {
                const currentCount = parseInt(unreadBadge.textContent.match(/\d+/)?.[0] || '0');
                if (currentCount > 1) {
                    unreadBadge.innerHTML = `<i class="bi bi-exclamation-circle"></i> ${currentCount - 1} Unread`;
                } else {
                    unreadBadge.style.display = 'none';
                }
            }
        }

        // Mark as read with date type and navigate
        function markAsRead(boxId, dateType, navigateAfter = false) {
            const parts = boxId.split('_');
            const bookingId = parts[0];
            dateType = dateType || parts[1] || 'pickup';
            
            return fetch(`/admin/topbar-calendar/bookings/${bookingId}/mark-as-read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ date_type: dateType })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update the booking item appearance
                    updateBookingItemToRead(boxId, dateType);
                    
                    if (!navigateAfter) {
                        closeBookingBox(boxId);
                    }
                    return data;
                } else {
                    throw new Error(data.message || 'Failed to mark as read');
                }
            })
            .catch(error => {
                console.error('Error marking as read:', error);
                if (!navigateAfter) {
                    closeBookingBox(boxId);
                }
                throw error;
            });
        }
    </script>
@endsection















