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
                            $pickupCount = count($pickupsByDate[$dateKey] ?? []);
                            $returnCount = count($returnsByDate[$dateKey] ?? []);
                            $hasActivity = $pickupCount > 0 || $returnCount > 0;
                            $cellClass = 'calendar-day-cell';
                            if ($isOtherMonth) $cellClass .= ' other-month';
                            if ($isToday) $cellClass .= ' today';
                            if ($hasActivity) $cellClass .= ' has-bookings';
                        @endphp
                        <div class="{{ $cellClass }}" 
                             data-date="{{ $dateKey }}">
                            <div class="calendar-day-number">{{ $currentDay->format('j') }}</div>
                            @if($hasActivity)
                                <div class="calendar-booking-count">
                                    @if($pickupCount > 0)
                                        <span class="text-success"><i class="bi bi-arrow-up-circle"></i> {{ $pickupCount }} pickup{{ $pickupCount > 1 ? 's' : '' }}</span>
                                    @endif
                                    @if($pickupCount > 0 && $returnCount > 0)
                                        <br>
                                    @endif
                                    @if($returnCount > 0)
                                        <span class="text-danger"><i class="bi bi-arrow-down-circle"></i> {{ $returnCount }} return{{ $returnCount > 1 ? 's' : '' }}</span>
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
@endsection



