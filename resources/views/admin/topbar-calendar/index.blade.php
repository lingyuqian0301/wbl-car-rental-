@extends('layouts.admin')

@php
    use Illuminate\Support\Facades\Storage;
@endphp

@section('title', 'Top Bar Calendar')

@push('styles')
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css' rel='stylesheet' />
@endpush

@push('scripts')
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
@endpush

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
            display: flex;
            flex-direction: column;
        }

        .calendar-day-cell:hover {
            background: #f8f9fa;
            z-index: 10;
        }

        /* Duration-based booking bars that span multiple days */
        .booking-bars-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 2px;
            margin-top: 4px;
            position: relative;
        }

        /* Multi-day booking bar */
        .booking-duration-bar {
            height: 28px;
            display: flex;
            align-items: center;
            padding: 0 6px;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: 500;
            color: white;
            cursor: pointer;
            position: relative;
            transition: all 0.15s ease;
            overflow: hidden;
            white-space: nowrap;
            min-width: 0;
            margin: 2px 0;
            border: 1px solid rgba(0, 0, 0, 0.1);
        }

        .booking-duration-bar:hover {
            filter: brightness(1.15);
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            z-index: 5;
        }

        .booking-duration-bar.unread {
            border: 2px solid currentColor;
            font-weight: 600;
        }

        /* Date type labels on bars */
        .bar-label {
            font-size: 0.6rem;
            font-weight: 700;
            text-transform: uppercase;
            padding: 2px 4px;
            border-radius: 2px;
            background: rgba(255,255,255,0.3);
            margin-right: 4px;
            letter-spacing: 0.3px;
            flex-shrink: 0;
        }

        .bar-label.pickup {
            background: rgba(255,255,255,0.4);
            margin-left: 0;
        }

        .bar-label.return {
            background: rgba(0,0,0,0.15);
            margin-right: 0;
            margin-left: 4px;
        }

        .bar-customer {
            overflow: hidden;
            text-overflow: ellipsis;
            flex: 1;
            min-width: 0;
            text-align: center;
            padding: 0 4px;
        }

        .bar-plate {
            font-size: 0.65rem;
            opacity: 0.9;
            margin-left: 4px;
            flex-shrink: 0;
        }

        /* Unread indicator on bar */
        .booking-duration-bar.unread::before {
            content: '';
            position: absolute;
            top: 3px;
            right: 3px;
            width: 7px;
            height: 7px;
            background: #fef08a;
            border-radius: 50%;
            box-shadow: 0 0 3px #fcd34d;
            z-index: 2;
        }

        .date-label {
            font-size: 0.6rem;
            font-weight: 700;
            text-transform: uppercase;
            padding: 1px 3px;
            border-radius: 2px;
            margin-top: 2px;
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

        /* Booking Detail Popup */
        .booking-detail-popup {
            position: fixed;
            background: white;
            border: 2px solid #dc2626;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            width: 320px;
            font-size: 0.9rem;
            font-family: inherit;
            animation: slideInUp 0.3s ease-out;
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .popup-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 12px 15px;
            background: #f8f9fa;
            border-bottom: 2px solid #dc2626;
            border-radius: 6px 6px 0 0;
        }

        .popup-close {
            background: none;
            border: none;
            font-size: 1.2rem;
            cursor: pointer;
            color: #666;
            padding: 0;
            margin-left: 10px;
        }

        .popup-close:hover {
            color: #dc2626;
        }

        .popup-body {
            padding: 12px 15px;
            max-height: 300px;
            overflow-y: auto;
        }

        .popup-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 0.85rem;
        }

        .popup-label {
            font-weight: 600;
            color: #666;
            min-width: 80px;
        }

        .popup-value {
            text-align: right;
            color: #333;
            flex: 1;
            margin-left: 10px;
            word-break: break-word;
        }

        .popup-footer {
            padding: 10px 15px;
            border-top: 1px solid #eee;
            text-align: center;
            border-radius: 0 0 6px 6px;
        }

        .popup-footer .btn {
            width: 100%;
        }

        /* FullCalendar Theme Styling */
        .fc-button-primary {
            background-color: #dc2626 !important;
            border-color: #dc2626 !important;
        }

        .fc-button-primary:hover {
            background-color: #b91c1c !important;
            border-color: #b91c1c !important;
        }

        .fc-button-primary:not(:disabled):active,
        .fc-button-primary:not(:disabled).fc-button-active {
            background-color: #991b1b !important;
            border-color: #991b1b !important;
        }

        .fc-button-primary:disabled {
            background-color: #fca5a5 !important;
            border-color: #fca5a5 !important;
        }

        .fc .fc-button-group > .fc-button.fc-button-primary.fc-button-active {
            background-color: #dc2626 !important;
            border-color: #dc2626 !important;
        }

        .fc .fc-col-header-cell {
            background-color: #f3f4f6;
            border-color: #e5e7eb;
        }

        .fc .fc-daygrid-day.fc-day-today {
            background-color: #fef2f2;
        }

        .fc .fc-highlight {
            background-color: #fee2e2;
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
            </h4>
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
                <!-- FullCalendar Container -->
                <div id="calendar"></div>

                @php
                    // Color palette for bookings
                    $colorPalette = [
                        '#3b82f6', '#ef4444', '#10b981', '#f59e0b', '#8b5cf6', 
                        '#ec4899', '#06b6d4', '#84cc16', '#f97316', '#6366f1',
                        '#14b8a6', '#a855f7', '#22c55e', '#eab308', '#0ea5e9',
                        '#d946ef', '#64748b', '#dc2626', '#059669', '#7c3aed'
                    ];

                    // Prepare booking events for FullCalendar
                    $calendarEvents = $bookings->map(function($booking) use ($colorPalette) {
                        // Generate consistent color based on customer + plate number
                        $customerName = $booking->customer && $booking->customer->user ? $booking->customer->user->name : 'Unknown';
                        $plateNumber = $booking->vehicle->plate_number ?? $booking->vehicle->plate_no ?? 'N/A';
                        $colorKey = md5($customerName . $plateNumber . $booking->bookingID);
                        $colorIndex = hexdec(substr($colorKey, 0, 8)) % count($colorPalette);
                        $backgroundColor = $colorPalette[$colorIndex];
                        
                        return [
                            'id' => (string)$booking->bookingID,
                            'title' => $plateNumber,
                            'start' => $booking->rental_start_date ? $booking->rental_start_date->format('Y-m-d') : null,
                            'end' => $booking->rental_end_date ? $booking->rental_end_date->addDay()->format('Y-m-d') : null,
                            'backgroundColor' => $backgroundColor,
                            'borderColor' => $backgroundColor,
                            'textColor' => '#fff',
                            'extendedProps' => [
                                'bookingId' => $booking->bookingID,
                                'customerId' => $customerName,
                                'plateNumber' => $plateNumber,
                                'vehicleModel' => $booking->vehicle->full_model ?? 'N/A',
                                'duration' => $booking->duration,
                                'status' => $booking->booking_status,
                                'pickupDate' => $booking->rental_start_date ? $booking->rental_start_date->format('Y-m-d H:i') : null,
                                'returnDate' => $booking->rental_end_date ? $booking->rental_end_date->format('Y-m-d H:i') : null
                            ]
                        ];
                    })->toArray();
                @endphp

                <!-- FullCalendar Script -->
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const calendarEl = document.getElementById('calendar');
                        const bookingEvents = @json($calendarEvents);
                        
                        const calendar = new FullCalendar.Calendar(calendarEl, {
                            initialView: 'dayGridMonth',
                            initialDate: '{{ $currentDate }}',
                            events: bookingEvents,
                            headerToolbar: {
                                left: 'prev,next today',
                                center: 'title',
                                right: 'dayGridMonth,dayGridWeek,dayGridDay'
                            },
                            editable: false,
                            selectable: true,
                            selectConstraint: 'businessHours',
                            selectOverlap: false,
                            eventClick: function(info) {
    info.jsEvent.preventDefault();
    info.jsEvent.stopPropagation();

    showBookingDetails(
        info.event.id,
        info.event.extendedProps,
        info.jsEvent,
        true
    );
}

                        });
                        
                        calendar.render();
                    });

                    // State tracking for popup locking
                    let lockedPopup = null;
                    let popupTimeouts = {};

                    document.addEventListener('click', function (e) {
    if (!lockedPopup) return;

    const popup = document.getElementById('booking-popup-' + lockedPopup);

    if (popup && !popup.contains(e.target)) {
        closeAndUnlockPopup(lockedPopup);
    }
});


                    function showBookingDetails(bookingId, props, event, isLocking = false) {
                        // Remove existing popup
                        const existingPopup = document.getElementById('booking-popup-' + bookingId);
                        if (existingPopup) {
                            existingPopup.remove();
                        }

                        // Clear any pending timeout for this popup
                        if (popupTimeouts[bookingId]) {
                            clearTimeout(popupTimeouts[bookingId]);
                            delete popupTimeouts[bookingId];
                        }

                        // Create popup element
                        const popup = document.createElement('div');
                        popup.id = 'booking-popup-' + bookingId;
                        popup.className = 'booking-detail-popup';
                        popup.style.zIndex = '10000';
                        popup.dataset.bookingId = bookingId;
                        
                        // Format dates
                        const pickupDate = new Date(props.pickupDate);
                        const returnDate = new Date(props.returnDate);
                        const pickupFormatted = pickupDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' });
                        const returnFormatted = returnDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' });

                        popup.innerHTML = `
                            <div class="popup-header">
                                <strong style="color: #dc2626; font-size: 1.1rem;">Booking #${props.bookingId}</strong>
                                <button type="button" class="popup-close" onclick="closeAndUnlockPopup(${bookingId})" title="Close">
                                    <i class="bi bi-x-lg"></i> ×
                                </button>
                            </div>
                            <div class="popup-body">
                                <div class="popup-row">
                                    <span class="popup-label">Customer:</span>
                                    <span class="popup-value">${props.customerId}</span>
                                </div>
                                <div class="popup-row">
                                    <span class="popup-label">Vehicle:</span>
                                    <span class="popup-value">${props.vehicleModel}</span>
                                </div>
                                <div class="popup-row">
                                    <span class="popup-label">Plate:</span>
                                    <span class="popup-value">${props.plateNumber}</span>
                                </div>
                                <div class="popup-row">
                                    <span class="popup-label">Duration:</span>
                                    <span class="popup-value">${props.duration} days</span>
                                </div>
                                <div class="popup-row">
                                    <span class="popup-label">Status:</span>
                                    <span class="popup-value"><span class="badge ${props.status === 'Confirmed' ? 'bg-success' : (props.status === 'Pending' ? 'bg-warning text-dark' : 'bg-secondary')}">${props.status}</span></span>
                                </div>
                                <div class="popup-row">
                                    <span class="popup-label">Pickup:</span>
                                    <span class="popup-value">${pickupFormatted}</span>
                                </div>
                                <div class="popup-row">
                                    <span class="popup-label">Return:</span>
                                    <span class="popup-value">${returnFormatted}</span>
                                </div>
                            </div>
                            <div class="popup-footer">
                                <button class="btn btn-sm btn-primary" onclick="goToBookingDetail(${props.bookingId})">
                                    <i class="bi bi-eye"></i> View Details
                                </button>
                            </div>
                        `;

                        document.body.appendChild(popup);
                        popup.addEventListener('click', function(e) {
    e.stopPropagation();
});


                        // Position popup near the event but ensure it stays inside screen area
                        if (event) {
                            const rect = event.target.getBoundingClientRect();
                            const popupRect = popup.getBoundingClientRect();
                            const viewportWidth = window.innerWidth;
                            const viewportHeight = window.innerHeight;
                            const margin = 15;
                            
                            // Calculate initial position (below the event)
                            let top = rect.bottom + 10;
                            let left = rect.left - 150;
                            
                            // Constrain horizontally to stay within viewport
                            if (left + popupRect.width > viewportWidth - margin) {
                                left = viewportWidth - popupRect.width - margin;
                            }
                            if (left < margin) {
                                left = margin;
                            }
                            
                            // Constrain vertically to stay within viewport
                            if (top + popupRect.height > viewportHeight - margin) {
                                // Try positioning above the event
                                const topAbove = rect.top - popupRect.height - 10;
                                if (topAbove >= margin) {
                                    top = topAbove;
                                } else {
                                    // If can't fit above, position at top with max-height
                                    top = margin;
                                    popup.style.maxHeight = (viewportHeight - margin * 2) + 'px';
                                    popup.querySelector('.popup-body').style.overflowY = 'auto';
                                }
                            }
                            
                            popup.style.position = 'fixed';
                            popup.style.top = top + 'px';
                            popup.style.left = left + 'px';
                        }

                        // When clicked, popup is locked and stays open until closed
                        // Always lock the popup since there's no hover
                        lockedPopup = bookingId;
                    }
                    function closeAndUnlockPopup(bookingId) {
    const popup = document.getElementById('booking-popup-' + bookingId);
    if (popup) {
        popup.remove();
    }
    lockedPopup = null;
}


                   
                </script>
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
       
            }
        }

        function hideBookingBox(bookingId) {
            hoveredBoxes[bookingId] = false;
            
            if (hideTimeouts[bookingId]) {
                clearTimeout(hideTimeouts[bookingId]);
            }
            
            hideTimeouts[bookingId] = setTimeout(() => {
                const box = document.getElementById('booking-box-' + bookingId);
                const isMouseOverBox = box && (box.matches(':hover') || box.querySelector(':hover'));
                
                if (box && !stickyBoxes[bookingId] && !hoveredBoxes[bookingId] && !isMouseOverBox) {
                    box.style.display = 'none';
                }
                delete hideTimeouts[bookingId];
            }, 200);
        }

        function keepBookingBoxOpen(bookingId) {
            hoveredBoxes[bookingId] = true;
            if (hideTimeouts[bookingId]) {
                clearTimeout(hideTimeouts[bookingId]);
                delete hideTimeouts[bookingId];
            }
        }

        function positionBookingBox(box, event) {
            if (!box) return;
            
            const viewportWidth = window.innerWidth;
            const viewportHeight = window.innerHeight;
            const margin = 15;
            
            // Get the booking bar element
            const bookingBar = document.querySelector(`.booking-duration-bar[data-booking-id="${box.dataset.bookingId}"]`);
            if (!bookingBar) return;
            
            const barRect = bookingBar.getBoundingClientRect();
            
            // Reset position
            box.style.top = 'auto';
            box.style.left = 'auto';
            box.style.right = 'auto';
            box.style.bottom = 'auto';
            box.style.maxHeight = '';
            box.style.overflowY = '';
            
            // Get box dimensions
            const boxRect = box.getBoundingClientRect();
            const boxWidth = boxRect.width;
            const boxHeight = boxRect.height;
            
            // Position below the bar
            let top = barRect.bottom + 8;
            let left = barRect.left;
            
            // Constrain horizontally
            if (left + boxWidth > viewportWidth - margin) {
                left = viewportWidth - boxWidth - margin;
            }
            if (left < margin) {
                left = margin;
            }
            
            // Constrain vertically
            if (top + boxHeight > viewportHeight - margin) {
                const topAbove = barRect.top - boxHeight - 8;
                if (topAbove >= margin) {
                    top = topAbove;
                } else {
                    top = margin;
                    box.style.maxHeight = (viewportHeight - margin * 2) + 'px';
                    box.style.overflowY = 'auto';
                }
            }
            
            box.style.top = top + 'px';
            box.style.left = left + 'px';
        }

        function toggleBookingBox(bookingId) {
            const box = document.getElementById('booking-box-' + bookingId);
            if (box) {
                if (stickyBoxes[bookingId]) {
                    box.classList.remove('sticky');
                    box.style.display = 'none';
                    delete stickyBoxes[bookingId];
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
                    stickyBoxes[bookingId] = true;
                    
                    const fakeEvent = { clientX: 0, clientY: 0 };
                    positionBookingBox(box, fakeEvent);
                }
            }
        }

        // Close floating boxes when clicking outside
        document.addEventListener('click', function(event) {
            if (!event.target.closest('.booking-duration-bar') && !event.target.closest('.booking-floating-box')) {
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

        function showReceipt(receiptUrl) {
            document.getElementById('receiptImage').src = receiptUrl;
            document.getElementById('receiptDownloadLink').href = receiptUrl;
            new bootstrap.Modal(document.getElementById('receiptModal')).show();
        }

        function closeBookingBox(bookingId) {
            const box = document.getElementById('booking-box-' + bookingId);
            if (box) {
                box.classList.remove('sticky');
                box.style.display = 'none';
                delete stickyBoxes[bookingId];
                delete hoveredBoxes[bookingId];
            }
        }

        function goToBookingDetail(bookingId) {
            window.location.href = `/admin/bookings/reservations/${bookingId}`;
        }
    </script>
@endsection















