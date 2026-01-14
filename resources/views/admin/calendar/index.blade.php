@extends('layouts.admin')

@section('title', 'Booking Calendar')

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
            min-height: 100px;
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

        .calendar-booking-count {
            font-size: 0.7rem;
            color: #666;
            margin-top: 5px;
            line-height: 1.3;
        }

        .calendar-booking-count .text-success {
            color: #059669;
            font-weight: 600;
        }

        .calendar-booking-count .text-danger {
            color: #dc2626;
            font-weight: 600;
        }

        /* Background color for days with activity */
        .calendar-day-cell.has-bookings {
            background: #fee2e2;
        }

        .booking-details-popup {
            position: absolute;
            top: 100%;
            left: 0;
            background: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 10px;
            min-width: 250px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            z-index: 1000;
            display: none;
        }

        .calendar-day-cell:hover .booking-details-popup {
            display: block;
        }

        .booking-item {
            padding: 5px 0;
            border-bottom: 1px solid #eee;
            font-size: 0.85rem;
        }

        .booking-item:last-child {
            border-bottom: none;
        }

        /* Vehicle availability modal */
        .vehicle-availability-modal .modal-body {
            max-height: 70vh;
            overflow-y: auto;
        }

        .vehicle-list-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
            transition: background-color 0.2s;
        }

        .vehicle-list-item:hover {
            background-color: #f9fafb;
        }

        .vehicle-list-item:last-child {
            border-bottom: none;
        }

        .vehicle-info {
            flex: 1;
        }

        .vehicle-name {
            font-weight: 600;
            color: #111827;
            margin-bottom: 4px;
        }

        .vehicle-details {
            font-size: 0.875rem;
            color: #6b7280;
        }

        .vehicle-status {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-available {
            background-color: #d1fae5;
            color: #065f46;
        }

        .status-booked {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .status-maintenance {
            background-color: #fef3c7;
            color: #92400e;
        }
    </style>

    <div class="calendar-container">
        <div class="calendar-header">
            <h4 class="mb-0"><i class="bi bi-calendar3"></i> Booking Calendar</h4>
            <div class="calendar-controls">
                <div class="btn-group" role="group">
                    <a href="{{ route('admin.bookings.calendar', ['view' => 'month', 'vehicle_id' => $selectedVehicle, 'date' => $currentDate]) }}" 
                       class="btn btn-sm {{ $currentView === 'month' ? 'btn-danger' : 'btn-outline-danger' }}">
                        Month
                    </a>
                    <a href="{{ route('admin.bookings.calendar', ['view' => 'week', 'vehicle_id' => $selectedVehicle, 'date' => $currentDate]) }}" 
                       class="btn btn-sm {{ $currentView === 'week' ? 'btn-danger' : 'btn-outline-danger' }}">
                        Week
                    </a>
                    <a href="{{ route('admin.bookings.calendar', ['view' => 'day', 'vehicle_id' => $selectedVehicle, 'date' => $currentDate]) }}" 
                       class="btn btn-sm {{ $currentView === 'day' ? 'btn-danger' : 'btn-outline-danger' }}">
                        Day
                    </a>
                </div>
                <div class="btn-group ms-2" role="group">
                    <a href="{{ route('admin.bookings.calendar', ['view' => $currentView, 'vehicle_id' => $selectedVehicle, 'date' => \Carbon\Carbon::parse($currentDate)->subMonth()->format('Y-m-d')]) }}" 
                       class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                    <button class="btn btn-sm btn-outline-secondary" onclick="document.getElementById('datePicker').showPicker()">
                        {{ \Carbon\Carbon::parse($currentDate)->format('M Y') }}
                    </button>
                    <input type="month" id="datePicker" value="{{ \Carbon\Carbon::parse($currentDate)->format('Y-m') }}" 
                           style="display: none;" 
                           onchange="window.location.href='{{ route('admin.bookings.calendar', ['view' => $currentView, 'vehicle_id' => $selectedVehicle]) }}&date=' + this.value + '-01'">
                    <a href="{{ route('admin.bookings.calendar', ['view' => $currentView, 'vehicle_id' => $selectedVehicle, 'date' => \Carbon\Carbon::parse($currentDate)->addMonth()->format('Y-m-d')]) }}" 
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
                                    {{ $car->full_model }} ({{ $car->plate_number ?? $car->plate_no ?? 'N/A' }})
                                </option>
                            @endforeach
                        </optgroup>
                        <optgroup label="Motorcycles">
                            @foreach($motorcycles as $motorcycle)
                                <option value="motorcycle_{{ $motorcycle->id }}" {{ $selectedVehicle == 'motorcycle_' . $motorcycle->id ? 'selected' : '' }}>
                                    {{ $motorcycle->full_model }} ({{ $motorcycle->plate_number ?? $motorcycle->plate_no ?? 'N/A' }})
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
                            
                            // Calculate available and unavailable vehicles for this date
                            $targetDate = \Carbon\Carbon::parse($dateKey);
                            $bookingsOnDate = \App\Models\Booking::where('booking_status', '!=', 'Cancelled')
                                ->whereDate('rental_start_date', '<=', $targetDate)
                                ->whereDate('rental_end_date', '>=', $targetDate)
                                ->pluck('vehicleID')
                                ->toArray();
                            
                            $allVehicles = \App\Models\Vehicle::where('isActive', true)->get();
                            $availableCount = 0;
                            $unavailableCount = 0;
                            
                            foreach ($allVehicles as $vehicle) {
                                $isBooked = in_array($vehicle->vehicleID, $bookingsOnDate);
                                $isMaintenance = $vehicle->availability_status === 'maintenance';
                                if ($isBooked || $isMaintenance || !$vehicle->isActive) {
                                    $unavailableCount++;
                                } else {
                                    $availableCount++;
                                }
                            }
                            
                            $hasActivity = $availableCount > 0 || $unavailableCount > 0;
                            $cellClass = 'calendar-day-cell';
                            if ($isOtherMonth) $cellClass .= ' other-month';
                            if ($isToday) $cellClass .= ' today';
                            // Remove has-bookings class to keep cells white
                        @endphp
                        <div class="{{ $cellClass }}" 
                             data-date="{{ $dateKey }}"
                             onclick="showVehiclesForDate('{{ $dateKey }}')">
                            <div class="calendar-day-number">{{ $currentDay->format('j') }}</div>
                            @if($hasActivity)
                                <div class="calendar-booking-count">
                                    @if($availableCount > 0)
                                        <span class="text-success"><i class="bi bi-check-circle"></i> {{ $availableCount }} available</span>
                                    @endif
                                    @if($availableCount > 0 && $unavailableCount > 0)
                                        <br>
                                    @endif
                                    @if($unavailableCount > 0)
                                        <span class="text-danger"><i class="bi bi-x-circle"></i> {{ $unavailableCount }} unavailable</span>
                                    @endif
                                </div>
                                <div class="booking-details-popup">
                                    <strong>Bookings for {{ $currentDay->format('M d, Y') }}</strong>
                                    @foreach($dayBookings as $booking)
                                        <div class="booking-item">
                                            <div><strong>{{ $booking->customer && $booking->customer->user ? $booking->customer->user->name : 'Unknown Customer' }}</strong></div>
                                            <div>{{ $booking->vehicle->full_model ?? 'N/A' }}</div>
                                            <div class="text-muted small">
                                                @php
                                                    $start = $booking->start_date;
                                                    $end = $booking->end_date;
                                                @endphp
                                                @if($start && !empty($start))
                                                    @if($start instanceof \Carbon\Carbon)
                                                        {{ $start->format('M d') }}
                                                    @else
                                                        {{ \Carbon\Carbon::parse($start)->format('M d') }}
                                                    @endif
                                                @else
                                                    N/A
                                                @endif
                                                 - 
                                                @if($end && !empty($end))
                                                    @if($end instanceof \Carbon\Carbon)
                                                        {{ $end->format('M d, Y') }}
                                                    @else
                                                        {{ \Carbon\Carbon::parse($end)->format('M d, Y') }}
                                                    @endif
                                                @else
                                                    N/A
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        @php $currentDay->addDay(); @endphp
                    @endwhile
                </div>
            @elseif($currentView === 'week')
                <div class="alert alert-info">
                    Week view will be implemented here. Selected week: {{ \Carbon\Carbon::parse($currentDate)->startOfWeek()->format('M d') }} - {{ \Carbon\Carbon::parse($currentDate)->endOfWeek()->format('M d, Y') }}
                </div>
            @else
                <div class="alert alert-info">
                    Day view will be implemented here. Selected day: {{ \Carbon\Carbon::parse($currentDate)->format('M d, Y') }}
                </div>
            @endif
        </div>
    </div>

    <!-- Vehicle Availability Modal -->
    <div class="modal fade vehicle-availability-modal" id="vehicleAvailabilityModal" tabindex="-1" aria-labelledby="vehicleAvailabilityModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="vehicleAvailabilityModalLabel">
                        <i class="bi bi-calendar-check"></i> Vehicle Availability
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="selectedDateDisplay" class="mb-3">
                        <strong>Date: </strong><span id="modalDate"></span>
                    </div>
                    <div id="vehiclesList">
                        <div class="text-center py-4">
                            <div class="spinner-border text-danger" role="status">
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

    @push('scripts')
    <script>
        function showVehiclesForDate(date) {
            const modal = new bootstrap.Modal(document.getElementById('vehicleAvailabilityModal'));
            const modalDateElement = document.getElementById('modalDate');
            const vehiclesListElement = document.getElementById('vehiclesList');
            
            // Format date for display
            const dateObj = new Date(date + 'T00:00:00');
            const formattedDate = dateObj.toLocaleDateString('en-US', { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });
            modalDateElement.textContent = formattedDate;
            
            // Show loading
            vehiclesListElement.innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-danger" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `;
            
            // Show modal
            modal.show();
            
            // Fetch vehicles
            fetch(`{{ route('admin.bookings.calendar.vehicles') }}?date=${date}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    vehiclesListElement.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-circle"></i> ${data.error}
                        </div>
                    `;
                    return;
                }
                
                const vehicles = data.vehicles || [];
                
                if (vehicles.length === 0) {
                    vehiclesListElement.innerHTML = `
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> No vehicles found.
                        </div>
                    `;
                    return;
                }
                
                // Group vehicles by type
                const cars = vehicles.filter(v => v.vehicle_type === 'Car');
                const motorcycles = vehicles.filter(v => v.vehicle_type === 'Motorcycle');
                
                let html = '';
                
                // Render cars
                if (cars.length > 0) {
                    html += `<h6 class="mt-3 mb-2"><i class="bi bi-car-front"></i> Cars (${cars.length})</h6>`;
                    cars.forEach(vehicle => {
                        html += renderVehicleItem(vehicle);
                    });
                }
                
                // Render motorcycles
                if (motorcycles.length > 0) {
                    html += `<h6 class="mt-4 mb-2"><i class="bi bi-bicycle"></i> Motorcycles (${motorcycles.length})</h6>`;
                    motorcycles.forEach(vehicle => {
                        html += renderVehicleItem(vehicle);
                    });
                }
                
                vehiclesListElement.innerHTML = html;
            })
            .catch(error => {
                console.error('Error fetching vehicles:', error);
                vehiclesListElement.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-circle"></i> Error loading vehicles. Please try again.
                    </div>
                `;
            });
        }
        
        function renderVehicleItem(vehicle) {
            let statusClass = 'status-available';
            let statusText = 'Available';
            let statusIcon = '<i class="bi bi-check-circle"></i>';
            let currentStatus = 'available';
            
            if (vehicle.is_maintenance) {
                statusClass = 'status-maintenance';
                statusText = 'Maintenance';
                statusIcon = '<i class="bi bi-tools"></i>';
                currentStatus = 'maintenance';
            } else if (vehicle.is_booked) {
                statusClass = 'status-booked';
                statusText = 'Booked';
                statusIcon = '<i class="bi bi-x-circle"></i>';
                currentStatus = 'rented';
            } else if (vehicle.availability_status) {
                currentStatus = vehicle.availability_status;
            }
            
            // Determine available options based on current status
            let availableOptions = '';
            if (vehicle.is_booked) {
                // If booked, can only change to maintenance
                availableOptions = `
                    <option value="rented" selected>Booked</option>
                    <option value="maintenance">Maintenance</option>
                `;
            } else {
                // If not booked, can change between available and maintenance
                availableOptions = `
                    <option value="available" ${currentStatus === 'available' ? 'selected' : ''}>Available</option>
                    <option value="maintenance" ${currentStatus === 'maintenance' ? 'selected' : ''}>Maintenance</option>
                `;
            }
            
            return `
                <div class="vehicle-list-item d-flex justify-content-between align-items-center mb-2 p-2 border rounded">
                    <div class="vehicle-info flex-grow-1">
                        <div class="vehicle-name fw-semibold">${vehicle.full_model}</div>
                        <div class="vehicle-details small text-muted">
                            <i class="bi bi-upc"></i> ${vehicle.plate_number} | 
                            <i class="bi bi-tag"></i> ${vehicle.vehicle_type}
                        </div>
                    </div>
                    <div class="vehicle-status ms-3">
                        <select class="form-select form-select-sm vehicle-availability-select" 
                                data-vehicle-id="${vehicle.vehicleID}"
                                onchange="updateVehicleAvailability(this, ${vehicle.vehicleID})"
                                ${vehicle.is_booked ? 'disabled' : ''}>
                            ${availableOptions}
                        </select>
                    </div>
                </div>
            `;
        }
        
        function updateVehicleAvailability(select, vehicleId) {
            const newStatus = select.value;
            const originalValue = select.getAttribute('data-original-value') || select.value;
            
            // Store original value if not already stored
            if (!select.getAttribute('data-original-value')) {
                select.setAttribute('data-original-value', originalValue);
            }
            
            // Disable select during update
            select.disabled = true;
            
            fetch(`{{ route('admin.bookings.calendar.update-availability') }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    vehicle_id: vehicleId,
                    availability_status: newStatus
                })
            })
            .then(response => response.json())
            .then(data => {
                select.disabled = false;
                if (data.success) {
                    select.setAttribute('data-original-value', newStatus);
                    // Reload vehicles for the current date
                    const modal = bootstrap.Modal.getInstance(document.getElementById('vehicleAvailabilityModal'));
                    if (modal) {
                        const currentDate = document.getElementById('modalDate').getAttribute('data-date') || 
                                          document.querySelector('.calendar-day-cell.today')?.getAttribute('data-date');
                        if (currentDate) {
                            showVehiclesForDate(currentDate);
                        }
                    }
                } else {
                    select.value = select.getAttribute('data-original-value');
                    alert(data.message || 'Failed to update vehicle availability.');
                }
            })
            .catch(error => {
                select.disabled = false;
                select.value = select.getAttribute('data-original-value');
                console.error('Error:', error);
                alert('An error occurred while updating vehicle availability.');
            });
        }
    </script>
    @endpush
@endsection



